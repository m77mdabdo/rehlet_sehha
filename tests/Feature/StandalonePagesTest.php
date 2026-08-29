<?php

declare(strict_types=1);

use App\Models\Faq;
use App\Models\Post;
use App\Models\Specialty;
use Database\Seeders\FaqSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The seven standalone pages, checked against the rules that made them worth
 * building at all.
 *
 * Seeded selectively rather than with a bare seed(): seeding videos queues a
 * thumbnail fetch per row through dispatchAfterResponse, and with no request
 * in flight those callbacks all fire on the FIRST request a test makes, which
 * lands inside any query-count measurement.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(FaqSeeder::class);
    $this->seed(PostSeeder::class);

    /*
     * PostSeeder seeds DRAFTS. It has to: those three articles are placeholder
     * clinical prose nobody reviewed, and the model refuses to publish an
     * article without a named clinical reviewer.
     *
     * That left every loop over Post::published() in this file running zero
     * times — the articles catalogue was empty and no article page was ever
     * fetched. So one genuinely publishable article is created here, with a
     * reviewer, which is what a published article actually looks like.
     */
    Post::factory()->create([
        'slug' => 'a-published-article',
        'title' => ['ar' => 'مقال منشور للاختبار', 'en' => 'A published article'],
        'category' => ['ar' => 'تغذية', 'en' => 'Nutrition'],
    ]);
});

/** Every standalone page, and the homepage section it must not restate. */
function standalonePages(): array
{
    return [
        'services' => ['path' => 'services', 'section' => 'specialties', 'nav' => 'nav.services'],
        'how-it-works' => ['path' => 'how-it-works', 'section' => 'how-it-works', 'nav' => 'nav.how_it_works'],
        'about' => ['path' => 'about', 'section' => 'about', 'nav' => 'nav.about'],
        'articles' => ['path' => 'articles', 'section' => 'articles', 'nav' => 'nav.articles'],
        'faq' => ['path' => 'faq', 'section' => 'faq', 'nav' => 'nav.faq'],
        'contact' => ['path' => 'contact', 'section' => 'contact', 'nav' => 'nav.contact'],
    ];
}

/**
 * The strings a page and its homepage section are SUPPOSED to share, because
 * both are describing the same records.
 *
 * @return list<string>
 */
function catalogueFor(string $page, string $locale): array
{
    $entries = [];

    if ($page === 'articles') {
        expect(Post::published()->get())->not->toBeEmpty(
            'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
        );

        foreach (Post::published()->get() as $post) {
            foreach (['title', 'excerpt', 'category'] as $field) {
                $entries[] = (string) $post->getTranslation($field, $locale, false);
            }
        }
    }

    if ($page === 'services') {
        expect(Specialty::where('is_active', true)->get())->not->toBeEmpty(
            'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
        );

        foreach (Specialty::where('is_active', true)->get() as $specialty) {
            foreach (['name', 'description'] as $field) {
                $entries[] = (string) $specialty->getTranslation($field, $locale, false);
            }
        }
    }

    if ($page === 'faq') {
        expect(Faq::where('is_active', true)->get())->not->toBeEmpty(
            'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
        );

        foreach (Faq::where('is_active', true)->get() as $faq) {
            foreach (['question', 'answer'] as $field) {
                $entries[] = (string) $faq->getTranslation($field, $locale, false);
            }
        }
    }

    if ($page === 'about') {
        // The same facts about the same person, correctly repeated.
        $entries[] = (string) __('about.name', [], $locale);
        $entries[] = (string) __('about.title', [], $locale);
        $entries[] = (string) __('about.philosophy', [], $locale);
        $entries[] = (string) __('about.registration', [], $locale);

        /*
         * The credentials themselves, straight from the record they are both
         * rendered from.
         *
         * This block used to read a translation key, `about.credentials`, that
         * no longer exists — and a missing key does not fail, it returns its
         * own name, so the catalogue was silently being handed the literal
         * string "about.credentials" and the real credentials were never
         * excluded at all. Reading config means the entry cannot go stale
         * without the page going stale with it.
         */
        $entries[] = (string) config('clinic.practitioner.degree_ar');
        $entries[] = (string) config('clinic.practitioner.licence_body_ar');
        $entries[] = (string) config('clinic.practitioner.licence_number');

        /*
         * And the reserved-photograph caption. It is one UI string shown by
         * one component in both places — the empty state for a portrait that
         * has not been supplied — so counting it measures the component, not
         * anybody restating prose.
         */
        $entries[] = (string) __('about.portrait_pending', [], $locale);
        $entries[] = (string) __('about.portrait_pending_title', [], $locale);
    }

    if ($page === 'contact') {
        $entries[] = (string) config('clinic.contact.address.'.$locale);
        $entries[] = (string) config('clinic.contact.phone_display');
        $entries[] = (string) config('clinic.contact.email');
    }

    return $entries;
}

