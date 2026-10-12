<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Contact;
use App\Support\DeploymentState;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Process\Process;
use Throwable;

/**
 * Finish the deploy that hPanel started.
 *
 * ---------------------------------------------------------------------------
 * WHY THIS EXISTS
 * ---------------------------------------------------------------------------
 *
 * hPanel's git deployment fires on a push and runs unattended. It clones the
 * repository, checks out a detached HEAD, copies the tree across and dumps the
 * autoloader — and then stops. It does not run `composer install --no-dev`, and
 * it does not rebuild the route or config caches.
 *
 * So from the moment anybody pushes, production runs NEW CODE AGAINST OLD
 * CACHES until a human remembers to finish the job. On 2026-10-12 that window
 * was three hours and it took the admin panel down: a newly added Filament page
 * is discovered by scanning the directory, so the sidebar began rendering a
 * link to a route that the cached route table did not contain. Every
 * authenticated panel page returned 500, eighteen seconds after the push.
 *
 * And it looked fine from outside. The login screen uses Filament's simple
 * layout and has no sidebar, so /admin/login stayed 200, /admin kept 302ing to
 * it, the public site was untouched and /up reported all six checks green. Only
 * somebody already signed in ever saw it.
 *
 * hPanel has no post-deploy command field — checked. Git hooks cannot help
 * either: the deploy clones fresh every time, and a fresh clone's .git/hooks
 * holds nothing but .sample files, so a hook installed in the live repo is
 * destroyed by the next deploy before it could fire. This command is what is
 * left, and it closes the window to about a minute.
 *
 * ---------------------------------------------------------------------------
 * THE FOUR WAYS THIS COULD BE WORSE THAN THE PROBLEM, AND WHAT STOPS EACH
 * ---------------------------------------------------------------------------
 *
 * FIRING MID-COPY. The deploy clones first and copies afterwards, so HEAD
 * already points at the new commit while the tree is still being written.
 * Rebuilding caches against half-copied code is worse than stale caches. So a
 * commit must sit unchanged for a full cycle before this acts on it — see
 * DeploymentState::STABLE_AFTER_SECONDS for why that rather than keying off a
 * marker the deploy writes.
 *
 * TWO RUNS AT ONCE. A rebuild takes longer than the one-minute schedule, so
 * overlapping runs are not hypothetical, and two composers installing into the
 * same vendor directory is a broken site. An flock on disk prevents it —
 * deliberately not a cache lock, because this command runs `config:cache` and
 * `composer install`, and a lock that its own work could clear is not a lock.
 *
 * FAILING IN A LOOP. A build that fails every minute, alerting every minute, is
 * noise that gets muted — and a muted alert is no alert. A failure is recorded
 * against the commit that caused it and not retried; the next push is what
 * clears it.
 *
 * LEAVING THE SITE DOWN. The rebuild happens behind maintenance mode, because
 * rewriting routes-v7.php under a live site means a request can land mid-write
 * and get `require(...): Failed to open stream` — which happened once, on
 * 2026-10-12 at 18:24:01. `up` therefore runs in a finally, and a stale
 * maintenance marker from a killed process is cleared at the start of the next
 * run rather than waiting for a human.
 */
class WatchDeployment extends Command
{
    protected $signature = 'clinic:watch-deployment
                            {--force : Rebuild now, skipping the stability guard and the failed-commit check}';

    protected $description = 'Finish the deploy hPanel started: rebuild caches when the deployed commit changes';

    private const LOCK = 'app/private/deployment-watch.lock';

    private const MAINTENANCE_MARKER = 'app/private/deployment-watch-maintenance';

    /** A build that has not finished in this long has died; its lock is stale. */
    private const STUCK_AFTER_SECONDS = 900;

    public function handle(): int
    {
        $this->clearStaleMaintenance();

        $commit = DeploymentState::currentCommit();

        if ($commit === null) {
            // No repository: a tarball deploy or a developer machine. Nothing
            // to watch, and nothing is wrong.
            return self::SUCCESS;
        }

        if (! $this->option('force')) {
            if ($commit === DeploymentState::builtCommit()) {
                return self::SUCCESS;
            }

            if (DeploymentState::hasFailedFor($commit)) {
                // Already alerted on this one. Saying so every minute is how an
                // alert becomes wallpaper.
                return self::SUCCESS;
            }

            if (! DeploymentState::observe($commit)) {
                $this->line('Deploy seen at '.substr($commit, 0, 7).'; waiting for it to settle.');

                return self::SUCCESS;
            }
        }

        return $this->rebuild($commit);
    }

