<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Review;
use App\Models\Service;
use App\Services\Availability\AvailabilityEngine;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

/**
 * The cancel token is a bearer credential.
 *
 * Anyone holding it can read a patient's medical history, move their
 * appointment, and delete their clinical record — with no password. So the one
 * thing that must never happen is the URL escaping the message it was sent in.
 *
 * The leak this guards against already happened once: the layout emitted
 * canonical, hreflang and og:url tags echoing the current URL, which on this
 * page contains the token. Every one of those tags exists specifically to be
 * handed to a search engine or a link-preview generator.
 */
beforeEach(function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', 'Africa/Cairo'));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
    Carbon::setTestNow();
});

function tokenAppointment(): Appointment
{
    $service = Service::active()->firstOrFail();

    $slot = app(AvailabilityEngine::class)->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(7)->utc(),
        null,
        $service,
    )->firstOrFail();

    return Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $slot->staffId,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
    ]);
}

/**
 * Every route whose URL carries a token.
 *
 * Hardcoded, and held to the real route table by the drift check below.
 *
 * It cannot be derived here: Pest resolves datasets before the application
 * boots, so Route::getRoutes() is still empty at this point. Deriving it
 * produced an empty dataset and a file that reported green while testing
 * nothing at all — which is the exact failure mode this file exists to
 * prevent elsewhere.
 *
 * @return list<string>
 */
function tokenBearingRouteNames(): array
{
    return [
        'appointment.export',
        'appointment.manage',
        'review.show',
        'review.store',
        'review.withdraw',
    ];
}

/**
 * The token routes a browser can GET, which is what the page-level checks
 * below need.
 *
 * review.store is POST and renders nothing, so there is no markup to inspect
 * for a leaked token. It is not therefore unguarded: the drift test asserts
 * every token-bearing route carries the token-url middleware, which is where
 * the noindex, no-store and no-referrer headers come from.
 *
 * @return list<string>
 */
function tokenBearingPages(): array
{
    return ['appointment.export', 'appointment.manage', 'review.show'];
}

/**
 * A working URL for whichever token route is being checked.
 *
 * The review routes carry a REVIEW token, not the appointment's cancel token —
 * two different bearer credentials for two different capabilities, and using
 * one where the other belongs would test nothing.
 */
function tokenUrlFor(string $routeName): string
{
    $appointment = tokenAppointment();

    if (str_starts_with($routeName, 'review.')) {
        $review = Review::factory()->create([
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
        ]);

        return route($routeName, ['locale' => 'ar', 'token' => $review->token]);
    }

    return route($routeName, ['locale' => 'ar', 'token' => $appointment->cancel_token]);
}

it('guards every token-bearing route the application actually registers', function () {
    /*
     * The drift check. The list above is hardcoded because a dataset cannot
     * read the route table, so this is what stops it going stale: register a
     * second route with a {token} segment and this fails until it is added.
     *
     * A test that silently guards nothing is worse than no test.
     */
    $registered = [];

    foreach (Route::getRoutes() as $route) {
        if (in_array('token', $route->parameterNames(), true) && $route->getName() !== null) {
            $registered[] = $route->getName();
        }
    }

    sort($registered);
    $covered = tokenBearingRouteNames();
    sort($covered);

    expect($registered)->toBe(
        $covered,
        "Token-bearing routes have changed.\n"
        .'Registered: '.implode(', ', $registered)."\n"
        .'Covered:    '.implode(', ', $covered)."\n"
        .'Add the new route to tokenBearingRouteNames() so it is checked too.'
    );

    /*
     * And every one of them must carry the middleware that sets the headers.
     * This is what covers review.store, which is POST and renders no markup
     * for the page-level checks to inspect — a token route without these
     * headers leaks through the Referer of whatever it redirects to.
     */
    foreach (Route::getRoutes() as $route) {
        if (! in_array('token', $route->parameterNames(), true) || $route->getName() === null) {
            continue;
        }

        expect(in_array('token-url', $route->gatherMiddleware(), true))->toBeTrue(
            "Route {$route->getName()} takes a token but does not use the token-url middleware, "
            .'so it emits no noindex, no no-store and no no-referrer.'
        );
    }

    expect($covered)->not->toBeEmpty();
});

it('never emits the token in a tag meant for machines', function (string $routeName) {
    $appointment = tokenAppointment();

    $content = $this->get(tokenUrlFor($routeName))
        ->assertOk()
        ->getContent();

    $token = $appointment->cancel_token;

    /*
     * link and meta tags are, by definition, the things a page hands to
     * something that is not a person: search engines, previewers, archivers.
     * The token must appear in none of them — not canonical, not hreflang, not
     * og:url, not any future tag somebody adds without thinking about this.
     */
    preg_match_all('/<(link|meta)\b[^>]*>/i', $content, $tags);

    $leaking = array_values(array_filter(
        $tags[0],
        fn (string $tag): bool => str_contains($tag, $token),
    ));

    expect($leaking)->toBeEmpty(
        'The cancel token appears in '.count($leaking)." machine-readable tag(s).\n"
        ."Anything in a <link> or <meta> is meant to be given away.\n\n"
        .implode("\n", $leaking)."\n"
    );
})->with(tokenBearingPages());

