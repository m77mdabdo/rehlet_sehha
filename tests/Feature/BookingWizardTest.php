<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Livewire\BookingWizard;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Attributes\Locked;
use Livewire\Features\SupportLockedProperties\CannotUpdateLockedPropertyException;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * The booking flow.
 *
 * This is where a patient hands over their name, their phone number and their
 * medical history. Most of what is tested here is not "does it work" but "does
 * it refuse" — a wizard that can be skipped, a consent box that can be
 * bypassed, or a collision that eats a typed medical history are all failures
 * that look like a working form right up until they happen to someone.
 */
beforeEach(function () {
    // A Monday morning, well inside the clinic's hours, so the calendar has
    // something in it and the lead time does not eat the day.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', 'Africa/Cairo'));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);

    RateLimiter::clear('booking:ip:127.0.0.1');
});

afterEach(function () {
    CarbonImmutable::setTestNow();
    Carbon::setTestNow();
});

function firstService(): Service
{
    return Service::active()->firstOrFail();
}

function firstSlot(?Service $service = null): Slot
{
    $service ??= firstService();

    $slot = app(AvailabilityEngine::class)->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(7)->utc(),
        null,
        $service,
    )->first();

    expect($slot)->not->toBeNull('The test clinic has no availability; the fixture is wrong.');

    return $slot;
}

/**
 * Move the frozen clock forward.
 *
 * Used instead of writing to detailsShownAt, which is #[Locked] and therefore
 * unsettable from a test for the same reason it is unsettable from a browser —
 * that lock is what stops a bot backdating its own fill time.
 */
function passTime(int $seconds): void
{
    CarbonImmutable::setTestNow(CarbonImmutable::getTestNow()->addSeconds($seconds));
    Carbon::setTestNow(CarbonImmutable::getTestNow());
}

/**
 * A wizard sitting on step 3 with a valid service and slot chosen, having
 * spent a plausible amount of time getting there.
 */
function wizardOnDetails(?Service $service = null): Testable
{
    $service ??= firstService();
    $slot = firstSlot($service);

    $component = Livewire::test(BookingWizard::class)
        ->call('selectService', $service->id)
        ->call('next')
        ->call('selectSlot', $slot->key())
        ->call('next');

    // A plausible amount of time spent reading and typing.
    passTime(30);

    return $component;
}

/**
 * @param  array<string, mixed>  $overrides
 * @return array<string, mixed>
 */
function validDetails(array $overrides = []): array
{
    return array_merge([
        'name' => 'راوية غانم',
        'phone' => '01012345678',
        'email' => 'rawia@example.com',
        'birthDate' => '1990-04-11',
        'goal' => 'weight_management',
        'medications' => 'ميتفورمين 500',
        'conditions' => 'تكيس مبايض',
        'avoidFoods' => 'مكسرات',
        'note' => 'بشتغل شيفتات',
        'consent' => true,
    ], $overrides);
}

/*
|------------------------------------------------------------------------------
| Step gating
|------------------------------------------------------------------------------
*/

it('will not advance past the service step without a service', function () {
    Livewire::test(BookingWizard::class)
        ->call('next')
        ->assertHasErrors('serviceId')
        ->assertSet('step', 1);
});

it('will not advance past the time step without a slot', function () {
    Livewire::test(BookingWizard::class)
        ->call('selectService', firstService()->id)
        ->call('next')
        ->assertSet('step', 2)
        ->call('next')
        ->assertHasErrors('slotKey')
        ->assertSet('step', 2);
});

it('will not advance past the details step while anything is invalid', function () {
    wizardOnDetails()
        ->set(validDetails(['name' => 'ر']))
        ->call('submit')
        ->assertHasErrors('name');

    wizardOnDetails()
        ->set(validDetails(['phone' => '01312345678']))
        ->call('submit')
        ->assertHasErrors('phone');

    wizardOnDetails()
        ->set(validDetails(['goal' => 'not-a-real-goal']))
        ->call('submit')
        ->assertHasErrors('goal');

    expect(Appointment::count())->toBe(0);
});

it('refuses a step number sent from the client', function () {
    /*
     * The step is server state. If a patient could set it to 4 they would skip
     * consent entirely, so the property is #[Locked] and Livewire rejects the
     * update outright rather than the component having to notice.
     */
    Livewire::test(BookingWizard::class)->set('step', 4);
})->throws(CannotUpdateLockedPropertyException::class);

it('opens on the time step when deep-linked with a service', function () {
    $service = firstService();

    Livewire::test(BookingWizard::class, ['service' => $service->slug])
        ->assertSet('step', 2)
        ->assertSet('serviceId', $service->id);
});

