<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\Specialty;
use App\Models\User;
use App\Support\ArticleBody;
use App\Support\Locales;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Support\Facades\Cache;

/**
 * WHAT A FINISHED ARTICLE ON THIS SITE HAS TO BE.
 *
 * The clinical gates live in BlogModuleTest and CitationGateTest. This file is
 * about the standard the fourteen were written to, and every assertion here
 * corresponds to something that would otherwise decay silently:
 *
 *   - An article half the length in English because somebody drafted in Arabic
 *     and translated the headings. Nobody reading the Arabic page would ever
 *     notice.
 *   - An internal link to a specialty that was renamed in the admin panel two
 *     years after the article was written. ArticleBody fails safe and renders
 *     the words as plain text, which is the right runtime behaviour and the
 *     wrong thing to discover in production.
 *   - PRACTITIONER_VOICE quietly filled in by whoever is next at the keyboard,
 *     because it looks like a gap and reads like an invitation. It is neither.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(PostSeeder::class);
});

/**
 * Body words as a READER counts them: link syntax resolved to its label, and
 * the two marker blocks removed, since neither is prose and neither publishes.
 */
function readerWords(Post $post, string $locale): int
{
    $body = ArticleBody::plain((string) $post->getTranslation('body', $locale, false));

    $body = (string) preg_replace(
        '/^('.implode('|', Post::markers()).'):.*$/mu',
        '',
        $body
    );

    return count(preg_split('/\s+/u', trim($body)) ?: []);
}

/*
|------------------------------------------------------------------------------
| Length, in both languages
|------------------------------------------------------------------------------
*/

it('writes every article to length in both locales', function (string $locale) {
    /*
     * 1,200–1,800 words. The floor is the point: below it the piece stops
     * being able to report guidance, explain a mechanism, correct a myth AND
     * sit in an Egyptian kitchen, and becomes a listicle with citations
     * stapled on.
     *
     * The ceiling matters less but is real — nobody finishes 2,500 words about
     * their own diagnosis on a phone.
     */
    foreach (Post::all() as $post) {
        $words = readerWords($post, $locale);

        expect($words)->toBeGreaterThanOrEqual(1200, "{$post->slug} ({$locale}) is only {$words} words.");
        expect($words)->toBeLessThanOrEqual(1800, "{$post->slug} ({$locale}) is {$words} words — too long to finish.");
    }
})->with(['ar', 'en']);

it('does not let one language quietly become the shorter one', function () {
    /*
     * Arabic runs naturally more compact than English for the same content, so
     * a ratio rather than a difference — and a generous one. What this catches
     * is a section written in one language and never carried into the other,
     * which is the likely shape of the mistake and is invisible from either
     * page on its own.
     */
    foreach (Post::all() as $post) {
        $ar = readerWords($post, 'ar');
        $en = readerWords($post, 'en');

        expect($ar / $en)->toBeGreaterThan(
            0.6,
            "{$post->slug}: the Arabic is {$ar} words against {$en} English. A section is missing from one of them."
        );
    }
});

/*
|------------------------------------------------------------------------------
| The two markers
|------------------------------------------------------------------------------
*/

it('leaves directed recommendations to the clinician, in both locales', function () {
    foreach (Post::all() as $post) {
        foreach (Locales::all() as $locale) {
            $body = (string) $post->getTranslation('body', $locale, false);

            expect(substr_count($body, Post::CLINICAL_MARKER))->toBeGreaterThan(
                0,
                "{$post->slug} ({$locale}) contains no CLINICAL_INPUT. Either it makes no directed "
                .'recommendation at all, or somebody answered one by guessing.'
            );
        }

        // The same prompts in both languages, or one page is answering a
        // question the other never asked.
        expect(substr_count((string) $post->getTranslation('body', 'ar', false), Post::CLINICAL_MARKER))
            ->toBe(
                substr_count((string) $post->getTranslation('body', 'en', false), Post::CLINICAL_MARKER),
                "{$post->slug} asks a different number of clinical questions in each language."
            );
    }
});

