<?php

declare(strict_types=1);

namespace App\Support;

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
    public static function toJson(array $trail): string
    {
        $graph = [ClinicSchema::build()];

        if ($trail !== []) {
            $graph[] = self::breadcrumbs($trail);
        }

        return json_encode(
            ['@context' => 'https://schema.org', '@graph' => $graph],
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR,
        );
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
