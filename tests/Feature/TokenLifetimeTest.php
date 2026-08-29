<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Review;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

/**
 * A TOKEN IS A BEARER CREDENTIAL, AND ONE THAT NEVER EXPIRES IS A PERMANENT KEY.
 *
 * Whoever holds a manage URL can cancel or reschedule without proving anything
 * else. Whoever holds a review URL can write under a patient's name. Both used
 * to work forever, which meant every forwarded email, shared screenshot and
 * synced mailbox stayed live indefinitely.
 *
 * Expiry is DERIVED, not stored: the appointment already knows when it ended
 * and the invitation already knows when it was sent. A column would be a
 * second copy of a fact the row already holds, and a second thing to keep
 * right.
 *
 * WHAT AN EXPIRED LINK MUST DO. Not 404 — she is a patient holding an email we
 * sent her, and "not found" tells her she did something wrong. An UNKNOWN
 * token still 404s, because a URL that never existed has to stay
 * indistinguishable from a wrong guess or the page becomes a way to probe for
 * live tokens.
 */
beforeEach(function () {
    Cache::flush();
    $this->seed(ServiceSeeder::class);
});

/*
|------------------------------------------------------------------------------
| The appointment manage link
|------------------------------------------------------------------------------
*/

it('keeps the manage link working through the appointment and its grace period', function (int $daysAfter) {
    $appointment = Appointment::factory()->create([
        'status' => AppointmentStatus::Confirmed,
        'starts_at' => Carbon::now()->subDays($daysAfter)->subHour(),
        'ends_at' => Carbon::now()->subDays($daysAfter),
    ]);

    $this->get(route('appointment.manage', ['locale' => 'ar', 'token' => $appointment->cancel_token]))
        ->assertOk()
        ->assertDontSee(__('tokens.expired.title', [], 'ar'), false);
})->with([0, 1, 13]);

it('explains itself rather than 404ing once the manage link has aged out', function () {
    $appointment = Appointment::factory()->create([
        'status' => AppointmentStatus::Completed,
        'starts_at' => Carbon::now()->subDays(30)->subHour(),
        'ends_at' => Carbon::now()->subDays(30),
    ]);

    expect($appointment->tokenHasExpired())->toBeTrue();

    $response = $this->get(route('appointment.manage', [
        'locale' => 'ar',
        'token' => $appointment->cancel_token,
    ]));

    // 200 and an explanation, NOT a 404.
    $response->assertOk();
    $response->assertSee(__('tokens.expired.title', [], 'ar'), false);
    $response->assertSee(__('tokens.expired.appointment', [], 'ar'), false);

    // And it still refuses to be indexed: the URL in the bar is a spent
    // credential, but it is still a credential.
    $response->assertSee('noindex', false);
});

it('still 404s a token that never existed', function () {
    /*
     * The distinction the whole design rests on. If an unknown token returned
     * the expiry page, the difference between the two responses would tell
     * somebody probing which tokens are real.
     */
    $this->get(route('appointment.manage', ['locale' => 'ar', 'token' => str_repeat('z', 64)]))
        ->assertNotFound();
});

/*
|------------------------------------------------------------------------------
| The review invitation
|------------------------------------------------------------------------------
*/

it('accepts a review inside the invitation window and refuses it after', function () {
    $fresh = Review::factory()->create(['invited_at' => Carbon::now()->subDays(29)]);
    $stale = Review::factory()->create(['invited_at' => Carbon::now()->subDays(31)]);

    expect($fresh->tokenHasExpired())->toBeFalse();
    expect($stale->tokenHasExpired())->toBeTrue();

    $this->get(route('review.show', ['locale' => 'ar', 'token' => $fresh->token]))
        ->assertOk()
        ->assertDontSee(__('tokens.expired.title', [], 'ar'), false);

    $this->get(route('review.show', ['locale' => 'ar', 'token' => $stale->token]))
        ->assertOk()
        ->assertSee(__('tokens.expired.review', [], 'ar'), false);
});

