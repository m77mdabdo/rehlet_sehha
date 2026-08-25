<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\Patient;
use App\Models\Review;
use App\Models\Testimonial;
use App\Models\User;
use App\Notifications\ReviewRequested;
use App\Support\Reviews;
use Database\Seeders\FaqSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use LogicException;

/**
 * Two rules, and neither is cosmetic.
 *
 * NOTHING CLAIMS A QUALIFICATION CONFIG DOES NOT HOLD. These are statements
 * about a real person's professional standing published under her name. The
 * version this replaced claimed a different university, a master's degree she
 * does not hold, and the medical syndicate rather than the agricultural one
 * that actually licenses her — a set of mistakes only ever discovered by the
 * person they misrepresent, or by a patient checking.
 *
 * NOTHING IS PUBLISHED THAT A PATIENT DID NOT AGREE TO PUBLISH. Consent is a
 * separate, unticked decision from writing the review, and it is enforced in
 * the model rather than in the form — a rule that lives in a UI holds until
 * somebody writes a seeder.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(FaqSeeder::class);
});

/*
|------------------------------------------------------------------------------
| Credentials
|------------------------------------------------------------------------------
*/

it('claims no qualification or licensing body that config does not hold', function (string $locale) {
    $html = $this->get("/{$locale}/about")->assertOk()->getContent();
    $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');

    /*
     * The specific false claims that used to be on this page. Named
     * individually rather than checked generically, because a generic check
     * would not have caught any of them — each read as ordinary credential
     * copy.
     */
    $falseClaims = [
        'جامعة القاهرة',      // she studied at Mansoura, not Cairo
        'Cairo University',
        'ماجستير',            // she holds a bachelor's degree
        "master's",
        'نقابة المهن الطبية', // licensed by the AGRICULTURAL syndicate
    ];

    foreach ($falseClaims as $claim) {
        expect(mb_stripos($text, $claim))->toBeFalse(
            "The about page claims «{$claim}», which is not in config and is not true."
        );
    }

    // And what it does say comes from config.
    expect($text)->toContain((string) config('clinic.practitioner.name_ar'));
    expect($text)->toContain((string) config('clinic.practitioner.licence_body_ar'));
    expect($text)->toContain((string) config('clinic.practitioner.licence_number'));
})->with(['ar', 'en']);

it('publishes the licence number so a patient can verify it', function () {
    /*
     * A membership number checkable against the syndicate register is the most
     * verifiable fact on this site, and a far stronger trust signal than any
     * adjective. It is displayed rather than summarised on purpose.
     */
    $html = $this->get('/ar/about')->assertOk()->getContent();

    expect($html)->toContain((string) config('clinic.practitioner.licence_number'));
    expect($html)->toContain((string) config('clinic.practitioner.licence_year'));
});

it('lists every training entry from config, with its hours', function () {
    $html = $this->get('/ar/about')->assertOk()->getContent();

    foreach (config('clinic.training') as $entry) {
        expect($html)->toContain($entry['institution_ar']);

        if ($entry['hours'] !== null) {
            expect($html)->toContain((string) $entry['hours']);
        }
    }
});

/*
|------------------------------------------------------------------------------
| The stats strip
|------------------------------------------------------------------------------
*/

it('writes no figure into the stats section', function () {
    /*
     * Every number in that strip is a claim a patient may act on, and a number
     * typed into a Blade file is a claim nobody can trace back to evidence.
     * config/clinic.php carries what the evidence is for each one.
     *
     * This reads the TEMPLATE, not the output — the output legitimately
     * contains numbers; the point is where they came from.
     */
    $source = file_get_contents(resource_path('views/components/sections/stats.blade.php'));

    /*
     * Strip the things that legitimately contain digits before looking: the
     * comments (whose reasoning mentions the old 4.9), and the CSS classes
     * (py-14, sm:text-5xl, text-white/70). What is left is code and content,
     * and a bare number there is a figure somebody typed in.
     *
     * An allowlist of "safe" numbers was the first attempt and it was the
     * wrong instrument — it failed on py-14 and would have needed extending
     * every time a spacing class changed, which trains people to extend it
     * without reading why.
     */
    $code = (string) preg_replace([
        '/\{\{--.*?--\}\}/su',      // Blade comments
        '/\/\*.*?\*\//su',          // PHP block comments
        '/\/\/[^\n]*/',             // PHP line comments
        '/class="[^"]*"/s',         // CSS classes
        '/@class\(\[.*?\]\)/su',    // conditional CSS classes
    ], '', $source);

    preg_match_all('/\b\d+\b/', $code, $matches);

    expect($matches[0])->toBeEmpty(
        'The stats section contains the literal number(s) '.implode(', ', array_unique($matches[0]))
        .". Every figure must be read from config/clinic.php, where its evidence is recorded.\n"
        .'A number typed into a Blade file is a claim nobody can trace.'
    );

    // And it must actually read from config, rather than from a view variable
    // somebody could fill from anywhere.
    expect($source)->toContain("config('clinic.practitioner");
});