it('answers in both locales', function (string $locale) {
    foreach (standalonePages() as $name => $page) {
        $this->get("/{$locale}/{$page['path']}")->assertOk();
    }

    // And every published article.
    expect(Post::published()->get())->not->toBeEmpty(
        'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
    );

    foreach (Post::published()->get() as $post) {
        $this->get("/{$locale}/articles/{$post->slug}")->assertOk();
    }
})->with(['ar', 'en']);

it('gives every page its own title and description', function (string $locale) {
    $titles = [];

    foreach (standalonePages() as $name => $page) {
        $html = $this->get("/{$locale}/{$page['path']}")->assertOk()->getContent();

        preg_match('/<title>(.*?)<\/title>/su', $html, $match);

        expect($match)->not->toBeEmpty("{$name} has no title.");

        /*
         * Unique, and never the homepage default. Two pages sharing a title
         * are indistinguishable to a search engine reading nothing but the
         * head, which is most of what it reads.
         */
        expect(in_array($match[1], $titles, true))->toBeFalse("{$name} reuses the title «{$match[1]}».");
        expect($match[1])->not->toBe(__('home.meta_title', [], $locale));

        $titles[] = $match[1];

        expect($html)->toContain('rel="canonical" href="'.url("/{$locale}/{$page['path']}").'"');
        expect($html)->toContain('hreflang="x-default"');
    }
})->with(['ar', 'en']);

it('has exactly one h1 on every page', function (string $locale) {
    foreach (standalonePages() as $name => $page) {
        $html = $this->get("/{$locale}/{$page['path']}")->assertOk()->getContent();

        expect(substr_count($html, '<h1'))->toBe(1, "{$name} does not have exactly one h1.");
    }
})->with(['ar', 'en']);

it('emits a breadcrumb that matches the one on screen', function (string $locale) {
    foreach (standalonePages() as $name => $page) {
        $html = $this->get("/{$locale}/{$page['path']}")->assertOk()->getContent();

        preg_match('/<script type="application\/ld\+json">(.*?)<\/script>/su', $html, $match);

        expect($match)->not->toBeEmpty("{$name} has no structured data.");

        $graph = json_decode(html_entity_decode($match[1], ENT_QUOTES | ENT_HTML5, 'UTF-8'), true, 512, JSON_THROW_ON_ERROR);
        $breadcrumbs = collect($graph['@graph'])->firstWhere('@type', 'BreadcrumbList');

        expect($breadcrumbs)->not->toBeNull("{$name} has no BreadcrumbList.");

        $items = $breadcrumbs['itemListElement'];

        expect(array_column($items, 'position'))->toBe(range(1, count($items)));

        // The last item is the current page: named, never linked.
        expect(array_key_exists('item', $items[count($items) - 1]))->toBeFalse();

        // And the visible trail says the same thing.
        preg_match('/<nav aria-label="'.preg_quote(__('common.breadcrumb', [], $locale), '/').'".*?<\/nav>/su', $html, $nav);

        expect($nav)->not->toBeEmpty("{$name} has no visible breadcrumb.");

        foreach ($items as $item) {
            expect(str_contains($nav[0], $item['name']))->toBeTrue(
                "«{$item['name']}» is in {$name}'s breadcrumb data but not on screen."
            );
        }
    }
})->with(['ar', 'en']);

