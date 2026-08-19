<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\Patient;
use App\Models\Service;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use App\Services\Booking\BookingService;
use App\Services\Booking\SlotTakenException;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use LogicException;
use RuntimeException;

/**
 * The write path, without a browser.
 *
 * The component tests prove the patient-facing behaviour. These prove the
 * transaction itself, including the case the component can never reach on its
 * own: two requests that BOTH pass the availability re-check, where only the
 * unique index can decide.
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

function bookingService(): BookingService
{
    return app(BookingService::class);
}

function anySlot(?Service $service = null): Slot
{
    $service ??= Service::active()->firstOrFail();

    return app(AvailabilityEngine::class)->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(7)->utc(),
        null,
        $service,
    )->firstOrFail();
}

/**
 * @return array<string, mixed>
 */
function patientPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'راوية غانم',
        'phone' => '+201012345678',
        'email' => null,
        'birth_date' => null,
    ], $overrides);
}

/**
 * @return array<string, mixed>
 */
function intakePayload(): array
{
    return [
        'goal' => 'weight_management',
        'medications' => 'ميتفورمين 500',
        'conditions' => null,
        'avoid_foods' => null,
        'note' => null,
    ];
}

it('writes the appointment and its intake form in one transaction', function () {
    $service = Service::active()->firstOrFail();
    $slot = anySlot($service);

    $appointment = bookingService()->book(
        service: $service,
        startsAtUtc: $slot->startsAtUtc,
        staffId: $slot->staffId,
        mode: AppointmentMode::Online,
        patientData: patientPayload(),
        intakeData: intakePayload(),
        consentIp: '203.0.113.9',
    );

    expect($appointment->status)->toBe(AppointmentStatus::Pending);
    expect($appointment->intakeForm->consent_ip)->toBe('203.0.113.9');
    expect(Appointment::count())->toBe(1);
    expect(IntakeForm::count())->toBe(1);
});

it('leaves nothing behind when the intake write fails', function () {
    $service = Service::active()->firstOrFail();
    $slot = anySlot($service);

    /*
     * Force the SECOND write to fail, after the appointment row already
     * exists. A model event rather than an ALTER TABLE: DDL causes an implicit
     * commit in MySQL, which would end the very transaction under test and
     * make this pass for entirely the wrong reason.
     */
    IntakeForm::creating(function (): void {
        throw new RuntimeException('intake write failed');
    });

    expect(fn () => bookingService()->book(
        service: $service,
        startsAtUtc: $slot->startsAtUtc,
        staffId: $slot->staffId,
        mode: AppointmentMode::Online,
        patientData: patientPayload(),
        intakeData: intakePayload(),
        consentIp: '203.0.113.9',
    ))->toThrow(RuntimeException::class);

    /*
     * An appointment with no intake form is a patient arriving for a
     * consultation the clinician cannot prepare for — and it would look
     * completely normal in the calendar. The transaction is what stops it.
     */
    expect(Appointment::count())->toBe(0);
    expect(IntakeForm::count())->toBe(0);

    // The slot is still on offer, because nothing was ever really booked.
    expect(app(AvailabilityEngine::class)->isSlotBookable($slot->startsAtUtc, $slot->staffId, $service))
        ->toBeTrue();
});

it('translates the unique index violation into SlotTakenException', function () {
    /*
     * The case the component cannot reach alone: TWO requests both pass the
     * availability re-check, and only the index can settle it.
     *
     * Simulated by making the engine always say yes, which is exactly what a
     * concurrent request would experience — it read the calendar before the
     * other write landed.
     */
    $service = Service::active()->firstOrFail();
    $slot = anySlot($service);

    $alwaysAvailable = new class extends AvailabilityEngine
    {
        public function isSlotBookable(CarbonImmutable $startsAtUtc, ?int $staffId, Service $service): bool
        {
            return true;
        }
    };

    $booking = new BookingService($alwaysAvailable);

    $booking->book(
        service: $service,
        startsAtUtc: $slot->startsAtUtc,
        staffId: $slot->staffId,
        mode: AppointmentMode::Online,
        patientData: patientPayload(),
        intakeData: intakePayload(),
        consentIp: '203.0.113.9',
    );

    // The loser of the race. The pre-check passes; the index does not.
    expect(fn () => $booking->book(
        service: $service,
        startsAtUtc: $slot->startsAtUtc,
        staffId: $slot->staffId,
        mode: AppointmentMode::Online,
        patientData: patientPayload(['phone' => '+201112345678', 'name' => 'سارة']),
        intakeData: intakePayload(),
        consentIp: '203.0.113.10',
    ))->toThrow(SlotTakenException::class);

    // And nothing partial survived the losing attempt.
    expect(Appointment::count())->toBe(1);
    expect(IntakeForm::count())->toBe(1);
});

