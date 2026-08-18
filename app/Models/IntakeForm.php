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

    use LogsActivity;

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
        ];
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            // Only non-clinical metadata is logged. Logging the encrypted
            // fields would write the decrypted health data straight into
            // activity_log.properties as plaintext JSON, defeating the casts.
            ->logOnly(['goal', 'consent_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName('intake_form');
    }
}