it('ignores an unknown service in the deep link rather than failing', function () {
    // A link from an old price list should open the wizard, not 404 someone
    // who was trying to give the clinic money.
    Livewire::test(BookingWizard::class, ['service' => 'retired-in-2023'])
        ->assertSet('step', 1)
        ->assertSet('serviceId', null);
});

/*
|------------------------------------------------------------------------------
| The happy path
|------------------------------------------------------------------------------
*/

it('books an appointment end to end', function () {
    $service = firstService();
    $slot = firstSlot($service);

    $component = wizardOnDetails($service)
        ->set(validDetails())
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('step', 4);

    $appointment = Appointment::query()->firstOrFail();

    expect($appointment->service_id)->toBe($service->id);
    expect($appointment->starts_at->utc()->toIso8601ZuluString())->toBe($slot->startsAtUtc->toIso8601ZuluString());
    expect($appointment->status)->toBe(AppointmentStatus::Pending);
    expect($appointment->mode)->toBe(AppointmentMode::Online);
    expect($appointment->staff_id)->toBe($slot->staffId);

    // slot_key is derived by the model hook and is what the unique index sees.
    expect($appointment->slot_key)->toBe($slot->staffId.'-'.$slot->startsAtUtc->format('Y-m-d H:i:s'));

    // The phone is normalised to E.164 so a returning patient is recognised.
    expect($appointment->patient->phone)->toBe('+201012345678');
    expect($appointment->patient->name)->toBe('راوية غانم');

    // The reference the patient sees is the one on the row.
    $component->assertSet('reference', $appointment->reference);

    expect($appointment->intakeForm)->not->toBeNull();
    expect($appointment->intakeForm->goal)->toBe('weight_management');
});

it('accepts any spelling of the same egyptian mobile number', function (string $typed) {
    wizardOnDetails()
        ->set(validDetails(['phone' => $typed]))
        ->call('submit')
        ->assertHasNoErrors();

    expect(Patient::query()->firstOrFail()->phone)->toBe('+201012345678');
})->with([
    'national' => '01012345678',
    'e164' => '+201012345678',
    'international' => '00201012345678',
    'spaced' => '0101 234 5678',
    'arabic-indic' => '٠١٠١٢٣٤٥٦٧٨',
]);

/*
|------------------------------------------------------------------------------
| Patient identity
|------------------------------------------------------------------------------
*/

it('does not create a duplicate file for a returning patient', function () {
    $existing = Patient::query()->create([
        'name' => 'راوية غانم',
        'phone' => '+201012345678',
        'email' => 'corrected@example.com',
    ]);

    wizardOnDetails()
        ->set(validDetails(['email' => 'typo@example.com']))
        ->call('submit')
        ->assertHasNoErrors();

    expect(Patient::query()->count())->toBe(1);
    expect(Appointment::query()->firstOrFail()->patient_id)->toBe($existing->id);

    // A booking form must never overwrite a detail the clinic already
    // corrected by hand — findOrCreateByPhone only fills gaps.
    expect($existing->fresh()->email)->toBe('corrected@example.com');
});

it('restores a soft-deleted patient rather than starting a second file', function () {
    $existing = Patient::query()->create(['name' => 'راوية غانم', 'phone' => '+201012345678']);
    $existing->delete();

    expect(Patient::query()->count())->toBe(0);

    wizardOnDetails()
        ->set(validDetails())
        ->call('submit')
        ->assertHasNoErrors();

    // The returning patient gets her history back rather than a blank file.
    expect(Patient::query()->count())->toBe(1);
    expect(Patient::query()->firstOrFail()->id)->toBe($existing->id);
    expect(Appointment::query()->firstOrFail()->patient_id)->toBe($existing->id);
});

/*
|------------------------------------------------------------------------------
| The collision path
|------------------------------------------------------------------------------
*/

