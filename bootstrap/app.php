<?php

use App\Http\Middleware\ForceHttps;
use App\Http\Middleware\NoIndexAdminPanel;
use App\Http\Middleware\ProtectTokenUrl;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        /*
         * NO `health:` ENTRY, DELIBERATELY.
         *
         * Laravel's built-in /up answers 200 as long as the framework boots.
         * That is true of a site whose cron has been dead for a week and whose
         * queue has not delivered a reminder since Tuesday — exactly the
         * failures worth being told about. /up is defined in routes/web.php
         * instead and actually inspects them. See App\Http\Controllers\HealthController.
         */
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'locale' => SetLocale::class,
            'token-url' => ProtectTokenUrl::class,
        ]);

        /*
         * The panel's no-index headers are appended GLOBALLY as well as being
         * listed on the panel itself, and the reason is the redirect.
         *
         * An unauthenticated hit on /admin never reaches a Filament page: the
         * Authenticate middleware throws, and the exception handler builds the
         * 302 to the login screen. That response comes back up the stack
         * without the panel's own middleware having a chance to touch it, so
         * the redirect alone shipped with no X-Robots-Tag on it.
         *
         * Appended here it runs outermost, so every response under /admin
         * carries the headers — the redirect included. The middleware filters
         * on the path itself; nothing outside the panel is affected.
         */
        $middleware->append(NoIndexAdminPanel::class);

        /*
         * Security headers on every response, prepended so they are set before
         * anything downstream can send output. See SecurityHeaders for why the
         * public site and the panel carry different script policies.
         */
        $middleware->append(SecurityHeaders::class);

        /*
         * http -> https, production only. Before everything, so a redirect
         * costs one round trip rather than rendering a page over plain http
         * and then throwing it away.
         */
        $middleware->prepend(ForceHttps::class);

        /*
         * Shared hosting terminates TLS at a proxy, so without this Laravel
         * sees http on every request — ForceHttps would loop, secure cookies
         * would never be set, and every generated URL would be http.
         */
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