/*
|------------------------------------------------------------------------------
| The rule that matters most
|------------------------------------------------------------------------------
*/

it('does not restate its homepage section', function (string $locale) {
    /*
     * Same method and same threshold as the packages page: five-word runs,
     * measured against the SECTION as denominator, catalogue strings removed
     * first because a summary and a detail page naming the same product is
     * correct rather than duplication. See PackagesPageTest for the full
     * argument behind each of those three choices.
     */
    $threshold = 20.0;

    $home = $this->get("/{$locale}")->assertOk()->getContent();

    foreach (standalonePages() as $name => $page) {
        if (preg_match('/<section id="'.preg_quote($page['section'], '/').'".*?<\/section>/su', $home, $match) !== 1) {
            continue;
        }

        /*
         * TWO KINDS OF TEXT ARE REMOVED BEFORE COMPARING, and both for the
         * same reason the packages page removes its catalogue: they are
         * SUPPOSED to appear in both places, so counting them measures the
         * schema working rather than anybody duplicating prose.
         *
         *   PLACEHOLDERS. The about section and the about page are both still
         *   largely TODO_COPY. Comparing them measured a 63% overlap of
         *   identical markers — a real number about nothing.
         *
         *   SHARED FACTS. A homepage strip listing three articles and an index
         *   listing the same three necessarily share those three titles. So do
         *   the six general questions the homepage shows and the twelve the FAQ
         *   page groups. A practitioner's name and credentials are the same
         *   facts wherever they appear. None of that is duplication; a summary
         *   naming the same things as its detail page is the summary working.
         *
         * What is left after both is prose, which is the thing that has to
         * differ, and it is what the threshold is applied to.
         */
        $catalogue = catalogueFor($name, $locale);

        $strip = function (string $text) use ($catalogue): string {
            /*
             * CATALOGUE FIRST, PLACEHOLDERS SECOND. The order is not cosmetic.
             *
             * The placeholder regex clears one SENTENCE around the marker, and
             * the philosophy placeholder is two sentences. Running it first
             * chewed the front off a string that the catalogue was about to
             * remove whole, so the exact match failed and the placeholder's
             * own tail — «من ٤٠ لـ ٦٠ كلمة، بنفس نبرة باقي الموقع» — was
             * counted as prose duplicated between the page and the homepage.
             *
             * Two strips that overlap have to run widest-first or they fight.
             */
            foreach ($catalogue as $entry) {
                if (trim($entry) !== '') {
                    $text = str_replace($entry, ' ', $text);
                }
            }

            $text = (string) preg_replace('/[^.!؟?\n]*TODO_COPY[^.!؟?\n]*/u', ' ', $text);

            return trim((string) preg_replace('/\s+/u', ' ', $text));
        };

        $pageHtml = $this->get("/{$locale}/{$page['path']}")->assertOk()->getContent();

        $sectionGrams = pageShingles($strip(pageVisibleText($match[0])), 5);
        $pageGrams = pageShingles($strip(pageVisibleText($pageHtml)), 5);

        /*
         * A PAGE STILL WAITING ON COPY CANNOT BE MEASURED, and must not
         * silently stay unmeasured.
         *
         * THIS USED TO TEST FOR THE MARKER, and that was too blunt. About was
         * almost entirely TODO_COPY when it was written, so «has a marker» and
         * «has nothing to measure» were the same condition. They stopped being
         * the same the moment real credentials replaced most of the
         * placeholders: the page carried 302 five-word runs of genuine prose
         * and was still being skipped whole, because ONE paragraph — the
         * philosophy, which has to be in the practitioner's own voice and
         * cannot be invented — still held a marker.
         *
         * So the condition is now what it always meant: is there enough real
         * prose LEFT, after the placeholders are stripped, to compare? A page
         * that is genuinely still a skeleton has almost none and is skipped; a
         * page waiting on one last paragraph is measured like every other.
         *
         * The exemption still removes itself, and now it removes itself in
         * proportion rather than all at once.
         */
        if (count($pageGrams) < 40) {
            continue;
        }

        /*
         * And a section that is ITSELF nothing but catalogue cannot be
         * duplicated. The homepage FAQ strip is six questions and a heading;
         * once the questions are excluded as shared records there is no prose
         * left in it to restate.
         *
         * The guard against that becoming a loophole is that the PAGE must
         * still have said something substantial of its own.
         */
        if (count($sectionGrams) < 15) {
            expect(count($pageGrams))->toBeGreaterThan(
                40,
                "The homepage #{$page['section']} section has no prose to compare against, and "
                ."{$name} has almost none of its own either. The page is empty."
            );

            continue;
        }

        $shared = array_intersect_key($sectionGrams, $pageGrams);
        $overlap = 100 * count($shared) / count($sectionGrams);

        expect($overlap)->toBeLessThan(
            $threshold,
            sprintf(
                "%s restates %.1f%% of the homepage #%s section in five-word runs (%d of %d).\n"
                ."The section summarises; the page has to say something it does not.\n"
                .'Shared: %s',
                $name, $overlap, $page['section'], count($shared), count($sectionGrams),
                implode(' | ', array_slice(array_keys($shared), 0, 4)) ?: '(none)',
            ),
        );
    }
})->with(['ar', 'en']);

