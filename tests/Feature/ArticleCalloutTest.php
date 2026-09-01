<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use App\Support\ArticleBody;
use App\Support\Locales;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\TagSeeder;

/**
 * THE CALLOUT IS THE THIRD AND LAST THING THE BODY COLUMN CAN SAY.
 *
 * The column is plain text and stays that way — an editor pasting from a word
 * processor into a medical page must not be able to inject markup — so the only
 * structure available is a line prefix. There are three: `## ` for a heading,
 * `> ` for a callout, and the two marker prefixes for a gap that has not been
 * answered yet.
 *
 * This file covers the one that a reader actually sees. It exists because the
 * callout carries something specific: a paragraph that is a TOOL rather than an
 * argument, meant to be taken out of the article and used on the next claim.
 * The three questions in pcos-and-food-judging-a-claim are the practitioner's
 * own and are, in her framing, the most reusable thing on the blog. A tool set
 * in the same type as the paragraph arguing with the last claim is a tool
 * nobody notices they were handed.
 *
 * The three assertions that matter: the prefixes never reach the reader as
 * characters, the block renders as a block rather than as five loose lines, and
 * the grammar cannot be used to smuggle markup in — because a convention that
 * escapes its own content less carefully than the paragraph renderer would be a
 * hole in the reason the column is plain text at all.
 */
beforeEach(function () {
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(PostSeeder::class);
});

/**
 * The article, published, because the seeder leaves every piece a draft and a
 * draft is a 404.
 *
 * Satisfying the gates rather than bypassing them: scopePublished() wants a
 * named reviewer, a review date and no unverified reference, so all three are
 * supplied. Whether those gates are RIGHT is CitationGateTest's subject; this
 * file only needs the page to render.
 */
function calloutArticle(): Post
{
    $post = Post::query()->where('slug', 'pcos-and-food-judging-a-claim')->firstOrFail();

    $reviewer = User::factory()->create();

    $post->citations()->update([
        'verified_by' => $reviewer->id,
        'verified_at' => now(),
    ]);

    $post->forceFill([
        'reviewed_by' => $reviewer->id,
        'reviewed_at' => now(),
        'published_at' => now(),
    ])->saveQuietly();

    return $post;
}

/*
|------------------------------------------------------------------------------
| The grammar
|------------------------------------------------------------------------------
*/

it('recognises a callout and leaves an ordinary paragraph alone', function () {
    expect(ArticleBody::isCallout('> A tool.'))->toBeTrue();
    expect(ArticleBody::isCallout(">Tight against the angle bracket.\n> Second line."))->toBeTrue();

    expect(ArticleBody::isCallout('An ordinary paragraph.'))->toBeFalse();
    expect(ArticleBody::isCallout('## A heading'))->toBeFalse();

    // A `>` in the MIDDLE of a paragraph is arithmetic or a quotation mark, not
    // structure. Only the first character of the block opens a callout.
    expect(ArticleBody::isCallout('Insulin runs > usual.'))->toBeFalse();
});

it('groups adjacent list lines into one run and keeps the prose around them', function () {
    $pieces = ArticleBody::callout(<<<'TXT'
    > Ask three questions:
    > - One?
    > - Two?
    > - Three?
    > If the answer is not clear, look again.
    TXT);

    expect($pieces)->toHaveCount(3);

    expect($pieces[0])->toBe(['type' => 'text', 'text' => 'Ask three questions:']);
    expect($pieces[1])->toBe(['type' => 'list', 'items' => ['One?', 'Two?', 'Three?']]);
    expect($pieces[2])->toBe(['type' => 'text', 'text' => 'If the answer is not clear, look again.']);
});

it('starts a second run when prose interrupts the list', function () {
    /*
     * Adjacency is the rule, not "every item in the block". Otherwise a callout
     * with two separate short lists would silently merge them into one, and the
     * sentence between them would jump to the end.
     */
    $pieces = ArticleBody::callout("> - One\n> Then this.\n> - Two");

    expect(array_column($pieces, 'type'))->toBe(['list', 'text', 'list']);
});

/*
|------------------------------------------------------------------------------
| What reaches the page
|------------------------------------------------------------------------------
*/

it('renders the practitioner questions as a block, in both locales', function (string $locale) {
    $post = calloutArticle();

    $html = $this->get("/{$locale}/articles/{$post->slug}")->assertOk()->getContent();

    // The container, and the accent edge that makes it read as set apart. It is
    // a LOGICAL border so the edge is on the right in Arabic and the left in
    // English without a second rule; if somebody swaps it for border-l the
    // Arabic page grows a stripe down its trailing edge.
    expect(substr_count($html, 'border-s-accent'))->toBe(
        1,
        "Expected exactly one callout on the {$locale} page."
    );

    // Her three questions, as three items rather than as one run-on paragraph.
    foreach (['ar' => 'هل ينطبق على حالتي أنا؟', 'en' => 'Does it apply to my case?'] as $l => $question) {
        if ($l === $locale) {
            expect($html)->toContain($question);
        }
    }
})->with(['ar', 'en']);

it('never shows the prefix characters to a reader', function (string $locale) {
    /*
     * The failure this catches is the whole convention leaking: a renderer that
     * printed the block verbatim would put "> - Is there evidence for this?" on
     * a medical page, and the page would still be a valid 200 with the right
     * words in it.
     */
    calloutArticle();

    $html = $this->get("/{$locale}/articles/pcos-and-food-judging-a-claim")->assertOk()->getContent();

    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    expect($text)->not->toContain('> -');
    expect($text)->not->toContain('&gt;');
})->with(['ar', 'en']);

it('escapes a callout as carefully as it escapes a paragraph', function () {
    /*
     * The point of the plain-text column is that markup cannot get in. A new
     * block convention that forgot to escape would be a way back in, and it
     * would be reached by exactly the same route: somebody pasting into the
     * body field in the admin.
     */
    $pieces = ArticleBody::callout('> - <script>alert(1)</script>');

    $rendered = (string) ArticleBody::render($pieces[0]['items'][0]);

    expect($rendered)->not->toContain('<script>');
    expect($rendered)->toContain('&lt;script&gt;');
});

it('still turns an internal link inside a callout into a link', function () {
    /*
     * The other half: escaping must not cost the one thing the body grammar is
     * allowed to produce. A callout that ends "book an appointment" and cannot
     * link to the booking page would be worse than no callout.
     */
    // route('booking') carries a {locale} that SetLocale supplies through
    // URL::defaults() on a real request. Nothing has made one here.
    Locales::applyToUrlGenerator('ar');

    $pieces = ArticleBody::callout('> Then [[booking|book an appointment]].');

    expect((string) ArticleBody::render($pieces[0]['text']))
        ->toContain(route('booking'))
        ->toContain('book an appointment');
});
