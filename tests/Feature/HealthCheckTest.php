<?php

declare(strict_types=1);

use App\Support\Heartbeat;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * THE HEALTH ROUTE HAS TO FAIL WHEN THINGS FAIL.
 *
 * A check that only ever returns 200 is the most dangerous thing in this
 * repository: it is a monitor that reports green through every outage, so
 * nobody looks. Every test below therefore breaks one subsystem for real and
 * insists the route notices.
 *
 * The heartbeat file is the dev machine's own, so it is saved and put back —
 * a test suite that leaves a fresh heartbeat behind would make a local /up
 * report a running scheduler on a machine where cron has never run.
 */
beforeEach(function (): void {
    $this->heartbeatBackup = is_file(Heartbeat::path())
        ? file_get_contents(Heartbeat::path())
        : null;
});

afterEach(function (): void {
    if ($this->heartbeatBackup === null) {
        @unlink(Heartbeat::path());

        return;
    }

    file_put_contents(Heartbeat::path(), $this->heartbeatBackup);
});

/**
 * A site where everything works: a fresh heartbeat and a dump from last night.
 */
function makeSiteHealthy(): void
{
    Heartbeat::record();

    Storage::fake('local');
    Storage::disk('local')->put(config('backup.backup.name').'/2026-01-01-02-30-00.zip', 'dump');
}

/*
|------------------------------------------------------------------------------
| The happy path
|------------------------------------------------------------------------------
*/

it('answers 200 with every check passing when the site is well', function (): void {
    makeSiteHealthy();

    $response = $this->getJson('/up')->assertOk();

    expect($response->json('status'))->toBe('ok');

    foreach ($response->json('checks') as $check => $passed) {
        expect($passed)->toBeTrue("The «{$check}» check failed on a healthy site.");
    }
});

it('reports on every subsystem that can fail silently', function (): void {
    makeSiteHealthy();

    /*
     * Named explicitly rather than counted. Each of these is a failure a
     * VISITOR cannot see — the site keeps serving pages while reminders stop,
     * or while the nightly dump stops being written. Dropping one from the
     * controller should break this test, because dropping one means going back
     * to finding out from a patient.
     */
    $checks = $this->getJson('/up')->assertOk()->json('checks');

    foreach (['database', 'storage', 'cache', 'scheduler', 'queue', 'backup'] as $expected) {
        expect(array_key_exists($expected, $checks))
            ->toBeTrue("The health route no longer checks «{$expected}».");
    }
});

it('is never cached', function (): void {
    makeSiteHealthy();

    /*
     * A status page held by a proxy for even a minute is a page that says the
     * site is fine while the site is not.
     */
    $header = $this->getJson('/up')->assertOk()->headers->get('Cache-Control');

    expect($header)->toContain('no-store');
});

/*
|------------------------------------------------------------------------------
| The failures it exists to catch
|------------------------------------------------------------------------------
*/

it('reports 503 when the scheduler has stopped running', function (): void {
    makeSiteHealthy();

    // Cron died a quarter of an hour ago: no reminders, no daily schedule, and
    // tonight's dump will not be written either.
    touch(Heartbeat::path(), Carbon::now()->subMinutes(Heartbeat::STALE_AFTER_MINUTES + 1)->getTimestamp());
    clearstatcache();

    $response = $this->getJson('/up')->assertStatus(503);

    expect($response->json('status'))->toBe('degraded');
    expect($response->json('checks.scheduler'))->toBeFalse();
});

it('reports 503 when the scheduler has never run on this host', function (): void {
    makeSiteHealthy();

    @unlink(Heartbeat::path());

    $this->getJson('/up')->assertStatus(503)->assertJsonPath('checks.scheduler', false);
});

it('reports 503 when there is no recent backup', function (): void {
    Heartbeat::record();

    // An empty backup directory: the dumps stopped, and nobody was told
    // because success notifications are deliberately off.
    Storage::fake('local');

    $this->getJson('/up')->assertStatus(503)->assertJsonPath('checks.backup', false);
});