/*
|------------------------------------------------------------------------------
| Images
|------------------------------------------------------------------------------
*/

it('gives every image a real alt, a size, and lazy loading', function (string $locale) {
    /*
     * COUNTED ACROSS ALL PAGES, not asserted per page.
     *
     * Several of these pages carry no photograph at all and are supposed to:
     * FAQ and contact have none, and the four specialties without an image get
     * none by rule. So "this page has images" is the wrong assertion.
     *
     * What must be true is that the sweep examined SOME images somewhere. It
     * did not: a non-empty guard added here failed immediately, which is how
     * the emptiness was found. Without the total, every assertion in the loop
     * was being skipped on every page and the test passed regardless.
     */
    $examined = 0;

    foreach (standalonePages() as $name => $page) {
        $html = $this->get("/{$locale}/{$page['path']}")->assertOk()->getContent();

        preg_match_all('/<img\b[^>]*>/s', $html, $images);

        $examined += count($images[0]);

        foreach ($images[0] as $tag) {
            // The logo and other decorative marks are SVG, so anything that
            // reaches here is a photograph and must carry all three.
            expect(preg_match('/\swidth="\d+"/', $tag))->toBe(1, "{$name}: an image has no width, which is a layout shift.");
            expect(preg_match('/\sheight="\d+"/', $tag))->toBe(1, "{$name}: an image has no height, which is a layout shift.");
            expect(preg_match('/\salt="[^"]/', $tag))->toBe(1, "{$name}: an image has an empty alt.");

            /*
             * The alt must not be the page title. That is the single most
             * common way an alt gets written and it tells a blind reader
             * nothing she did not already have from the heading.
             */
            preg_match('/\salt="([^"]*)"/', $tag, $alt);

            expect(trim($alt[1]))->not->toBe(__($page['nav'], [], $locale));
        }

        // Exactly one eager image per page at most: the first above the fold.
        expect(substr_count($html, 'loading="eager"'))->toBeLessThanOrEqual(
            1,
            "{$name} loads more than one image eagerly."
        );
    }

    expect($examined)->toBeGreaterThan(
        0,
        "No <img> was found on any standalone page in {$locale}, so every "
        .'attribute assertion above was skipped. Either the pages have lost '
        .'their photography or the extraction no longer matches how they emit it.'
    );
})->with(['ar', 'en']);

