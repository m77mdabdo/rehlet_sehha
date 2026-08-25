<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\User;
use Database\Seeders\PostSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Spatie\Permission\PermissionRegistrar;

/**
 * EVERY ARTICLE ON THIS SITE IS A MEDICAL CLAIM PUBLISHED UNDER A LICENSED
 * PRACTITIONER'S NAME.
 *
 * A woman searching her own diagnosis and landing on a clinic's article is not
 * reading a blog. She is reading what she has every reason to treat as advice
 * from the clinician she is about to book with, and if a sentence in it is
 * wrong, it is wrong in that clinician's name and against her licence.
 *
 * So the rule is not "articles should be checked". It is that an unchecked
 * article CANNOT be published — by the admin, by a seeder, by an import, by a
 * fix in tinker — and this file is what makes that true rather than intended.
 *
 * Three layers, each tested here:
 *
 *   1. The model refuses to save a published article with no named reviewer.
 *   2. The published scope refuses to serve one that reached the table anyway.
 *   3. The page names the reviewer and the review date where a reader sees
 *      them, and carries the disclaimer above the article rather than under it.
 */
beforeEach(function () {
    Cache::flush();
});

/*
|------------------------------------------------------------------------------
| The model refuses
|------------------------------------------------------------------------------
*/

it('refuses to publish an article that nobody reviewed', function () {
    $post = Post::factory()->draft()->create();

    expect(fn () => $post->update(['published_at' => Carbon::now()]))
        ->toThrow(LogicException::class);

    expect($post->fresh()->published_at)->toBeNull();
});

it('refuses a reviewer with no review date, and a date with no reviewer', function () {
    /*
     * Both halves, separately. WHO without WHEN cannot answer whether the
     * sign-off came before or after the paragraph that turned out to be wrong,
     * and WHEN without WHO cannot answer anything at all.
     */
    $reviewer = User::factory()->create();

    expect(fn () => Post::factory()->create([
        'published_at' => Carbon::now(),
        'reviewed_by' => $reviewer->id,
        'reviewed_at' => null,
    ]))->toThrow(LogicException::class);

    expect(fn () => Post::factory()->create([
        'published_at' => Carbon::now(),
        'reviewed_by' => null,
        'reviewed_at' => Carbon::now(),
    ]))->toThrow(LogicException::class);
});

it('lets a draft exist with no reviewer at all', function () {
    /*
     * Writing is unrestricted. The requirement attaches to PUBLISHING, because
     * published_at is what makes an article public — a rule that stopped
     * people drafting would just move the drafting somewhere with no rules.
     */
    $post = Post::factory()->draft()->create();

    expect($post->exists)->toBeTrue();
    expect($post->reviewed_by)->toBeNull();
});

it('always lets an article be taken down', function () {
    /*
     * Unpublishing must never be blocked by the rule that governs publishing.
     * If the fastest way to pull a dangerous article were to delete the row,
     * the record of what was said would go with it — so clearing published_at
     * is always allowed, whatever state the review fields are in.
     */
    $post = Post::factory()->create();

    $post->update(['published_at' => null, 'reviewed_by' => null, 'reviewed_at' => null]);

    expect($post->fresh()->published_at)->toBeNull();
});

/*
|------------------------------------------------------------------------------
| The scope refuses
|------------------------------------------------------------------------------
*/

it('never serves an unreviewed article even if one reaches the table', function () {
    /*
     * The second layer, and the reason it exists: the saving hook governs
     * WRITES through Eloquent. A restored backup from before this rule, a raw
     * SQL insert, or a migration that copied rows are none of them writes
     * through Eloquent.
     *
     * So the row is forced in here the same way those would force it in —
     * straight past the model — and the site must still refuse to serve it.
     */
    $post = Post::factory()->create();

    Post::query()->whereKey($post->id)->update([
        'reviewed_by' => null,
        'reviewed_at' => null,
    ]);

    expect(Post::published()->pluck('id')->all())->not->toContain($post->id);

    test()->get('/ar/articles/'.$post->slug)->assertNotFound();
});

/*
|------------------------------------------------------------------------------
| The page says so
|------------------------------------------------------------------------------
*/

it('names the reviewer and the review date on every published article', function (string $locale) {
    $reviewer = User::factory()->create(['name' => 'د. رنا محمد سالم']);

    $post = Post::factory()->create([
        'slug' => 'a-reviewed-article',
        'reviewed_by' => $reviewer->id,
        'reviewed_at' => Carbon::parse('2026-03-14'),
    ]);

    $html = $this->get("/{$locale}/articles/{$post->slug}")->assertOk()->getContent();

    expect(str_contains($html, $reviewer->name))->toBeTrue(
        'The article does not name the clinician who reviewed it.'
    );

    expect(str_contains($html, __('articles.reviewed_by', ['name' => $reviewer->name], $locale)))->toBeTrue(
        'The reviewer is on the page but not labelled as the reviewer, which reads as a byline.'
    );

    // And the review date, which is not the publication date.
    expect(str_contains($html, $post->reviewed_at->locale($locale)->translatedFormat('j F Y')))->toBeTrue(
        'The review date is missing. "Reviewed" with no date cannot be checked against anything.'
    );
})->with(['ar', 'en']);

