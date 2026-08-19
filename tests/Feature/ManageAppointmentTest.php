<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Livewire\AppointmentManager;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\Service;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use App\Support\Contact;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

/**
 * Cancelling and rescheduling, authenticated by a token in the URL.
 *
 * The token is a bearer credential. Most of what is tested here is about
 * treating it like one — it must be unguessable, it must not reach a third
 * party, and it must not be indexable.
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

function bookedAppointment(?CarbonImmutable $startsAt = null): Appointment
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
        'starts_at' => $startsAt ?? $slot->startsAtUtc,
        'ends_at' => ($startsAt ?? $slot->startsAtUtc)->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
    ]);
}

it('shows the appointment to whoever holds the token', function () {
    $appointment = bookedAppointment();

    $this->get('/ar/appointment/'.$appointment->cancel_token)
        ->assertOk()
        ->assertSee($appointment->reference, false);
});

it('404s an unknown token', function () {
    // Indistinguishable from a URL that never existed.
    $this->get('/ar/appointment/'.str_repeat('a', 64))->assertNotFound();
});

it('cancels and hands the hour back to the calendar', function () {
    $appointment = bookedAppointment();
    $service = $appointment->service;
    $instant = CarbonImmutable::instance($appointment->starts_at)->utc();

    expect(app(AvailabilityEngine::class)->isSlotBookable($instant, $appointment->staff_id, $service))
        ->toBeFalse();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('cancel')
        ->assertSet('flash', 'manage.cancelled');

    $appointment->refresh();

    expect($appointment->status)->toBe(AppointmentStatus::Cancelled);
    expect($appointment->cancelled_at)->not->toBeNull();

    // slot_key nulled by the model hook, so the slot is genuinely free again —
    // not merely marked cancelled.
    expect($appointment->slot_key)->toBeNull();
    expect(app(AvailabilityEngine::class)->isSlotBookable($instant, $appointment->staff_id, $service))
        ->toBeTrue();
});

it('moves an appointment to a new slot atomically', function () {
    $appointment = bookedAppointment();
    $original = CarbonImmutable::instance($appointment->starts_at)->utc();

    $component = Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startReschedule');

    $replacement = $component->instance()->slots()
        ->first(fn (Slot $slot): bool => ! $slot->startsAtUtc->equalTo($original));

    expect($replacement)->not->toBeNull();

    $component->call('selectSlot', $replacement->key())
        ->call('confirmReschedule')
        ->assertSet('flash', 'manage.rescheduled');

    $appointment->refresh();

    expect($appointment->starts_at->utc()->toIso8601ZuluString())
        ->toBe($replacement->startsAtUtc->toIso8601ZuluString());

    // The key moved with it, so the lock follows the appointment.
    expect($appointment->slot_key)
        ->toBe($appointment->staff_id.'-'.$replacement->startsAtUtc->format('Y-m-d H:i:s'));

    // And the old hour is available again.
    expect(app(AvailabilityEngine::class)->isSlotBookable($original, $appointment->staff_id, $appointment->service))
        ->toBeTrue();

    // Still exactly one appointment: a reschedule is a move, not a rebooking.
    expect(Appointment::count())->toBe(1);
});

it('reports a collision when the new slot goes during the reschedule', function () {
    $appointment = bookedAppointment();
    $original = CarbonImmutable::instance($appointment->starts_at)->utc();

    $component = Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startReschedule');

    $target = $component->instance()->slots()
        ->first(fn (Slot $slot): bool => ! $slot->startsAtUtc->equalTo($original));

    $component->call('selectSlot', $target->key());

    // Someone books the target while the patient is deciding.
    Appointment::factory()->create([
        'service_id' => $appointment->service_id,
        'staff_id' => $appointment->staff_id,
        'starts_at' => $target->startsAtUtc,
        'ends_at' => $target->startsAtUtc->addMinutes($appointment->service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $component->call('confirmReschedule')
        ->assertSet('slotWasTaken', true)
        ->assertSet('slotKey', null);

    // The original appointment is untouched — a failed move must not lose the
    // patient the slot they already had.
    $appointment->refresh();
    expect($appointment->starts_at->utc()->toIso8601ZuluString())->toBe($original->toIso8601ZuluString());
    expect($appointment->status)->toBe(AppointmentStatus::Confirmed);
});

it('allows changes outside the cutoff and refuses them inside it', function () {
    config()->set('clinic.booking.reschedule_min_hours', 4);

    // Six hours away: still changeable.
    $far = bookedAppointment(CarbonImmutable::now()->addHours(6)->utc());

    expect(Livewire::test(AppointmentManager::class, ['token' => $far->cancel_token])
        ->instance()->isChangeable())->toBeTrue();

    // Two hours away: inside the cutoff.
    $near = bookedAppointment(CarbonImmutable::now()->addHours(2)->utc());

    $component = Livewire::test(AppointmentManager::class, ['token' => $near->cancel_token]);

    expect($component->instance()->isChangeable())->toBeFalse();

    // Calling cancel anyway does nothing — the guard is in the method, not
    // only in the markup.
    $component->call('cancel');
    expect($near->fresh()->status)->toBe(AppointmentStatus::Confirmed);

    // And the page offers the clinic's phone number instead of a dead button.
    $component->assertSee(__('booking.manage.too_late_title'))
        ->assertSee(Contact::phoneDisplay());
});

it('refuses to change an already cancelled or past appointment', function () {
    $cancelled = bookedAppointment();
    $cancelled->cancel('test');

    expect(Livewire::test(AppointmentManager::class, ['token' => $cancelled->cancel_token])
        ->instance()->isChangeable())->toBeFalse();

    $past = bookedAppointment();
    $past->forceFill(['starts_at' => CarbonImmutable::now()->subDay()])->saveQuietly();

    expect(Livewire::test(AppointmentManager::class, ['token' => $past->cancel_token])
        ->instance()->isChangeable())->toBeFalse();
});

/*
|------------------------------------------------------------------------------
| The token as a credential
|------------------------------------------------------------------------------
*/

