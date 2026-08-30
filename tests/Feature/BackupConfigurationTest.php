<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Spatie\Backup\Notifications\Notifications\BackupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\BackupWasSuccessfulNotification;
use Spatie\Backup\Notifications\Notifications\CleanupHasFailedNotification;
use Spatie\Backup\Notifications\Notifications\HealthyBackupWasFoundNotification;
use Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFoundNotification;
use Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays;

/**
 * THE BACKUP DECISIONS, WRITTEN DOWN SOMEWHERE THAT BREAKS.
 *
 * config/backup.php is a 400-line vendor file that is republished on upgrade
 * and skimmed on review. Every choice asserted here was made for a reason
 * recorded beside it, and every one of them fails silently if reverted: a
 * whole-directory backup fills the hosting quota, a nightly success email gets
 * filtered along with the failure email, an unscheduled command never runs.
 *
 * Nothing here checks that a backup CAN be taken — that is a live-server
 * question, and section C of docs/deployment/hostinger.md is where it is
 * answered by actually taking one.
 */

/**
 * @return list<string>
 */
function scheduledCommands(): array
{
    return array_map(
        fn ($event): string => (string) $event->command,
        app(Schedule::class)->events()
    );
}

/*
|------------------------------------------------------------------------------
| What gets backed up
|------------------------------------------------------------------------------
*/

it('backs up the database and nothing else', function (): void {
    /*
     * The code is in git and the served photography with it. Archiving the
     * application directory nightly on shared hosting would take minutes, eat
     * the disk quota, and produce files nobody would ever restore from.
     */
    expect(config('backup.backup.source.files.include'))->toBe([]);
});

it('runs the dump on a schedule, with the cleanup before it', function (): void {
    $commands = scheduledCommands();

    $dump = collect($commands)->first(fn (string $c): bool => str_contains($c, 'backup:run'));
    $clean = collect($commands)->first(fn (string $c): bool => str_contains($c, 'backup:clean'));

    expect($dump)->not->toBeNull('Nothing schedules backup:run. The dump would only ever be taken by hand.');
    expect($clean)->not->toBeNull('Nothing schedules backup:clean. Old archives would accumulate until the disk filled.');

    /*
     * --only-db on the command as well as an empty include list in the config.
     * Belt and braces: either one alone would keep the archive small, and both
     * together mean a future edit to one of them cannot quietly start
     * archiving the whole account.
     */
    expect(str_contains($dump, '--only-db'))->toBeTrue("backup:run is scheduled without --only-db: «{$dump}»");
});

/*
|------------------------------------------------------------------------------
| How long it is kept, and how much room it may take
|------------------------------------------------------------------------------
*/

it('keeps a week of everything and thins out after that', function (): void {
    /*
     * Seven days covers the common case — somebody deletes the wrong record on
     * Friday and nobody notices until Monday. The thinned months cover the
     * slower one: a data problem introduced weeks ago and only visible now.
     */
    expect(config('backup.cleanup.default_strategy.keep_all_backups_for_days'))->toBeGreaterThanOrEqual(7);
    expect(config('backup.cleanup.default_strategy.keep_monthly_backups_for_months'))->toBeGreaterThanOrEqual(3);
});

it('cannot fill the hosting account', function (): void {
    /*
     * The failure this bounds is not lost backups, it is a full disk — at
     * which point new intake forms stop saving and the site starts throwing.
     * A database dump for this practice is measured in hundreds of kilobytes,
     * so 500 MB is headroom the retention policy above cannot reach.
     */
    $cap = config('backup.cleanup.default_strategy.delete_oldest_backups_when_using_more_megabytes_than');

    expect($cap)->toBeLessThanOrEqual(500);
    expect($cap)->toBeGreaterThan(0);
});

/*
|------------------------------------------------------------------------------
| Who hears about it
|------------------------------------------------------------------------------
*/

it('sends only the bad news', function (): void {
    /*
     * A nightly "backup succeeded" email to a clinic inbox is filtered into a
     * folder within a fortnight — and once it is filtered, the failure email,
     * same sender and same shape of subject, is filtered with it. Turning the
     * success notifications back on is how you lose the failure notification.
     */
    $notifications = config('backup.notifications.notifications');

    foreach ([BackupHasFailedNotification::class, UnhealthyBackupWasFoundNotification::class, CleanupHasFailedNotification::class] as $failure) {
        expect($notifications[$failure] ?? [])
            ->not->toBeEmpty(class_basename($failure).' goes nowhere. A failing backup would be silent.');
    }

    foreach ([BackupWasSuccessfulNotification::class, HealthyBackupWasFoundNotification::class] as $success) {
        expect($notifications[$success] ?? [])
            ->toBeEmpty(class_basename($success).' is on again. See the note in this test.');
    }
});

it('does not send the alerts to the package placeholder', function (): void {
    $to = config('backup.notifications.mail.to');

    expect($to)->not->toBe('your@example.com');
    expect($to)->toContain('@');
});

/*
|------------------------------------------------------------------------------
| Noticing that it stopped
|------------------------------------------------------------------------------
*/

it('treats a backup older than a day as unhealthy', function (): void {
    /*
     * The schedule is nightly, so anything past a day means a night was
     * missed. Two days here would mean a cron that stopped on Monday is first
     * complained about on Wednesday.
     */
    $checks = config('backup.monitor_backups.0.health_checks');

    expect($checks[MaximumAgeInDays::class] ?? null)->toBe(1);
});

it('watches the scheduler itself', function (): void {
    /*
     * Everything above is worthless if cron is dead, because then nothing runs
     * — not the dump, not the monitor, not the notification about the monitor.
     * The heartbeat is the one thing that can tell the difference between a
     * quiet night and a stopped scheduler. See App\Support\Heartbeat.
     */
    $callbacks = collect(app(Schedule::class)->events())
        ->map(fn ($event): string => (string) $event->description)
        ->filter(fn (string $description): bool => str_contains($description, 'heartbeat'));

    expect($callbacks)->not->toBeEmpty('Nothing schedules the heartbeat; /up could not detect a dead cron.');
});
