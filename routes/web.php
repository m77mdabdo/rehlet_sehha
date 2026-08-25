<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExportAppointmentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HowItWorksController;
use App\Http\Controllers\ManageAppointmentController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServicesController;
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

        /*
         * The access right under law 151/2020, as a file the patient keeps.
         * Same token, same protections — the body of this response IS the
         * medical record, so nothing may cache it.
         */
        Route::get('appointment/{token}/export', ExportAppointmentController::class)
            ->middleware('token-url')
            ->name('appointment.export')
            ->where('token', '[A-Za-z0-9]{32,80}');

        /*
         * The standalone pages behind the nav.
         *
         * Each one is the full treatment of a homepage section, not a copy of
         * it — the section summarises, the page decides. A page that repeated
         * its section verbatim would compete with it in search and both would
         * rank lower, so PackagesPageTest measures the overlap and fails above
         * a threshold.
         *
         * NOTE FOR WHOEVER BUILDS THE SITEMAP: THESE BELONG IN IT, alongside
         * the specialty and article pages. Seven more land here as they are
         * built — services, how-it-works, about, articles, faq, contact.
         */
        Route::get('services', ServicesController::class)->name('services');
        Route::get('packages', PackagesController::class)->name('packages');
        Route::get('how-it-works', HowItWorksController::class)->name('how-it-works');
        Route::get('about', AboutController::class)->name('about');
        Route::get('faq', FaqController::class)->name('faq');
        Route::get('contact', ContactController::class)->name('contact');

        /*
         * The article index sits ABOVE articles/{slug} so a post can never
         * be reached at a URL the index would have claimed.
         */
        Route::get('articles', ArticlesController::class)->name('articles');

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

        /*
         * Leaving a review. Same token discipline as the cancellation link:
         * the token is a bearer credential, so the page is noindex, no-store
         * and no-referrer, and never appears in a canonical, an hreflang or an
         * og:url. TokenUrlHygieneTest asserts all of that.
         */
        Route::get('review/{token}', [ReviewController::class, 'show'])
            ->middleware('token-url')
            ->name('review.show')
            ->where('token', '[A-Za-z0-9]{32,80}');

        Route::post('review/{token}', [ReviewController::class, 'store'])
            ->middleware('token-url')
            ->name('review.store')
            ->where('token', '[A-Za-z0-9]{32,80}');

        Route::get('site.webmanifest', ManifestController::class)
            ->middleware('cache.headers:public;max_age=86400;etag')
            ->name('manifest');
    });
