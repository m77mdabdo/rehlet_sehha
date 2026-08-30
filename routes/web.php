<?php

use App\Http\Controllers\AboutController;
use App\Http\Controllers\ArticlesController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ExportAppointmentController;
use App\Http\Controllers\FaqController;
use App\Http\Controllers\HealthController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\HowItWorksController;
use App\Http\Controllers\ManageAppointmentController;
use App\Http\Controllers\ManifestController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SitemapController;
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

/*
 * CONSTRAIN THE LOCALE SEGMENT. Without this, {locale} matches any single
 * path segment, so every unmatched top-level URL — /sitemap.xml, /robots,
 * /anything — is routed into the homepage controller and 404s from inside
 * SetLocale rather than from the router. It also means no route defined after
 * this group can ever have a single-segment path, which is how the sitemap
 * came to 404 while appearing correctly in route:list.
 */
Route::prefix('{locale}')
    ->middleware('locale')
    ->where(['locale' => implode('|', Locales::all())])
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
            ->middleware(['token-url', 'throttle:60,60'])
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
        /*
         * Category and tag indexes come BEFORE articles/{slug}, or the router
         * reads "category" as an article slug and every category page 404s.
         */
        Route::get('articles/category/{slug}', [ArticlesController::class, 'category'])
            ->name('articles.category');

        Route::get('articles/tag/{slug}', [ArticlesController::class, 'tag'])
            ->name('articles.tag');

        Route::get('articles/{slug}', [PostController::class, 'show'])
            ->name('posts.show');

        /*
         * Leaving a review. Same token discipline as the cancellation link:
         * the token is a bearer credential, so the page is noindex, no-store
         * and no-referrer, and never appears in a canonical, an hreflang or an
         * og:url. TokenUrlHygieneTest asserts all of that.
         */
        Route::get('review/{token}', [ReviewController::class, 'show'])
            ->middleware(['token-url', 'throttle:60,60'])
            ->name('review.show')
            ->where('token', '[A-Za-z0-9]{32,80}');

        /*
         * Throttled. The token is unguessable, so this is not brute-force
         * protection — it is protection against a token that HAS leaked being
         * used to hammer the endpoint, and against a stuck client retrying a
         * submission in a loop.
         */
        Route::post('review/{token}', [ReviewController::class, 'store'])
            ->middleware(['token-url', 'throttle:10,60'])
            ->name('review.store')
            ->where('token', '[A-Za-z0-9]{32,80}');

        /*
         * Taking a published review back down.
         *
         * A separate route rather than a flag on store(), because store()
         * refuses a second submission — which is right, and which left a
         * patient who ticked the consent box and changed her mind with no way
         * back except telephoning the clinic.
         */
        Route::post('review/{token}/withdraw', [ReviewController::class, 'withdraw'])
            ->middleware(['token-url', 'throttle:10,60'])
            ->name('review.withdraw')
            ->where('token', '[A-Za-z0-9]{32,80}');

        Route::get('site.webmanifest', ManifestController::class)
            ->middleware('cache.headers:public;max_age=86400;etag')
            ->name('manifest');
    });

/*
 * The sitemap. Outside the locale group on purpose: there is one sitemap for
 * the whole site and it lists both languages internally, so /ar/sitemap.xml
 * and /en/sitemap.xml would be the same file at two addresses.
 */
Route::get('sitemap.xml', SitemapController::class)
    ->middleware('cache.headers:public;max_age=3600;etag')
    ->name('sitemap');

/*
 * The health check.
 *
 * Outside the locale group and outside every cache header: a monitor asking
 * whether the site is alive must never be answered from a cache, and a status
 * page that is a minute stale is a status page that lies.
 *
 * Unauthenticated on purpose — an uptime monitor cannot hold a credential
 * without that credential being the thing most likely to leak — which is why
 * the response is names and pass/fail and nothing else. See HealthController.
 *
 * Throttled because each hit writes a file and touches the database, and an
 * unauthenticated endpoint that does work is an endpoint somebody will point a
 * load generator at. Thirty a minute is more than any monitor needs.
 */
Route::get('up', HealthController::class)
    ->middleware('throttle:30,1')
    ->name('health');