it('generates a token with enough entropy to be unguessable', function () {
    $tokens = collect(range(1, 200))->map(fn (): string => Appointment::generateCancelToken());

    // 64 characters of Str::random — alphanumeric, ~380 bits. Nobody is
    // enumerating that.
    expect($tokens->first())->toHaveLength(64);
    expect($tokens->unique())->toHaveCount(200);
    expect($tokens->first())->toMatch('/^[A-Za-z0-9]{64}$/');
});

it('gives every appointment a different token', function () {
    $first = bookedAppointment();
    $second = Appointment::factory()->create();

    expect($first->cancel_token)->not->toBe($second->cancel_token);

    // And one token does not open another appointment.
    $this->get('/ar/appointment/'.$first->cancel_token)
        ->assertOk()
        ->assertDontSee($second->reference, false);
});

it('keeps the token out of anything indexable or outbound', function () {
    $appointment = bookedAppointment();

    $content = $this->get('/ar/appointment/'.$appointment->cancel_token)->assertOk()->getContent();

    // noindex, so a crawler that somehow reached the URL cannot put a working
    // cancellation link into a search result.
    expect($content)->toContain('noindex');
    expect($content)->toContain('no-referrer');

    /*
     * The canonical and hreflang tags must NOT echo this URL — those are meant
     * to be handed to search engines, and doing so would publish the token.
     */
    preg_match_all('/<link[^>]*(rel="canonical"|hreflang)[^>]*>/', $content, $matches);

    foreach ($matches[0] as $tag) {
        expect($tag)->not->toContain($appointment->cancel_token);
    }

    // No og:url either, for the same reason: a pasted link would preview it.
    preg_match_all('/<meta property="og:url"[^>]*>/', $content, $og);

    foreach ($og[0] as $tag) {
        expect($tag)->not->toContain($appointment->cancel_token);
    }
});

/*
|------------------------------------------------------------------------------
| Data subject rights: access, correction, erasure
|------------------------------------------------------------------------------
|
| Egyptian law 151/2020 grants all three, and the privacy page now describes
| them as buttons rather than as a phone call. These tests are what make that
| description true.
|
*/

function appointmentWithIntake(?CarbonImmutable $startsAt = null): Appointment
{
    $appointment = bookedAppointment($startsAt);

    IntakeForm::factory()->create([
        'appointment_id' => $appointment->id,
        'goal' => 'weight_management',
        'medications' => 'ميتفورمين 500',
        'conditions' => 'تكيس مبايض',
        'avoid_foods' => 'مكسرات',
        'note' => 'بشتغل شيفتات',
        'consent_at' => now(),
        'consent_ip' => '203.0.113.4',
    ]);

    return $appointment->fresh();
}

it('shows the patient exactly what they submitted, decrypted', function () {
    $appointment = appointmentWithIntake();

    $component = Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('toggleIntake');

    // They wrote it about themselves; a system that stores it but will not
    // show it back is not protecting them from anything.
    $component->assertSee('ميتفورمين 500', false)
        ->assertSee('تكيس مبايض', false)
        ->assertSee('مكسرات', false)
        ->assertSee('بشتغل شيفتات', false)
        ->assertSee(__('booking.goals.weight_management'), false);
});

it('lets the patient correct their answers while the appointment is ahead', function () {
    $appointment = appointmentWithIntake();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startEditingIntake')
        ->assertSet('medications', 'ميتفورمين 500')
        ->set('medications', 'ميتفورمين 850')
        ->set('conditions', '')
        ->call('saveIntake')
        ->assertHasNoErrors()
        ->assertSet('flash', 'rights.updated');

    $intake = $appointment->intakeForm->fresh();

    expect($intake->medications)->toBe('ميتفورمين 850');
    // A cleared field becomes null, not an empty string — the patient said
    // "none", and null is how the rest of the system spells that.
    expect($intake->conditions)->toBeNull();
});

