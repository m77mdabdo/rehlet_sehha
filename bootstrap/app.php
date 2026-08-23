<?php

use App\Http\Middleware\NoIndexAdminPanel;
use App\Http\Middleware\ProtectTokenUrl;
use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
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
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
