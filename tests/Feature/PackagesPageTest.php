<?php

declare(strict_types=1);

use App\Models\Faq;
use App\Models\Service;
use App\Support\FeaturedPackage;
use Database\Seeders\FaqSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    // Every test starts cold: a leaked warm cache would make the query-count
    // assertion pass for the wrong reason.
    Cache::flush();

    /*
     * Seeded selectively, as the homepage test is, rather than with a bare
     * seed().
     *
     * Not tidiness — seeding videos queues a thumbnail fetch per row through
     * dispatchAfterResponse, and with no request in flight during seeding
     * those callbacks accumulate and all fire on the FIRST request a test
     * makes. That put twelve `videos` lookups inside the query-count
     * measurement below, which is a fact about the seeder rather than about
     * this page.
     */
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(FaqSeeder::class);
});

it('answers in both locales', function (string $locale) {
    $this->get("/{$locale}/packages")->assertOk();
})->with(['ar', 'en']);

it('carries its own title and description rather than the homepage defaults', function (string $locale) {
    $html = $this->get("/{$locale}/packages")->assertOk()->getContent();

    expect($html)->toContain('<title>'.__('packages.meta_title', [], $locale).'</title>');
    expect($html)->toContain(__('packages.meta_description', [], $locale));

    // Not the homepage's, which would make the two pages indistinguishable to
    // a search engine reading nothing but the head.
    expect(str_contains($html, '<title>'.__('home.meta_title', [], $locale).'</title>'))->toBeFalse();
})->with(['ar', 'en']);

it('advertises itself correctly to crawlers', function (string $locale) {
    $html = $this->get("/{$locale}/packages")->assertOk()->getContent();

    expect($html)->toContain('rel="canonical" href="'.url("/{$locale}/packages").'"');

    foreach (['ar', 'en'] as $alternate) {
        expect($html)->toContain('hreflang="'.$alternate.'" href="'.url("/{$alternate}/packages").'"');
    }

    expect($html)->toContain('hreflang="x-default"');
    expect(str_contains($html, 'noindex'))->toBeFalse('The packages page is marked noindex.');
})->with(['ar', 'en']);

/*
|------------------------------------------------------------------------------
| The rule that matters most: this page must not be its homepage section again
|------------------------------------------------------------------------------
*/