it('validates a correction exactly as it validates the booking', function () {
    $appointment = appointmentWithIntake();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startEditingIntake')
        ->set('goal', 'not-a-real-goal')
        ->call('saveIntake')
        ->assertHasErrors('goal');

    // A correction is not a lesser kind of clinical record.
    expect($appointment->intakeForm->fresh()->goal)->toBe('weight_management');
});

it('closes correction once the consultation has happened', function () {
    $appointment = appointmentWithIntake();
    $appointment->forceFill(['starts_at' => CarbonImmutable::now()->subDay()])->saveQuietly();

    $intake = $appointment->intakeForm->fresh();
    expect($intake->isCorrectable())->toBeFalse();

    $component = Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('toggleIntake');

    // The record the clinician read during the session must not change
    // afterwards, or the notes and the decision made from them disagree.
    $component->call('startEditingIntake')->assertSet('editingIntake', false);
    $component->set('medications', 'محاولة تعديل')->call('saveIntake');

    expect($appointment->intakeForm->fresh()->medications)->toBe('ميتفورمين 500');

    // And the page says why, and offers the phone number instead.
    $component->assertSee(__('booking.rights.correction_closed'));
});

it('erases the clinical content and keeps the booking', function () {
    $appointment = appointmentWithIntake();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startErasure')
        ->assertSet('confirmingErasure', true)
        // The confirmation states what goes and what stays. An "are you sure?"
        // that does not say what it deletes is not informed consent to delete.
        ->assertSee(__('booking.rights.erase_removes_heading'))
        ->assertSee(__('booking.rights.erase_keeps_heading'))
        ->call('eraseIntake')
        ->assertSet('flash', 'rights.erased');

    $intake = $appointment->intakeForm->fresh();

    foreach (['goal', 'medications', 'conditions', 'avoid_foods', 'note'] as $field) {
        expect($intake->{$field})->toBeNull();
    }

    expect($intake->erased_at)->not->toBeNull();
    expect($intake->isErased())->toBeTrue();

    // Consent evidence survives: destroying it would leave the clinic unable
    // to show it ever had permission for data it has since deleted.
    expect($intake->consent_at)->not->toBeNull();
    expect($intake->consent_ip)->toBe('203.0.113.4');

    // THE BOOKING SURVIVES. Erasure is of clinical content, not of the
    // clinic's record that the hour was used.
    $appointment->refresh();
    expect($appointment->exists)->toBeTrue();
    expect($appointment->trashed())->toBeFalse();
    expect($appointment->patient->name)->not->toBeNull();
    expect($appointment->starts_at)->not->toBeNull();
});

it('erases the encrypted columns at rest, not just in the model', function () {
    $appointment = appointmentWithIntake();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startErasure')
        ->call('eraseIntake');

    // Read straight from the table: an erasure that only clears the accessor
    // would leave the ciphertext sitting in every backup.
    $raw = DB::table('intake_forms')->where('appointment_id', $appointment->id)->first();

    foreach (['goal', 'medications', 'conditions', 'avoid_foods', 'note'] as $column) {
        expect($raw->{$column})->toBeNull();
    }

    expect($raw->erased_at)->not->toBeNull();
});

it('warns that erasing an upcoming appointment leaves the doctor unprepared', function () {
    $appointment = appointmentWithIntake();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startErasure')
        ->assertSee(__('booking.rights.erase_upcoming_warning'));
});

it('still allows erasure after the appointment has passed', function () {
    // The right to have clinical content removed does not expire when the
    // consultation ends; only the right to rewrite it does.
    $appointment = appointmentWithIntake();
    $appointment->forceFill(['starts_at' => CarbonImmutable::now()->subDay()])->saveQuietly();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startErasure')
        ->call('eraseIntake');

    expect($appointment->intakeForm->fresh()->isErased())->toBeTrue();
});

it('says the record is erased rather than showing an empty form', function () {
    $appointment = appointmentWithIntake();
    $appointment->intakeForm->eraseClinicalContent();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('toggleIntake')
        // An empty form reads like data loss; this reads like a request that
        // was honoured.
        ->assertSee(__('booking.rights.erased_title'))
        ->assertDontSee(__('booking.rights.correct'));
});

it('will not erase twice', function () {
    $appointment = appointmentWithIntake();
    $appointment->intakeForm->eraseClinicalContent();

    $first = $appointment->intakeForm->fresh()->erased_at;

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startErasure')
        ->assertSet('confirmingErasure', false);

    expect($appointment->intakeForm->fresh()->erased_at->timestamp)->toBe($first->timestamp);
});
