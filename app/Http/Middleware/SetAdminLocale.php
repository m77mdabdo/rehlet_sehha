<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * The panel is Arabic. Always.
 *
 * Set on every request rather than relied upon from config, because the app
 * locale is request-scoped and the public site's `locale` middleware changes
 * it constantly. Without this a staff member who was reading the English site
 * in another tab could land on an admin screen half-translated by whatever
 * Filament happens to ship in English.
 *
 * See AdminPanelProvider for why the panel has one language and the public
 * site has two.
 */
class SetAdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        App::setLocale('ar');

        return $next($request);
    }
}
