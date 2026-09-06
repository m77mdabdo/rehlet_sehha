<?php

declare(strict_types=1);

use App\Models\Specialty;
use App\Support\Photo;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\TagSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * The page header — what it must show, and what it must never.
 *
 * Contrast is measured in a browser rather than asserted here; the numbers are
 * in the task report. What this file pins are the decisions that measurement
 * depends on, and the two rules that are about honesty rather than legibility.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(FaqSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
});
it('gives every page in the mapping a photograph that actually exists', function () {
    /*
     * A slug that names a photo the pipeline never built falls through to the
     * navy ground, which is silent. Silent is fine as a runtime behaviour and
     * useless as a review: this is where a typo surfaces.
     */
    foreach (config('page-headers.pages') as $page => $entry) {
        expect(Photo::has($entry['photo']))->toBeTrue(
            "The «{$page}» header names «{$entry['photo']}», which is not in the photo library."
        );

        expect(trim((string) ($entry['note'] ?? '')))->not->toBeEmpty(
            "The «{$page}» header has no note saying why that image. Every one of these is a claim about what the page is about."
        );
    }
});

it('keeps a stock photograph off the page about who will be treating you', function () {
    /*
     * StandalonePagesTest has refused a stock photograph on the about page
     * since long before this header existed, and it would have caught the
     * header too — it did, in fact, which is how about ended up on the navy
     * ground. This states the rule where the mapping lives, so somebody adding
     * an entry reads it before adding it rather than after the build fails.
     *
     * A warm photograph of somebody's hands is not Rana's hands, and at the
     * top of her biography it implies it is.
     */
    /*
     * array_key_exists + toBeFalse, NOT toHaveKey($key, $message). Pest reads
     * a second argument to toHaveKey as the expected VALUE, so the sentence
     * below would be compared against the array entry rather than shown when
     * it fails. ExpectationMessagesTest scans for that mistake and found this
     * one.
     */
    expect(array_key_exists('about', config('page-headers.pages')))->toBeFalse(
        'The about page has a header photograph. On the page about who will be treating you, a stock image is a claim a patient cannot check.'
    );
});

it('scrims the photograph with a gradient rather than a wash', function () {
    /*
     * The reference this composition came from lays a flat colour filter over
     * its header photographs and the picture stops existing. Having chosen an
     * image on purpose, that throws away the reason for choosing it.
     *
     * A radial rather than a linear one, because the title is CENTRED: the
     * clear space is at the sides, not above or below.
     */
    $scrim = config('page-headers.scrim');

    expect($scrim)->toContain('radial-gradient');

    preg_match_all('/rgb\(14 46 77 \/ ([0-9.]+)\)/', $scrim, $match);

    $alphas = array_map('floatval', $match[1]);

    expect(max($alphas))->toBeGreaterThanOrEqual(0.80, 'The scrim is too thin behind the title.');
    expect(min($alphas))->toBeLessThanOrEqual(0.30, 'The scrim never lets go. That is a wash, not a gradient.');
});

it('sizes the header in rem so a mobile url bar cannot reflow the page', function () {
    /*
     * vh looks like the obvious unit for "40% of the screen" and it is the
     * wrong one: mobile browsers resize the viewport as the address bar hides,
     * and a vh-sized header moves the whole page while somebody is reading it.
     */
    expect(config('page-headers.height'))->not->toContain('vh');
    expect(config('page-headers.height'))->toContain('rem');
});

it('puts one header, one h1 and a matching breadcrumb on every page that has one', function (string $locale) {
    $paths = ['services', 'packages', 'how-it-works', 'about', 'articles', 'faq', 'contact'];

    $paths[] = 'specialties/'.Specialty::query()->where('is_active', true)->firstOrFail()->slug;

    foreach ($paths as $path) {
        $html = $this->get("/{$locale}/{$path}")->assertOk()->getContent();

        /*
         * The lookahead matters: the photo and the scrim inside the header are
         * data-page-header-SOMETHING, so a bare substring count finds three.
         */
        expect(preg_match_all('/data-page-header(?![-\w])/', $html))->toBe(
            1,
            "{$path} does not have exactly one page header."
        );
        expect(substr_count($html, '<h1'))->toBe(1, "{$path} does not have exactly one h1.");

        /*
         * A visible trail and no BreadcrumbList — or the reverse — is worse
         * than neither. Google treats a mismatch as a reason to distrust both.
         */
        expect(str_contains($html, 'aria-current="page"'))->toBeTrue("{$path} shows no breadcrumb.");
        expect(str_contains($html, 'BreadcrumbList'))->toBeTrue("{$path} emits no BreadcrumbList.");
    }
})->with(['ar', 'en']);
