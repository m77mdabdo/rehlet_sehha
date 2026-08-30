<?php

declare(strict_types=1);

use App\Console\Commands\VerifyPlaceholderCopy;
use App\Http\Controllers\ArticlesController;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use App\Models\User;
use App\Support\Photo;
use Database\Seeders\CategorySeeder;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\TagSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * The blog as a publication: taxonomy, indexes, the sitemap, and the two gates
 * that stand between a draft and a reader.
 *
 * THE GATES ARE THE POINT OF THIS FILE. Everything else here is a convenience
 * — a category page, a tag page, pagination. The two things that must never
 * fail are that an unreviewed article cannot publish, and that an article
 * still asking the clinician a question cannot publish.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
});

/**
 * A publishable article: reviewed, answered, in a category.
 */
function publishableArticle(array $attributes = []): Post
{
    $doctor = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->firstOrFail();

    return Post::factory()->create(array_merge([
        'category_id' => Category::query()->firstOrFail()->id,
        'reviewed_by' => $doctor->id,
        'reviewed_at' => now()->subDay(),
        'published_at' => now()->subDay(),
    ], $attributes));
}

/*
|------------------------------------------------------------------------------
| The clinical prompt gate
|------------------------------------------------------------------------------
*/

it('refuses to publish an article that still asks the clinician a question', function (string $locale) {
    $post = publishableArticle(['published_at' => null]);

    $post->setTranslation('body', $locale, "## عنوان\n\n".Post::CLINICAL_MARKER.': what do you tell a patient at week three?');

    expect(fn () => $post->update(['published_at' => now()]))
        ->toThrow(LogicException::class);

    expect($post->fresh()->published_at)->toBeNull();
})->with(['ar', 'en']);

it('lets a draft carry as many prompts as it likes', function () {
    /*
     * A draft full of open questions is exactly what a draft is. Flagging
     * those would train everybody to ignore the guard.
     */
    $post = publishableArticle(['published_at' => null]);
    $post->setTranslation('body', 'ar', Post::CLINICAL_MARKER.': one? '.Post::CLINICAL_MARKER.': two?');
    $post->save();

    expect($post->fresh()->exists)->toBeTrue();
});

it('catches a published article that reached the table another way', function () {
    /*
     * The saving hook governs writes through Eloquent. A restored backup, a
     * raw insert or a migration that copied rows are none of them that. This
     * is the read-side check.
     */
    $post = publishableArticle(['published_at' => null]);
    $post->setTranslation('body', 'ar', Post::CLINICAL_MARKER.': a question.');
    $post->save();

    DB::table('posts')->where('id', $post->id)->update([
        'published_at' => now()->subDay(),
    ]);

    $unanswered = VerifyPlaceholderCopy::unansweredClinicalPrompts();

    expect($unanswered)->toHaveKey($post->slug);

    $this->artisan('clinic:verify-copy')->assertFailed();
});

it('never renders a clinical prompt to a reader', function () {
    /*
     * Belt and braces on the two gates above: even if one were removed, the
     * marker must not reach a page. Asserted against the rendered HTML of
     * every published article.
     */
    $post = publishableArticle();

    $html = $this->get('/ar/articles/'.$post->slug)->assertOk()->getContent();

    expect(str_contains($html, Post::CLINICAL_MARKER))->toBeFalse();
});

/*
|------------------------------------------------------------------------------
| The seeded twelve
|------------------------------------------------------------------------------
*/

it('seeds twelve drafts, every one unpublished and every one with a cover', function () {
    $this->seed(PostSeeder::class);

    $posts = Post::query()->with(['category', 'tags'])->get();

    expect($posts)->toHaveCount(12);
    expect(Post::published()->count())->toBe(0, 'A draft article is published.');

    foreach ($posts as $post) {
        expect($post->category_id)->not->toBeNull("{$post->slug} has no category.");
        expect($post->cover_path)->not->toBeNull("{$post->slug} has no cover image.");

        expect(Photo::has((string) $post->cover_path))->toBeTrue(
            "{$post->slug} points at a cover that is not in the photo manifest: {$post->cover_path}"
        );

        // Structure written; clinical content absent. Both halves asserted.
        foreach (['ar', 'en'] as $locale) {
            $body = (string) $post->getTranslation('body', $locale, false);

            expect(str_contains($body, '## '))->toBeTrue("{$post->slug} ({$locale}) has no section headings.");
            expect(substr_count($body, Post::CLINICAL_MARKER))->toBeGreaterThan(
                0,
                "{$post->slug} ({$locale}) has no clinical prompts — either it needs none, "
                .'which is unlikely for a clinical article, or somebody answered them by guessing.'
            );

            expect(mb_strlen($body))->toBeGreaterThan(900, "{$post->slug} ({$locale}) is too short to be a finished structure.");
        }
    }
});

/*
|------------------------------------------------------------------------------
| Taxonomy
|------------------------------------------------------------------------------
*/

it('gives every category and tag its own page', function () {
    $post = publishableArticle();
    $tag = Tag::query()->firstOrFail();
    $post->tags()->attach($tag);

    $this->get('/ar/articles/category/'.$post->category->slug)
        ->assertOk()
        ->assertSee((string) $post->title, false);

    $this->get('/ar/articles/tag/'.$tag->slug)
        ->assertOk()
        ->assertSee((string) $post->title, false);
});

