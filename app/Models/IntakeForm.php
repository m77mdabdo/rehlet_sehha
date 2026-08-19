<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\IntakeFormFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * @property int $id
 * @property int $appointment_id
 * @property string|null $goal
 * @property string|null $medications
 * @property string|null $conditions
 * @property string|null $avoid_foods
 * @property string|null $note
 * @property Carbon|null $consent_at
 * @property string|null $consent_ip
 * @property Carbon|null $erased_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Appointment $appointment
 *
 * @method static \Database\Factories\IntakeFormFactory factory($count = null, $state = [])
 */
class IntakeForm extends Model
{
    /** @use HasFactory<IntakeFormFactory> */
    use HasFactory;

    use LogsActivity {
        buildChanges as private buildLoggedChanges;
        shouldSkipEmptyLog as private shouldSkipEmptyLoggedChanges;
    }

    /** @var list<string> */
    protected $fillable = [
        'appointment_id',
        'goal',
        'medications',
        'conditions',
        'avoid_foods',
        'note',
        'consent_at',
        'consent_ip',
        'erased_at',
    ];

    /**
     * Attributes whose CHANGE is worth auditing but whose VALUE must never
     * reach the audit trail.
     *
     * The four clinical fields are obvious: they are encrypted at rest
     * precisely so health data never sits in plaintext, and writing their
     * before/after values into activity_log — which is plaintext JSON in every
     * backup and every mysqldump — would undo that completely.
     *
     * `goal` is here too, and it did not used to be. It is stored unencrypted
     * because the clinic counts on it, but "حالة مرضية" or "حمل أو رضاعة"
     * attached to an identifiable patient is a health attribute, and Patient
     * already drops `gender` from its log for exactly that reason. A category
     * being coarse does not make it non-clinical.
     *
     * What remains auditable is the fact that an intake was created or
     * corrected, and when consent was given. That is what an audit trail is
     * for: accountability, not content.
     *
     * @var list<string>
     */
    public const CONFIDENTIAL_ATTRIBUTES = [
        'goal',
        'medications',
        'conditions',
        'avoid_foods',
        'note',
    ];

    /**
     * The four clinical fields are encrypted at the application layer: the
     * database stores only Laravel's ciphertext envelope, so health data never
     * appears in plaintext in a backup, a replica or a slow-query log.
     *
     * Consequence to keep in mind: these columns cannot be searched or sorted
     * in SQL. Any filtering on them has to happen in PHP after decryption.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'medications' => 'encrypted',
            'conditions' => 'encrypted',
            'avoid_foods' => 'encrypted',
            'note' => 'encrypted',
            'consent_at' => 'datetime',
            'erased_at' => 'datetime',
        ];
    }

    /**
     * Whether the clinical content has been erased at the patient's request.
     */
    public function isErased(): bool
    {
        return $this->erased_at !== null;
    }

    /**
     * Whether the patient may still correct what they wrote.
     *
     * Closes once the appointment has started. A clinical record consulted
     * during a consultation must not change afterwards: the clinician read
     * one thing, and a note edited that evening would leave the record
     * disagreeing with the decision that was made from it. That is not a
     * privacy protection, it is the opposite — it protects the patient from a
     * record that cannot be reconciled with their own care.
     *
     * Erasure is deliberately NOT bound by this. The right to have clinical
     * content removed does not expire when the appointment starts; only the
     * right to rewrite it does.
     */
    public function isCorrectable(): bool
    {
        if ($this->isErased()) {
            return false;
        }

        return $this->appointment->starts_at->isFuture();
    }

    /**
     * Remove the clinical content, keep the booking.
     *
     * Nulls the five fields the patient wrote about their health and stamps
     * erased_at. Everything the clinic needs to run and to bill — the
     * appointment, its time, the patient's name and phone — is untouched,
     * because erasing those would destroy the clinic's records rather than
     * the patient's narrative.
     *
     * consent_at and consent_ip also survive. They are the evidence that
     * consent was properly taken, and destroying that on request would leave
     * the clinic unable to show it ever had permission for data it has since
     * deleted.
     */
    public function eraseClinicalContent(): bool
    {
        foreach (self::CONFIDENTIAL_ATTRIBUTES as $attribute) {
            $this->setAttribute($attribute, null);
        }

        $this->erased_at = Carbon::now();

        return $this->save();
    }

    /**
     * Everything the patient wrote, for showing back to them.
     *
     * Decrypted by the casts. Only ever rendered on the token-authenticated
     * self-service page: they wrote it, so they can read it.
     *
     * @return array<string, string|null>
     */
    public function clinicalContent(): array
    {
        return [
            'goal' => $this->goal,
            'medications' => $this->medications,
            'conditions' => $this->conditions,
            'avoid_foods' => $this->avoid_foods,
            'note' => $this->note,
        ];
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * What the audit trail records for an intake form.
     *
     * Only consent_at and erased_at carry their values — both are timestamps
     * about the patient's own decisions, and both are exactly what someone
     * would need to answer "was consent given, and when was this erased".
     *
     * Everything in CONFIDENTIAL_ATTRIBUTES is excluded here, so those values
     * are never collected in the first place. The redaction is "do not
     * gather", not "gather then strip" — which cannot leak through a path
     * somebody forgot to scrub.
     *
     * That a confidential field changed is still recorded; see buildChanges().
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['consent_at', 'erased_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('intake_form');
    }

    /**
     * Record THAT clinical content was written or changed, never what it says.
     *
     * A patient who corrects her medication list produces:
     *
     *     {"redacted": ["medications"]}
     *
     * which answers "was this record touched, when, and by whom" — the whole
     * point of an audit trail — while answering nothing about her health.
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
     * dontLogEmptyChanges() would otherwise discard a correction to the
     * medication list as "nothing logged", because consent_at did not move —
     * losing the record of a real edit to a clinical document. A change to
     * something we deliberately do not log is still a change worth knowing
     * about.
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
            // On create, "changed" means "arrived with a value". An intake
            // submitted with no medications should not report setting them.
            'created' => array_filter(
                self::CONFIDENTIAL_ATTRIBUTES,
                fn (string $attribute): bool => $this->getAttribute($attribute) !== null,
            ),
            'updated' => array_filter(
                self::CONFIDENTIAL_ATTRIBUTES,
                fn (string $attribute): bool => array_key_exists($attribute, $this->getChanges()),
            ),
            default => [],
        };

        return array_values($touched);
    }
}