it('leaves every first-person clinical observation to the practitioner', function () {
    /*
     * THIS IS THE ONE THAT MATTERS MOST IN THIS FILE.
     *
     * PRACTITIONER_VOICE marks a sentence that is a claim about what one named
     * clinician has personally seen across her own patients. Writing one and
     * signing her name to it is inventing a professional memory — and it is
     * the easiest thing in the world to do convincingly, which is exactly why
     * it needs a gate rather than a note in a style guide.
     *
     * These sentences are also what makes the blog hers rather than a
     * competent translation of somebody else's.
     */
    foreach (Post::all() as $post) {
        foreach (Locales::all() as $locale) {
            expect(substr_count((string) $post->getTranslation('body', $locale, false), Post::PRACTITIONER_MARKER))
                ->toBeGreaterThan(
                    0,
                    "{$post->slug} ({$locale}) has no PRACTITIONER_VOICE prompt. Every one of these "
                    .'articles is published under her name and should carry at least one sentence '
                    .'only she can say.'
                );
        }
    }
});

it('refuses to publish an article still waiting for her own words', function (string $locale) {
    $post = Post::query()->where('slug', 'why-we-quit-in-week-three')->firstOrFail();

    // Answer the clinical prompts, leave the practitioner's own sentence.
    foreach (Locales::all() as $each) {
        $post->setTranslation('body', $each, str_replace(
            Post::CLINICAL_MARKER,
            'answered',
            (string) $post->getTranslation('body', $each, false)
        ));
    }

    $doctor = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->firstOrFail();
    $post->reviewed_by = $doctor->id;
    $post->reviewed_at = now();
    $post->published_at = now();

    expect(fn () => $post->save())
        ->toThrow(LogicException::class, 'waiting for');

    expect($locale)->toBeIn(Locales::all());
})->with(['ar', 'en']);

it('never renders either marker to a reader', function () {
    /*
     * Belt and braces on top of the save-time gate. If one ever reached a live
     * page it would look like advice and be a stage direction.
     */
    foreach (['articles', 'home'] as $route) {
        $html = $this->get(route($route, ['locale' => 'ar']))->assertOk()->getContent();

        foreach (Post::markers() as $marker) {
            expect(str_contains($html, $marker))->toBeFalse("«{$marker}» is on the {$route} page.");
        }
    }
});

/*
|------------------------------------------------------------------------------
| Internal links
|------------------------------------------------------------------------------
*/

it('resolves every internal link in every article', function () {
    /*
     * ArticleBody fails safe: an unknown destination renders as its label in
     * plain text, so a dead link never reaches a reader as a broken anchor.
     * That is the right runtime behaviour and precisely why this test has to
     * exist — without it, "fails safe" quietly becomes "drops links nobody
     * noticed", and an article that was supposed to send somebody to the PCOS
     * service simply stops doing so.
     *
     * Article targets are checked against the posts TABLE rather than through
     * ArticleBody::resolve(), which only links published articles. All fourteen
     * are drafts, so every cross-link between them would look dead here and
     * would be live the day they publish. What is being caught is a TYPO.
     */
    /*
     * route('booking') carries a {locale} parameter that SetLocale supplies
     * through URL::defaults() on every real request. Nothing has made a
     * request here, so it is set explicitly — otherwise this test fails on
     * plumbing rather than on a bad link.
     */
    Locales::applyToUrlGenerator('ar');

    $specialties = Specialty::query()->pluck('slug')->all();
    $articles = Post::query()->pluck('slug')->all();

    $found = 0;

    foreach (Post::all() as $post) {
        foreach (Locales::all() as $locale) {
            foreach (ArticleBody::links((string) $post->getTranslation('body', $locale, false)) as $link) {
                $found++;

                $where = "{$post->slug} ({$locale})";

                if ($link['type'] === 'specialty') {
                    expect($link['slug'])->toBeIn($specialties, "{$where} links to a specialty that does not exist: {$link['slug']}");

                    continue;
                }

                if ($link['type'] === 'article') {
                    expect($link['slug'])->toBeIn($articles, "{$where} links to an article that does not exist: {$link['slug']}");

                    continue;
                }

                expect(ArticleBody::resolve($link['type'], $link['slug']))
                    ->not->toBeNull("{$where} has a link this application cannot resolve: {$link['type']}");

                expect($link['label'])->not->toBeEmpty("{$where} has a link with no text.");
            }
        }
    }

    expect($found)->toBeGreaterThan(50, 'The articles barely link to anything.');
});