    private function rebuild(string $commit): int
    {
        $lockPath = storage_path(self::LOCK);
        File::ensureDirectoryExists(dirname($lockPath));

        $lock = fopen($lockPath, 'c');

        if ($lock === false) {
            Log::error('Deployment watcher could not open its lock file.', ['path' => $lockPath]);

            return self::FAILURE;
        }

        // Non-blocking: if another run holds it, this cycle simply does nothing
        // rather than queueing up a second composer behind the first.
        if (! flock($lock, LOCK_EX | LOCK_NB)) {
            fclose($lock);
            $this->line('Another rebuild is already running.');

            return self::SUCCESS;
        }

        $short = substr($commit, 0, 7);
        $started = microtime(true);

        Log::warning('Deployment watcher: rebuilding for a new commit.', [
            'commit' => $commit,
            'previous' => DeploymentState::builtCommit(),
        ]);

        $this->info("Rebuilding for {$short}…");

        $downTaken = false;

        try {
            /*
             * Down FIRST and up LAST, with the caches rebuilt in between.
             * Rewriting the route cache under a live site is what produced a
             * 500 on 2026-10-12; see the class note.
             */
            $this->call('down', ['--retry' => 60]);
            File::put(storage_path(self::MAINTENANCE_MARKER), (string) time());
            $downTaken = true;

            $this->runProcess([
                (string) config('clinic.deploy.composer'),
                'install', '--no-dev', '--optimize-autoloader', '--no-interaction',
            ]);

            // Artisan in-process rather than as subprocesses: the autoloader
            // has just been rewritten under us, and a fresh process per command
            // is both slower and another chance for PATH to matter.
            $this->call('migrate', ['--force' => true]);
            $this->call('config:cache');
            $this->call('route:cache');
            $this->call('view:clear');
        } catch (Throwable $e) {
            DeploymentState::markFailed($commit, $e->getMessage());

            Log::error('Deployment watcher: rebuild FAILED. The site may be running stale caches.', [
                'commit' => $commit,
                'error' => $e->getMessage(),
            ]);

            $this->alertClinic($commit, $e);

            return self::FAILURE;
        } finally {
            if ($downTaken) {
                // Always, including after a failure: a half-built site serving
                // is better than a site that is simply gone, and the health
                // endpoint will say the commit does not match.
                $this->call('up');
                File::delete(storage_path(self::MAINTENANCE_MARKER));
            }

            flock($lock, LOCK_UN);
            fclose($lock);
        }

        $seconds = microtime(true) - $started;

        DeploymentState::markBuilt($commit, $seconds);

        Log::warning('Deployment watcher: rebuild complete.', [
            'commit' => $commit,
            'seconds' => round($seconds, 1),
        ]);

        $this->info(sprintf('Rebuilt %s in %.1fs.', $short, $seconds));

        return self::SUCCESS;
    }

    /**
     * @param  array<int, string>  $argv
     */
    private function runProcess(array $argv): void
    {
        $process = new Process($argv, base_path(), [
            // Composer needs somewhere to put its cache; cron may not set HOME.
            'HOME' => base_path('storage/app/private'),
            'COMPOSER_HOME' => base_path('storage/app/private/.composer'),
            'COMPOSER_NO_INTERACTION' => '1',
        ], null, 600);

        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(
                implode(' ', $argv).' failed: '.trim($process->getErrorOutput() ?: $process->getOutput())
            );
        }
    }

    /**
     * A rebuild that failed is the clinic's problem before it is a
     * developer's: the site is live, on stale caches, and nothing else will
     * say so. Goes to the same address every other operational alert goes to.
     *
     * Sent with notifyNow, because the queue is one of the things that may be
     * broken — a failure notice that waits for a worker is a failure notice
     * about a worker that is not running.
     */
    private function alertClinic(string $commit, Throwable $e): void
    {
        $email = Contact::email();

        if ($email === null) {
            return;
        }

        try {
            Mail::raw(implode("\n", [
                'The deployment watcher could not finish a rebuild on rehletsehha.com.',
                '',
                'Commit:  '.$commit,
                'Error:   '.$e->getMessage(),
                '',
                'The site is serving, but it may be running the previous deploy\'s caches.',
                'It will NOT retry this commit — push again, or run the sequence in',
                'docs/deployment/hostinger.md section 11b by hand.',
            ]), fn ($m) => $m->to($email)->subject('Rehlet Sehha — deployment rebuild failed'));
        } catch (Throwable) {
            // An alert that throws must not mask the failure it is reporting,
            // and the log line above has already recorded it.
        }
    }

    /**
     * A build killed mid-flight leaves the site in maintenance for ever.
     *
     * Only OUR marker is honoured, so a deliberate `php artisan down` by a
     * human is never undone by this.
     */
    private function clearStaleMaintenance(): void
    {
        $marker = storage_path(self::MAINTENANCE_MARKER);

        if (! File::exists($marker)) {
            return;
        }

        if (time() - (int) File::lastModified($marker) < self::STUCK_AFTER_SECONDS) {
            return;
        }

        Log::error('Deployment watcher: maintenance mode was left on by a killed rebuild. Bringing the site up.');

        $this->call('up');
        File::delete($marker);
    }
}