it('places no image on a section that has none to illustrate it', function () {
    /*
     * Four of the eight clinical areas have no photograph in the library, and
     * under the placement rule they get none rather than a food picture
     * stretched to cover PCOS. This asserts the absence, because the failure
     * mode is somebody helpfully filling the gap.
     */
    $html = $this->get('/ar/services')->assertOk()->getContent();

    $withPhotos = ['medical-nutrition', 'weight-management', 'pregnancy-nutrition', 'child-nutrition'];

    expect(Specialty::where('is_active', true)->get())->not->toBeEmpty(
        'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
    );

    foreach (Specialty::where('is_active', true)->get() as $specialty) {
        preg_match('/<section id="'.preg_quote($specialty->slug, '/').'".*?<\/section>/su', $html, $match);

        if ($match === []) {
            continue;
        }

        $hasImage = str_contains($match[0], '<img');

        expect($hasImage)->toBe(
            in_array($specialty->slug, $withPhotos, true),
            $hasImage
                ? "«{$specialty->slug}» has an image but nothing in the library illustrates it."
                : "«{$specialty->slug}» lost the image that illustrates it."
        );
    }
});

it('reserves space for the photographs the clinic has not supplied yet', function (string $locale) {
    /*
     * About and Contact are waiting on real photographs of the practitioner
     * and the clinic. Until they arrive the frames hold their exact shape —
     * never a stock stand-in, which on a page about who will be treating you
     * is a false claim a patient cannot check, and never a broken frame.
     */
    /*
     * Only ABOUT now. The contact page used to reserve a frame for a
     * photograph of the clinic interior; there is no interior, because the
     * practice is online. A frame waiting for a picture of premises that do
     * not exist is a promise nobody can keep.
     */
    foreach (['about'] as $path) {
        $html = $this->get("/{$locale}/{$path}")->assertOk()->getContent();

        // str_contains rather than toContain(): Pest reads a second argument
        // to toContain as another needle, not as a failure message.
        expect(str_contains($html, 'aspect-'))->toBeTrue("{$path} reserves no space for a photograph.");

        // And no stock photograph has crept into either.
        expect(str_contains($html, '/media/'))->toBeFalse(
            "{$path} is showing a stock photograph where the clinic's own belongs."
        );
    }
})->with(['ar', 'en']);

/*
|------------------------------------------------------------------------------
| Cost
|------------------------------------------------------------------------------
*/

/**
 * The one published article, created in beforeEach.
 *
 * PostSeeder's three articles are DRAFTS — placeholder clinical prose nobody
 * reviewed cannot be published under a practitioner's byline — so beforeEach
 * adds a single properly reviewed one. Every loop over Post::published() in
 * this file depends on it existing, and so do the two tests that fetch an
 * article page directly.
 *
 * It is FETCHED rather than created here: creating a second one would collide
 * on the unique slug, and more importantly the article the page tests exercise
 * should be the same article the catalogue and duplicate-content checks see.
 */
function liveArticle(): Post
{
    return Post::query()->where('slug', 'a-published-article')->sole();
}

it('issues a bounded number of queries on every page', function () {
    /*
     * Every page pays for the cached sets it reads and nothing else. The
     * working_hours query on all of them is the uncached read behind the
     * clinic JSON-LD, which the homepage test also found — it is now on eight
     * pages, which is a stronger argument for caching it than it was on one,
     * and it belongs to whichever task owns the schema.
     *
     * A higher number means something in a Blade file started touching the
     * database. Raise one only with a reason written here.
     *
     * MEASURED IN ITS OWN SCOPE. An earlier version registered a fresh
     * DB::listen inside the loop while reassigning the same by-reference
     * variable, so every listener wrote into one array and the counts grew
     * with each iteration. Each page now gets a closure of its own.
     */
    $count = function (string $path): array {
        Cache::flush();

        $queries = [];

        DB::listen(function ($query) use (&$queries): void {
            $queries[] = $query->sql;
        });

        test()->get($path)->assertOk();

        return $queries;
    };

    $bounds = [
        // specialties + services (footer) + working_hours (JSON-LD)
        '/ar/services' => 3,
        // services + working_hours
        '/ar/how-it-works' => 2,
        '/ar/about' => 2,
        // posts + services + working_hours
        '/ar/articles' => 3,
        // faqs + services + working_hours
        '/ar/faq' => 3,
        // working_hours + services
        '/ar/contact' => 2,
        /*
         * The post, its REVIEWER, the cached set for related, services and
         * working_hours.
         *
         * Raised from 4 to 5 when the clinical-review byline landed. The extra
         * query is the reviewer lookup, and it is eager-loaded rather than
         * lazy so it stays one query rather than one per article.
         *
         * It is not avoidable by storing the reviewer's name on the post: a
         * denormalised name is how a byline ends up naming somebody by a title
         * they no longer hold, on an article about a condition, under a
         * licence. One join is the cheaper mistake.
         */
        '/ar/articles/'.liveArticle()->slug => 5,
    ];

    foreach ($bounds as $path => $expected) {
        $queries = $count($path);

        expect($queries)->toHaveCount(
            $expected,
            "{$path} issued ".count($queries)." queries, expected {$expected}:\n".implode("\n", $queries)
        );
    }
});