it('does not mistake another unique constraint for a slot collision', function () {
    /*
     * A duplicate-key error on patients.phone means something completely
     * different from one on slot_key. Swallowing it as "slot taken" would
     * tell the patient a lie and hide a real bug, so the handler checks which
     * index fired rather than trusting the SQLSTATE alone.
     */
    $service = Service::active()->firstOrFail();

    $reflection = new ReflectionMethod(BookingService::class, 'isSlotCollision');

    $phoneCollision = new QueryException(
        'mysql',
        'insert into patients',
        [],
        new PDOException("SQLSTATE[23000]: Integrity constraint violation: 1062 Duplicate entry '+2010' for key 'patients_phone_unique'"),
    );

    // errorInfo is what the handler reads; build it as the driver would.
    $phoneCollision->errorInfo = ['23000', 1062, "Duplicate entry for key 'patients_phone_unique'"];

    expect($reflection->invoke(bookingService(), $phoneCollision))->toBeFalse();

    $slotCollision = new QueryException(
        'mysql',
        'insert into appointments',
        [],
        new PDOException('Duplicate entry for key appointments_slot_key_unique'),
    );
    $slotCollision->errorInfo = ['23000', 1062, "Duplicate entry for key 'appointments_slot_key_unique'"];

    expect($reflection->invoke(bookingService(), $slotCollision))->toBeTrue();

    expect($service)->not->toBeNull();
});

it('refuses a disabled mode even when called directly', function () {
    config()->set('clinic.booking.modes', ['online']);

    $service = Service::active()->firstOrFail();
    $slot = anySlot($service);

    expect(fn () => bookingService()->book(
        service: $service,
        startsAtUtc: $slot->startsAtUtc,
        staffId: $slot->staffId,
        mode: AppointmentMode::Clinic,
        patientData: patientPayload(),
        intakeData: intakePayload(),
        consentIp: '203.0.113.9',
    ))->toThrow(LogicException::class);

    expect(Appointment::count())->toBe(0);
});

it('refuses an inactive service even when called directly', function () {
    $service = Service::active()->firstOrFail();
    $slot = anySlot($service);

    $service->update(['is_active' => false]);

    expect(fn () => bookingService()->book(
        service: $service->fresh(),
        startsAtUtc: $slot->startsAtUtc,
        staffId: $slot->staffId,
        mode: AppointmentMode::Online,
        patientData: patientPayload(),
        intakeData: intakePayload(),
        consentIp: '203.0.113.9',
    ))->toThrow(LogicException::class);

    expect(Appointment::count())->toBe(0);
});

it('refuses a slot in the past', function () {
    $service = Service::active()->firstOrFail();

    expect(fn () => bookingService()->book(
        service: $service,
        startsAtUtc: CarbonImmutable::now()->subDay()->utc(),
        staffId: 2,
        mode: AppointmentMode::Online,
        patientData: patientPayload(),
        intakeData: intakePayload(),
        consentIp: '203.0.113.9',
    ))->toThrow(SlotTakenException::class);

    expect(Appointment::count())->toBe(0);
});

it('reuses the patient file across two separate bookings', function () {
    $service = Service::active()->firstOrFail();

    $slots = app(AvailabilityEngine::class)->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(7)->utc(),
        null,
        $service,
    );

    foreach ([$slots->first(), $slots->get(3)] as $slot) {
        bookingService()->book(
            service: $service,
            startsAtUtc: $slot->startsAtUtc,
            staffId: $slot->staffId,
            mode: AppointmentMode::Online,
            patientData: patientPayload(),
            intakeData: intakePayload(),
            consentIp: '203.0.113.9',
        );
    }

    expect(Patient::count())->toBe(1);
    expect(Appointment::count())->toBe(2);
    expect(Appointment::query()->distinct()->count('patient_id'))->toBe(1);
});
