<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Filament\Pages\ClinicSettingsPage;
use App\Filament\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\RelationManagers\IntakeRelationManager;
use App\Filament\Resources\Services\Pages\CreateService;
use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingConfirmed;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use App\Support\ClinicSettings;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/**
 * Booking from the clinic side must behave exactly like booking from the
 * public side, because it IS the public side: CreateAppointment calls
 * BookingService::book(), the only booking path in this application.
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

function panelUser(string $role): User
{
    $user = User::factory()->create();
    $user->syncRoles([$role]);

    return $user->fresh();
}

function firstFreeSlot(?Service $service = null): Slot
{
    $service ??= Service::active()->firstOrFail();

    return app(AvailabilityEngine::class)->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(14)->utc(),
        null,
        $service,
    )->firstOrFail();
}

/*
|------------------------------------------------------------------------------
| Staff booking uses the one booking path
|------------------------------------------------------------------------------
*/

it('books from the panel and notifies the patient', function () {
    Notification::fake();

    $service = Service::active()->firstOrFail();
    $slot = firstFreeSlot($service);

    Livewire::actingAs(panelUser('receptionist'))
        ->test(CreateAppointment::class)
        ->fillForm([
            'service_id' => $service->id,
            'staff_id' => $slot->staffId,
            'mode' => AppointmentMode::Online->value,
            'slot' => $slot->key(),
            'patient_phone' => '01012345678',
            'patient_name' => 'راوية غانم',
            'patient_email' => 'rawia@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    $appointment = Appointment::query()->firstOrFail();

    expect($appointment->starts_at->utc()->toIso8601ZuluString())
        ->toBe($slot->startsAtUtc->toIso8601ZuluString());

    /*
     * The patient is told, exactly as she would be had she booked herself. A
     * booking taken over the telephone that sends her nothing leaves her with
     * no written record and no manage link.
     */
    Notification::assertSentOnDemandTimes(BookingConfirmed::class, 1);
});

it('lands a returning patient on her existing file instead of a duplicate', function () {
    Notification::fake();

    // She has been before. Note the number is stored in E.164.
    $existing = Patient::factory()->create([
        'phone' => '+201012345678',
        'name' => 'راوية غانم',
        'email' => 'rawia@example.com',
    ]);

    $service = Service::active()->firstOrFail();
    $slot = firstFreeSlot($service);

    Livewire::actingAs(panelUser('receptionist'))
        ->test(CreateAppointment::class)
        ->fillForm([
            'service_id' => $service->id,
            'staff_id' => $slot->staffId,
            'mode' => AppointmentMode::Online->value,
            'slot' => $slot->key(),
            // Typed the way it appears on a caller display, not in E.164.
            'patient_phone' => '01012345678',
            'patient_name' => 'راوية غانم',
            'patient_email' => 'rawia@example.com',
        ])
        ->call('create')
        ->assertHasNoFormErrors();

    /*
     * One patient, not two. A second file would split this woman's history in
     * half — the doctor would open the new one and see no past appointments,
     * no previous intake, and no reason to think any existed.
     */
    expect(Patient::query()->where('phone', '+201012345678')->count())->toBe(1);
    expect(Appointment::query()->firstOrFail()->patient_id)->toBe($existing->id);
});

it('collides on a taken slot exactly as a patient booking does', function () {
    Notification::fake();

    $service = Service::active()->firstOrFail();
    $slot = firstFreeSlot($service);

    // Somebody takes the slot between the form rendering and its submission.
    Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $slot->staffId,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $before = Appointment::query()->count();

    Livewire::actingAs(panelUser('receptionist'))
        ->test(CreateAppointment::class)
        ->fillForm([
            'service_id' => $service->id,
            'staff_id' => $slot->staffId,
            'mode' => AppointmentMode::Online->value,
            'slot' => $slot->key(),
            'patient_phone' => '01099887766',
            'patient_name' => 'سلمى فؤاد',
            'patient_email' => null,
        ])
        ->call('create')
        // Refused, and reported on the slot field — the same outcome the
        // public wizard produces, from the same exception.
        ->assertHasFormErrors(['slot']);

    expect(Appointment::query()->count())->toBe($before);
});

/*
|------------------------------------------------------------------------------
| Actions notify
|------------------------------------------------------------------------------
*/

it('notifies the patient when the clinic cancels', function () {
    Notification::fake();

    $service = Service::active()->firstOrFail();
    $slot = firstFreeSlot($service);

    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $slot->staffId,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
        'locale' => 'ar',
    ]);

    $appointment->patient->forceFill(['email' => 'patient@example.com'])->save();

    Livewire::actingAs(panelUser('receptionist'))
        ->test(EditAppointment::class, ['record' => $appointment->getKey()])
        ->callAction('cancel');

    expect($appointment->fresh()->status)->toBe(AppointmentStatus::Cancelled);

    // She cannot tell a clinic cancellation from her own, and should not have
    // to: either way she must not travel for an appointment that is gone.
    Notification::assertSentOnDemandTimes(BookingCancelled::class, 1);
});

/*
|------------------------------------------------------------------------------
| Clinical reads are logged
|------------------------------------------------------------------------------
*/

