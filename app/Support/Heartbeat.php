<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;

/**
 * PROOF THAT CRON IS STILL RUNNING.
 *
 * On shared hosting the scheduler is one cron entry in a control panel that
 * nobody looks at. When it stops — the account is migrated, the panel is
 * edited, PHP's path changes on an upgrade — nothing throws. The site keeps
 * serving pages perfectly while reminders stop going out, review invitations
 * stop, the daily schedule stops arriving, and the nightly dump stops being
 * written. The first symptom is a patient who was never reminded.
 *
 * So the scheduler writes a timestamp every five minutes, and the health route
 * reads it. A stale heartbeat is the difference between finding out today and
 * finding out when somebody misses an appointment.
 *
 * A FILE, NOT THE CACHE. The whole point is to survive the failures we are
 * watching for: `cache:clear` during a deploy would wipe a cache-backed
 * heartbeat and report a dead cron on a healthy site, and a cache driver that
 * is itself broken would do the same. The file needs nothing but a writable
 * storage directory — which the health route checks separately anyway.
 */
final class Heartbeat
{
    /**
     * How old the heartbeat may be before the site is considered unattended.
     *
     * The scheduler writes every five minutes, so fifteen tolerates two missed
     * ticks — a slow minute, a host hiccup — without crying wolf, and still
     * catches a genuinely stopped cron within a quarter of an hour.
     */
    public const STALE_AFTER_MINUTES = 15;

    public static function path(): string
    {
        return storage_path('app/private/scheduler-heartbeat');
    }

    public static function record(): void
    {
        $path = self::path();

        if (! is_dir(dirname($path))) {
            mkdir(dirname($path), 0755, true);
        }

        // The mtime is the signal; the contents are for a human reading the
        // file over SSH while wondering whether cron is alive.
        file_put_contents($path, Carbon::now()->toIso8601String().PHP_EOL);
    }

    /**
     * When the scheduler last ran, or null if it has never run on this host.
     */
    public static function lastBeatAt(): ?Carbon
    {
        $path = self::path();

        if (! is_file($path)) {
            return null;
        }

        $mtime = @filemtime($path);

        return $mtime === false ? null : Carbon::createFromTimestamp($mtime);
    }

    public static function isFresh(): bool
    {
        $beat = self::lastBeatAt();

        return $beat !== null && $beat->gt(Carbon::now()->subMinutes(self::STALE_AFTER_MINUTES));
    }
}
