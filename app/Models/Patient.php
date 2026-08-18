<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Gender;
use Database\Factories\PatientFactory;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property string $name
 * @property string $phone
 * @property string|null $email
 * @property Carbon|null $birth_date
 * @property Gender|null $gender
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Collection<int, Appointment> $appointments
 * @property-read Collection<int, IntakeForm> $intakeForms
 *
 * @method static \Database\Factories\PatientFactory factory($count = null, $state = [])
 */
class Patient extends Model
{
    /** @use HasFactory<PatientFactory> */
    use HasFactory;

    use LogsActivity {
        buildChanges as private buildLoggedChanges;
        shouldSkipEmptyLog as private shouldSkipEmptyLoggedChanges;
    }
    use SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'phone',
        'email',
        'birth_date',
        'gender',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'gender' => Gender::class,
        ];
    }

    /**
     * Resolve the patient behind a phone number, creating or reviving the file
     * as needed. This is the only supported way to turn a booking form into a
     * Patient row.
     *
     * Three branches:
     *   - a live patient exists   -> return it, filling in only attributes it
     *                                is currently missing. A booking form must
     *                                never quietly overwrite a name or email
     *                                the clinic has already corrected by hand.
     *   - a soft-deleted patient  -> restore it and apply the new attributes.
     *                                The returning patient gets their history
     *                                back instead of a blank second file.
     *   - nothing exists          -> create it.
     *
     * Wrapped in a transaction with lockForUpdate(). phone carries a unique
     * index, so SELECT ... FOR UPDATE takes a lock (a gap lock when the row
     * does not yet exist) that makes a second concurrent booking for the same
     * number wait rather than race. Without it, two simultaneous requests can
     * both read "no such patient" and both attempt the insert; one would then
     * die on the unique index mid-booking. The index remains the last-resort
     * backstop — this lock is what stops us ever reaching it.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function findOrCreateByPhone(string $phone, array $attributes = []): self
    {
        $attributes = Arr::only($attributes, (new self)->getFillable());
        unset($attributes['phone']);

        return DB::transaction(function () use ($phone, $attributes): self {
            /** @var self|null $patient */
            $patient = self::withTrashed()
                ->where('phone', $phone)
                ->lockForUpdate()
                ->first();

            if ($patient === null) {
                return self::create($attributes + ['phone' => $phone]);
            }

            if ($patient->trashed()) {
                $patient->restore();
                $patient->fill($attributes);
                $patient->save();

                return $patient;
            }

            foreach ($attributes as $key => $value) {
                if ($value !== null && $patient->getAttribute($key) === null) {
                    $patient->setAttribute($key, $value);
                }
            }

            if ($patient->isDirty()) {
                $patient->save();
            }

            return $patient;
        });
    }

    /**
     * @return HasMany<Appointment, $this>
     */
    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * @return HasManyThrough<IntakeForm, Appointment, $this>
     */
    public function intakeForms(): HasManyThrough
    {
        return $this->hasManyThrough(IntakeForm::class, Appointment::class);
    }

    /**
     * Attributes whose CHANGE is worth auditing but whose VALUE must never
     * reach the audit trail.
     *
     * A phone number and an email address identify a person on their own, and
     * a birth date is quasi-identifying. Writing their before/after values into
     * activity_log would rebuild, row by row, the very contact history we
     * encrypt elsewhere — and activity_log is plaintext JSON that lands in
     * every backup and every mysqldump.
     *
     * `notes` is here for the opposite reason: not because it identifies the
     * patient, but because it is free text a clinician writes ABOUT them. Its
     * content is clinical and must stay out of the trail, while the fact that
     * someone edited it is exactly the sort of thing an audit trail exists to
     * capture — a note quietly rewritten after the fact should leave a mark.
     *
     * @var list<string>
     */
    public const CONFIDENTIAL_ATTRIBUTES = ['phone', 'email', 'birth_date', 'notes'];

    /**
     * What the audit trail records for a patient.
     *
     * Only `name` is logged with its values: staff genuinely need to see that
     * "أ. راوية غانم" became "راوية غانم أحمد", otherwise a rename looks like a
     * different person. Everything in CONFIDENTIAL_ATTRIBUTES is excluded here,
     * so those values are never collected in the first place — the redaction is
     * "do not gather", not "gather then strip", which cannot leak through a
     * path we forgot to scrub.
     *
     * `gender` is dropped from the log entirely: it is clinical data with no
     * accountability value in an audit trail, and logging it would put a health
     * attribute in plaintext next to the name.
     *
     * That a confidential field changed is still recorded — see buildChanges().
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('patient');
    }

    /**
     * Record THAT a confidential attribute changed, without recording what it
     * changed from or to.
     *
     * The result of this method becomes activity_log.attribute_changes, so a
     * patient whose phone and email were corrected produces:
     *
     *     {"redacted": ["phone", "email"]}
     *
     * which answers "was this record touched, when, and by whom" — the whole
     * point of an audit trail — while answering nothing about the values.
     *
     * @return array<string, mixed>
     */
    protected function buildChanges(string $processingEvent): array
    {
        $changes = $this->buildLoggedChanges($processingEvent);

        $redacted = $this->confidentialAttributesTouchedBy($processingEvent);

        if ($redacted !== []) {
            $changes['redacted'] = $redacted;
        }

        return $changes;
    }

    /**
     * Keep an entry that carries only a redaction list.
     *
     * dontLogEmptyChanges() would otherwise discard a phone-only update as
     * "nothing logged", because `name` did not change — losing the audit record
     * of a real edit. A change to something we deliberately do not log is still
     * a change worth knowing about.
     *
     * @param  array<string, mixed>  $changes
     */
    protected function shouldSkipEmptyLog(array $changes): bool
    {
        if (! empty($changes['redacted'] ?? [])) {
            return false;
        }

        return $this->shouldSkipEmptyLoggedChanges($changes);
    }

    /**
     * Which confidential attributes this event touched, by name only.
     *
     * @return list<string>
     */
    protected function confidentialAttributesTouchedBy(string $processingEvent): array
    {
        $touched = match ($processingEvent) {
            // On create, "changed" means "arrived with a value". A patient
            // booked without an email should not be reported as having set one.
            'created' => array_filter(
                self::CONFIDENTIAL_ATTRIBUTES,
                fn (string $attribute): bool => $this->getAttribute($attribute) !== null,
            ),
            'updated' => array_filter(
                self::CONFIDENTIAL_ATTRIBUTES,
                fn (string $attribute): bool => array_key_exists($attribute, $this->getChanges()),
            ),
            // Deleting or restoring a patient changes no contact detail.
            default => [],
        };

        return array_values($touched);
    }
}
