<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Throwable;

/**
 * What commit is deployed, and what commit was last built successfully.
 *
 * Those are two different things on this host, and the gap between them is the
 * thing that broke the admin panel on 2026-10-12.
 *
 * hPanel's git deployment fires on a push and runs unattended: it clones the
 * repository, checks out a detached HEAD, copies the tree across and dumps the
 * autoloader. It does NOT rebuild the route or config caches. So from the
 * instant somebody pushes, production is running new code against the previous
 * deploy's caches — and a push that adds a route or a discovered Filament page
 * is a 500 on every authenticated panel page until somebody notices.
 *
 * WHY A FILE AND NOT THE DATABASE OR THE CACHE.
 *
 * This is read by the health endpoint and written by a command that runs
 * `config:cache` and `composer install` in the middle of its work. The cache is
 * out because those commands clear it; the settings table is out because the
 * rebuild should not depend on the database being reachable to record that it
 * failed. A small JSON file under storage survives everything the rebuild does
 * to the application around it.
 *
 * WHY .git IS READ DIRECTLY RATHER THAN SHELLING OUT TO git.
 *
 * currentCommit() is called on every /up request. Spawning `git rev-parse`
 * for a health check is a process per request, and /up is the thing a monitor
 * hits every minute. Reading two small files is free.
 */
final class DeploymentState
{
    /**
     * How long a commit must sit unchanged before the watcher will act on it.
     *
     * THE MID-COPY GUARD. The deploy clones first and copies afterwards, so
     * HEAD is already pointing at the new commit while the working tree is
     * still being written. Rebuilding caches against a half-copied tree would
     * be worse than the stale caches it is fixing.
     *
     * Measured on the host: clone at 18:35:14, autoloader dumped at 18:35:24 —
     * about ten seconds. Fifty seconds is comfortably past that and still
     * inside a one-minute schedule, so a deploy is picked up on the cycle
     * after the one that first saw it.
     *
     * This is deliberately NOT keyed off a marker the deploy writes, such as
     * bootstrap/cache/packages.php changing. That would mean depending on an
     * undocumented detail of somebody else's deployment tool, which can change
     * without warning and would fail silently when it did. A commit that has
     * not moved for a full cycle is self-evident and depends on nothing.
     */
    public const STABLE_AFTER_SECONDS = 50;

    private const FILE = 'app/private/deployment-state.json';

    /**
     * The commit the working tree is actually on.
     *
     * Returns null when there is no repository — a tarball deploy, or a test
     * environment — which callers must treat as "cannot tell", never as
     * "out of date".
     */
    public static function currentCommit(): ?string
    {
        try {
            $head = base_path('.git/HEAD');

            if (! File::exists($head)) {
                return null;
            }

            $contents = trim(File::get($head));

            // Detached HEAD, which is what this host's deploy always leaves.
            if (preg_match('/^[0-9a-f]{40}$/i', $contents) === 1) {
                return strtolower($contents);
            }

            // On a branch: follow the ref, loose first then packed.
            if (preg_match('#^ref:\s*(\S+)$#', $contents, $match) === 1) {
                $loose = base_path('.git/'.$match[1]);

                if (File::exists($loose)) {
                    return strtolower(trim(File::get($loose)));
                }

                $packed = base_path('.git/packed-refs');

                if (File::exists($packed)
                    && preg_match('/^([0-9a-f]{40})\s+'.preg_quote($match[1], '/').'$/mi', File::get($packed), $ref) === 1) {
                    return strtolower($ref[1]);
                }
            }

            return null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function read(): array
    {
        try {
            $path = storage_path(self::FILE);

            if (! File::exists($path)) {
                return [];
            }

            $decoded = json_decode((string) File::get($path), true);

            return is_array($decoded) ? $decoded : [];
        } catch (Throwable) {
            return [];
        }
    }

    /**
     * @param  array<string, mixed>  $values
     */
    public static function write(array $values): void
    {
        $path = storage_path(self::FILE);

        File::ensureDirectoryExists(dirname($path));
        File::put($path, json_encode(array_merge(self::read(), $values), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }

    public static function builtCommit(): ?string
    {
        $value = self::read()['built_commit'] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * Has the deployed commit been built?
     *
     * TRUE when there is nothing to compare, deliberately. A missing
     * repository or a state file that has never been written is "we cannot
     * tell", and a health check that goes red because it cannot tell teaches
     * people to ignore it. The watcher's own logging is what surfaces a
     * watcher that has stopped; this flags the specific, checkable case where
     * the deployed commit is known and does not match.
     */
    public static function isUpToDate(): bool
    {
        $current = self::currentCommit();
        $built = self::builtCommit();

        if ($current === null || $built === null) {
            return true;
        }

        return $current === $built;
    }

    /**
     * Whether the last attempt on THIS commit failed.
     *
     * The watcher refuses to retry a commit it has already failed on, so that
     * a broken deploy becomes one alert rather than one every minute until
     * somebody wakes up.
     */
    public static function hasFailedFor(string $commit): bool
    {
        $state = self::read();

        return ($state['failed'] ?? false) === true
            && ($state['attempted_commit'] ?? null) === $commit;
    }

    public static function markBuilt(string $commit, float $seconds): void
    {
        self::write([
            'built_commit' => $commit,
            'built_at' => Carbon::now()->toIso8601String(),
            'build_seconds' => round($seconds, 1),
            'attempted_commit' => $commit,
            'failed' => false,
            'failure' => null,
        ]);
    }

    public static function markFailed(string $commit, string $failure): void
    {
        self::write([
            'attempted_commit' => $commit,
            'attempted_at' => Carbon::now()->toIso8601String(),
            'failed' => true,
            'failure' => $failure,
        ]);
    }

    /**
     * Record that a commit has been seen, for the stability guard.
     *
     * @return bool whether it has now been stable long enough to act on
     */
    public static function observe(string $commit): bool
    {
        $state = self::read();

        if (($state['seen_commit'] ?? null) !== $commit) {
            self::write([
                'seen_commit' => $commit,
                'seen_at' => Carbon::now()->toIso8601String(),
            ]);

            return false;
        }

        $seenAt = $state['seen_at'] ?? null;

        if (! is_string($seenAt)) {
            return false;
        }

        return Carbon::parse($seenAt)->addSeconds(self::STABLE_AFTER_SECONDS)->isPast();
    }
}