it('shows the real figures, from config', function () {
    $html = $this->get('/ar')->assertOk()->getContent();

    expect($html)->toContain(number_format((int) config('clinic.practitioner.cases_followed')));
    expect($html)->toContain((string) config('clinic.practitioner.clinical_training_hours'));
    expect($html)->toContain((string) config('clinic.practitioner.years_practising'));
});

it('no longer carries an invented rating', function () {
    /*
     * The 4.9 that used to sit in the stats strip came from nowhere. A rating
     * is now computed from real approved reviews, and only above a threshold.
     */
    expect(config('clinic.stats.rating'))->toBeNull();

    $html = $this->get('/ar')->assertOk()->getContent();

    expect(str_contains($html, '4.9'))->toBeFalse('An invented rating is back on the homepage.');
});

/*
|------------------------------------------------------------------------------
| Consent
|------------------------------------------------------------------------------
*/

it('refuses to approve a review the patient did not consent to publish', function () {
    /*
     * THE RULE, AT THE MODEL. Not in the form, not in the admin — either can
     * be bypassed by the next person who writes a command, and what is being
     * bypassed is somebody's decision about their own medical care being made
     * public.
     */
    $review = Review::factory()->submitted()->create();

    expect($review->consented_at)->toBeNull();

    expect(fn () => $review->update(['approved_at' => now()]))
        ->toThrow(LogicException::class);

    expect($review->fresh()->approved_at)->toBeNull();
});

it('allows approval once she has consented', function () {
    $review = Review::factory()->submitted()->consented()->create();

    $review->update(['approved_at' => now()]);

    expect($review->fresh()->approved_at)->not->toBeNull();
    expect($review->fresh()->isPublishable())->toBeTrue();
});

it('never reads an unconsented review into the published set', function () {
    Review::factory()->submitted()->count(5)->create();
    Review::factory()->submitted()->consented()->count(5)->create();

    Cache::flush();

    // None of the ten is approved, so none is published, however many exist.
    expect(Reviews::count())->toBe(0);
    expect(Reviews::shouldDisplay())->toBeFalse();
});

/*
|------------------------------------------------------------------------------
| Thresholds
|------------------------------------------------------------------------------
*/

it('does not render the reviews section below three approved', function () {
    Review::factory()->approved()->submitted()->count(2)->create();

    Cache::flush();

    expect(Reviews::shouldDisplay())->toBeFalse();

    $html = $this->get('/ar')->assertOk()->getContent();

    expect(str_contains($html, 'id="stories"'))->toBeFalse(
        'The reviews section rendered with fewer than three approved reviews. '
        .'An almost-empty testimonials block advertises that nobody has said anything.'
    );
});

it('renders the reviews section once three are approved', function () {
    Review::factory()->approved()->submitted()->count(3)->create();

    Cache::flush();

    expect(Reviews::shouldDisplay())->toBeTrue();

    expect($this->get('/ar')->assertOk()->getContent())->toContain('id="stories"');
});

it('shows no aggregate rating below ten approved reviews', function () {
    Review::factory()->approved()->submitted()->count(9)->create();

    Cache::flush();

    /*
     * Nine fives average to "5.0 out of 5", which reads as a fact about the
     * practice and is really a fact about the sample size. Null rather than a
     * number, so a caller that forgets to check renders nothing.
     */
    expect(Reviews::aggregate())->toBeNull();

    $html = $this->get('/ar')->assertOk()->getContent();

    expect(str_contains($html, __('home.stories.aggregate', ['count' => 9], 'ar')))->toBeFalse();
});