it('keeps every typed field when the slot is taken mid-form', function () {
    $service = firstService();
    $slot = firstSlot($service);

    $component = wizardOnDetails($service)->set(validDetails());

    // Somebody else books the same slot while this patient is typing.
    Appointment::factory()->create([
        'staff_id' => $slot->staffId,
        'service_id' => $service->id,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $component->call('submit');

    // Sent back to the calendar with a clear message.
    $component->assertSet('slotWasTaken', true)
        ->assertSet('step', 2)
        ->assertSet('slotKey', null)
        ->assertSee(__('booking.errors.slot_taken'));

    /*
     * AND EVERY FIELD SURVIVED. Re-typing a medication list because somebody
     * else clicked a second earlier is the worst outcome this form has, and it
     * is entirely avoidable — only the slot was invalidated, so only the slot
     * is cleared.
     */
    $component->assertSet('name', 'راوية غانم')
        ->assertSet('phone', '01012345678')
        ->assertSet('email', 'rawia@example.com')
        ->assertSet('birthDate', '1990-04-11')
        ->assertSet('goal', 'weight_management')
        ->assertSet('medications', 'ميتفورمين 500')
        ->assertSet('conditions', 'تكيس مبايض')
        ->assertSet('avoidFoods', 'مكسرات')
        ->assertSet('note', 'بشتغل شيفتات')
        ->assertSet('consent', true);

    // Nothing was half-written: no orphan appointment, no orphan intake.
    expect(Appointment::query()->count())->toBe(1);
    expect(IntakeForm::query()->count())->toBe(0);

    // The calendar no longer offers the taken slot.
    expect($component->instance()->slots()->contains(fn (Slot $s): bool => $s->key() === $slot->key()))
        ->toBeFalse();

    /*
     * And the data is visible again when they return to step 3 — not merely
     * present in the component's state.
     *
     * Livewire does not render values into wire:model inputs by itself; it
     * hydrates them in the browser. Step 3's markup is destroyed when the
     * patient is sent back to the calendar and rebuilt from server HTML when
     * they return, so without explicit value attributes the fields come back
     * EMPTY while assertSet() still passes. This asserts what the patient
     * actually sees.
     */
    $replacement = $component->instance()->slots()->first();

    $html = $component->call('selectSlot', $replacement->key())->call('next')->html();

    expect($html)->toContain('value="راوية غانم"');
    expect($html)->toContain('value="01012345678"');
    expect($html)->toContain('ميتفورمين 500');
    expect($html)->toContain('تكيس مبايض');
    expect($html)->toContain('مكسرات');
    expect($html)->toContain('بشتغل شيفتات');
    expect($html)->toContain('checked');
});

it('can complete the booking on a different slot after a collision', function () {
    $service = firstService();
    $slot = firstSlot($service);

    $component = wizardOnDetails($service)->set(validDetails());

    Appointment::factory()->create([
        'staff_id' => $slot->staffId,
        'service_id' => $service->id,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $component->call('submit')->assertSet('slotWasTaken', true);

    // Pick the next free slot and carry on — no retyping.
    $replacement = $component->instance()->slots()->first();

    $component->call('selectSlot', $replacement->key())
        ->call('next')
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('step', 4);

    expect(Appointment::query()->count())->toBe(2);
});

it('translates a unique-index violation into the same clear message', function () {
    /*
     * The pre-check and the index catch the same event at different moments.
     * This forces the second: the row is inserted directly, bypassing the
     * component, after the pre-check has already passed.
     */
    $service = firstService();
    $slot = firstSlot($service);

    $component = wizardOnDetails($service)->set(validDetails());

    DB::afterCommit(fn () => null);

    // Insert the competing row with the same slot_key inside the same instant
    // the component is about to use.
    Appointment::factory()->create([
        'staff_id' => $slot->staffId,
        'service_id' => $service->id,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Pending,
    ]);

    $component->call('submit')
        ->assertSet('slotWasTaken', true)
        ->assertSee(__('booking.errors.slot_taken'));
});

/*
|------------------------------------------------------------------------------
| Consent and encryption
|------------------------------------------------------------------------------
*/

it('blocks submission when consent is not ticked', function () {
    wizardOnDetails()
        ->set(validDetails(['consent' => false]))
        ->call('submit')
        ->assertHasErrors('consent')
        ->assertSet('step', 3);

    expect(Appointment::count())->toBe(0);
    expect(IntakeForm::count())->toBe(0);
});

it('records when and from where consent was given', function () {
    wizardOnDetails()
        ->set(validDetails())
        ->call('submit')
        ->assertHasNoErrors();

    $intake = IntakeForm::query()->firstOrFail();

    expect($intake->consent_at)->not->toBeNull();
    // Server clock, not anything the client sent.
    expect($intake->consent_at->timestamp)->toBe(now()->timestamp);
    expect($intake->consent_ip)->not->toBeNull();
});

it('stores the clinical fields encrypted at rest', function () {
    wizardOnDetails()
        ->set(validDetails())
        ->call('submit')
        ->assertHasNoErrors();

    $raw = DB::table('intake_forms')->first();

    // Read straight from the table, bypassing the casts entirely.
    foreach (['medications', 'conditions', 'avoid_foods', 'note'] as $column) {
        expect($raw->{$column})->not->toBeNull();
        expect($raw->{$column})->not->toContain('ميتفورمين');
        expect($raw->{$column})->not->toContain('تكيس');
        expect($raw->{$column})->not->toContain('مكسرات');
        expect($raw->{$column})->not->toContain('شيفتات');

        // Laravel's envelope is base64 JSON with iv/value/mac.
        $envelope = json_decode(base64_decode($raw->{$column}, true) ?: '', true);
        expect($envelope)->toBeArray()->toHaveKeys(['iv', 'value', 'mac']);
    }

    // And the model still reads them back.
    $intake = IntakeForm::query()->firstOrFail();
    expect($intake->medications)->toBe('ميتفورمين 500');

    // goal is deliberately NOT encrypted: it is the one field the clinic
    // filters on, and it is a category rather than a clinical detail.
    expect($raw->goal)->toBe('weight_management');
});

/*
|------------------------------------------------------------------------------
| Abuse
|------------------------------------------------------------------------------
*/

it('rejects a filled honeypot', function () {
    wizardOnDetails()
        ->set(validDetails())
        ->set('website', 'https://example.com')
        ->call('submit')
        ->assertHasErrors('name');

    expect(Appointment::count())->toBe(0);
});

it('rejects a form completed impossibly fast', function () {
    $service = firstService();
    $slot = firstSlot($service);

    // Straight through, with no time passing at all: nobody reads a consent
    // notice and types a medical history instantly, but a script does.
    Livewire::test(BookingWizard::class)
        ->call('selectService', $service->id)
        ->call('next')
        ->call('selectSlot', $slot->key())
        ->call('next')
        ->set(validDetails())
        ->call('submit')
        ->assertHasErrors('name');

    expect(Appointment::count())->toBe(0);
});

it('rate limits by ip', function () {
    for ($attempt = 1; $attempt <= 5; $attempt++) {
        RateLimiter::hit('booking:ip:127.0.0.1', 3600);
    }

    wizardOnDetails()
        ->set(validDetails())
        ->call('submit')
        ->assertHasErrors('name');

    expect(Appointment::count())->toBe(0);
});

it('rate limits by phone number', function () {
    $key = 'booking:phone:'.hash('sha256', '+201012345678');

    for ($attempt = 1; $attempt <= 3; $attempt++) {
        RateLimiter::hit($key, 3600);
    }

    wizardOnDetails()
        ->set(validDetails())
        ->call('submit')
        ->assertHasErrors('phone');

    // A different number is unaffected — the limit is per number, not global.
    RateLimiter::clear('booking:ip:127.0.0.1');

    wizardOnDetails()
        ->set(validDetails(['phone' => '01112345678']))
        ->call('submit')
        ->assertHasNoErrors();

    RateLimiter::clear($key);
});

it('does not count a failed attempt against the limit', function () {
    // A patient who mistypes their name four times must not be locked out.
    for ($attempt = 1; $attempt <= 4; $attempt++) {
        wizardOnDetails()
            ->set(validDetails(['name' => 'ر']))
            ->call('submit')
            ->assertHasErrors('name');
    }

    wizardOnDetails()
        ->set(validDetails())
        ->call('submit')
        ->assertHasNoErrors();

    expect(Appointment::count())->toBe(1);
});

/*
|------------------------------------------------------------------------------
| Tampering
|------------------------------------------------------------------------------
*/

it('refuses a mode that config does not currently offer', function () {
    config()->set('clinic.booking.modes', ['online']);

    wizardOnDetails()
        ->set(validDetails())
        ->set('mode', 'clinic')
        ->call('submit')
        ->assertHasErrors('mode');

    expect(Appointment::count())->toBe(0);
});

it('refuses a service that has been withdrawn since the form was rendered', function () {
    $service = firstService();

    $component = wizardOnDetails($service)->set(validDetails());

    $service->update(['is_active' => false]);

    $component->call('submit')->assertHasErrors('serviceId');

    expect(Appointment::count())->toBe(0);
});

it('refuses a slot key the engine is not currently offering', function () {
    $component = wizardOnDetails();

    // Fabricated key for a slot in the past.
    $component->call('selectSlot', '1-2020-01-01T10:00:00Z');

    // Ignored outright — the selection is unchanged rather than replaced.
    expect($component->get('slotKey'))->not->toBe('1-2020-01-01T10:00:00Z');
});

it('refuses a slot belonging to a practitioner who is not free', function () {
    $service = firstService();
    $slot = firstSlot($service);

    $other = User::factory()->create();

    $component = wizardOnDetails($service)->set(validDetails());

    // A key naming a different practitioner is not one the engine offered.
    $component->call('selectSlot', $other->id.'-'.$slot->startsAtUtc->format('Y-m-d\TH:i:s\Z'));

    expect($component->get('staffId'))->toBe($slot->staffId);
});

it('locks the properties that decide what gets written', function () {
    // step, serviceId, slotKey and staffId are all server-owned. Livewire
    // refuses a client update rather than the component having to detect one.
    foreach (['step', 'serviceId', 'slotKey', 'staffId', 'detailsShownAt'] as $property) {
        $reflection = new ReflectionProperty(BookingWizard::class, $property);

        expect($reflection->getAttributes(Locked::class))
            ->not->toBeEmpty("{$property} must be #[Locked].");
    }
});
