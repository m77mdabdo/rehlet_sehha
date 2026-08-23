<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\RelationManagers\IntakeRelationManager;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\Service;
use App\Models\User;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Carbon;
use Livewire\Livewire;

/**
 * Who sees what, and — the part that matters — what is in the payload.
 *
 * A receptionist schedules appointments and telephones patients. She has no
 * clinical role and must not read what a patient wrote about her own body.
 * Hiding the field would not be enough: a hidden Filament field is still
 * resolved server-side and still serialised into the Livewire snapshot the
 * browser receives, where anyone can read it in the network tab.
 *
 * So these tests assert against the RESPONSE BODY, not against what the screen
 * appears to show.
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

function staffWithRole(string $role): User
{
    $user = User::factory()->create();
    $user->syncRoles([$role]);

    return $user->fresh();
}

/**
 * An appointment carrying clinical content distinctive enough to grep for.
 */
function clinicalAppointment(): Appointment
{
    $service = Service::active()->firstOrFail();
    $staff = User::query()->firstOrFail();

    $startsAt = CarbonImmutable::now()->addDays(2)->setTime(10, 0);

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
        'medications' => 'MEDICATION_CANARY_ميتفورمين',
        'conditions' => 'CONDITION_CANARY_تكيس',
        'avoid_foods' => 'AVOID_CANARY_مكسرات',
        'note' => 'NOTE_CANARY_شيفتات',
        'consent_at' => now(),
        'consent_ip' => '203.0.113.4',
    ]);

    return $appointment->fresh();
}

/**
 * @return list<string>
 */
function clinicalCanaries(): array
{
    return [
        'MEDICATION_CANARY_ميتفورمين',
        'CONDITION_CANARY_تكيس',
        'AVOID_CANARY_مكسرات',
        'NOTE_CANARY_شيفتات',
    ];
}

/*
|------------------------------------------------------------------------------
| The door
|------------------------------------------------------------------------------
*/

it('sends an anonymous visitor to the login screen', function () {
    $this->get('/admin')->assertRedirect('/admin/login');
});

it('refuses a user with no role', function () {
    $nobody = User::factory()->create();

    $this->actingAs($nobody)->get('/admin')->assertForbidden();
});

it('lets each clinic role in', function (string $role) {
    $this->actingAs(staffWithRole($role))->get('/admin')->assertOk();
})->with(['admin', 'doctor', 'receptionist']);

it('keeps the panel out of search results and out of referrers', function () {
    $response = $this->actingAs(staffWithRole('admin'))->get('/admin');

    expect($response->headers->get('X-Robots-Tag'))->toContain('noindex');
    expect($response->headers->get('Referrer-Policy'))->toBe('no-referrer');
    expect($response->headers->get('Cache-Control'))->toContain('no-store');
    expect($response->headers->get('X-Frame-Options'))->toBe('DENY');
});

it('puts those headers on the login redirect too', function () {
    // The redirect is built by the exception handler, which unwinds past the
    // panel's own middleware — hence the global registration.
    $response = $this->get('/admin');

    expect($response->headers->get('X-Robots-Tag'))->toContain('noindex');
});

it('renders the panel in arabic and right-to-left', function () {
    $html = $this->actingAs(staffWithRole('doctor'))->get('/admin')->assertOk()->getContent();

    expect($html)->toContain('dir="rtl"');
    expect($html)->toContain('lang="ar"');
});

/*
|------------------------------------------------------------------------------
| The clinical boundary — asserted against the payload
|------------------------------------------------------------------------------
*/

it('mounts the clinical relation manager for the doctor', function () {
    $appointment = clinicalAppointment();

    $html = $this->actingAs(staffWithRole('doctor'))
        ->get("/admin/appointments/{$appointment->id}/edit")
        ->assertOk()
        ->getContent();

    /*
     * The component is registered on the page, which is what "the tab exists"
     * means here. Its CONTENT arrives on a later Livewire request — Filament
     * renders relation managers lazily — so the heading is deliberately not
     * what is asserted; see the Livewire test below for the content itself.
     */
    expect($html)->toContain('IntakeRelationManager');
});

