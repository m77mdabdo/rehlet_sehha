<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Resolve the locale from the {locale} route segment.
     *
     * The segment is user input. It is checked against an allow-list and a
     * miss is a 404 — it is never passed to App::setLocale(). An unchecked
     * locale is a path traversal waiting to happen, because Laravel resolves
     * translation files by locale name and a value like "../../something"
     * would be a filesystem lookup driven by the URL.
     *
     * 404 rather than a redirect to the default: /fr/services does not exist,
     * and saying so plainly keeps search engines from indexing an endless
     * family of URLs that all serve the same Arabic page.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! is_string($locale) || ! Locales::isSupported($locale)) {
            abort(404);
        }

        App::setLocale($locale);

        // So route() calls elsewhere never have to pass the locale by hand.
        Locales::applyToUrlGenerator($locale);

        // Drop the segment from the route's parameters: controllers and route
        // model binding should not have to accept a $locale they never use.
        $request->route()?->forgetParameter('locale');

        return $next($request);
    }
}