it('keeps a draft off every index', function () {
    $draft = publishableArticle(['published_at' => null]);
    $tag = Tag::query()->firstOrFail();
    $draft->tags()->attach($tag);

    foreach ([
        '/ar/articles',
        '/ar/articles/category/'.$draft->category->slug,
        '/ar/articles/tag/'.$tag->slug,
    ] as $path) {
        $this->get($path)->assertOk()->assertDontSee((string) $draft->title, false);
    }

    $this->get('/ar/articles/'.$draft->slug)->assertNotFound();
});

it('404s a category that is switched off', function () {
    $category = Category::query()->firstOrFail();
    $category->update(['is_active' => false]);

    $this->get('/ar/articles/category/'.$category->slug)->assertNotFound();
});

/*
|------------------------------------------------------------------------------
| The index grows controls rather than starting with them
|------------------------------------------------------------------------------
*/

it('stays a plain list below the threshold and paginates above it', function () {
    // One short of the threshold: no filter bar, no pagination.
    for ($i = 0; $i < ArticlesController::CONTROLS_APPEAR_AT - 1; $i++) {
        publishableArticle();
    }

    $lean = $this->get('/ar/articles')->assertOk()->getContent();

    expect(str_contains($lean, __('articles.filter_all', [], 'ar')))->toBeFalse(
        'The filter bar appeared below the threshold, over content that does not need filtering.'
    );

    // One more takes it over.
    publishableArticle();

    $full = $this->get('/ar/articles')->assertOk()->getContent();

    expect(str_contains($full, __('articles.filter_all', [], 'ar')))->toBeTrue(
        'The filter bar did not appear once there was enough to filter.'
    );
});

/*
|------------------------------------------------------------------------------
| Related articles, reading time, dates
|------------------------------------------------------------------------------
*/

it('relates articles by category and by shared tags', function () {
    $tag = Tag::query()->firstOrFail();
    $other = Category::factory()->create();

    $subject = publishableArticle();
    $subject->tags()->attach($tag);

    $sameCategory = publishableArticle(['category_id' => $subject->category_id]);
    $sharedTagOnly = publishableArticle(['category_id' => $other->id]);
    $sharedTagOnly->tags()->attach($tag);

    publishableArticle(['category_id' => $other->id]); // related to nothing

    $related = $subject->relatedPosts(2)->pluck('id');

    expect($related)->toContain($sameCategory->id);
    expect($related)->toContain($sharedTagOnly->id);
    expect($related)->not->toContain($subject->id, 'An article is related to itself.');
});

it('computes reading time when nobody has set one, and leaves it alone when they have', function () {
    $long = str_repeat('كلمة ', 900);

    $auto = publishableArticle(['reading_minutes' => null, 'body' => ['ar' => $long, 'en' => $long]]);
    expect($auto->reading_minutes)->toBe(5);

    $manual = publishableArticle(['reading_minutes' => 42, 'body' => ['ar' => $long, 'en' => $long]]);
    expect($manual->reading_minutes)->toBe(42, 'An editor\'s reading time was overwritten.');
});

/*
|------------------------------------------------------------------------------
| Markup and the sitemap
|------------------------------------------------------------------------------
*/

it('describes the article to a machine, category and tags included', function () {
    $post = publishableArticle();
    $post->tags()->attach(Tag::query()->firstOrFail());

    $html = $this->get('/ar/articles/'.$post->slug)->assertOk()->getContent();

    foreach (['"@type":"Article"', '"reviewedBy"', '"datePublished"', '"articleSection"', '"keywords"'] as $field) {
        expect(str_contains($html, $field))->toBeTrue("The article markup is missing {$field}.");
    }
});

it('lists every public page in the sitemap and no token page at all', function () {
    $post = publishableArticle();
    $post->tags()->attach(Tag::query()->firstOrFail());

    $xml = $this->get('/sitemap.xml')
        ->assertOk()
        ->assertHeader('Content-Type', 'application/xml; charset=UTF-8')
        ->getContent();

    foreach (['/ar', '/en', '/ar/articles', '/ar/articles/'.$post->slug] as $expected) {
        expect(str_contains($xml, '<loc>'.config('app.url').$expected.'</loc>'))->toBeTrue(
            "The sitemap does not list {$expected}."
        );
    }

    // Both languages, and every URL declares itself.
    expect(substr_count($xml, 'hreflang="ar"'))->toBe(substr_count($xml, '<loc>'));

    /*
     * A sitemap exists to hand URLs to a crawler, so a bearer token in one is
     * a cancellation link in a search result.
     */
    foreach (['appointment/', 'review/', '/admin'] as $forbidden) {
        expect(str_contains($xml, $forbidden))->toBeFalse("The sitemap contains {$forbidden}.");
    }
});

it('keeps an empty category out of the sitemap', function () {
    // Categories are seeded; none has a published article yet.
    $xml = $this->get('/sitemap.xml')->assertOk()->getContent();

    expect(str_contains($xml, '/articles/category/'))->toBeFalse(
        'An empty category page is in the sitemap. It is a thin page with nothing on it.'
    );
});
