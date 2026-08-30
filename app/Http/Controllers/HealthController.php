<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Heartbeat;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * IS THE SITE ACTUALLY WORKING?
 *
 * Not "does the web server answer" — an uptime monitor pointed at the home page
 * already tells you that, and it would have gone on reporting green through
 * every failure this clinic can realistically have. The site stays perfectly
 * serviceable while the queue stops draining and no patient is ever reminded,
 * while cron dies and no dump is written, while the disk fills and the next
 * intake form cannot be saved.
 *
 * Each check below is one of those: a thing that fails silently, that a visitor
 * cannot see, and that somebody would otherwise discover from a patient.
 *
 * WHAT THIS DELIBERATELY DOES NOT SAY. Names and pass/fail, nothing else. No
 * versions, no paths, no row counts, no error messages, no queue depth. The
 * route is unauthenticated because a monitor has to reach it without a
 * credential to leak, so it must be worthless to anyone else: an attacker
 * learning that our cache is a bit slow today is not a foothold, and the
 * exception text that would have been a foothold never leaves the server.
 *
 * The status code is the contract. 200 when everything passes, 503 when
 * anything fails, so a monitor needs no parsing at all.
 */
final class HealthController extends Controller
{
    /**
     * How stale the newest dump may be.
     *
     * The schedule runs at 02:30 Cairo, so in a healthy week the newest backup
     * is under 24 hours old at every moment of the day. Thirty-six hours
     * forgives exactly one missed night — a host reboot, a slow dump — and
     * still flags a cron that has genuinely stopped before the second night is
     * lost.
     */
    private const BACKUP_STALE_AFTER_HOURS = 36;

    /**
     * How long a job may sit in the queue before something is wrong.
     *
     * The worker starts every minute. A job whose turn came ten minutes ago and
     * is still waiting means the worker is not running or is dying on the same
     * job repeatedly — either way, notifications have stopped.
     *
     * Measured from `available_at`, not `created_at`: a reminder scheduled for
     * tomorrow is not late today.
     */
    private const QUEUE_STUCK_AFTER_MINUTES = 10;

    public function __invoke(Request $request): Response
    {
        $checks = [
            'database' => $this->database(),
            'storage' => $this->storage(),
            'cache' => $this->cache(),
            'scheduler' => $this->scheduler(),
            'queue' => $this->queue(),
            'backup' => $this->backup(),
        ];

        $healthy = ! in_array(false, $checks, true);
        $status = $healthy ? 200 : 503;

        $response = ($request->expectsJson() || $request->query('format') === 'json')
            ? response()->json([
                'status' => $healthy ? 'ok' : 'degraded',
                'checks' => $checks,
            ], $status)
            : response()->view('health', [
                'healthy' => $healthy,
                'checks' => $checks,
            ], $status);

        /*
         * Never store this, anywhere, for any length of time.
         *
         * The whole value of the answer is that it describes this second. A
         * proxy or a browser holding it for even a minute turns the page into
         * a thing that says the site is fine while the site is not, which is
         * worse than having no health check at all.
         */
        return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * A real query against a real table.
     *
     * `SELECT 1` proves only that a connection opened, which is true of a
     * database restored into the wrong schema and of one whose tables were
     * dropped. Counting a table the application cannot work without proves the
     * data is there.
     */
    private function database(): bool
    {
        try {
            DB::table('appointments')->count();

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * A full write-read-delete, not is_writable().
     *
     * A disk quota that is exactly full reports the directory as writable and
     * then fails on the write — which is the shared-hosting failure mode this
     * check exists for.
     */
    private function storage(): bool
    {
        $path = 'health-check-'.Str::random(12);

        try {
            $disk = Storage::disk('local');
            $disk->put($path, 'ok');
            $written = $disk->get($path) === 'ok';
            $disk->delete($path);

            return $written;
        } catch (Throwable) {
            return false;
        }
    }

    private function cache(): bool
    {
        $key = 'health-check:'.Str::random(12);

        try {
            Cache::put($key, 'ok', 60);
            $read = Cache::get($key) === 'ok';
            Cache::forget($key);

            return $read;
        } catch (Throwable) {
            return false;
        }
    }

    private function scheduler(): bool
    {
        return Heartbeat::isFresh();
    }

    /**
     * Nothing overdue in the queue.
     *
     * An empty queue passes, and so does a queue full of jobs whose time has
     * not come. What fails is a job that should already have been delivered.
     */
    private function queue(): bool
    {
        try {
            /*
             * The TABLE, not the configured driver.
             *
             * Guarding on `queue.default === 'database'` looked tidier and was
             * wrong twice over. It reported a pass on a check that had never
             * run — the precise dishonesty this whole route exists to avoid —
             * and it went blind in the one case that matters most: somebody
             * sets QUEUE_CONNECTION=sync on a live server, and the reminders
             * already sitting in `jobs` are now never going to be delivered by
             * anything. A row that is overdue is overdue whatever the config
             * says today.
             *
             * No table at all means the feature is not installed, which is a
             * structural fact rather than a guess about intent.
             */
            if (! Schema::hasTable('jobs')) {
                return true;
            }

            $overdue = DB::table('jobs')
                ->where('available_at', '<', Carbon::now()->subMinutes(self::QUEUE_STUCK_AFTER_MINUTES)->getTimestamp())
                ->exists();

            return ! $overdue;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * A recent dump exists on disk.
     *
     * Reads the filenames rather than asking the backup package, so this stays
     * true even if the package's own bookkeeping is confused: what matters is
     * whether there is a file somebody could restore from tonight.
     */
    private function backup(): bool
    {
        try {
            $disk = Storage::disk((string) config('backup.backup.destination.disks.0', 'local'));
            $directory = (string) config('backup.backup.name');

            $newest = null;

            foreach ($disk->files($directory) as $file) {
                if (! str_ends_with($file, '.zip')) {
                    continue;
                }

                $modified = $disk->lastModified($file);

                if ($newest === null || $modified > $newest) {
                    $newest = $modified;
                }
            }

            if ($newest === null) {
                return false;
            }

            return $newest > Carbon::now()->subHours(self::BACKUP_STALE_AFTER_HOURS)->getTimestamp();
        } catch (Throwable) {
            return false;
        }
    }
}