it('does not offer a contact form', function (string $locale) {
    /*
     * Deliberate, and the page says so. A patient who fills in a "get in
     * touch" box has done something that feels like progress and is not, and
     * then waits — for a reply that competes with the booking she wanted.
     *
     * And no embedded map: a third-party request on a site built not to track
     * its visitors, to draw an address we render as text.
     */
    $html = $this->get("/{$locale}/contact")->assertOk()->getContent();

    expect(preg_match('/<form\b/', $html))->toBe(0, 'The contact page has a form on it.');
    expect(str_contains($html, 'google.com/maps'))->toBeFalse('The contact page embeds a map.');
    expect(str_contains($html, '<iframe'))->toBeFalse('The contact page embeds a third party.');

    /*
     * AND NO ADDRESS EITHER. The practice is online and has no premises, so
     * this page says where sessions happen — the platform list from config —
     * rather than naming a place that does not exist.
     */
    expect(str_contains($html, '<address'))->toBeFalse('The contact page renders an address for a practice with no premises.');

    expect(config('clinic.platforms'))->not->toBeEmpty(
        'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
    );

    foreach (config('clinic.platforms') as $platform) {
        expect($html)->toContain(__("contact.platforms.{$platform}", [], $locale));
    }
})->with(['ar', 'en']);

it('keeps the practitioner page honest about what it does not know', function () {
    /*
     * ABOUT IS STILL WAITING ON REAL COPY and must stay that way. Credentials,
     * a university and a registration number are claims about a real person's
     * qualifications — the structure is ours to design, the facts are not ours
     * to invent. clinic:verify-copy blocks production until the clinic answers.
     */
    $html = $this->get('/ar/about')->assertOk()->getContent();

    expect($html)->toContain('TODO_COPY');
});

it('lists articles without pretending there are more than there are', function () {
    /*
     * Three published posts, so a list. Filters and pagination over three
     * items are scaffolding for content that does not exist, and a dropdown
     * with one item behind it announces the emptiness more loudly than three
     * honest entries.
     */
    $html = $this->get('/ar/articles')->assertOk()->getContent();

    expect(Post::published()->get())->not->toBeEmpty(
        'A loop that never runs is a test that lies: this collection was empty, so every assertion inside the loop below was skipped and the test passed without checking anything.'
    );

    foreach (Post::published()->get() as $post) {
        expect($html)->toContain($post->getTranslation('title', 'ar'));
    }

    expect(str_contains($html, 'rel="next"'))->toBeFalse('The article list is paginated over three items.');
    expect(preg_match('/<select\b/', $html))->toBe(0, 'The article list has a filter over three items.');
});

it('shares without handing a third party the reading history', function () {
    /*
     * A wa.me link and a copy button. No Facebook SDK, no Twitter widget, no
     * share-count service — each of those is a script from another company
     * that learns which article a patient read, on a site whose position is
     * that it does not do that.
     */
    $html = $this->get('/ar/articles/'.liveArticle()->slug)->assertOk()->getContent();

    expect($html)->toContain('wa.me');
    expect($html)->toContain('data-copy=');

    foreach (['facebook.net', 'platform.twitter', 'addthis', 'sharethis', 'connect.facebook'] as $tracker) {
        expect(str_contains($html, $tracker))->toBeFalse("The article page loads {$tracker}.");
    }
});
