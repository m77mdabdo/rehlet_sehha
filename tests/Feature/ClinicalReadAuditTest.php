<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\RelationManagers\IntakeRelationManager;
use App\Filament\Resources\Patients\Pages\EditPatient;
use App\Filament\Resources\Patients\RelationManagers\AppointmentsRelationManager;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\Service;
use App\Models\User;
use App\Services\Clinical\ClinicalAccessLog;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Filament\Actions\Testing\TestAction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * The read audit trail.
 *
 * Task 1 logs writes, which answers "who changed this". The question actually
 * asked of a medical record is "who looked at mine", and unauthorised READING
 * is the realistic failure — nobody edits a stranger's intake out of
 * curiosity, but people do open one.
 *
 * Three things have to hold, and each is tested against the stored row rather
 * than against the code that wrote it:
 *
 *   1. A permitted read names the reader, the record and the time, and carries
 *      NO clinical content — the same discipline as the delivery log.
 *   2. A REFUSED attempt is logged too. It is the more interesting row: a
 *      permitted read repeated is somebody doing their job, a denied read
 *      repeated is somebody who was told no and tried again.
 *   3. One look is one row. Livewire re-renders must not inflate the count.
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

function auditStaff(string $role): User
{
    $user = User::factory()->create();
    $user->syncRoles([$role]);

    return $user->fresh();
}

/**
 * Each call lands on a DIFFERENT hour.
 *
 * appointments.slot_key is unique per (practitioner, start instant), so two
 * fixtures at the same time collide on the double-booking guard — correctly.
 * The counter keeps the fixtures apart without weakening the constraint.
 */
function auditedAppointment(): Appointment
{
    static $hour = 9;

    $service = Service::active()->firstOrFail();
    $staff = User::query()->firstOrFail();

    $startsAt = CarbonImmutable::now()->addDays(2)->setTime($hour++, 0);

    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $staff->id,
        'starts_at' => $startsAt->utc(),
        'ends_at' => $startsAt->utc()->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
        'mode' => AppointmentMode::Online,
        'locale' => 'ar',
    ]);

    IntakeForm::factory()->create([
        'appointment_id' => $appointment->id,
        'goal' => 'weight_management',
        'medications' => 'AUDIT_CANARY_ميتفورمين',
        'conditions' => 'AUDIT_CANARY_تكيس',
        'avoid_foods' => 'AUDIT_CANARY_مكسرات',
        'note' => 'AUDIT_CANARY_شيفتات',
        'consent_at' => now(),
        'consent_ip' => '203.0.113.4',
    ]);

    return $appointment->fresh();
}

function openClinicalBlock(User $user, Appointment $appointment): Testable
{
    return Livewire::actingAs($user)->test(IntakeRelationManager::class, [
        'ownerRecord' => $appointment->fresh(),
        'pageClass' => EditAppointment::class,
    ]);
}

/**
 * @return Builder<ActivityLog>
 */
function auditRows(?string $event = null)
{
    $query = ActivityLog::query()->where('log_name', ClinicalAccessLog::LOG_NAME);

    return $event === null ? $query : $query->where('event', $event);
}

/*
|------------------------------------------------------------------------------
| 1. A permitted read
|------------------------------------------------------------------------------
*/

it('records who read a clinical record, which one, and when', function () {
    $appointment = auditedAppointment();
    $doctor = auditStaff('doctor');

    $before = CarbonImmutable::now();

    openClinicalBlock($doctor, $appointment)->assertSuccessful();

    $row = auditRows(ClinicalAccessLog::EVENT_READ)->latest('id')->firstOrFail();

    // The reader, by identity — not "a doctor", but which one.
    expect($row->causer_id)->toBe($doctor->id);
    expect($row->causer_type)->toBe(User::class);

    // The record.
    expect($row->subject_type)->toBe(IntakeForm::class);
    expect($row->subject_id)->toBe($appointment->intakeForm->id);
    expect($row->properties['appointment_id'])->toBe($appointment->id);

    // The time.
    expect($row->created_at->greaterThanOrEqualTo($before))->toBeTrue();

    // And where in the panel it happened, so a read from the patient's history
    // is distinguishable from one on the appointment screen.
    expect($row->properties['context'])->toBe('appointment.intake');
});