it('does not restate its homepage section', function (string $locale) {
    /*
     * THE THRESHOLD IS 20% OF THE SECTION'S PROSE, IN FIVE-WORD RUNS.
     *
     * Three choices, each with a reason.
     *
     * FIVE-WORD RUNS rather than single words. Two texts about the same four
     * packages will share most of their vocabulary no matter how differently
     * they are written — "session", "plan", "package" — so word overlap
     * measures the subject, not the copying. A matching run of five words is
     * a matching sentence fragment, which is what a person pasting produces
     * and what a search engine's near-duplicate detection looks for.
     *
     * MEASURED AGAINST THE SECTION, NOT THE PAGE. The question is "is the page
     * just the section again", so the denominator is the section. Using the
     * page would make the figure shrink automatically as the page grew, and a
     * verbatim paste would pass simply by being surrounded by enough new text.
     *
     * CATALOGUE STRINGS ARE REMOVED FIRST. Package names, subtitles,
     * descriptions and feature lines come from the services table and are
     * SUPPOSED to appear in both places — a summary card and a comparison
     * table naming the same product is correct, not duplication. Left in, they
     * accounted for the entire measured overlap (16.3% ar / 13.6% en) and the
     * test would have been reporting the schema working as designed. What
     * remains after removing them is prose, which is the thing that must
     * differ.
     *
     * 20% because the honest floor is not zero: both texts are written in one
     * brand voice about one offer, and a few shared phrasings are a style, not
     * a paste. Measured at the time of writing this leaves a wide margin —
     * the real figures are reported below on failure so the next person can
     * see the trend rather than guess at it.
     */
    $threshold = 20.0;

    $home = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('/<section id="packages".*?<\/section>/su', $home, $match);

    expect($match)->not->toBeEmpty('The homepage packages section did not render.');

    // Everything the services table supplies, in this locale.
    $catalogue = [];

    foreach (Service::all() as $service) {
        foreach (['name', 'subtitle', 'description'] as $field) {
            $value = $service->getTranslation($field, $locale, false);

            if (is_string($value) && $value !== '') {
                $catalogue[] = $value;
            }
        }

        foreach ((array) $service->getTranslation('features', $locale, false) as $feature) {
            if (is_string($feature) && $feature !== '') {
                $catalogue[] = $feature;
            }
        }
    }

    expect($catalogue)->not->toBeEmpty('No catalogue strings found to exclude.');

    $strip = function (string $text) use ($catalogue): string {
        foreach ($catalogue as $entry) {
            $text = str_replace($entry, ' ', $text);
        }

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    };

    $sectionProse = $strip(pageVisibleText($match[0]));
    $pageProse = $strip(pageVisibleText($this->get("/{$locale}/packages")->assertOk()->getContent()));

    $sectionGrams = pageShingles($sectionProse, 5);
    $pageGrams = pageShingles($pageProse, 5);

    expect($sectionGrams)->not->toBeEmpty('The section had no prose left to compare.');

    $shared = array_intersect_key($sectionGrams, $pageGrams);
    $overlap = 100 * count($shared) / count($sectionGrams);

    expect($overlap)->toBeLessThan(
        $threshold,
        sprintf(
            "The packages page restates %.1f%% of its homepage section's prose in five-word runs "
            ."(%d of %d), against a %.0f%% limit.\n\n"
            ."A page that repeats its section is treated as duplication and both rank lower.\n"
            ."The section summarises; the page has to say something the section does not.\n\n"
            .'Shared runs include: %s',
            $overlap,
            count($shared),
            count($sectionGrams),
            $threshold,
            implode(' | ', array_slice(array_keys($shared), 0, 5)) ?: '(none)',
        ),
    );
})->with(['ar', 'en']);

it('says substantially more than its homepage section', function (string $locale) {
    /*
     * The other half of the rule. A page can avoid duplication by being empty,
     * which is worse — "a thin page with an obvious placeholder is worse than
     * a dense page that never promised one".
     */
    $home = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('/<section id="packages".*?<\/section>/su', $home, $match);

    $sectionWords = count(preg_split('/\s+/u', pageVisibleText($match[0]), -1, PREG_SPLIT_NO_EMPTY) ?: []);
    $pageWords = count(preg_split('/\s+/u', pageVisibleText($this->get("/{$locale}/packages")->getContent()), -1, PREG_SPLIT_NO_EMPTY) ?: []);

    expect($pageWords)->toBeGreaterThan(
        $sectionWords * 3,
        "The packages page has {$pageWords} words against the section's {$sectionWords}. "
        .'It is meant to be the full treatment, not a reformat.'
    );
})->with(['ar', 'en']);

/*
|------------------------------------------------------------------------------
| Structure
|------------------------------------------------------------------------------
*/