it('logs who read a patient clinical record', function () {
    $service = Service::active()->firstOrFail();
    $slot = firstFreeSlot($service);

    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $slot->staffId,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $intake = IntakeForm::factory()->create([
        'appointment_id' => $appointment->id,
        'medications' => 'ميتفورمين 500',
        'consent_at' => now(),
        'consent_ip' => '203.0.113.4',
    ]);

    $doctor = panelUser('doctor');

    Livewire::actingAs($doctor)->test(IntakeRelationManager::class, [
        'ownerRecord' => $appointment->fresh(),
        'pageClass' => EditAppointment::class,
    ])->assertSuccessful();

    $entry = ActivityLog::query()
        ->where('log_name', 'clinical_access')
        ->where('subject_type', IntakeForm::class)
        ->where('subject_id', $intake->id)
        ->latest('id')
        ->first();

    expect($entry)->not->toBeNull('Opening a clinical record must leave a trace.');
    expect($entry->causer_id)->toBe($doctor->id);
    expect($entry->event)->toBe('read');

    /*
     * The fact, never the content. A log that copies the record it protects
     * has doubled the number of places that record exists — and activity_log
     * is kept longer than the intake and read by more people.
     */
    $serialised = json_encode($entry->properties, JSON_UNESCAPED_UNICODE);

    expect($serialised)->not->toContain('ميتفورمين');
});

it('writes no read log when the reader is refused', function () {
    $service = Service::active()->firstOrFail();
    $slot = firstFreeSlot($service);

    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $slot->staffId,
        'starts_at' => $slot->startsAtUtc,
        'ends_at' => $slot->startsAtUtc->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
    ]);

    IntakeForm::factory()->create([
        'appointment_id' => $appointment->id,
        'consent_at' => now(),
        'consent_ip' => '203.0.113.4',
    ]);

    $this->actingAs(panelUser('receptionist'))
        ->get("/admin/appointments/{$appointment->id}/edit")
        ->assertOk();

    // There was no read, so there is no read entry. A log row here would be a
    // lie about what happened.
    expect(ActivityLog::query()->where('log_name', 'clinical_access')->count())->toBe(0);
});

/*
|------------------------------------------------------------------------------
| Settings
|------------------------------------------------------------------------------
*/

it('busts the caches when settings are written', function () {
    // Warm both caches: the settings overlay and the rendered public content.
    ClinicSettings::all();
    $this->get('/ar')->assertOk();

    expect(Cache::has('clinic-settings'))->toBeTrue();
    expect(Cache::has('public-content:services'))->toBeTrue();

    Livewire::actingAs(panelUser('admin'))
        ->test(ClinicSettingsPage::class)
        ->fillForm([
            'clinic__contact__email' => 'new@rehletsehha.com',
            'clinic__contact__phone' => '+201004818303',
            'clinic__contact__phone_display' => '0100 481 8303',
            'clinic__contact__whatsapp' => '201004818303',
            'clinic__booking__lead_time_hours' => 4,
            'clinic__booking__horizon_days' => 30,
            'clinic__booking__buffer_minutes' => 15,
            'clinic__booking__reschedule_min_hours' => 6,
        ])
        ->call('save')
        ->assertHasNoFormErrors();

    /*
     * Both, not one. These values feed the booking engine AND the phone number
     * baked into every rendered page; clearing one leaves the site showing an
     * old number while the rules have already changed.
     */
    expect(Cache::has('clinic-settings'))->toBeFalse();
    expect(Cache::has('public-content:services'))->toBeFalse();

    // And the new value is genuinely in force on the next request.
    ClinicSettings::apply();
    expect(config('clinic.booking.lead_time_hours'))->toBe(4);
    expect(config('clinic.contact.email'))->toBe('new@rehletsehha.com');
});

it('keeps settings away from reception', function () {
    $this->actingAs(panelUser('receptionist'))
        ->get(ClinicSettingsPage::getUrl())
        ->assertForbidden();
});

/*
|------------------------------------------------------------------------------
| The slot-grid guard, from the panel
|------------------------------------------------------------------------------
*/

it('refuses an over-long service from the panel form', function () {
    $shortest = (int) WorkingHour::query()->where('is_active', true)->min('slot_minutes');

    Livewire::actingAs(panelUser('admin'))
        ->test(CreateService::class)
        ->fillForm([
            'name_ar' => 'باقة طويلة',
            'name_en' => 'Long package',
            'description_ar' => 'وصف',
            'description_en' => 'Description',
            'slug' => 'long-package',
            'price' => 1000,
            // One minute longer than the grid allows.
            'duration_minutes' => $shortest + 1,
            'sessions_count' => 1,
            'is_active' => true,
        ])
        ->call('create')
        ->assertHasFormErrors(['duration_minutes']);

    expect(Service::query()->where('slug', 'long-package')->exists())->toBeFalse();
});

it('still refuses an over-long service at the model, with the form rule bypassed', function () {
    /*
     * The form rule is a courtesy — it turns a stack trace into a sentence.
     * The ENFORCEMENT is Service::guardAgainstOutgrowingTheSlotGrid on the
     * saving event, and it has to hold for every path: a seeder, tinker, a
     * future import script, or a Filament action written by somebody who did
     * not know about the rule.
     */
    $shortest = (int) WorkingHour::query()->where('is_active', true)->min('slot_minutes');

    expect(fn () => Service::query()->create([
        'slug' => 'bypasses-the-form',
        'name' => ['ar' => 'باقة', 'en' => 'Package'],
        'description' => ['ar' => 'وصف', 'en' => 'Description'],
        'price' => 1000,
        'duration_minutes' => $shortest + 1,
        'sessions_count' => 1,
        'is_active' => true,
    ]))->toThrow(LogicException::class);
});
