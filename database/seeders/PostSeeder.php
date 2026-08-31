<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Citation;
use App\Models\Post;
use App\Models\Tag;
use Database\Seeders\Articles\ArticleDefinition;
use Illuminate\Database\Seeder;

/**
 * THE FOURTEEN ARTICLES, AS DRAFTS.
 *
 * Each one is a complete, publishable piece of writing carrying three kinds of
 * hole, each held open by a gate rather than by good intentions.
 *
 *   1. CLINICAL_INPUT — a directed recommendation. A quantity, a target, an
 *      instruction aimed at the reader. Written by the clinician, not here.
 *
 *   2. PRACTITIONER_VOICE — a first-person clinical observation. "What I see
 *      in patients who…" is a claim about one named practitioner's own
 *      experience; writing one and signing her name to it would be inventing a
 *      professional memory. These are also the sentences that make the blog
 *      hers rather than a competent translation of somebody else's, which is
 *      the better reason to wait for them.
 *
 *   3. Unverified citations — these were drafted without internet access, from
 *      memory, and they report what the ADA, WHO, NICE, ESHRE, AAP and
 *      Cochrane say. That is a legitimate way to draft and an indefensible way
 *      to publish.
 *
 * NOTHING HERE PUBLISHES, and no single mistake can make it. published_at is
 * null; Post::booted() refuses to save a published article that still contains
 * either marker, that has no named clinical reviewer, or that carries a
 * citation nobody has checked; scopePublished() refuses to serve one even if a
 * row reached the table by some other route.
 *
 * WHAT IS WRITTEN OUT IN FULL. Everything attributable to a published source:
 * guideline recommendations, physiology, what systematic reviews found, why a
 * myth spreads and what the evidence says instead — and the Egyptian kitchen
 * throughout, because a translated foreign article is exactly what somebody in
 * Cairo does not need another of.
 */
class PostSeeder extends Seeder
{
    public function run(): void
    {
        /*
         * The two categories the old free-text column left behind. They were
         * created by the data migration from values like "تغذية", carry no
         * description and now hold no articles.
         */
        Category::query()->whereIn('slug', ['nutrition', 'general-health'])->delete();

        /*
         * The three placeholder articles this file used to hold.
         *
         * They existed to give the templates something to render and were
         * never clinically reviewed; one of them read vitamin D near the
         * bottom of its range against a complaint of fatigue, which is a
         * clinical statement under a licensed byline.
         */
        Post::query()->whereIn('slug', [
            'building-a-habit-that-lasts',
            'protein-on-an-egyptian-budget',
            'reading-your-lab-results',
        ])->delete();

        foreach (ArticleDefinition::all() as $class) {
            $this->seedArticle((new $class)->definition());
        }
    }

    /**
     * @param  array<string, mixed>  $article
     */
    private function seedArticle(array $article): void
    {
        $category = Category::query()->where('slug', $article['category'])->first();

        $post = Post::updateOrCreate(
            ['slug' => $article['slug']],
            [
                'category_id' => $category?->id,
                'title' => $article['title'],
                'excerpt' => $article['excerpt'],
                'body' => $article['body'],
                'cover_path' => $article['cover'] ?? null,
                'is_featured' => $article['featured'] ?? false,

                /*
                 * Draft, and re-drafted on every run.
                 *
                 * Deliberately destructive: if a seeded article has been
                 * reviewed and published, re-seeding takes it back down. The
                 * alternative is worse — a run that silently leaves a live
                 * page showing the previous draft's text while the seeder
                 * reports success. Nothing here is a place to edit published
                 * copy; the admin panel is.
                 */
                'published_at' => null,
                'reviewed_by' => null,
                'reviewed_at' => null,
            ],
        );

        $post->tags()->sync(
            Tag::query()->whereIn('slug', $article['tags'])->pluck('id')->all()
        );

        $this->seedCitations($post, $article['citations'] ?? []);
    }

    /**
     * References are REPLACED, not merged.
     *
     * If a citation has been removed from the draft it has to disappear from
     * the article, and a merge would leave it attached for ever — supporting a
     * sentence that is no longer there. Verification state is deliberately not
     * preserved across a re-seed either: if the text changed, whatever was
     * checked was checked against different words.
     *
     * @param  list<array<string, mixed>>  $citations
     */
    private function seedCitations(Post $post, array $citations): void
    {
        $post->citations()->delete();

        foreach ($citations as $index => $citation) {
            Citation::create([
                'post_id' => $post->id,
                'sort_order' => $index,
                'organisation' => $citation['organisation'],
                'title' => $citation['title'],
                'year' => $citation['year'] ?? null,
                'confidence' => $citation['confidence'],
                'note' => $citation['note'],
            ]);
        }
    }
}