it('emits a breadcrumb trail that matches the one on screen', function (string $locale) {
    $html = $this->get("/{$locale}/packages")->assertOk()->getContent();

    preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/su', $html, $match);

    expect($match)->not->toBeEmpty('No structured data on the page.');

    $graph = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);

    expect($graph)->toHaveKey('@graph');

    $breadcrumbs = collect($graph['@graph'])->firstWhere('@type', 'BreadcrumbList');

    expect($breadcrumbs)->not->toBeNull('No BreadcrumbList in the graph.');

    $items = $breadcrumbs['itemListElement'];

    expect($items)->toHaveCount(2);

    // Positions are one-based and consecutive.
    expect(array_column($items, 'position'))->toBe([1, 2]);

    expect($items[0]['name'])->toBe(__('nav.home', [], $locale));
    expect($items[0]['item'])->toBe(url("/{$locale}"));

    expect($items[1]['name'])->toBe(__('nav.packages', [], $locale));

    // The current page is named, never linked: a trail that links where you
    // already are is a loop.
    expect(array_key_exists('item', $items[1]))->toBeFalse();

    /*
     * And the markup must agree with the screen. Google treats a
     * BreadcrumbList that disagrees with the visible trail as a markup
     * problem, so checking the JSON in isolation would miss the failure that
     * actually matters.
     */
    preg_match('/<nav aria-label="'.preg_quote(__('common.breadcrumb', [], $locale), '/').'".*?<\/nav>/su', $html, $nav);

    expect($nav)->not->toBeEmpty('No breadcrumb nav rendered.');

    foreach ($items as $item) {
        expect(str_contains($nav[0], $item['name']))->toBeTrue(
            "«{$item['name']}» is in the breadcrumb data but not in the visible trail."
        );
    }
})->with(['ar', 'en']);

it('has exactly one h1', function (string $locale) {
    $html = $this->get("/{$locale}/packages")->assertOk()->getContent();

    expect(substr_count($html, '<h1'))->toBe(1);
    expect($html)->toContain(__('packages.title', [], $locale));
})->with(['ar', 'en']);

it('gives every active package a row in the comparison matrix', function (string $locale) {
    /*
     * The matrix is keyed by slug in the translation files, so a package added
     * to the database with no matching entry would render a column of em
     * dashes and nobody would notice until a patient did.
     */
    $matrix = __('packages.matrix', [], $locale);
    $rows = ['format', 'plan', 'between', 'labs', 'adjust', 'suits'];

    expect(Service::query()->where('is_active', true)->get())->not->toBeEmpty(
        'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
    );

    foreach (Service::query()->where('is_active', true)->get() as $service) {
        // array_key_exists rather than toHaveKey(): Pest reads a second
        // argument to toHaveKey as the EXPECTED VALUE at that key, not as a
        // failure message, so passing one silently asserts the entry equals
        // the message text.
        expect(array_key_exists($service->slug, $matrix))->toBeTrue(
            "The package «{$service->slug}» has no entry in packages.matrix for {$locale}."
        );

        foreach ($rows as $row) {
            expect($matrix[$service->slug])->toHaveKey($row);
            expect(trim((string) $matrix[$service->slug][$row]))->not->toBe('');
        }
    }
})->with(['ar', 'en']);

it('shows buying questions rather than the homepage general ones', function (string $locale) {
    $html = $this->get("/{$locale}/packages")->assertOk()->getContent();

    $buying = Faq::active()->category(Faq::CATEGORY_BUYING)->get();
    $general = Faq::active()->category(Faq::CATEGORY_GENERAL)->get();

    expect($buying)->not->toBeEmpty();

    foreach ($buying as $faq) {
        expect($html)->toContain($faq->getTranslation('question', $locale));
    }

    /*
     * And NOT the general ones. Someone on this page has already decided the
     * clinic does what she needs; re-answering "is it available online" here
     * would both waste her attention and duplicate the homepage.
     */
    foreach ($general as $faq) {
        expect(str_contains($html, $faq->getTranslation('question', $locale)))->toBeFalse(
            'A general FAQ has leaked onto the packages page.'
        );
    }
})->with(['ar', 'en']);

it('reads its prices from the services table rather than from copy', function () {
    /*
     * The comparison table and the homepage cards must never quote different
     * numbers at the same patient, which is only guaranteed if there is one
     * source. A price written into a translation file is a second source.
     */
    $html = $this->get('/ar/packages')->assertOk()->getContent();

    expect(Service::query()->where('is_active', true)->get())->not->toBeEmpty(
        'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
    );

    foreach (Service::query()->where('is_active', true)->get() as $service) {
        expect($html)->toContain(number_format((float) $service->price));
        expect($html)->toContain((string) $service->sessions_count);
    }

    foreach (['ar', 'en'] as $locale) {
        $flat = json_encode(__('packages', [], $locale), JSON_UNESCAPED_UNICODE);

        expect(Service::all())->not->toBeEmpty(
            'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
        );

        foreach (Service::all() as $service) {
            expect(str_contains((string) $flat, number_format((float) $service->price)))->toBeFalse(
                'A package price is hardcoded in the packages translation file. Read it from the model.'
            );
        }
    }
});