it('puts no clinical content in the audit row itself', function () {
    /*
     * Same discipline as Task 1.6 and the delivery log. A log that copies the
     * record it protects has doubled the number of places that record exists —
     * and activity_log is retained for a year, longer than the intake, and is
     * read by more people than the intake is.
     */
    $appointment = auditedAppointment();

    openClinicalBlock(auditStaff('doctor'), $appointment)->assertSuccessful();

    $row = auditRows(ClinicalAccessLog::EVENT_READ)->latest('id')->firstOrFail();

    $serialised = json_encode(
        [$row->properties, $row->attribute_changes, $row->description],
        JSON_UNESCAPED_UNICODE,
    );

    foreach ([
        'AUDIT_CANARY_ميتفورمين',
        'AUDIT_CANARY_تكيس',
        'AUDIT_CANARY_مكسرات',
        'AUDIT_CANARY_شيفتات',
    ] as $canary) {
        expect($serialised)->not->toContain($canary);
    }

    // It does record whether there was anything to read — a fact about the
    // access, not content from the record.
    expect($row->properties)->toHaveKey('erased');
});

/*
|------------------------------------------------------------------------------
| 2. A refused attempt
|------------------------------------------------------------------------------
*/

it('logs a receptionist refused the clinical record', function () {
    $appointment = auditedAppointment();
    $receptionist = auditStaff('receptionist');

    openClinicalBlock($receptionist, $appointment)->assertForbidden();

    $row = auditRows(ClinicalAccessLog::EVENT_DENIED)->latest('id')->firstOrFail();

    expect($row->causer_id)->toBe($receptionist->id);
    expect($row->subject_id)->toBe($appointment->intakeForm->id);
    expect($row->properties['context'])->toBe('appointment.intake');

    // The role at the time, so the row stays legible after the policy changes.
    expect($row->properties['roles'])->toContain('receptionist');

    // Refused means refused: no read row was written alongside it.
    expect(auditRows(ClinicalAccessLog::EVENT_READ)->count())->toBe(0);
});

it('does not collapse repeated refusals', function () {
    /*
     * Deliberately not deduplicated, unlike permitted reads. Somebody who is
     * told no and tries again is the finding; collapsing three attempts into
     * one row deletes the only evidence that anyone probed.
     */
    $appointment = auditedAppointment();
    $receptionist = auditStaff('receptionist');

    foreach (range(1, 3) as $ignored) {
        openClinicalBlock($receptionist, $appointment)->assertForbidden();
    }

    expect(auditRows(ClinicalAccessLog::EVENT_DENIED)->count())->toBe(3);
});

it('does not log a denial for an ordinary page the receptionist is entitled to open', function () {
    /*
     * She opens appointments all day; that is the job. Logging each one as a
     * denial because the page asked "should the clinical tab exist" would fill
     * the table with rows meaning nothing, and bury the real ones.
     */
    $appointment = auditedAppointment();

    $this->actingAs(auditStaff('receptionist'))
        ->get("/admin/appointments/{$appointment->id}/edit")
        ->assertOk();

    expect(auditRows()->count())->toBe(0);
});

/*
|------------------------------------------------------------------------------
| 3. One look, one row
|------------------------------------------------------------------------------
*/

