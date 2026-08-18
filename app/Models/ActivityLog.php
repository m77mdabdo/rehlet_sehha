<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * The clinic's audit trail.
 *
 * This extends Spatie's Activity model for one reason: to put the audit trail
 * on a retention clock. config('activitylog.activity_model') points here, so
 * every entry the package writes is an instance of this class.
 *
 * @property int $id
 * @property string|null $log_name
 * @property string $description
 * @property string|null $subject_type
 * @property int|null $subject_id
 * @property string|null $causer_type
 * @property int|null $causer_id
 * @property string|null $event
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class ActivityLog extends Activity
{
    use Prunable;

    /**
     * Entries older than the retention window are deleted by `model:prune`,
     * which runs daily from the scheduler.
     *
     * A year, deliberately longer than the ninety days we keep notification
     * logs for. An audit trail earns the extra time because it serves a real
     * accountability purpose: it answers who changed a patient record, when,
     * and what it said before — which matters when a clinical decision is
     * questioned months later, and which a delivery log never does.
     *
     * But not forever. Even redacted, this table accumulates a timeline of
     * every patient interaction, and data nobody has a use for is data that can
     * only be lost or subpoenaed. Override with
     * CLINIC_ACTIVITY_LOG_RETENTION_DAYS.
     *
     * @return Builder<static>
     */
    public function prunable(): Builder
    {
        $days = (int) config('clinic.activity_log_retention_days', 365);

        return static::query()->where('created_at', '<=', Carbon::now()->subDays($days));
    }
}
