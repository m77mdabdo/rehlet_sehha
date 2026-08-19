<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Headers for any route whose URL contains a bearer token.
 *
 * A meta tag is not enough, and that is the whole reason this exists.
 *
 *   X-Robots-Tag  A meta robots tag only works if something parses the HTML.
 *                 A link-preview generator in a chat app, a crawler fetching
 *                 with HEAD, an archiver, a corporate proxy — plenty of things
 *                 index a URL without ever building a DOM. The header is read
 *                 by all of them, before a byte of body is looked at.
 *
 *   Referrer-Policy  Without it, clicking the privacy link from the
 *                 self-service page sends the FULL current URL — token and
 *                 all — to the next page as the Referer. That is the leak
 *                 that costs nothing to close and is invisible when it opens.
 *
 * Applied as route middleware rather than globally: these headers are correct
 * for token pages and wrong everywhere else. no-referrer on the whole site
 * would strip the analytics the clinic may later want, and noindex on the
 * whole site would be a catastrophe nobody notices for a month.
 */
class ProtectTokenUrl
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive');
        $response->headers->set('Referrer-Policy', 'no-referrer');

        /*
         * And do not let a shared cache hold it. A token page is a personal
         * document; a proxy that cached it could serve one patient's
         * appointment to another.
         */
        $response->headers->set('Cache-Control', 'no-store, private');

        return $response;
    }
}