it('does not inflate the count when livewire re-renders the same component', function () {
    $appointment = auditedAppointment();

    $component = openClinicalBlock(auditStaff('doctor'), $appointment)->assertSuccessful();

    expect(auditRows(ClinicalAccessLog::EVENT_READ)->count())->toBe(1);

    // The ordinary interactions inside the component: searching, refreshing,
    // changing the page size. None of these is a new look at the record.
    $component->set('tableSearch', 'anything');
    $component->call('$refresh');
    $component->set('tableRecordsPerPage', 25);

    expect(auditRows(ClinicalAccessLog::EVENT_READ)->count())->toBe(1);
});

it('collapses a remount within the dedupe window', function () {
    // A page reload, a tab switch, coming back to the same record during one
    // sitting. Livewire remounts the component each time; it is still one look.
    $appointment = auditedAppointment();
    $doctor = auditStaff('doctor');

    openClinicalBlock($doctor, $appointment)->assertSuccessful();

    CarbonImmutable::setTestNow(CarbonImmutable::getTestNow()->addMinutes(4));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    openClinicalBlock($doctor, $appointment)->assertSuccessful();

    expect(auditRows(ClinicalAccessLog::EVENT_READ)->count())->toBe(1);
});

it('records a separate look once the window has passed', function () {
    // Opening the same record again after lunch IS a second read, and an
    // auditor would want to see it. Five minutes is the line.
    $appointment = auditedAppointment();
    $doctor = auditStaff('doctor');

    openClinicalBlock($doctor, $appointment)->assertSuccessful();

    CarbonImmutable::setTestNow(CarbonImmutable::getTestNow()->addMinutes(6));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    openClinicalBlock($doctor, $appointment)->assertSuccessful();

    expect(auditRows(ClinicalAccessLog::EVENT_READ)->count())->toBe(2);
});

it('keeps two different readers apart inside the same window', function () {
    // The window collapses one person looking twice, never two people looking
    // once — which is the whole question the log exists to answer.
    $appointment = auditedAppointment();

    openClinicalBlock(auditStaff('doctor'), $appointment)->assertSuccessful();
    openClinicalBlock(auditStaff('admin'), $appointment)->assertSuccessful();

    expect(auditRows(ClinicalAccessLog::EVENT_READ)->count())->toBe(2);
});

it('keeps two different records apart inside the same window', function () {
    $doctor = auditStaff('doctor');

    openClinicalBlock($doctor, auditedAppointment())->assertSuccessful();
    openClinicalBlock($doctor, auditedAppointment())->assertSuccessful();

    expect(auditRows(ClinicalAccessLog::EVENT_READ)->count())->toBe(2);
});

/*
|------------------------------------------------------------------------------
| The second reading surface
|------------------------------------------------------------------------------
*/

it('logs a read from the patient history with its own context', function () {
    /*
     * The doctor can also reach the intake from the patient's file, through a
     * modal on the appointment history. That is a second place clinical
     * content is displayed, so it is a second place a read must be recorded —
     * tagged with its own context so an auditor can tell the two apart.
     */
    $appointment = auditedAppointment();
    $doctor = auditStaff('doctor');

    Livewire::actingAs($doctor)
        ->test(AppointmentsRelationManager::class, [
            'ownerRecord' => $appointment->patient,
            'pageClass' => EditPatient::class,
        ])
        ->mountAction(TestAction::make('viewIntake')->table($appointment->getKey()))
        ->assertSuccessful();

    $row = auditRows(ClinicalAccessLog::EVENT_READ)->latest('id')->firstOrFail();

    expect($row->causer_id)->toBe($doctor->id);
    expect($row->properties['context'])->toBe('patient.history');
});

it('refuses the patient history intake modal to a receptionist', function () {
    $appointment = auditedAppointment();

    Livewire::actingAs(auditStaff('receptionist'))
        ->test(AppointmentsRelationManager::class, [
            'ownerRecord' => $appointment->patient,
            'pageClass' => EditPatient::class,
        ])
        // The action is authorised by IntakeFormPolicy, so it is not merely
        // hidden — calling it is refused.
        ->assertActionHidden(TestAction::make('viewIntake')->table($appointment->getKey()));
});