it('contains the wide table so it cannot widen the page', function () {
    // Renamed from .table-scroller: it no longer scrolls. overflow-x is `clip`
    // rather than `auto` because `auto` makes the element a scroll container,
    // which is what position:sticky resolves against — the column headers were
    // pinning to a box that scrolled away.
    /*
     * A REGRESSION GUARD FOR A BUG THAT DID NOT LOOK LIKE ONE.
     *
     * The comparison table is 789px wide inside a 350px wrapper. overflow-x
     * alone does not stop it widening the document: Chrome sizes the mobile
     * layout viewport from intrinsic width, and an overflow:auto box still
     * contributes its content's max-content width. The page went to 722px, the
     * body scrolled sideways, and in Arabic — where that overflow runs leftward
     * — every section below the table left the screen entirely.
     *
     * It hid because a section still waiting to be revealed carries a
     * transform, and a transformed box does contain its overflow. So the page
     * measured clean at the top and broke as soon as the reveal finished, and
     * broke immediately for anyone on reduced motion or without JavaScript,
     * where that transform never exists.
     *
     * This asserts the mechanism rather than the symptom, because the symptom
     * needs a browser. The browser check was run at 390px in both locales:
     * layout viewport 390, scrollX 0, and scrollWidth still greater than
     * clientWidth so the table itself continues to scroll.
     */
    $html = $this->get('/ar/packages')->assertOk()->getContent();

    expect($html)->toContain('table-frame');

    $css = file_get_contents(resource_path('css/app.css'));
    $position = strpos($css, '.table-frame {');

    expect($position)->not->toBeFalse('The .table-frame rule is gone.');

    $block = substr($css, $position, (int) strpos($css, '}', $position) - $position);

    expect($block)->toContain('overflow-x: clip');
    expect($block)->toContain(
        'contain: paint',
        // Left as the sole assertion it would read as a performance tweak, so
        // say what it is: without it the Arabic page pushes itself off screen.
    );
});

it('renders one presentation per breakpoint, never both at once', function () {
    /*
     * The same facts twice in the markup, with exactly one of them in the
     * document at a time. display:none rather than a visual-only hide, so a
     * screen reader is never offered the table AND the cards — hearing the
     * whole comparison twice is worse than not having it.
     */
    $html = $this->get('/ar/packages')->assertOk()->getContent();

    // The table exists only from lg up.
    expect($html)->toContain('table-frame');
    expect($html)->toMatch('/class="table-frame[^"]*\bhidden\b[^"]*\blg:block\b/');

    // The cards exist only below it.
    expect($html)->toMatch('/<ul class="[^"]*\blg:hidden\b/');

    /*
     * And the table must not be a horizontal scroller any more. Scrolling a
     * comparison sideways defeats what a comparison is for, and it is what
     * widened the mobile layout viewport. Verified in a browser at 390, 768
     * and 1440 in both locales: layout viewport equals the device width,
     * scrollX is 0, and there is no element in main with a horizontal
     * scrollbar at all.
     */
    expect(str_contains($html, 'overflow-x-auto'))->toBeFalse(
        'The comparison is a horizontal scroller again.'
    );
});

