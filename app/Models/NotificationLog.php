<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\NotificationChannel;
use Database\Factories\NotificationLogFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int|null $appointment_id
 * @property NotificationChannel $channel
 * @property string $recipient
 * @property string $template
 * @property string $status
 * @property string|null $error
 * @property Carbon|null $sent_at
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Appointment|null $appointment
 *
 * @method static \Database\Factories\NotificationLogFactory factory($count = null, $state = [])
 */
class NotificationLog extends Model
{
    /** @use HasFactory<NotificationLogFactory> */
    use HasFactory;

    use Prunable;

    /** @var list<string> */
    protected $fillable = [
        'appointment_id',
        'channel',
        'recipient',
        'template',
        'status',
        'error',
        'sent_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'channel' => NotificationChannel::class,
            // The patient's email address or phone number. Encrypted because
            // this table would otherwise accumulate a plaintext directory of
            // every patient contact detail. Safe to encrypt precisely because
            // nothing queries by it — lookups go through appointment_id.
            'recipient' => 'encrypted',
            'sent_at' => 'datetime',
        ];
    }

    /**
     * Rows older than the retention window are deleted by `model:prune`, which
     * runs daily from the scheduler.
     *
     * A delivery log is operational data: useful for a few weeks to answer
     * "did the reminder actually go out?", and pure liability after that, since
     * every row ties a contact detail to an appointment. Ninety days is the
     * default; override with CLINIC_NOTIFICATION_LOG_RETENTION_DAYS.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        $days = (int) config('clinic.notification_log_retention_days', 90);

        return static::query()->where('created_at', '<=', Carbon::now()->subDays($days));
    }

    /**
     * @return BelongsTo<Appointment, $this>
     */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }
}
