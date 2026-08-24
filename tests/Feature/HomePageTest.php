<?php

declare(strict_types=1);

use App\Models\Faq;
use App\Models\Post;
use App\Models\Service;
use App\Models\Specialty;
use App\Models\Testimonial;
use App\Support\PublicContent;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\TestimonialSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Every test starts cold. A leaked warm cache would make the query-count
    // assertions pass for the wrong reason.
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(FaqSeeder::class);
    $this->seed(TestimonialSeeder::class);
    $this->seed(PostSeeder::class);

    Cache::flush();
});

/**
 * Count the queries a callback issues.
 *
 * @return array{0: mixed, 1: list<string>}
 */
function countQueries(Closure $callback): array
{
    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $result = $callback();

    return [$result, $queries];
}

it('renders every section in both locales', function (string $locale) {
    $response = $this->get("/{$locale}")->assertOk();

    foreach (['specialties', 'packages', 'how-it-works', 'stories', 'articles', 'faq', 'book', 'contact'] as $id) {
        $response->assertSee('id="'.$id.'"', false);
    }
})->with(['ar', 'en']);

it('resolves every nav anchor to a section that exists', function () {
    $content = $this->get('/ar')->assertOk()->getContent();

    // Pull the fragment out of every in-page nav link and prove the target id
    // is actually rendered. This is the check that would have caught #about
    // pointing at a section nobody built.
    preg_match_all('/href="[^"]*#([a-z-]+)"/', $content, $matches);

    $fragments = array_unique($matches[1]);

    expect($fragments)->not->toBeEmpty();

    foreach ($fragments as $fragment) {
        // str_contains rather than toContain(): Pest's toContain is VARIADIC,
        // so a second string argument is another needle to find, not a failure
        // message. Passing one silently asserts the message text is on the page.
        expect(str_contains($content, 'id="'.$fragment.'"'))->toBeTrue(
            "The nav links to #{$fragment} but no element on the page carries that id."
        );
    }
});

it('issues a bounded number of queries cold', function () {
    /*
     * Six: one per cached set — services, specialties, testimonials, faqs,
     * posts, and the working hours behind the JSON-LD. Nothing lazy-loads a
     * relationship and nothing queries inside a loop.
     *
     * The sixth was found by this test rather than by reading the code: the
     * schema builder was hitting working_hours on every single request,
     * uncached, and because nobody ever looks at a JSON-LD block it would have
     * stayed there indefinitely.
     *
     * Cache reads do not appear here because the test store is `array`. With
     * the database store they become reads on the `cache` table instead — see
     * the note on the warm test.
     *
     * If this number grows, something in a Blade file started touching the
     * database. Raise it only with a reason.
     *
     * Raised from six to EIGHT when Task 8 added the video gallery and the
     * plate builder: one query each, both through PublicContent, neither in a
     * loop. Two new sets on the page, two new queries — which is the shape
     * that is allowed. Nine would mean something is lazy-loading.
     */
    [$response, $queries] = countQueries(fn () => $this->get('/ar'));

    $response->assertOk();

    expect($queries)->toHaveCount(8, "Cold homepage queries:\n".implode("\n", $queries));
});

it('issues no content queries at all when the cache is warm', function () {
    /*
     * Zero, with the array store the test suite uses.
     *
     * Worth stating plainly: in production CACHE_STORE is `database`, so a
     * warm request trades eight content queries for eight reads of the `cache`
     * table. That is a smaller win than it looks — the caching pays for itself
     * properly only on a store that is not the database.
     */
    $this->get('/ar')->assertOk();

    [$response, $queries] = countQueries(fn () => $this->get('/ar'));

    $response->assertOk();

    expect($queries)->toBeEmpty("Warm homepage should hit no tables:\n".implode("\n", $queries));
});

it('serves both locales from one cache entry', function () {
    // The models keep both languages in one JSON column, so a warm Arabic page
    // must leave the English page warm too. If this ever fails, someone has
    // made the cache key locale-dependent and doubled the entries for nothing.
    $this->get('/ar')->assertOk();

    [$response, $queries] = countQueries(fn () => $this->get('/en'));

    $response->assertOk();
    expect($queries)->toBeEmpty();
});

it('busts the cache when content changes', function () {
    $this->get('/ar')->assertOk();

    $service = Service::query()->orderBy('sort_order')->firstOrFail();
    $service->update(['name' => ['ar' => 'باقة اتغيرت', 'en' => 'Changed package']]);

    // A stale price or a renamed package lingering for a day is the failure
    // that makes people delete caching entirely.
    $this->get('/ar')->assertOk()->assertSee('باقة اتغيرت', false);
});

