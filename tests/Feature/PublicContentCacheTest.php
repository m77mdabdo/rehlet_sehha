<?php

declare(strict_types=1);

use App\Models\WorkingHour;
use App\Support\PublicContent;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\PlateFoodSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The public pages read the same handful of tables on every render.
 *
 * The one that matters most here is working_hours, because its consumer is a
 * MACHINE. It feeds openingHoursSpecification in the JSON-LD, which no human
 * ever looks at — so a query nobody notices would sit in every request on
 * every page forever, and nothing on screen would ever look wrong.
 *
 * That is the specific failure this file exists to catch. It is easy to see a
 * slow page; it is not easy to see a query behind structured data.
 */
beforeEach(function () {
    Cache::flush();

    /*
     * Roles and a doctor FIRST. WorkingHoursSeeder attaches hours to each
     * practitioner individually and quietly skips itself when there is no
     * doctor user — which would leave working_hours empty and let every
     * assertion below pass without measuring anything.
     */
    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);

    $this->seed(ServiceSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(FaqSeeder::class);
    $this->seed(PlateFoodSeeder::class);
});

/**
 * Every public page, by path. Not route names: what is being measured is what
 * a visitor's request costs, and a visitor arrives at a URL.
 *
 * @return list<string>
 */
function publicPaths(): array
{
    return [
        '/ar',
        '/ar/about',
        '/ar/services',
        '/ar/packages',
        '/ar/how-it-works',
        '/ar/faq',
        '/ar/contact',
        '/ar/articles',
    ];
}

/**
 * Run a request and return the SQL it issued.
 *
 * The listener gets its own closure per call. Sharing one `use (&$queries)`
 * across a loop means every iteration writes into the same array and the
 * counts accumulate — a mistake that has been made twice in this codebase
 * already, once in a tinker script and once in StandalonePagesTest.
 *
 * @return list<string>
 */
function queriesFor(string $path): array
{
    $queries = [];

    DB::flushQueryLog();
    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    test()->get($path)->assertOk();

    return $queries;
}

it('actually has a schedule to cache', function () {
    /*
     * The guard on every measurement in this file. WorkingHoursSeeder skips
     * itself without a doctor user, and an empty table makes «queried once»
     * and «warm page touches nothing» both true and both meaningless.
     */
    expect(WorkingHour::query()->count())->toBeGreaterThan(0);
    expect(PublicContent::openingHours())->not->toBeEmpty();
});

it('reads working_hours at most once across every public page', function () {
    /*
     * ONCE, not once per page. The cache is warmed by whichever page renders
     * first and every page after it is served from that entry, so the whole
     * sweep costs a single SELECT.
     *
     * The assertion is on the count of queries against this table rather than
     * on a total query count, so that adding a legitimate query elsewhere on
     * a page does not fail a test about caching opening hours.
     */
    $total = 0;
    $perPage = [];

    foreach (publicPaths() as $path) {
        $hits = count(array_filter(
            queriesFor($path),
            fn (string $sql): bool => str_contains($sql, 'working_hours'),
        ));

        $perPage[$path] = $hits;
        $total += $hits;
    }

    expect($total)->toBeLessThanOrEqual(1, sprintf(
        "working_hours was queried %d times across %d pages:\n\n%s\n\n"
        .'It is cached through PublicContent::openingHours(). If this count has '
        .'grown, something is reading the model directly again.',
        $total,
        count($perPage),
        collect($perPage)->map(fn (int $n, string $p): string => "  {$p}: {$n}")->implode("\n"),
    ));
});

it('serves a warm page without touching any cached table', function () {
    /*
     * The second visitor's request. Every cached set — services, specialties,
     * FAQs, opening hours, approved reviews, posts, videos, plate foods —
     * should already be in the store, so a warm homepage reads none of them.
     */
    $this->get('/ar')->assertOk();

    $cachedTables = [
        'working_hours', 'services', 'specialties', 'faqs',
        'reviews', 'posts', 'videos', 'plate_foods',
    ];

    $warm = queriesFor('/ar');

    $touched = [];

    foreach ($cachedTables as $table) {
        $hits = count(array_filter($warm, fn (string $sql): bool => str_contains($sql, $table)));

        if ($hits > 0) {
            $touched[] = "{$table} ({$hits})";
        }
    }

    expect($touched)->toBe([], 'A warm homepage still queries: '.implode(', ', $touched));
});

it('shows a schedule change on the next request rather than on a TTL boundary', function () {
    /*
     * The half of caching that goes wrong quietly. Reception changes Saturday's
     * hours, the site keeps announcing the old ones, and nobody finds out until
     * a patient turns up to a video call that is not there.
     *
     * The flush is on the model's save event, so the correction is live on the
     * very next render — no TTL to wait out, no cache command to remember.
     */
    $this->get('/ar')->assertOk();

    $saturday = WorkingHour::query()->where('day_of_week', 6)->firstOrFail();
    $saturday->update(['end_time' => '18:00:00']);

    expect(PublicContent::openingHours()->firstWhere('day_of_week', 6)->end_time)
        ->toStartWith('18:00', 'The cached schedule still holds the old closing time.');

    // str_contains, not toContain: Pest reads a second argument to toContain
    // as ANOTHER NEEDLE, not as a failure message, so the message itself gets
    // searched for in the page and the test fails for the wrong reason.
    $content = $this->get('/ar')->assertOk()->getContent();

    expect(str_contains($content, '"closes":"18:00"'))->toBeTrue(
        'The JSON-LD still advertises the old closing time.'
    );
});
