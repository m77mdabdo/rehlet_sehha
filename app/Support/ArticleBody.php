<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Post;
use App\Models\Specialty;
use Illuminate\Support\HtmlString;

/**
 * LINKS INSIDE AN ARTICLE, WITHOUT LETTING HTML INTO THE DATABASE.
 *
 * The body column is plain text and stays plain text. That was a deliberate
 * decision taken when the blog was built: an editor pasting from a word
 * processor into a medical page must not be able to inject markup, and the
 * safest input filter is a column that never contained markup in the first
 * place.
 *
 * But an article that cannot link is a worse article. A paragraph about PCOS
 * that mentions the clinic's PCOS service and does not link to it makes the
 * reader go and find it, and most readers do not. Internal links are also how
 * fourteen separate pages become one body of work rather than fourteen
 * orphans.
 *
 * So: one bracket convention, a closed list of destinations, and no way to
 * express a URL.
 *
 *     [[booking|احجزي موعد]]
 *     [[specialty:pcos-hormonal|تغذية تكيس المبايض]]
 *     [[article:normal-results-still-tired|التحاليل سليمة والتعب مستمر]]
 *
 * THE POINT IS WHAT CANNOT BE WRITTEN. There is no `[[url:…]]`, and there is no
 * way to add one from the admin panel — every destination is a route this
 * application already owns, resolved by name. An editor cannot link to an
 * external site, cannot write `javascript:`, and cannot produce an anchor with
 * a `target` or an `onclick` on it, because none of those are expressible in
 * the grammar. The label is escaped like any other text.
 *
 * FAILING SAFE. An unknown destination renders as its label, in plain text.
 * A dead link on a medical page is worse than a missing one — it reads as
 * carelessness about everything else — and ArticleLinksResolveTest asserts
 * that no seeded article contains one, so "fails safe" never becomes "quietly
 * drops links nobody noticed".
 */
final class ArticleBody
{
    /**
     * [[type|label]] or [[type:slug|label]].
     *
     * The label may not contain `]` and the slug is restricted to the
     * characters a slug is made of, so the pattern cannot be walked out of.
     */
    private const PATTERN = '/\[\[([a-z]+)(?::([a-z0-9-]+))?\|([^\]|]+)\]\]/u';

    /**
     * Destinations with no slug. Route names, resolved by the router.
     *
     * @var array<string, string>
     */
    private const SIMPLE = [
        'booking' => 'booking',
        'contact' => 'contact',
        'services' => 'services',
        'articles' => 'articles',
        'about' => 'about',
        'faq' => 'faq',
    ];