it('sends every article to the booking page and to a specialty', function () {
    /*
     * Not decoration. Somebody who has read 1,300 words about her own
     * condition is the most likely person on the site to want an appointment,
     * and making her go and find the menu is the whole conversion funnel
     * failing at its last step.
     */
    foreach (Post::all() as $post) {
        $types = collect(ArticleBody::links((string) $post->getTranslation('body', 'ar', false)))
            ->pluck('type');

        /*
         * contains() rather than toContain(): Pest reads a second argument to
         * toContain() as ANOTHER NEEDLE, not as a failure message, so the
         * obvious spelling silently asserts that the collection contains the
         * message text too — and then fails saying exactly that.
         */
        expect($types->contains('booking'))->toBeTrue("{$post->slug} never links to booking.");
        expect($types->contains('specialty'))->toBeTrue("{$post->slug} never links to a specialty.");
    }
});

it('cannot be made to emit a link to another site', function () {
    /*
     * The grammar has no way to express an external URL, and that is the
     * point of having a grammar rather than allowing markup. Checked rather
     * than asserted in a comment, because the day somebody adds a `[[url:…]]`
     * type "just for the references" is the day an editor can put anything on
     * a medical page.
     */
    $attempts = [
        '[[url:https://example.com|click]]',
        '[[external:https://example.com|click]]',
        '[[booking:javascript:alert(1)|click]]',
        '[[specialty:../../etc/passwd|click]]',
    ];

    foreach ($attempts as $attempt) {
        $html = (string) ArticleBody::render($attempt);

        /*
         * WHAT IS BEING ASSERTED IS "NOT CLICKABLE", not "the characters are
         * absent". None of these match the grammar at all, so each is rendered
         * as escaped literal text — «example.com» does appear on the page, as
         * inert words in a paragraph, exactly as it would if somebody had
         * typed the host into a sentence. That is fine. An anchor is not.
         */
        expect(str_contains($html, '<a '))->toBeFalse("«{$attempt}» produced an anchor.");
        expect(str_contains($html, 'href'))->toBeFalse("«{$attempt}» produced an href.");
        expect(str_contains($html, '<'))->toBeFalse("«{$attempt}» produced markup of any kind.");
    }
});

it('escapes article text rather than trusting it', function () {
    $html = (string) ArticleBody::render('A paragraph with <script>alert(1)</script> in it.');

    expect(str_contains($html, '<script>'))->toBeFalse('Markup in a body reached the page.');
    expect($html)->toContain('&lt;script&gt;');
});

/*
|------------------------------------------------------------------------------
| Structure
|------------------------------------------------------------------------------
*/

it('gives every article headings, an excerpt and a cover', function () {
    foreach (Post::all() as $post) {
        expect($post->cover_path)->not->toBeNull("{$post->slug} has no cover.");
        expect($post->category_id)->not->toBeNull("{$post->slug} has no category.");
        expect($post->tags)->not->toBeEmpty("{$post->slug} has no tags.");

        foreach (Locales::all() as $locale) {
            $body = (string) $post->getTranslation('body', $locale, false);

            expect(substr_count($body, "\n## "))->toBeGreaterThanOrEqual(
                5,
                "{$post->slug} ({$locale}) has fewer than five sections."
            );

            expect(trim((string) $post->getTranslation('excerpt', $locale, false)))
                ->not->toBeEmpty("{$post->slug} ({$locale}) has no excerpt.");

            expect(trim((string) $post->getTranslation('title', $locale, false)))
                ->not->toBeEmpty("{$post->slug} ({$locale}) has no title.");
        }
    }
});

it('estimates reading time from the words a reader actually reads', function () {
    /*
     * This used to be wrong, and wrong in a way nobody would have caught by
     * looking at a page. str_word_count() is byte-based; handed UTF-8 Arabic
     * it reported about 55% more words than exist, and the old code took the
     * LARGER of it and the whitespace count — so the inflated figure always
     * won and every Arabic article overstated its own reading time by minutes.
     */
    foreach (Post::all() as $post) {
        $expected = (int) ceil(readerWords($post, 'ar') / 180);

        expect($post->reading_minutes)->toBeGreaterThanOrEqual(
            $expected - 1,
            "{$post->slug} claims {$post->reading_minutes} minutes for roughly {$expected}."
        );

        expect($post->reading_minutes)->toBeLessThanOrEqual(
            $expected + 1,
            "{$post->slug} claims {$post->reading_minutes} minutes for roughly {$expected}."
        );
    }
});