it('reports 503 when the newest backup is too old to be last night', function (): void {
    Heartbeat::record();

    Storage::fake('local');

    $path = config('backup.backup.name').'/2026-01-01-02-30-00.zip';
    Storage::disk('local')->put($path, 'dump');

    // Two nights missed. One is forgiven; two means the schedule has stopped.
    touch(Storage::disk('local')->path($path), Carbon::now()->subHours(50)->getTimestamp());
    clearstatcache();

    $this->getJson('/up')->assertStatus(503)->assertJsonPath('checks.backup', false);
});

it('reports 503 when a job has been sitting in the queue', function (): void {
    makeSiteHealthy();

    /*
     * The failure that costs a patient something. Every notification in this
     * application is queued, so a worker that has stopped means confirmations
     * and reminders silently stop while the site looks perfect.
     */
    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => Carbon::now()->subHour()->getTimestamp(),
        'created_at' => Carbon::now()->subHour()->getTimestamp(),
    ]);

    $this->getJson('/up')->assertStatus(503)->assertJsonPath('checks.queue', false);
});

it('does not fail on a job whose turn has not come yet', function (): void {
    makeSiteHealthy();

    // A reminder queued for tomorrow morning is not a stuck queue.
    DB::table('jobs')->insert([
        'queue' => 'default',
        'payload' => '{}',
        'attempts' => 0,
        'reserved_at' => null,
        'available_at' => Carbon::now()->addHours(6)->getTimestamp(),
        'created_at' => Carbon::now()->getTimestamp(),
    ]);

    $this->getJson('/up')->assertOk()->assertJsonPath('checks.queue', true);
});

/*
|------------------------------------------------------------------------------
| What it must not give away
|------------------------------------------------------------------------------
*/

it('says nothing beyond names and pass or fail', function (): void {
    makeSiteHealthy();

    @unlink(Heartbeat::path());

    $body = $this->getJson('/up')->assertStatus(503)->getContent();

    /*
     * The route is unauthenticated because a monitor cannot hold a credential
     * without that credential being the thing most likely to leak. The price
     * of that is that the response must be worthless to everybody else.
     */
    foreach ([base_path(), storage_path(), config('database.connections.mysql.database'), app()->version()] as $secret) {
        expect(str_contains($body, (string) $secret))
            ->toBeFalse('The health response leaks «'.$secret.'».');
    }

    foreach (['Exception', 'SQLSTATE', 'vendor/', 'stack'] as $needle) {
        expect(str_contains($body, $needle))
            ->toBeFalse("The health response leaks internals: «{$needle}».");
    }
});

it('keeps the human page out of search results', function (): void {
    makeSiteHealthy();

    /*
     * It is a page on the public site with no link to it, which is exactly the
     * kind of URL that ends up in an index via a referrer or a toolbar.
     */
    $this->get('/up')->assertOk()->assertSee('noindex', escape: false);
});

it('is throttled', function (): void {
    makeSiteHealthy();

    /*
     * Unauthenticated and it does real work — a database query, a file write,
     * a cache round trip. Without a limit it is a free amplifier.
     */
    $limited = false;

    for ($i = 0; $i < 40; $i++) {
        if ($this->getJson('/up')->getStatusCode() === 429) {
            $limited = true;
            break;
        }
    }

    expect($limited)->toBeTrue('The health route accepted 40 hits a minute without throttling.');
});

/*
|------------------------------------------------------------------------------
| The page a receptionist reads
|------------------------------------------------------------------------------
*/

it('explains a failure in terms of what stopped working for a patient', function (): void {
    makeSiteHealthy();

    @unlink(Heartbeat::path());

    /*
     * The person most likely to open this page is at the front desk, not in a
     * terminal. "المهام التلقائية واقفة" is useless on its own; what she needs
     * to know is that patients are not being reminded.
     */
    $this->get('/up')
        ->assertStatus(503)
        ->assertSee(__('health.degraded'))
        ->assertSee(__('health.checks.scheduler.failed'));
});