it('recommends the same package the homepage features', function () {
    /*
     * One source of truth. A patient who sees the monthly package featured on
     * the homepage and a different one recommended here has been told the
     * clinic does not know its own mind — and because both used to derive it
     * inline, nothing would have caught the drift.
     */
    $services = Service::query()->where('is_active', true)->orderBy('sort_order')->get();
    $slug = FeaturedPackage::slugIn($services);

    expect($slug)->not->toBeNull();

    $home = $this->get('/ar')->assertOk()->getContent();
    $page = $this->get('/ar/packages')->assertOk()->getContent();

    $name = $services->firstWhere('slug', $slug)->getTranslation('name', 'ar');

    // The homepage badge and the page's ribbon both sit beside that name.
    expect($home)->toContain(__('home.packages.featured', [], 'ar'));
    expect($page)->toContain(__('packages.comparison.recommended', [], 'ar'));
    expect($page)->toContain($name);

    // The recommendation is the cheaper of the two middle options — a default
    // answer that cost more would be a sales tactic rather than guidance.
    $prices = $services->pluck('price')->map(fn ($p): float => (float) $p)->sort()->values();
    $recommended = (float) $services->firstWhere('slug', $slug)->price;

    expect($recommended)->toBeLessThan(
        $prices->last(),
        'The recommended package is the most expensive one. That is a sales tactic, not guidance.'
    );
});

it('knows every way the matrix says no', function (string $locale) {
    /*
     * Two comparison rows carry a real yes/no and get a tick or a dash beside
     * the sentence. Which one is decided by matching the start of the value
     * against a list in the translation file — so if the copy is reworded to a
     * negation the list does not know about, the cell would quietly show a
     * tick next to the word "none".
     *
     * This makes that loud. Any value in a stateful row that reads as a
     * negation must start with a listed marker.
     */
    $matrix = __('packages.matrix', [], $locale);
    $markers = __('packages.comparison.absent_markers', [], $locale);

    expect($markers)->toBeArray()->not->toBeEmpty();

    $negativeWords = $locale === 'ar' ? ['مفيش', 'مافيش', 'لا يوجد'] : ['none', 'no follow-up', 'not included'];

    foreach ($matrix as $slug => $cells) {
        foreach (['between', 'adjust'] as $row) {
            $value = $cells[$row] ?? '';
            $lower = mb_strtolower($value);

            $readsNegative = false;

            foreach ($negativeWords as $word) {
                if (str_contains($lower, mb_strtolower($word))) {
                    $readsNegative = true;
                }
            }

            if (! $readsNegative) {
                continue;
            }

            $matched = false;

            foreach ($markers as $marker) {
                if (str_starts_with($value, $marker)) {
                    $matched = true;
                }
            }

            expect($matched)->toBeTrue(
                "«{$slug}.{$row}» in {$locale} reads as a negation but starts with none of the "
                ."absent_markers, so it would render with a tick:\n\n  {$value}"
            );
        }
    }
})->with(['ar', 'en']);

it('issues a bounded number of queries cold', function () {
    /*
     * THREE, and the third is the interesting one.
     *
     *   1. services      — the cached set the homepage also uses
     *   2. faqs (buying)  — its own cached set, not the homepage's general one
     *   3. working_hours  — read by ClinicSchema for the JSON-LD, UNCACHED
     *
     * I wrote this expecting two and the test corrected me. The third is the
     * same uncached schema query the homepage test found and documented: the
     * clinic graph hits working_hours on every request, and because nobody
     * ever looks at a JSON-LD block it goes unnoticed. It is now on two pages
     * rather than one, which is an argument for caching it — but that belongs
     * to whichever task owns the schema, not to this page, and pretending the
     * number is two would bury it.
     *
     * A fourth would mean something in a Blade file started touching the
     * database. Raise it only with a reason written here.
     */
    Cache::flush();

    $queries = [];

    DB::listen(function ($query) use (&$queries): void {
        $queries[] = $query->sql;
    });

    $this->get('/ar/packages')->assertOk();

    expect($queries)->toHaveCount(3, "Queries issued:\n".implode("\n", $queries));

    // And zero on a warm cache.
    $queries = [];

    $this->get('/en/packages')->assertOk();

    expect($queries)->toBeEmpty("Warm cache still queried:\n".implode("\n", $queries));
});
