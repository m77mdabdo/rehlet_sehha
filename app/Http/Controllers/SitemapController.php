<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Post;
use App\Models\Specialty;
use App\Models\Tag;
use App\Support\Locales;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;

/**
 * The sitemap, generated rather than maintained.
 *
 * EVERY PUBLIC PAGE, IN EVERY LOCALE, WITH hreflang. A bilingual site whose
 * sitemap lists one language teaches a search engine that the other does not
 * exist; listing both without hreflang teaches it that they are duplicates.
 * Both mistakes cost the Arabic pages, which is most of the site.
 *
 * WHAT IS DELIBERATELY ABSENT:
 *
 *   Token pages — the manage link, the export, the review form. They are
 *   bearer credentials. A sitemap is the one file whose entire purpose is
 *   handing URLs to a crawler, so a token in it is a cancellation link in a
 *   search result.
 *
 *   The admin panel, for the same reason and more so.
 *
 *   Unpublished, scheduled and unreviewed articles. Post::published() already
 *   refuses all three, so this inherits the clinical review gate rather than
 *   restating it — the sitemap cannot advertise an article the site would 404.
 *
 *   Empty categories and tags. A taxonomy page with nothing on it is a thin
 *   page, and submitting a set of them is a good way to be judged for it.
 */
class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $urls = [];

        // Static pages. Named routes, so a rename breaks the build rather than
        // silently emitting a 404 into the sitemap.
        foreach (['home', 'services', 'packages', 'how-it-works', 'about', 'articles', 'faq', 'contact', 'booking', 'privacy'] as $name) {
            $urls[] = ['route' => $name, 'params' => [], 'priority' => $name === 'home' ? '1.0' : '0.8'];
        }

        foreach (Specialty::query()->where('is_active', true)->get() as $specialty) {
            $urls[] = ['route' => 'specialties.show', 'params' => ['slug' => $specialty->slug], 'priority' => '0.7'];
        }

        foreach (Post::published()->get() as $post) {
            $urls[] = [
                'route' => 'posts.show',
                'params' => ['slug' => $post->slug],
                'priority' => '0.6',
                'lastmod' => $post->content_updated_at ?? $post->published_at,
            ];
        }

        foreach (Category::query()->active()->has('posts')->get() as $category) {
            if ($category->posts()->published()->doesntExist()) {
                continue;
            }

            $urls[] = ['route' => 'articles.category', 'params' => ['slug' => $category->slug], 'priority' => '0.5'];
        }

        foreach (Tag::query()->has('posts')->get() as $tag) {
            if ($tag->posts()->published()->doesntExist()) {
                continue;
            }

            $urls[] = ['route' => 'articles.tag', 'params' => ['slug' => $tag->slug], 'priority' => '0.4'];
        }

        return response($this->render($urls), 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $urls
     */
    private function render(array $urls): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>'."\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" '
            .'xmlns:xhtml="http://www.w3.org/1999/xhtml">'."\n";

        foreach ($urls as $entry) {
            foreach (Locales::all() as $locale) {
                $xml .= "  <url>\n";
                $xml .= '    <loc>'.e(route($entry['route'], $entry['params'] + ['locale' => $locale]))."</loc>\n";

                /*
                 * Every URL declares every language INCLUDING ITSELF. That is
                 * what the specification asks for, and omitting the self
                 * reference is the most common way an hreflang set is quietly
                 * ignored.
                 */
                foreach (Locales::all() as $alternate) {
                    $xml .= '    <xhtml:link rel="alternate" hreflang="'.$alternate.'" href="'
                        .e(route($entry['route'], $entry['params'] + ['locale' => $alternate]))."\" />\n";
                }

                if (isset($entry['lastmod']) && $entry['lastmod'] instanceof Carbon) {
                    $xml .= '    <lastmod>'.$entry['lastmod']->toDateString()."</lastmod>\n";
                }

                $xml .= '    <priority>'.$entry['priority']."</priority>\n";
                $xml .= "  </url>\n";
            }
        }

        return $xml.'</urlset>'."\n";
    }
}
