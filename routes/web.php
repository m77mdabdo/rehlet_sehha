<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PostController;
use App\Support\Locales;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Locale-prefixed routes
|--------------------------------------------------------------------------
|
| Every public page lives under /{locale}/. A bare / redirects to the default
| locale rather than serving content, so there is exactly one canonical URL
| per page per language and no duplicate-content ambiguity for search engines.
|
| The redirect is 302, not 301: the day we add locale detection from
| Accept-Language, a cached permanent redirect on every visitor's machine would
| be very hard to undo.
|
*/

Route::redirect('/', '/'.Locales::DEFAULT);

Route::prefix('{locale}')
    ->middleware('locale')
    ->group(function (): void {
        Route::get('/', [HomeController::class, '__invoke'])->name('home');

        /*
         * Built from config rather than served as a static file, so the brand
         * colours live in one config mirror instead of a third copy. Inside
         * the locale group because the manifest carries lang, dir and the app
         * name, and an English visitor should not install an Arabic app.
         */
        /*
         * Booking. A placeholder page until Task 5 fills in the form — it
         * exists now so the packages section can deep-link a preselected
         * service instead of pointing the homepage at a 404.
         */
        Route::get('booking', BookingController::class)->name('booking');

        /*
         * Articles. Only the single-post page exists; an index belongs to
         * whichever task owns the blog. The homepage links three cards here.
         */
        Route::get('articles/{slug}', [PostController::class, 'show'])
            ->name('posts.show');

        Route::get('site.webmanifest', ManifestController::class)
            ->middleware('cache.headers:public;max_age=86400;etag')
            ->name('manifest');
    });
