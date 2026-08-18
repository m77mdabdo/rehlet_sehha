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

    use LogsActivity;
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

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'phone', 'email', 'birth_date', 'gender'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('patient');
    }
}