it('shows no weight, bmi or calorie figures anywhere', function (string $locale) {
    /*
     * A standing clinical and brand rule, not a preference — see the comment
     * at the top of the hero section. Asserted rather than trusted, because
     * this is exactly the sort of thing a future "improvement" adds back.
     */
    $content = $this->get("/{$locale}")->assertOk()->getContent();

    $forbidden = ['كيلو', 'كجم', 'سعرة', 'سعرات', 'مؤشر كتلة', 'BMI', 'kcal', 'calorie', 'Calorie'];

    foreach ($forbidden as $term) {
        expect(str_contains($content, $term))->toBeFalse("The page mentions «{$term}».");
    }

    // "kg" and "lb" as standalone words, so "kg" inside a hashed asset name
    // does not trip it.
    expect($content)->not->toMatch('/\b(kg|lbs?|pounds)\b/i');
})->with(['ar', 'en']);

it('has exactly one h1 and never skips a heading level', function (string $locale) {
    $content = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match_all('/<h([1-6])\b/i', $content, $matches);

    $levels = array_map('intval', $matches[1]);

    expect(array_count_values($levels)[1] ?? 0)->toBe(1, 'A page must have exactly one h1.');

    $previous = 1;

    foreach ($levels as $level) {
        expect($level)->toBeLessThanOrEqual(
            $previous + 1,
            "Heading jumped from h{$previous} to h{$level}."
        );

        $previous = $level;
    }
})->with(['ar', 'en']);

it('puts the clinic positioning in the h1, not just the brand name', function (string $locale) {
    $content = $this->get("/{$locale}")->assertOk()->getContent();

    expect(preg_match('/<h1[^>]*>(.*?)<\/h1>/s', $content, $match))->toBe(1);

    $h1 = trim(strip_tags($match[1]));

    expect($h1)->toBe(__('home.hero.title', [], $locale));

    // The brand name alone tells a search engine nothing about what the clinic
    // does, and tells a visitor nothing either.
    expect($h1)->not->toBe(__('common.brand', [], $locale));
    expect(str_word_count($h1) > 2 || mb_strlen($h1) > 12)->toBeTrue();
})->with(['ar', 'en']);

it('renders no untranslated keys', function (string $locale) {
    $content = $this->get("/{$locale}")->assertOk()->getContent();

    expect($content)->not->toMatch('/>\s*(home|nav|common|footer|booking)\.[a-z_.]+\s*</');
})->with(['ar', 'en']);

it('deep-links each package to the booking route with its service preselected', function () {
    $content = $this->get('/ar')->assertOk()->getContent();

    foreach (Service::active()->get() as $service) {
        expect($content)->toContain('booking?service='.$service->slug);
    }

    // And the link actually resolves with the service picked up.
    $first = Service::active()->firstOrFail();

    $this->get('/ar/booking?service='.$first->slug)
        ->assertOk()
        ->assertSee($first->getTranslation('name', 'ar'), false);
});

it('ignores an unknown service slug rather than erroring', function () {
    // Someone following a link from an old price list must land on the booking
    // page, not on a 404 or an exception.
    $this->get('/ar/booking?service=this-was-retired-in-2023')->assertOk();
    $this->get('/ar/booking?service=<script>alert(1)</script>')->assertOk();
});

it('hides scheduled and unpublished articles', function () {
    Post::query()->update(['published_at' => null]);
    PublicContent::flush();

    $future = Post::factory()->create([
        'slug' => 'scheduled-post',
        'title' => ['ar' => 'مقال مجدول', 'en' => 'Scheduled post'],
        'published_at' => now()->addWeek(),
    ]);

    $content = $this->get('/ar')->assertOk()->getContent();

    expect($content)->not->toContain('مقال مجدول');
    $this->get('/ar/articles/'.$future->slug)->assertNotFound();
});

it('degrades to empty states rather than breaking when there is no content', function (string $locale) {
    Service::query()->delete();
    Specialty::query()->delete();
    Testimonial::query()->delete();
    Faq::query()->delete();
    Post::query()->delete();
    PublicContent::flush();

    $response = $this->get("/{$locale}")->assertOk();

    // Every section still renders its heading and says so plainly.
    $response->assertSee(__('home.specialties.empty', [], $locale), false);
    $response->assertSee(__('home.packages.empty', [], $locale), false);
    $response->assertSee(__('home.stories.empty', [], $locale), false);
    $response->assertSee(__('home.articles.empty', [], $locale), false);
    $response->assertSee(__('home.faq.empty', [], $locale), false);
})->with(['ar', 'en']);

it('uses a native details accordion rather than javascript', function () {
    $content = $this->get('/ar')->assertOk()->getContent();

    expect(substr_count($content, '<details'))->toBe(Faq::active()->count());
    expect($content)->toContain('<summary');
});

it('reads the stats band from config', function () {
    config()->set('clinic.stats.cases', 1234);
    config()->set('clinic.stats.years', 42);

    $this->get('/ar')
        ->assertOk()
        ->assertSee('1,234+', false)
        ->assertSee('42+', false);
});