    /**
     * One paragraph of body text, escaped, with its links turned into anchors.
     */
    public static function render(string $text): HtmlString
    {
        $out = '';
        $offset = 0;

        preg_match_all(self::PATTERN, $text, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        foreach ($matches as $match) {
            [$whole, $start] = $match[0];

            // Everything since the last link, escaped as the plain text it is.
            $out .= e(substr($text, $offset, $start - $offset));
            $offset = $start + strlen($whole);

            $type = $match[1][0];
            /*
             * Group 2 is the optional slug, and it is not the LAST group, so
             * preg fills it with an empty string when it does not participate
             * rather than omitting it. `[[booking|…]]` therefore arrives here
             * as '' rather than as a missing offset — which is why there is no
             * null coalesce, and why an empty string is what means "no slug".
             */
            $slug = $match[2][0] === '' ? null : $match[2][0];
            $label = trim($match[3][0]);

            $href = self::resolve($type, $slug);

            $out .= $href === null
                // Fail safe: the words survive, the broken link does not.
                ? e($label)
                : '<a href="'.e($href).'" class="font-medium text-accent-dark underline underline-offset-4 decoration-line hover:decoration-accent-dark">'.e($label).'</a>';
        }

        $out .= e(substr($text, $offset));

        return new HtmlString($out);
    }

    /**
     * The URL for one destination, or null if it does not exist.
     */
    public static function resolve(string $type, ?string $slug): ?string
    {
        if (isset(self::SIMPLE[$type])) {
            return $slug === null ? route(self::SIMPLE[$type]) : null;
        }

        if ($slug === null) {
            return null;
        }

        return match ($type) {
            /*
             * Existence is CHECKED, not assumed. A specialty can be renamed or
             * retired in the admin panel long after an article was written,
             * and the article has no way of knowing. Cached for the request
             * because a long piece may link to the same service four times.
             */
            'specialty' => in_array($slug, self::specialtySlugs(), true)
                ? route('specialties.show', ['slug' => $slug])
                : null,

            'article' => in_array($slug, self::articleSlugs(), true)
                ? route('posts.show', ['slug' => $slug])
                : null,

            default => null,
        };
    }

    /**
     * Every link in a body, as [type, slug] pairs. Used by the test that
     * refuses to let a dead internal link ship.
     *
     * @return list<array{type: string, slug: string|null, label: string}>
     */
    public static function links(string $text): array
    {
        preg_match_all(self::PATTERN, $text, $matches, PREG_SET_ORDER);

        return array_map(fn (array $m): array => [
            'type' => $m[1],
            // Empty string, not a missing offset — see render().
            'slug' => $m[2] === '' ? null : $m[2],
            'label' => trim($m[3]),
        ], $matches);
    }

    /**
     * A block that is a callout rather than a paragraph.
     *
     * THE THIRD AND LAST CONVENTION. The body column is plain text and stays
     * that way — see the note at the top of this file — so a block that needs
     * to be set apart from the prose around it says so with a prefix, exactly
     * as a heading does with `## `.
     *
     *     > Every time you hear advice about PCOS, ask three questions:
     *     > - Is there evidence behind this?
     *     > - Does it apply to my case?
     *     > - Can I keep it up healthily?
     *     > If the answer is not clear, the advice needs review first.
     *
     * It exists because some paragraphs are a TOOL rather than an argument.
     * The three questions above are meant to be carried out of the article and
     * used on the next claim the reader meets; a tool set in the same type as
     * the paragraph before it is a tool nobody notices they were handed.
     *
     * There is deliberately no way to nest one, no way to choose a colour, and
     * no way to put a heading inside one. An editor gets a box, not a layout.
     */
    public static function isCallout(string $block): bool
    {
        return str_starts_with(ltrim($block), '>');
    }

    /**
     * One callout, as the pieces a view renders: paragraphs and one run of
     * list items per group of adjacent `- ` lines.
     *
     * The grouping lives here rather than in the Blade file because it is
     * parsing, and a template that has to track whether it is currently inside
     * a list is a template nobody will edit safely later.
     *
     * @return list<array{type: string, text?: string, items?: list<string>}>
     */
    public static function callout(string $block): array
    {
        $pieces = [];

        foreach (preg_split('/\R/u', $block) ?: [] as $line) {
            // Tolerate `>`, `> ` and a stray indent, because all three are
            // things a person types.
            $line = trim(ltrim(trim($line), '>'));

            if ($line === '') {
                continue;
            }

            if (str_starts_with($line, '- ')) {
                $item = trim(substr($line, 2));
                $last = array_key_last($pieces);

                if ($last !== null && $pieces[$last]['type'] === 'list') {
                    $pieces[$last]['items'][] = $item;

                    continue;
                }

                $pieces[] = ['type' => 'list', 'items' => [$item]];

                continue;
            }

            $pieces[] = ['type' => 'text', 'text' => $line];
        }

        return $pieces;
    }

    /**
     * Body text with the link syntax removed, leaving only the words.
     *
     * For anywhere the brackets would be noise rather than structure: an
     * excerpt, a meta description, a word count, a reading-time estimate.
     */
    public static function plain(string $text): string
    {
        return (string) preg_replace(self::PATTERN, '$3', $text);
    }

    /**
     * @return list<string>
     */
    private static function specialtySlugs(): array
    {
        return self::memoise('specialty-slugs', fn (): array => Specialty::query()->pluck('slug')->all());
    }

    /**
     * PUBLISHED articles only.
     *
     * A link to a draft degrades to plain text, which is the behaviour you
     * want: the sentence still reads, and nobody is sent to a 404 because a
     * piece that was going to be published next week was not.
     *
     * The seeded cross-links between the fourteen therefore go live as the
     * articles do, one by one, with no second pass to remember.
     *
     * @return list<string>
     */
    private static function articleSlugs(): array
    {
        return self::memoise('article-slugs', fn (): array => Post::query()->published()->pluck('slug')->all());
    }

    /**
     * Memoised for the REQUEST, via the container, not for the process.
     *
     * A static property or the array cache driver would both outlive the
     * request — which is harmless in production and quietly poisonous in the
     * test suite, where one test's slug list would be answered from the
     * previous test's database. The container is rebuilt per test and per
     * request, so binding into it gets the lifetime that was actually meant.
     *
     * @param  callable(): list<string>  $resolve
     * @return list<string>
     */
    private static function memoise(string $key, callable $resolve): array
    {
        $binding = 'article-body.'.$key;

        if (! app()->bound($binding)) {
            app()->instance($binding, $resolve());
        }

        /** @var list<string> */
        return app()->make($binding);
    }
}
