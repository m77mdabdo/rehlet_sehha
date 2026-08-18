<?php

use App\Models\NotificationLog;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
|
| On Hostinger the scheduler is driven by a single cron entry:
|
|     * * * * * cd /home/USER/domains/DOMAIN/public_html \
|         && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
|
*/

/*
 * Daily canary on APP_KEY.
 *
 * If the key ever changes, every encrypted clinical field becomes permanently
 * unreadable and NOTHING else in the application will complain — reads return
 * a DecryptException only when someone happens to open an affected record,
 * which could be weeks later. This turns a silent catastrophe into a failed
 * scheduled task on the day it happens, while a key backup is still findable.
 *
 * This is a monitor, not a safety net. The real protection is running
 * `php artisan clinic:verify-key` as the FIRST step of any deploy script, so a
 * bad deploy aborts before it touches the database. See docs/deployment/APP_KEY.md.
 */
Schedule::command('clinic:verify-key')
    ->dailyAt('03:00')
    ->timezone(config('clinic.timezone'))
    ->onFailure(function (): void {
        logger()->critical('clinic:verify-key failed — APP_KEY no longer matches the stored fingerprint.');
    });

/*
 * Delete notification logs past their retention window. Each row ties a patient
 * contact detail to an appointment, so they are pruned rather than kept
 * forever. See config('clinic.notification_log_retention_days').
 */
Schedule::command('model:prune', ['--model' => [NotificationLog::class]])
    ->dailyAt('03:30')
    ->timezone(config('clinic.timezone'));
