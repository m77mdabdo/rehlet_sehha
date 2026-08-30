<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Redirect http to https, in production only.
 *
 * Belt and braces beside HSTS, which only protects a browser that has already
 * visited once over https. The FIRST visit — a patient typing the domain, or
 * following a printed link — has no HSTS entry and would otherwise be served
 * over plain http, with her appointment token in the URL.
 *
 * Shared hosting terminates TLS at a proxy, so the scheme Laravel sees is
 * http even when the visitor is on https. TrustProxies handles that, and this
 * checks $request->secure() which respects the forwarded header — checking
 * getScheme() directly would produce an infinite redirect loop behind the
 * proxy, which is the classic way to take a site down while securing it.
 */
class ForceHttps
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->isProduction() && ! $request->secure()) {
            return redirect()->secure($request->getRequestUri(), 301);
        }

        return $next($request);
    }
}