it('refuses a submission against an expired invitation on the server', function () {
    /*
     * Hiding the form is not enough. A tab left open for a month would still
     * POST, and the endpoint has to say no on its own.
     */
    $stale = Review::factory()->create(['invited_at' => Carbon::now()->subDays(40)]);

    $this->post(route('review.store', ['locale' => 'ar', 'token' => $stale->token]), [
        'rating' => 5,
        'comment' => 'submitted from a stale tab',
    ])->assertStatus(410);

    expect($stale->fresh()->submitted_at)->toBeNull();
});

it('never expires an invitation she has already answered', function () {
    /*
     * Expiry stops her ANSWERING. It must not stop her reading back what she
     * said or withdrawing consent, or the clock becomes a lock on her own
     * words.
     */
    $answered = Review::factory()->submitted()->consented()->create([
        'invited_at' => Carbon::now()->subDays(200),
    ]);

    expect($answered->tokenHasExpired())->toBeFalse();

    $this->get(route('review.show', ['locale' => 'ar', 'token' => $answered->token]))
        ->assertOk()
        ->assertSee(__('review.withdraw_action', [], 'ar'), false);
});

/*
|------------------------------------------------------------------------------
| Withdrawing consent
|------------------------------------------------------------------------------
*/

it('lets a patient take a published review back down', function () {
    $review = Review::factory()->submitted()->approved()->create();

    expect(Review::approved()->pluck('id')->all())->toContain($review->id);

    $this->post(route('review.withdraw', ['locale' => 'ar', 'token' => $review->token]))
        ->assertRedirect();

    $review->refresh();

    expect($review->consented_at)->toBeNull();
    expect($review->approved_at)->toBeNull('Withdrawing consent must also drop the approval.');
    expect($review->comment)->not->toBeNull('Withdrawal is not erasure; the clinic keeps her words.');

    expect(Review::approved()->pluck('id')->all())->not->toContain($review->id);
});

it('withdraws even from an invitation that has otherwise aged out', function () {
    $review = Review::factory()->submitted()->approved()->create([
        'invited_at' => Carbon::now()->subDays(400),
    ]);

    $this->post(route('review.withdraw', ['locale' => 'ar', 'token' => $review->token]))
        ->assertRedirect();

    expect($review->fresh()->consented_at)->toBeNull();
});

/*
|------------------------------------------------------------------------------
| Erasure reaches the review
|------------------------------------------------------------------------------
*/

it('takes her name and her words off the site when she erases her record', function () {
    /*
     * The gap this closes: erasure stopped at the intake form, so a patient
     * who exercised her right to be forgotten could still find her own name
     * and her own words published on the front page. The privacy page promises
     * erasure under Egyptian law 151/2020, and a promise the code does not
     * keep is worse than no promise.
     */
    $review = Review::factory()->submitted()->approved()->create([
        'comment' => 'المتابعة كانت منظمة جدًا.',
        'display_name' => 'رنا م.',
    ]);

    $review->eraseForPatient();
    $review->refresh();

    expect($review->exists)->toBeTrue('The row survives: it is the clinic\'s record that it asked.');
    expect($review->comment)->toBeNull();
    expect($review->display_name)->toBeNull();
    expect($review->rating)->toBeNull();
    expect($review->consented_at)->toBeNull();
    expect($review->approved_at)->toBeNull();

    expect(Review::approved()->pluck('id')->all())->not->toContain($review->id);
});

it('cannot be re-approved after erasure', function () {
    /*
     * The structural half. With consent gone the model refuses the approval
     * outright, so no amount of admin-side enthusiasm can put an erased
     * patient's words back on the site.
     */
    $review = Review::factory()->submitted()->approved()->create();
    $review->eraseForPatient();

    $approver = User::factory()->create();

    expect(fn () => $review->update([
        'approved_at' => Carbon::now(),
        'approved_by' => $approver->id,
    ]))->toThrow(LogicException::class);
});
