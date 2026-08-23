<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Keeps the panel out of search results, and out of referrers.
 *
 * A robots.txt Disallow would not do this on its own. Disallow asks a crawler
 * not to FETCH a URL; it does not stop the URL being indexed when it is linked
 * from somewhere else, and the result is a search listing for the clinic's
 * login page with no description under it. X-Robots-Tag is an instruction
 * about the response itself and is obeyed even when the crawler arrived by
 * another route.
 *
 * Referrer-Policy matters for a different reason. Panel URLs carry record
 * identifiers — /admin/appointments/412/edit — and without this the next
 * outbound request from that page hands that path to a third party. There
 * should be no outbound requests from the panel, but the header costs nothing
 * and does not depend on that staying true.
 *
 * no-store because these pages render patient data: a shared clinic computer
 * must not serve an appointment list out of the back/forward cache after
 * somebody has logged out.
 *
 * The same rule belongs in the sitemap whenever one is built: /admin is
 * excluded, along with every token URL.
 */
class NoIndexAdminPanel
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        /*
         * Registered both on the panel and globally (see bootstrap/app.php),
         * so the path check is what keeps it from touching the public site.
         * Running twice on a panel response is harmless — it sets the same
         * four headers to the same four values.
         */
        if (! $request->is('admin', 'admin/*')) {
            return $response;
        }

        $response->headers->set('X-Robots-Tag', 'noindex, nofollow, noarchive, noimageindex');
        $response->headers->set('Referrer-Policy', 'no-referrer');
        $response->headers->set('Cache-Control', 'no-store, private');
        $response->headers->set('X-Frame-Options', 'DENY');

        return $response;
    }
}
