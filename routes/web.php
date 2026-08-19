<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ManageAppointmentController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\SpecialtyController;
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
        // The booking wizard. ?service={slug} preselects and opens on step 2.
        Route::get('booking', BookingController::class)->name('booking');

        /*
         * Cancel or reschedule, authenticated by an unguessable token rather
         * than a login. A clinic whose patients needed an account to cancel
         * would have patients who simply do not turn up instead.
         *
         * The token is a bearer credential: it is noindex'd, it is never put
         * in an outbound link, and it must not be handed to any third party.
         */
        Route::get('appointment/{token}', ManageAppointmentController::class)
            ->middleware('token-url')
            ->name('appointment.manage')
            ->where('token', '[A-Za-z0-9]{32,80}');

        // Stub. The consent notice links here, so it must resolve today.
        Route::view('privacy', 'pages.privacy')->name('privacy');

        /*
         * Clinical areas. Landing pages for search traffic — someone who
         * searched their own condition arrives here, not on the homepage.
         * Note for whoever builds the sitemap: THESE PAGES BELONG IN IT,
         * along with the article pages. They are the reason the specialties
         * table exists as its own entity rather than a list of strings.
         */
        Route::get('specialties/{slug}', [SpecialtyController::class, 'show'])
            ->name('specialties.show');

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
