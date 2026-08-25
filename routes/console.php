<?php

use App\Models\ActivityLog;
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
 * Delete log rows past their retention window.
 *
 * notification_logs: 90 days. Each row ties a patient contact detail to an
 * appointment, useful for a few weeks and pure liability after that.
 *
 * activity_log: 365 days. An audit trail earns longer because it answers who
 * changed a patient record and when — a question that can arrive months later.
 * Not forever, though: even with contact values redacted it accumulates a
 * timeline of every patient interaction.
 *
 * See config('clinic.notification_log_retention_days') and
 * config('clinic.activity_log_retention_days').
 */
Schedule::command('model:prune', ['--model' => [NotificationLog::class, ActivityLog::class]])
    ->dailyAt('03:30')
    ->timezone(config('clinic.timezone'));

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/

/*
 * Drain the queue.
 *
 * --stop-when-empty, NOT a daemon. A persistent `queue:work` process on
 * Hostinger shared hosting is a long-running background process, which their
 * terms forbid and their process reaper kills — repeatedly starting one is a
 * good way to have the account suspended. So the worker is started by cron,
 * works until the queue is empty, and exits.
 *
 * --max-time=50 bounds it well inside the minute so two workers never overlap:
 * the next cron tick fires at 60 seconds, and a worker still running then would
 * mean two processes competing for the same jobs. Ten seconds of headroom
 * covers a slow SMTP handshake on the last job.
 *
 * withoutOverlapping() is belt and braces on top of that, in case a job hangs
 * past --max-time.
 *
 * Every notification is queued (see App\Notifications\AppointmentNotification),
 * so if this entry is missing NOTHING is delivered — and the delivery log will
 * show every row stuck at `queued`, which is exactly the symptom to look for.
 */
Schedule::command('queue:work --stop-when-empty --max-time=50 --tries=3')
    ->everyMinute()
    ->withoutOverlapping()
    ->runInBackground();

/*
 * Appointment reminders, 24 hours and 1 hour ahead.
 *
 * Every minute, because a reminder window is a moment rather than a slot: an
 * hourly sweep would send the "in an hour" reminder anywhere from 60 to 0
 * minutes ahead. Sending is idempotent by database claim rather than by
 * timing, so running this often is cheap and safe — see the command.
 */
Schedule::command('clinic:send-reminders')
    ->everyMinute()
    ->withoutOverlapping();

/*
 * The day's appointments, to the clinic, at 07:00 Cairo.
 *
 * The timezone is explicit. Without it this would fire at 07:00 UTC — 09:00 or
 * 10:00 in Cairo depending on daylight saving — which is after the first
 * appointment of the day has already started.
 */
/*
 * Review invitations, once a day.
 *
 * Not every minute like the reminders: the trigger is "three days have
 * passed", which does not need minute precision, and a patient receiving this
 * at 10am reads better than one receiving it at 03:14.
 */
Schedule::command('clinic:send-review-requests')
    ->dailyAt('10:00')
    ->timezone(config('clinic.timezone'))
    ->withoutOverlapping();

Schedule::command('clinic:send-daily-schedule')
    ->dailyAt('07:00')
    ->timezone(config('clinic.timezone'));