it('never advertises a token url to a crawler as a translation', function (string $routeName) {
    $appointment = tokenAppointment();

    $content = $this->get(tokenUrlFor($routeName))
        ->assertOk()
        ->getContent();

    /*
     * The language switcher necessarily links to this same page in the other
     * language, so its href contains the token. That is navigation, and it is
     * acceptable: same-origin, the patient already holds the token, and
     * Referrer-Policy: no-referrer stops it travelling anywhere else.
     *
     * What is not acceptable is rel="alternate" and hreflang on that anchor.
     * Those tell a crawler the URL is a translation worth following and
     * indexing — the same instruction the <link> tags carry, which is why
     * those are suppressed here too.
     */
    preg_match_all('/<a\b[^>]*'.preg_quote($appointment->cancel_token, '/').'[^>]*>/i', $content, $anchors);

    // Zero is a valid answer — the exported file has no navigation at all.
    // What matters is that none of the anchors that DO exist advertise.
    foreach ($anchors[0] as $anchor) {
        expect($anchor)->not->toContain('rel="alternate"');
        expect($anchor)->not->toContain('hreflang');
    }
})->with(tokenBearingPages());

it('has a language switcher on the manage page that is clean rather than absent', function () {
    // Guards the guard above: if the switcher ever stopped rendering, that
    // test would pass by iterating nothing.
    $appointment = tokenAppointment();

    $content = $this->get(route('appointment.manage', [
        'locale' => 'ar',
        'token' => $appointment->cancel_token,
    ]))->assertOk()->getContent();

    preg_match_all('/<a\b[^>]*'.preg_quote($appointment->cancel_token, '/').'[^>]*>/i', $content, $anchors);

    expect($anchors[0])->not->toBeEmpty('The language switcher is missing from the manage page.');

    foreach ($anchors[0] as $anchor) {
        expect($anchor)->not->toContain('hreflang');
    }
});

it('never sends a token to somewhere that is not us', function (string $routeName) {
    $appointment = tokenAppointment();

    $content = $this->get(tokenUrlFor($routeName))
        ->assertOk()
        ->getContent();

    // Any absolute URL carrying the token must point at our own host. A token
    // in a third-party URL — an analytics beacon, a font CDN, an embedded
    // widget — is the credential leaving the building.
    preg_match_all(
        '#(?:href|src|action|content)="(https?://[^"]*'.preg_quote($appointment->cancel_token, '/').'[^"]*)"#i',
        $content,
        $urls,
    );

    /*
     * ZERO IS THE CORRECT ANSWER, so the loop alone proves nothing.
     *
     * A non-empty guard here failed immediately, which is how this was found:
     * no absolute URL on the page carries the token, and the loop below had
     * been running zero times since it was written.
     *
     * Two assertions replace it. The first proves the EXTRACTION works, by
     * finding absolute URLs on the page at all — without that, a regex that
     * silently stopped matching would look exactly like a clean page. The
     * second is the actual rule.
     */
    preg_match_all('#(?:href|src|action|content)="(https?://[^"]+)"#i', $content, $absolute);

    expect($absolute[1])->not->toBeEmpty(
        'No absolute URL was found on the page at all. The extraction has stopped '
        .'matching how URLs are emitted, so this test can no longer see a leak.'
    );

    foreach ($absolute[1] as $url) {
        expect(str_contains($url, $appointment->cancel_token))->toBeFalse(
            "An absolute URL carries the token: {$url}\n"
            .'If it points anywhere but our own host, the credential has left the building.'
        );
    }

    // And any that DID carry it would have to be ours.
    foreach ($urls[1] as $url) {
        expect(parse_url($url, PHP_URL_HOST))->toBe(parse_url(config('app.url'), PHP_URL_HOST));
    }
})->with(tokenBearingPages());

it('tells crawlers to stay away in a header, not only a meta tag', function (string $routeName) {
    $appointment = tokenAppointment();

    /*
     * A meta tag only works on something that parses the HTML. Link-preview
     * generators, HEAD requests, archivers and proxies read headers and often
     * never build a DOM at all.
     */
    $response = $this->get(tokenUrlFor($routeName))->assertOk();

    expect($response->headers->get('X-Robots-Tag'))->toBe('noindex, nofollow, noarchive');

    // Without this, clicking the privacy link hands the token to the next page
    // in the Referer header.
    expect($response->headers->get('Referrer-Policy'))->toBe('no-referrer');

    // And a shared cache must not hold one patient's page for another.
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
})->with(tokenBearingPages());

it('keeps the token-page hardening off ordinary pages', function () {
    /*
     * WHAT CHANGED, AND WHY THIS ASSERTION IS NARROWER THAN IT WAS.
     *
     * This used to insist that ordinary pages carry NO Referrer-Policy at all.
     * That stopped being right when App\Http\Middleware\SecurityHeaders
     * started setting `strict-origin-when-cross-origin` site-wide — which is a
     * better default than nothing, and does not cost the clinic anything: it
     * sends the full referrer on our own pages and only the bare origin off
     * site. The original worry was about `no-referrer` site-wide stripping
     * analytics, and that is still true and still avoided.
     *
     * So what matters is no longer "no header" but "not the STRICTER header".
     * A token page suppresses the referrer entirely because the URL itself is
     * the credential; an ordinary page has no secret in its URL and gets the
     * ordinary default.
     *
     * The noindex half is unchanged and is the one that would really hurt: a
     * stray X-Robots-Tag on the public site is a mistake nobody notices for a
     * month, by which time the clinic has fallen out of the index.
     */
    $response = $this->get('/ar')->assertOk();

    expect($response->headers->get('X-Robots-Tag'))->toBeNull();

    expect($response->headers->get('Referrer-Policy'))
        ->not->toBe('no-referrer')
        ->toBe('strict-origin-when-cross-origin');
});

it('does not put the token in the confirmation page markup for a crawler to find', function () {
    // The booking page renders a "manage this booking" link after a successful
    // booking. Its own URL carries no token, so canonical and og:url are clean
    // — but the link is in the body, and the page IS indexable. A crawler only
    // ever sees step 1, because the confirmation exists solely in one
    // visitor's component state.
    $content = $this->get('/ar/booking')->assertOk()->getContent();

    expect($content)->not->toContain('/appointment/');
});
