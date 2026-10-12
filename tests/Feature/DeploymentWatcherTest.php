<?php

declare(strict_types=1);

use App\Support\DeploymentState;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;

/*
|------------------------------------------------------------------------------
| The watcher that finishes the deploy hPanel starts
|------------------------------------------------------------------------------
|
| The push IS the deploy on this host. hPanel clones, checks out, copies and
| dumps the autoloader, then stops — leaving production running new code
| against the previous deploy's route and config caches. On 2026-10-12 that
| window was three hours and it returned 500 on every authenticated panel page,
| while /admin/login stayed 200 and /up stayed green.
|
| There is no post-deploy hook to use: hPanel has no such field, and git hooks
| die with the fresh clone the deploy makes. So a minutely watcher closes the
| window instead — and a watcher is a background job that can fail silently,
| which on this project has happened three times now. Hence the guards below,
| and hence the /up check that notices when the watcher itself has stopped.
*/
beforeEach(function () {
    File::delete(storage_path('app/private/deployment-state.json'));
    File::delete(storage_path('app/private/deployment-watch-maintenance'));
});

afterEach(function () {
    File::delete(storage_path('app/private/deployment-state.json'));
    File::delete(storage_path('app/private/deployment-watch-maintenance'));
    Carbon::setTestNow();
});

/*
|------------------------------------------------------------------------------
| The mid-copy guard
|------------------------------------------------------------------------------
*/

it('will not act on a commit it has only just seen', function () {
    /*
     * THE FAILURE THIS PREVENTS: the deploy clones first and copies afterwards,
     * so HEAD already points at the new commit while the tree is still being
     * written. Rebuilding caches against half-copied code is worse than the
     * stale caches it would be fixing.
     */
    expect(DeploymentState::observe('a'.str_repeat('0', 39)))->toBeFalse();
});

it('acts once the same commit has been stable for a full cycle', function () {
    $commit = 'b'.str_repeat('0', 39);

    // First sighting: recorded, not acted on.
    expect(DeploymentState::observe($commit))->toBeFalse();

    // A cycle later, unchanged.
    Carbon::setTestNow(Carbon::now()->addSeconds(DeploymentState::STABLE_AFTER_SECONDS + 5));

    expect(DeploymentState::observe($commit))->toBeTrue();
});

it('restarts the clock when the commit changes again mid-wait', function () {
    // A second push landing while the first is settling must not inherit the
    // first one's stability.
    expect(DeploymentState::observe('c'.str_repeat('0', 39)))->toBeFalse();

    Carbon::setTestNow(Carbon::now()->addSeconds(DeploymentState::STABLE_AFTER_SECONDS + 5));

    expect(DeploymentState::observe('d'.str_repeat('0', 39)))->toBeFalse();
});

/*
|------------------------------------------------------------------------------
| Not retrying a commit that already failed
|------------------------------------------------------------------------------
*/

it('refuses to retry a commit whose build failed', function () {
    /*
     * A build failing every minute, alerting every minute, is noise that gets
     * muted — and a muted alert is no alert at all. One failure, one notice;
     * the next push is what clears it.
     */
    $commit = 'e'.str_repeat('0', 39);

    DeploymentState::markFailed($commit, 'composer exploded');

    expect(DeploymentState::hasFailedFor($commit))->toBeTrue();

    // A different commit is a fresh attempt, not a repeat.
    expect(DeploymentState::hasFailedFor('f'.str_repeat('0', 39)))->toBeFalse();
});

it('clears the failure once a build succeeds', function () {
    $commit = 'a'.str_repeat('1', 39);

    DeploymentState::markFailed($commit, 'boom');
    expect(DeploymentState::hasFailedFor($commit))->toBeTrue();

    DeploymentState::markBuilt($commit, 12.3);

    expect(DeploymentState::hasFailedFor($commit))->toBeFalse();
    expect(DeploymentState::builtCommit())->toBe($commit);
});

/*
|------------------------------------------------------------------------------
| What /up reports
|------------------------------------------------------------------------------
*/

it('reports the deploy as healthy when the built commit matches', function () {
    $current = DeploymentState::currentCommit();

    expect($current)->not->toBeNull('There is no git repository to read, so this test proves nothing.');

    DeploymentState::markBuilt($current, 1.0);

    expect(DeploymentState::isUpToDate())->toBeTrue();

    // Not assertOk(): /up is 503 in the test environment for unrelated reasons
    // — no scheduler has ever run, no backup exists. This test is about one key.
    $this->getJson('/up?format=json')->assertJsonPath('checks.deploy', true);
});

