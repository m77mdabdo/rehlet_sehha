<?php

declare(strict_types=1);

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\IntakeForm;
use App\Models\NotificationLog;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Database\QueryException;

it('resolves the appointment relationships in both directions', function () {
    $patient = Patient::factory()->create();
    $service = Service::factory()->create();
    $staff = User::factory()->create();

    $appointment = Appointment::factory()->create([
        'patient_id' => $patient->id,
        'service_id' => $service->id,
        'staff_id' => $staff->id,
    ]);

    expect($appointment->patient->id)->toBe($patient->id)
        ->and($appointment->service->id)->toBe($service->id)
        ->and($appointment->staff?->id)->toBe($staff->id);

    expect($patient->appointments()->pluck('id')->all())->toContain($appointment->id)
        ->and($service->appointments()->pluck('id')->all())->toContain($appointment->id)
        ->and($staff->appointments()->pluck('id')->all())->toContain($appointment->id);
});

it('resolves the intake form relationship in both directions', function () {
    $appointment = Appointment::factory()->create();
    $intake = IntakeForm::factory()->create(['appointment_id' => $appointment->id]);

    expect($appointment->fresh()?->intakeForm?->id)->toBe($intake->id)
        ->and($intake->appointment->id)->toBe($appointment->id);
});

it('reaches a patient intake forms through their appointments', function () {
    $patient = Patient::factory()->create();
    $appointment = Appointment::factory()->create(['patient_id' => $patient->id]);
    $intake = IntakeForm::factory()->create(['appointment_id' => $appointment->id]);

    expect($patient->intakeForms()->pluck('intake_forms.id')->all())->toContain($intake->id);
});

it('resolves the notification log relationship in both directions', function () {
    $appointment = Appointment::factory()->create();
    $log = NotificationLog::factory()->create(['appointment_id' => $appointment->id]);

    expect($log->appointment?->id)->toBe($appointment->id)
        ->and($appointment->notificationLogs()->pluck('id')->all())->toContain($log->id);
});

it('resolves the staff schedule relationships in both directions', function () {
    $staff = User::factory()->create();

    $workingHour = WorkingHour::factory()->create(['staff_id' => $staff->id]);
    $blockedSlot = BlockedSlot::factory()->create(['staff_id' => $staff->id]);

    expect($workingHour->staff?->id)->toBe($staff->id)
        ->and($blockedSlot->staff?->id)->toBe($staff->id)
        ->and($staff->workingHours()->pluck('id')->all())->toContain($workingHour->id)
        ->and($staff->blockedSlots()->pluck('id')->all())->toContain($blockedSlot->id);
});

it('leaves an unassigned appointment with a null staff relation', function () {
    $appointment = Appointment::factory()->create(['staff_id' => null]);

    expect($appointment->staff)->toBeNull();
});

it('cascades the intake form away when its appointment is force deleted', function () {
    $appointment = Appointment::factory()->create();
    $intake = IntakeForm::factory()->create(['appointment_id' => $appointment->id]);

    $appointment->forceDelete();

    $this->assertDatabaseMissing('intake_forms', ['id' => $intake->id]);
});

it('refuses to delete a service that still has appointments', function () {
    $service = Service::factory()->create();
    Appointment::factory()->create(['service_id' => $service->id]);

    expect(fn () => $service->delete())
        ->toThrow(QueryException::class);
});