it('computes an aggregate rating once there are ten', function () {
    Review::factory()->approved()->submitted(5)->count(5)->create();
    Review::factory()->approved()->submitted(4)->count(5)->create();

    Cache::flush();

    // Computed, never stored: (5*5 + 4*5) / 10.
    expect(Reviews::aggregate())->toBe(4.5);
});

/*
|------------------------------------------------------------------------------
| The practice has no premises
|------------------------------------------------------------------------------
*/

it('publishes no address anywhere, because there is nowhere to go', function (string $locale) {
    /*
     * A published address for a practice with no premises is worse than none:
     * it looks authoritative and sends a patient to a door that is not there.
     */
    expect(config('clinic.contact.address'))->toBeNull();

    $contact = $this->get("/{$locale}/contact")->assertOk()->getContent();

    expect(str_contains($contact, '<address'))->toBeFalse('The contact page renders an address block.');

    $home = $this->get("/{$locale}")->assertOk()->getContent();

    expect(str_contains($home, 'PostalAddress'))->toBeFalse(
        'The structured data still declares a postal address, which puts a pin on a map for premises that do not exist.'
    );

    // And says what it is instead.
    expect($home)->toContain('areaServed');
})->with(['ar', 'en']);

it('lists the consultation platforms from config', function (string $locale) {
    $html = $this->get("/{$locale}/contact")->assertOk()->getContent();

    expect(config('clinic.platforms'))->not->toBeEmpty();

    foreach (config('clinic.platforms') as $platform) {
        expect($html)->toContain(__("contact.platforms.{$platform}", [], $locale));
    }
})->with(['ar', 'en']);

it('seeds no invented testimonial', function () {
    /*
     * The three that used to be here read like patients and were written by
     * nobody. The seeder is deliberately empty; real reviews arrive through
     * the invitation flow.
     */
    $seeder = file_get_contents(database_path('seeders/TestimonialSeeder.php'));

    expect($seeder)->not->toContain('quote');
    expect($seeder)->toContain('DELIBERATELY EMPTY');
});

/*
|------------------------------------------------------------------------------
| The invitation
|------------------------------------------------------------------------------
|
| How the token reaches the patient at all. Everything above this point tests
| what happens once she has the link; none of it matters if the link is never
| delivered, or is delivered twice, or is delivered to a patient who gave no
| address and therefore silently disappears.
*/

/**
 * A completed appointment, old enough to invite, with a real patient on it.
 *
 * Declared here rather than borrowed from NotificationTest: a helper defined
 * in another test file is only reachable by accident of load order, and a test
 * that passes in the suite and dies when run alone is worse than no test.
 */
function reviewableAppointment(?string $email = 'patient@example.com'): Appointment
{
    $patient = Patient::factory()->create([
        'name' => 'رنا محمود سالم',
        'email' => $email,
    ]);

    return Appointment::factory()->for($patient)->create([
        'status' => AppointmentStatus::Completed,
        'starts_at' => Carbon::now()->subDays(4),
        'ends_at' => Carbon::now()->subDays(4)->addHour(),
    ]);
}

it('invites a patient once, some days after the visit', function () {
    Notification::fake();

    $appointment = reviewableAppointment();

    $this->artisan('clinic:send-review-requests')->assertSuccessful();

    $review = Review::query()->where('appointment_id', $appointment->id)->firstOrFail();

    expect($review->invited_at)->not->toBeNull();
    expect($review->submitted_at)->toBeNull();
    expect($review->consented_at)->toBeNull();
    expect($review->approved_at)->toBeNull();

    // A first name and an initial, not the full name she gave the clinic.
    expect($review->display_name)->toBe('رنا م.');

    Notification::assertSentOnDemand(ReviewRequested::class);

    /*
     * And running again does not ask her twice. The command filters on the
     * absence of a review, and the appointment_id is unique besides — one
     * invitation per visit, because a second one is a nuisance and a third is
     * a reason to distrust the clinic.
     */
    $this->artisan('clinic:send-review-requests')->assertSuccessful();

    expect(Review::query()->where('appointment_id', $appointment->id)->count())->toBe(1);
});