it('shows the doctor the actual clinical content', function () {
    $appointment = clinicalAppointment();

    Livewire::actingAs(staffWithRole('doctor'))
        ->test(IntakeRelationManager::class, [
            'ownerRecord' => $appointment,
            'pageClass' => EditAppointment::class,
        ])
        ->assertSuccessful()
        ->assertSee('MEDICATION_CANARY_ميتفورمين')
        ->assertSee('CONDITION_CANARY_تكيس');
});

it('refuses to mount the clinical relation manager for a receptionist', function () {
    $appointment = clinicalAppointment();

    // Not "hidden": the component is never constructed, so the query behind it
    // never runs and there is nothing to leak into any payload.
    expect(IntakeRelationManager::canViewForRecord($appointment, EditAppointment::class))
        ->toBeFalse();
})->group('boundary');

it('refuses a receptionist who asks livewire for the clinical component directly', function () {
    /*
     * The attack the payload assertion cannot cover on its own.
     *
     * Filament renders relation managers lazily, so the clinical content is
     * never in the first HTML response for ANYBODY — doctor included. What
     * separates the two roles is whether the component can be mounted at all.
     * A receptionist who reads the page source, finds the component name and
     * asks Livewire for it directly must be refused, not merely un-shown.
     */
    $appointment = clinicalAppointment();

    Livewire::actingAs(staffWithRole('receptionist'))
        ->test(IntakeRelationManager::class, [
            'ownerRecord' => $appointment,
            'pageClass' => EditAppointment::class,
        ])
        ->assertForbidden();
})->group('boundary');

it('puts NO clinical content in the receptionist response, in any form', function () {
    $appointment = clinicalAppointment();

    $response = $this->actingAs(staffWithRole('receptionist'))
        ->get("/admin/appointments/{$appointment->id}/edit")
        ->assertOk();

    $body = $response->getContent();

    /*
     * The whole point of the test. Not "the field is hidden" — the string is
     * not in the bytes that left the server, in the rendered HTML or in the
     * Livewire snapshot embedded in it.
     */
    foreach (clinicalCanaries() as $canary) {
        expect($body)->not->toContain($canary);

        // And not smuggled through as escaped or encoded output either.
        expect($body)->not->toContain(e($canary));
        expect($body)->not->toContain(json_encode($canary, JSON_UNESCAPED_UNICODE));
        expect($body)->not->toContain(json_encode($canary));
    }

    // The relation manager itself is never mounted for her.
    expect($body)->not->toContain('المعلومات الطبية');

    // She can still do her job on the same screen.
    expect($body)->toContain($appointment->reference);
});

it('refuses a receptionist the intake policy outright', function () {
    $receptionist = staffWithRole('receptionist');

    expect($receptionist->can('viewAny', IntakeForm::class))->toBeFalse();
    expect($receptionist->can('view', clinicalAppointment()->intakeForm))->toBeFalse();
});

it('keeps clinical content out of the appointments list for reception', function () {
    clinicalAppointment();

    $body = $this->actingAs(staffWithRole('receptionist'))
        ->get('/admin/appointments')
        ->assertOk()
        ->getContent();

    foreach (clinicalCanaries() as $canary) {
        expect($body)->not->toContain($canary);
    }
});

/*
|------------------------------------------------------------------------------
| User management
|------------------------------------------------------------------------------
*/

it('keeps user management to administrators', function () {
    expect(staffWithRole('admin')->can('viewAny', User::class))->toBeTrue();
    expect(staffWithRole('doctor')->can('viewAny', User::class))->toBeFalse();
    expect(staffWithRole('receptionist')->can('viewAny', User::class))->toBeFalse();
});

it('stops an administrator deleting their own account', function () {
    // The last admin deleting themselves locks the clinic out of its own
    // panel, and the recovery is a developer with database access.
    $admin = staffWithRole('admin');
    $other = staffWithRole('admin');

    expect($admin->can('delete', $admin))->toBeFalse();
    expect($admin->can('delete', $other))->toBeTrue();
});