it('reports the deploy as unhealthy when a commit was never built', function () {
    /*
     * The case that matters: the watcher has stopped, a deploy has landed, and
     * nothing else on this site would notice. /admin/login stays 200 and the
     * public pages stay 200 — that is exactly what happened on 2026-10-12.
     */
    DeploymentState::markBuilt('9'.str_repeat('f', 39), 1.0);

    expect(DeploymentState::isUpToDate())->toBeFalse();

    $this->getJson('/up?format=json')
        ->assertStatus(503)
        ->assertJsonPath('checks.deploy', false);
});

it('does not go red merely because it cannot tell', function () {
    /*
     * No state file yet, or no repository. A health check that reports a
     * problem when it has no information is one people learn to ignore, and an
     * ignored check is the thing that lets the next silent failure run for a
     * month.
     */
    expect(DeploymentState::builtCommit())->toBeNull();
    expect(DeploymentState::isUpToDate())->toBeTrue();

    // Not assertOk(): /up is 503 in the test environment for unrelated reasons
    // — no scheduler has ever run, no backup exists. This test is about one key.
    $this->getJson('/up?format=json')->assertJsonPath('checks.deploy', true);
});

/*
|------------------------------------------------------------------------------
| The command's own behaviour
|------------------------------------------------------------------------------
*/

it('does nothing when the deployed commit is already built', function () {
    DeploymentState::markBuilt((string) DeploymentState::currentCommit(), 1.0);

    $this->artisan('clinic:watch-deployment')->assertSuccessful();

    // Still built, and no maintenance was taken.
    expect(File::exists(storage_path('app/private/deployment-watch-maintenance')))->toBeFalse();
});

it('waits rather than rebuilding the first time it sees a new commit', function () {
    // Nothing built yet, so the live commit is "new".
    $this->artisan('clinic:watch-deployment')
        ->expectsOutputToContain('waiting for it to settle')
        ->assertSuccessful();

    expect(DeploymentState::builtCommit())->toBeNull();
});

it('records the failure, alerts the clinic, and does not retry', function () {
    /*
     * Driven through a composer binary that cannot succeed, because a failure
     * path nobody has executed is a guess. /bin/false exits non-zero and
     * writes nothing, which is exactly what a broken composer looks like.
     */
    /*
     * No Mail::fake() here: it records Mailable instances, and this alert is a
     * raw send, so a faked mailer would report zero and the test would pass
     * while proving nothing. The suite runs on the array transport (phpunit.xml),
     * so the messages it actually collected are readable.
     */
    $sentBefore = Mail::mailer()->getSymfonyTransport()->messages()->count();

    config()->set('clinic.deploy.composer', '/bin/false');

    $commit = (string) DeploymentState::currentCommit();

    // Get past the stability guard.
    DeploymentState::observe($commit);
    Carbon::setTestNow(Carbon::now()->addSeconds(DeploymentState::STABLE_AFTER_SECONDS + 5));

    $this->artisan('clinic:watch-deployment')->assertFailed();

    expect(DeploymentState::hasFailedFor($commit))->toBeTrue();
    expect(DeploymentState::builtCommit())->toBeNull();

    // The clinic is told, once.
    expect(Mail::mailer()->getSymfonyTransport()->messages()->count() - $sentBefore)->toBe(1);

    // And the site was brought back up despite the failure: a half-built site
    // serving is better than a site that is simply gone.
    expect(app()->isDownForMaintenance())->toBeFalse();
    expect(File::exists(storage_path('app/private/deployment-watch-maintenance')))->toBeFalse();

    // A second run must not try again, and must not alert again.
    $afterFirst = Mail::mailer()->getSymfonyTransport()->messages()->count();

    $this->artisan('clinic:watch-deployment')->assertSuccessful();

    expect(Mail::mailer()->getSymfonyTransport()->messages()->count())->toBe($afterFirst);
});

it('gives up its slot when another rebuild already holds the lock', function () {
    /*
     * Two composers installing into one vendor directory is a broken site, and
     * a rebuild takes longer than the one-minute schedule, so this is not
     * hypothetical.
     *
     * The lock is an flock on disk rather than a cache lock, deliberately:
     * this command runs config:cache and composer install, and a lock its own
     * work could clear is not a lock.
     */
    $path = storage_path('app/private/deployment-watch.lock');
    File::ensureDirectoryExists(dirname($path));

    $held = fopen($path, 'c');
    expect(flock($held, LOCK_EX | LOCK_NB))->toBeTrue();

    $commit = (string) DeploymentState::currentCommit();
    DeploymentState::observe($commit);
    Carbon::setTestNow(Carbon::now()->addSeconds(DeploymentState::STABLE_AFTER_SECONDS + 5));

    $this->artisan('clinic:watch-deployment')
        ->expectsOutputToContain('Another rebuild is already running')
        ->assertSuccessful();

    // It did not build, and it did not take the site down.
    expect(DeploymentState::builtCommit())->toBeNull();
    expect(app()->isDownForMaintenance())->toBeFalse();

    flock($held, LOCK_UN);
    fclose($held);
});
