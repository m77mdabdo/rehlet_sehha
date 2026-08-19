<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Support\Locales;
use Illuminate\Http\JsonResponse;

/**
 * The web app manifest, built rather than stored.
 *
 * It used to be public/brand/site.webmanifest, a flat JSON file that repeated
 * the navy and the page background as literals. That made three copies of the
 * palette: the @theme block in app.css, the mirror in config/clinic.php, and
 * the manifest. Three copies of a colour is two chances to change it in the
 * wrong places and not notice — the manifest especially, because nothing
 * renders it where anyone would see the mismatch.
 *
 * Serving it from a route puts it back on the config mirror, so the navy now
 * exists in exactly two places: the CSS token, and the one config entry that
 * exists precisely because HTML and JSON cannot read a CSS variable.
 *
 * It is also per-locale. A static manifest hard-codes lang, dir and the app
 * name, which means an English visitor installing the site to their home
 * screen gets an Arabic name and an RTL manifest. Since the whole site is
 * already addressed by locale, the manifest may as well be too.
 */
class ManifestController extends Controller
{
    public function __invoke(): JsonResponse
    {
        $locale = Locales::current();

        $manifest = [
            'id' => '/'.$locale,
            'name' => __('home.meta_title'),
            'short_name' => __('common.brand'),
            'description' => __('home.meta_description'),

            'lang' => $locale,
            'dir' => Locales::direction($locale),

            // start_url points straight at the locale rather than at "/", which
            // redirects. A redirect on every launch of an installed app is a
            // visible stutter on the splash screen for no reason.
            'start_url' => '/'.$locale,

            // Scope stays at the root so following a language switch does not
            // eject the visitor out of the installed app and into a browser tab.
            'scope' => '/',

            'display' => 'standalone',
            'theme_color' => config('clinic.brand.ink'),
            'background_color' => config('clinic.brand.paper'),

            'icons' => [
                [
                    'src' => '/brand/icon-192.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                [
                    'src' => '/brand/icon-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'any',
                ],
                /*
                 * Android crops an icon to whatever shape the launcher uses —
                 * circle, squircle, teardrop. A maskable icon carries the
                 * padding that survives that crop; without one, Android pads
                 * the "any" icon itself and the mark ends up small inside a
                 * white box.
                 */
                [
                    'src' => '/brand/icon-maskable-512.png',
                    'sizes' => '512x512',
                    'type' => 'image/png',
                    'purpose' => 'maskable',
                ],
            ],
        ];

        /*
         * Caching is applied by the `cache.headers` middleware on the route
         * rather than here.
         *
         * Two reasons. It computes the ETag from the rendered bytes and then
         * actually answers a matching If-None-Match with a 304 — setEtag()
         * alone only writes the header, and the revalidation request still
         * comes back 200 with the full body, which is the more expensive half
         * of what caching was supposed to avoid.
         *
         * And the cache belongs at the HTTP layer, not in the application
         * cache: this body is a few hundred bytes assembled from config and
         * language files already in memory, so a Cache::remember() would buy
         * no measurable time while happily serving a stale palette after a
         * deploy, because config:cache does not clear the application cache.
         * An ETag over the real bytes cannot go stale by construction.
         */
        return response()->json($manifest, 200, [
            'Content-Type' => 'application/manifest+json',
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