it('puts the medical disclaimer above the article, not only in the footer', function (string $locale) {
    /*
     * POSITION IS THE WHOLE POINT. A footer disclaimer is read after the
     * article, by which time it has qualified nothing. This asserts the
     * disclaimer appears BEFORE the body, not merely that it appears.
     */
    $post = Post::factory()->create([
        'slug' => 'a-disclaimed-article',
        'body' => ['ar' => 'الفقرة الأولى من المقال.', 'en' => 'The first paragraph of the article.'],
    ]);

    $html = $this->get("/{$locale}/articles/{$post->slug}")->assertOk()->getContent();

    $disclaimer = strpos($html, (string) __('articles.disclaimer_body', [], $locale));
    $body = strpos($html, $locale === 'ar' ? 'الفقرة الأولى من المقال.' : 'The first paragraph of the article.');

    expect($disclaimer)->not->toBeFalse('The article carries no disclaimer in its body.');
    expect($body)->not->toBeFalse('The article body did not render.');
    expect($disclaimer)->toBeLessThan($body, 'The disclaimer sits after the article it is supposed to qualify.');
})->with(['ar', 'en']);

it('tells a machine who reviewed it, not just a reader', function () {
    $reviewer = User::factory()->create(['name' => 'د. رنا محمد سالم']);

    $post = Post::factory()->create([
        'slug' => 'a-marked-up-article',
        'reviewed_by' => $reviewer->id,
    ]);

    $html = $this->get('/ar/articles/'.$post->slug)->assertOk()->getContent();

    /*
     * Showing a reviewer on the page and omitting it from the structured data
     * tells the reader one thing and the search engine another. reviewedBy is
     * the field Google's health-content guidance asks for by name.
     */
    expect(str_contains($html, '"reviewedBy"'))->toBeTrue('The article markup has no reviewedBy.');
    expect(str_contains($html, '"@type":"Article"'))->toBeTrue('The article emits no Article node.');
});

/*
|------------------------------------------------------------------------------
| Who may sign one off
|------------------------------------------------------------------------------
*/

it('lets a doctor and an admin mark an article reviewed, and nobody else', function () {
    $this->seed(RoleSeeder::class);
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $expected = ['doctor' => true, 'admin' => true, 'receptionist' => false];

    foreach ($expected as $role => $allowed) {
        $user = User::factory()->create();
        $user->assignRole($role);

        expect($user->can('review', Post::class))->toBe($allowed, sprintf(
            'A %s %s mark a clinical article reviewed.',
            $role,
            $allowed ? 'should be able to' : 'must not be able to',
        ));
    }
});

it('hides the review fields from anyone who may not use them', function () {
    /*
     * Hidden rather than disabled. A disabled field tells a receptionist that
     * signing an article off is something she is nearly allowed to do, and the
     * next step is asking somebody to tick it on her behalf.
     */
    $form = file_get_contents(app_path('Filament/Resources/Posts/Schemas/PostForm.php'));

    expect(str_contains($form, "->visible(fn (): bool => Auth::user()?->can('review', Post::class)"))->toBeTrue(
        'The clinical review section is no longer gated on the review ability.'
    );

    expect(str_contains($form, '->disabled('))->toBeFalse(
        'The review fields are disabled rather than hidden. Hide them.'
    );
});

/*
|------------------------------------------------------------------------------
| The articles that already existed
|------------------------------------------------------------------------------
*/

it('seeds the placeholder articles as drafts', function () {
    /*
     * The three seeded pieces were written to give the templates something to
     * render. Nobody clinically reviewed them, and one of them reads vitamin D
     * near the bottom of its reference range against a complaint of fatigue —
     * a clinical reading, under a licensed byline.
     *
     * Seeding them published and reviewed would have been one line and would
     * have made this entire rule decorative. They stay drafts until Dr. Rana
     * writes or approves each body and is recorded against it.
     */
    $this->seed(PostSeeder::class);

    expect(Post::query()->count())->toBeGreaterThan(0);
    expect(Post::published()->count())->toBe(0, 'A placeholder article is published again.');

    foreach (Post::all() as $post) {
        expect($post->reviewed_by)->toBeNull("{$post->slug} has a reviewer nobody assigned.");
    }
});