it('records a skipped delivery when the invited patient gave no email address', function () {
    Notification::fake();

    $appointment = reviewableAppointment(email: null);

    $this->artisan('clinic:send-review-requests')->assertSuccessful();

    /*
     * The specific failure this catches: the invitation is the ONLY thing that
     * carries her review token. If it goes nowhere and says nothing, the token
     * exists and she never learns of it, and the clinic sits waiting for a
     * reply that was never asked for. The skipped row is what makes that
     * visible.
     *
     * This is also why the command dispatches through AppointmentNotifier
     * rather than calling notify() on the patient — that path has no delivery
     * log, and there is no address on the model to notify in the first place.
     */
    $log = NotificationLog::query()
        ->where('appointment_id', $appointment->id)
        ->where('template', 'review_requested')
        ->firstOrFail();

    expect($log->status)->toBe(NotificationLog::STATUS_SKIPPED);
    expect($log->error)->toContain('did not give an email address');

    Notification::assertNothingSent();
});

it('does not invite anyone whose appointment did not happen', function () {
    Notification::fake();

    /*
     * Cancelled, no-show, and still only booked. Asking somebody to review
     * care she did not receive is the single worst thing this command could
     * do, and "completed" is the only status that means she received it.
     */
    foreach ([AppointmentStatus::Cancelled, AppointmentStatus::NoShow, AppointmentStatus::Confirmed, AppointmentStatus::Pending] as $status) {
        Appointment::factory()->create([
            'status' => $status,
            'starts_at' => Carbon::now()->subDays(4),
            'ends_at' => Carbon::now()->subDays(4)->addHour(),
        ]);
    }

    // And one completed visit too long ago to be a reasonable thing to receive.
    Appointment::factory()->create([
        'status' => AppointmentStatus::Completed,
        'starts_at' => Carbon::now()->subDays(60),
        'ends_at' => Carbon::now()->subDays(60)->addHour(),
    ]);

    $this->artisan('clinic:send-review-requests')->assertSuccessful();

    expect(Review::query()->count())->toBe(0);
    Notification::assertNothingSent();
});

/*
|------------------------------------------------------------------------------
| No premises
|------------------------------------------------------------------------------
*/

it('claims no clinic location on any page, in either locale', function (string $locale) {
    /*
     * The practice is online and there is nowhere to visit. The contact page
     * was rebuilt around that; the FOOTER was not, and it says its piece on
     * every page of the site — which is exactly how «a clinical nutrition
     * practice in Cairo» outlived the rebuild that removed the address.
     *
     * A patient who reads that and goes looking finds nothing, which is worse
     * than a site that never mentioned a city.
     *
     * The needles below are the location CLAIM. Cairo as a TIME ZONE is a
     * different statement and a true one, so it is deliberately not matched:
     * "بتوقيت القاهرة" and "Cairo time" survive this test.
     */
    $needles = $locale === 'ar'
        ? ['عيادة تغذية علاجية في القاهرة', 'في القاهرة.', 'مقرنا', 'العنوان:']
        : ['practice in Cairo', 'clinic in Cairo', 'our address', 'visit us at'];

    foreach (['', '/about', '/contact', '/packages'] as $path) {
        $content = $this->get("/{$locale}{$path}")->assertOk()->getContent();

        foreach ($needles as $needle) {
            expect(str_contains($content, $needle))->toBeFalse(
                "«{$needle}» appears on /{$locale}{$path}. There are no premises; "
                .'a patient who goes looking for them finds nothing.'
            );
        }
    }
})->with(['ar', 'en']);

it('offers no way to author a testimonial in the admin', function () {
    /*
     * The door the three invented quotes came through, closed.
     *
     * Removing them from the seeder is not enough on its own: the admin had a
     * Testimonials resource with a Create page, labelled «رأي» — the same word
     * as a real review, one menu item away from the real one. Somebody would
     * have typed a name and a quote into it in perfect good faith.
     *
     * A review now has one origin: an invitation to a patient who attended.
     */
    expect(is_dir(app_path('Filament/Resources/Testimonials')))->toBeFalse(
        'The Testimonials admin resource is back. Quotes come from patients, not from the admin.'
    );

    $this->seed(RoleSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    expect($admin->can('create', Testimonial::class))->toBeFalse(
        'An admin can create a testimonial again. Nothing invented ships.'
    );
});
