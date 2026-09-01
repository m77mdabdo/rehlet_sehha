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

        /*
         * A PUBLISHED ARTICLE IS LEFT ALONE. THIS USED TO OVERWRITE IT.
         *
         * The seeder wrote `published_at => null` unconditionally on every
         * run, so `db:seed` silently took every live article off the site and
         * replaced its text with the unanswered draft — throwing away the
         * practitioner's answers, the clinical sign-off and the citation
         * verification in one command, and reporting success.
         *
         * That was harmless while all fourteen were drafts and became a live
         * foot-gun the moment two of them went out. Seeders get run: after a
         * restore, on a fresh clone, by somebody chasing an unrelated bug.
         *
         * The original reasoning was sound — a run that leaves a live page
         * showing the previous draft's text while claiming success is worse
         * than one that resets it. The answer is to do NEITHER: skip the row
         * and say so, loudly enough that nobody mistakes silence for a
         * successful update.
         *
         * To re-seed a published article deliberately, unpublish it first —
         * one click in the admin, and reversible.
         */
        $existing = Post::query()->where('slug', $article['slug'])->first();

        if ($existing?->published_at !== null) {
            $this->command?->warn(
                "  skipped {$article['slug']} — it is published. Unpublish it first to re-seed it."
            );

            return;
        }

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
                 * Recomputed, not preserved. The body has just been replaced,
                 * so any stored reading time describes the previous draft —
                 * and Post::booted() only fills this when it is null. Leaving
                 * it alone is how twelve articles kept a two-minute estimate
                 * from when they were outlines.
                 */
                'reading_minutes' => null,

                /*
                 * Still a draft, and still re-drafted on every run — but only
                 * ever for a row that was ALREADY a draft. A published one
                 * never reaches this array; see the guard at the top of this
                 * method.
                 *
                 * Nothing here is a place to edit published copy. The admin
                 * panel is.
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
