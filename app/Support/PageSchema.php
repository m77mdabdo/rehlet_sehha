<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Post;

/**
 * Structured data for a standalone page.
 *
 * Two graphs go on every one of these pages: the clinic itself, and the trail
 * that leads to the page. They ship as an @graph array rather than two separate
 * script tags, so the clinic is described once and the breadcrumb can point at
 * it by @id instead of restating it.
 *
 * WHY BREADCRUMBS ARE WORTH EMITTING AT ALL. On a result page, a breadcrumb
 * replaces the raw URL under the title with a readable path. For a clinic whose
 * pages are reached by people searching a symptom rather than a brand, that
 * line is often the only signal of what kind of site they are about to open.
 *
 * The trail must mirror what is on screen. Google treats a BreadcrumbList that
 * disagrees with the visible navigation as a markup problem, and a test in
 * PackagesPageTest checks the two against each other rather than checking the
 * JSON in isolation.
 */
final class PageSchema
{
    /**
     * @param  list<array{label: string, url: string|null}>  $trail
     *                                                               Ordered from the site root to the current page. The last entry is the
     *                                                               page itself and carries no url — schema.org wants the current item
     *                                                               named but not linked.
     */
    public static function toJson(array $trail, ?Post $article = null): string
    {
        $graph = [ClinicSchema::build()];

        if ($trail !== []) {
            $graph[] = self::breadcrumbs($trail);
        }

        if ($article !== null) {
            $graph[] = self::article($article);
        }

        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
    }

    /**
     * A clinical article, with the person who checked it named in the markup.
     *
     * `reviewedBy` is the field that matters here and it is not decoration.
     * Google's own guidance for health content asks who reviewed it and when,
     * and a clinic that shows a reviewer on the page but omits it from the
     * markup is telling the reader one thing and the machine another.
     *
     * `author` and `reviewedBy` are the same person for now, and are still
     * emitted as separate properties, because they answer different questions
     * and will not always have the same answer: the clinic may commission a
     * piece it reviews without writing.
     *
     * NOTHING HERE IS medicalSpecialty OR MedicalWebPage. Those types invite
     * rich results that present the page as clinical reference material, which
     * is a claim this practice has not earned and does not want. An Article is
     * what it is.
     *
     * @return array<string, mixed>
     */
    private static function article(Post $post): array
    {
        $reviewer = $post->reviewer?->name;

        return array_filter([
            '@type' => 'Article',
            'headline' => (string) $post->title,
            'description' => (string) $post->excerpt,
            'inLanguage' => Locales::current(),
            'datePublished' => $post->published_at?->toIso8601String(),
            'author' => $reviewer === null ? null : ['@type' => 'Person', 'name' => $reviewer],
            'reviewedBy' => $reviewer === null ? null : ['@type' => 'Person', 'name' => $reviewer],

            /*
             * dateModified is the CONTENT date, not updated_at. A row touched
             * to fix a typo is not a revised article, and telling a search
             * engine otherwise on a medical page is a small lie about how
             * current the advice is.
             */
            'dateModified' => ($post->content_updated_at ?? $post->published_at)?->toIso8601String(),

            'articleSection' => $post->category?->name,
            'keywords' => $post->tags->isEmpty()
                ? null
                : $post->tags->map(fn ($tag): string => (string) $tag->name)->implode(', '),

            'publisher' => ['@id' => ClinicSchema::id()],
        ], static fn (mixed $value): bool => $value !== null && $value !== '');
    }

    /**
     * @param  list<array{label: string, url: string|null}>  $trail
     * @return array<string, mixed>
     */
    private static function breadcrumbs(array $trail): array
    {
        return [
            '@type' => 'BreadcrumbList',
            'itemListElement' => array_map(
                static function (array $crumb, int $index): array {
                    $item = [
                        '@type' => 'ListItem',
                        // One-based: position 0 is not a thing in this vocabulary.
                        'position' => $index + 1,
                        'name' => $crumb['label'],
                    ];

                    // The current page is named but not linked. Linking it
                    // would make the trail a loop back to where you already are.
                    if (($crumb['url'] ?? null) !== null) {
                        $item['item'] = $crumb['url'];
                    }

                    return $item;
                },
                $trail,
                array_keys($trail),
            ),
        ];
    }
}
