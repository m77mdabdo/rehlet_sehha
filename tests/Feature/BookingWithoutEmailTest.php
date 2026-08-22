<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Enums\ContactPreference;
use App\Livewire\BookingWizard;
use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\Service;
use App\Models\User;
use App\Notifications\BookingConfirmed;
use App\Notifications\DailySchedule;
use App\Services\Availability\AvailabilityEngine;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use Livewire\Features\SupportTesting\Testable;
use Livewire\Livewire;

/**
 * Booking without an email address.
 *
 * Email is optional and stays optional — a real share of patients here do not
 * use one, and requiring it costs the clinic those bookings outright. The
 * whole of this file is about the other half of that decision: she may
 * proceed, but she may not proceed unaware.
 *
 * Without an address she receives nothing. No confirmation, no reminder the
 * day before, no reminder an hour before, and no link to cancel or move the
 * appointment. The form, up to the moment she submits, gives her no reason to
 * expect any of that — "optional" reads as "we do not need it", not as "we
 * cannot reach you".
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

/**
 * A wizard sitting on step 3, with everything filled in except the email.
 */
function wizardWithoutEmail(array $overrides = []): Testable
{
    $service = Service::active()->firstOrFail();

    $slot = app(AvailabilityEngine::class)->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(7)->utc(),
        null,
        $service,
    )->firstOrFail();

    $component = Livewire::test(BookingWizard::class)
        ->call('selectService', $service->id)
        ->call('next')
        ->call('selectSlot', $slot->key())
        ->call('next');

    // The form has a minimum fill time; a submission with none reads as a bot.
    CarbonImmutable::setTestNow(CarbonImmutable::getTestNow()->addSeconds(30));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    return $component->set(array_merge([
        'name' => 'راوية غانم',
        'phone' => '01012345678',
        'email' => '',
        'goal' => 'weight_management',
        'medications' => 'ميتفورمين 500',
        'conditions' => 'تكيس مبايض',
        'consent' => true,
    ], $overrides));
}

/*
|------------------------------------------------------------------------------
| A — the notice
|------------------------------------------------------------------------------
*/

it('shows the no-email notice instead of booking, and does not call it an error', function () {
    Notification::fake();

    $component = wizardWithoutEmail()->call('submit');

    // A notice, not a validation failure. Nothing she typed is wrong.
    $component->assertHasNoErrors();
    $component->assertSet('showNoEmailNotice', true);

    // And it has NOT booked. She is still on step 3, deciding.
    $component->assertSet('step', 3);
    expect(Appointment::query()->count())->toBe(0);
});

it('states exactly what will not arrive', function (string $locale) {
    app()->setLocale($locale);

    $component = wizardWithoutEmail()->call('submit');

    /*
     * The three things she loses, named. A notice that said only "you will not
     * get emails" would be true and useless — she has to be able to weigh the
     * loss, and the manage link in particular is the one she cannot get back.
     */
    $component->assertSee(__('booking.no_email.title'), false);

    foreach (__('booking.no_email.losses') as $loss) {
        $component->assertSee($loss, false);
    }

    // And what happens instead, so it does not read as a dead end.
    $component->assertSee(__('booking.no_email.fallback'), false);

    // Both doors, explicitly. Continuing is a button she presses, not a
    // default she falls through.
    $component->assertSee(__('booking.no_email.add'), false);
    $component->assertSee(__('booking.no_email.continue'), false);
})->with(['ar', 'en']);

it('puts the notice away and sends her back to the field', function () {
    $component = wizardWithoutEmail()->call('submit')->call('addEmailInstead');

    $component->assertSet('showNoEmailNotice', false);
    $component->assertSet('step', 3);

    // The focus is dispatched to the browser: the notice sits below the fold
    // from the email input on a phone.
    $component->assertDispatched('focus-field', field: 'email');

    // Nothing booked — she is going back to fill it in.
    expect(Appointment::query()->count())->toBe(0);
});

it('does not ask twice once she has decided', function () {
    Notification::fake();

    // She acknowledges, then a later validation failure re-renders the form.
    // The acknowledgement has to survive, or she is made to decide again.
    $component = wizardWithoutEmail()->call('submit')->assertSet('showNoEmailNotice', true);

    $component->set('name', '')->call('submit')->assertHasErrors('name');

    $component->set('name', 'راوية غانم')->call('submit');

    $component->assertSet('noEmailAcknowledged', false);
    $component->assertSet('showNoEmailNotice', true);
});

/*
|------------------------------------------------------------------------------
| Continuing anyway
|------------------------------------------------------------------------------
*/

it('books when she chooses to continue without an email', function () {
    Notification::fake();

    $component = wizardWithoutEmail()
        ->call('submit')
        ->assertSet('showNoEmailNotice', true)
        ->call('continueWithoutEmail');

    $component->assertHasNoErrors()->assertSet('step', 4);
    $component->assertSet('noEmailAcknowledged', true);
    $component->assertSet('showNoEmailNotice', false);

    $appointment = Appointment::query()->firstOrFail();

    expect($appointment->patient->email)->toBeNull();
    expect($appointment->contactPreference())->toBe(ContactPreference::PhoneOnly);
    expect($appointment->isReachableByEmail())->toBeFalse();
});

it('records a skipped delivery rather than nothing at all', function () {
    Notification::fake();

    wizardWithoutEmail()->call('submit')->call('continueWithoutEmail');

    $appointment = Appointment::query()->firstOrFail();

    /*
     * The row exists precisely because the message does not. A delivery log
     * with no row for this booking would be indistinguishable from a queue
     * that died, and the clinic would have no way to tell that this patient
     * is waiting on a telephone call rather than an email.
     */
    $log = NotificationLog::query()
        ->where('appointment_id', $appointment->id)
        ->where('template', 'booking_confirmed')
        ->firstOrFail();

    expect($log->status)->toBe(NotificationLog::STATUS_SKIPPED);
    expect($log->error)->toContain('did not give an email address');

    Notification::assertNotSentTo(
        new AnonymousNotifiable,
        BookingConfirmed::class
    );
});

/*
|------------------------------------------------------------------------------
| B — the confirmation screen as the record
|------------------------------------------------------------------------------
*/

it('makes the confirmation screen the record when nothing will be sent', function () {
    Notification::fake();

    $component = wizardWithoutEmail()->call('submit')->call('continueWithoutEmail');

    $appointment = Appointment::query()->firstOrFail();
    $manageUrl = route('appointment.manage', ['locale' => 'ar', 'token' => $appointment->cancel_token]);

    $html = $component->html();

    // It says plainly that this is the only copy she will have.
    expect($html)->toContain(__('booking.keepsake.title'));
    expect($html)->toContain(__('booking.keepsake.link_note'));

    // The reference and the manage link are both present and copyable.
    expect($html)->toContain('data-copy="'.$appointment->reference.'"');
    expect($html)->toContain('data-copy="'.e($manageUrl).'"');

    // Selectable, so a failed clipboard is not a dead end.
    expect($html)->toContain('select-all');
});

it('does not show the keepsake block to a patient who will get an email', function () {
    Notification::fake();

    $component = wizardWithoutEmail(['email' => 'rawia@example.com'])->call('submit');

    $component->assertHasNoErrors()->assertSet('step', 4);
    $component->assertSet('showNoEmailNotice', false);

    // She has the booking in writing already; this screen is a receipt.
    expect($component->html())->not->toContain(__('booking.keepsake.title'));
});

it('sends the confirmation when she adds an email from the confirmation screen', function () {
    Notification::fake();

    $component = wizardWithoutEmail()->call('submit')->call('continueWithoutEmail');

    $appointment = Appointment::query()->firstOrFail();
    expect($appointment->patient->email)->toBeNull();

    $component->set('lateEmail', 'rawia@example.com')->call('saveLateEmail')->assertHasNoErrors();

    // Written to the patient record, which is what arms the reminders too:
    // they read the address at send time, not at booking time.
    expect($appointment->patient->fresh()->email)->toBe('rawia@example.com');
    expect($appointment->fresh()->isReachableByEmail())->toBeTrue();

    Notification::assertSentOnDemandTimes(BookingConfirmed::class, 1);

    $component->assertSet('lateEmailSaved', true);
    expect($component->html())->toContain(__('booking.keepsake.add_email_saved'));
});

it('refuses a malformed late email without losing the booking', function () {
    Notification::fake();

    $component = wizardWithoutEmail()->call('submit')->call('continueWithoutEmail');

    $component->set('lateEmail', 'not-an-address')->call('saveLateEmail')->assertHasErrors('lateEmail');

    expect(Appointment::query()->firstOrFail()->patient->email)->toBeNull();
    $component->assertSet('lateEmailSaved', false);
});

/*
|------------------------------------------------------------------------------
| The WhatsApp record
|------------------------------------------------------------------------------
*/

it('prefills whatsapp with the whole appointment and nothing clinical', function () {
    Notification::fake();

    $component = wizardWithoutEmail()->call('submit')->call('continueWithoutEmail');

    $appointment = Appointment::query()->firstOrFail();
    $cairo = $appointment->starts_at->clone()->setTimezone(config('clinic.timezone'));

    preg_match_all('#https://wa\.me/[^"\'\s<>]+#', $component->html(), $links);

    $prefilled = collect($links[0])
        ->map(fn (string $link): string => html_entity_decode($link))
        ->first(fn (string $link): bool => str_contains($link, 'text='));

    expect($prefilled)->not->toBeNull('The keepsake block should offer a WhatsApp record.');

    $query = [];
    parse_str((string) parse_url($prefilled, PHP_URL_QUERY), $query);

    // Reference, date, time and mode — the appointment, so that sending it to
    // herself produces the record the email would have been.
    expect($query['text'])->toContain($appointment->reference);
    expect($query['text'])->toContain($cairo->translatedFormat('j F'));
    expect($query['text'])->toContain($cairo->format('H:i'));
    expect($query['text'])->toContain(__('booking.mode.online'));

    /*
     * And nothing about her health. This text becomes a URL, and a URL lands
     * in browser history, in a screenshot sent to a relative, and in the
     * address bar during a screen share.
     */
    foreach (['ميتفورمين', 'تكيس', 'مبايض', __('booking.goals.weight_management')] as $clinical) {
        expect($query['text'])->not->toContain($clinical);
    }

    /*
     * The manage token is NOT in it either. It is a bearer credential, and a
     * WhatsApp message is forwarded far more casually than an email.
     */
    expect($query['text'])->not->toContain($appointment->cancel_token);
});

/*
|------------------------------------------------------------------------------
| C and D — what the clinic sees
|------------------------------------------------------------------------------
*/

it('separates the reachable from the unreachable on the daily schedule', function () {
    Notification::fake();

    $zone = config('clinic.timezone');
    $now = CarbonImmutable::parse('2026-06-09 07:00:00', $zone);
    CarbonImmutable::setTestNow($now);
    Carbon::setTestNow($now);

    $service = Service::active()->firstOrFail();
    $staff = User::query()->firstOrFail();

    $make = function (string $startsAt, ?string $email) use ($service, $staff, $zone): Appointment {
        $at = CarbonImmutable::parse($startsAt, $zone);

        $appointment = Appointment::factory()->create([
            'service_id' => $service->id,
            'staff_id' => $staff->id,
            'starts_at' => $at->utc(),
            'ends_at' => $at->utc()->addMinutes($service->duration_minutes),
            'status' => AppointmentStatus::Confirmed,
            'mode' => AppointmentMode::Online,
            'locale' => 'ar',
        ]);

        $appointment->patient->forceFill(['email' => $email])->save();

        return $appointment->fresh();
    };

    $todayReachable = $make('2026-06-09 09:00:00', 'reachable@example.com');
    $todayUnreachable = $make('2026-06-09 10:00:00', null);
    $tomorrowReachable = $make('2026-06-10 09:00:00', 'also@example.com');
    $tomorrowUnreachable = $make('2026-06-10 11:00:00', null);

    Artisan::call('clinic:send-daily-schedule');

    Notification::assertSentOnDemand(
        DailySchedule::class,
        function (DailySchedule $notification) use (
            $todayReachable, $todayUnreachable, $tomorrowReachable, $tomorrowUnreachable
        ): bool {
            $today = $notification->appointments->pluck('id')->all();
            $calls = $notification->callList->pluck('id')->all();

            return
                // Today's schedule is today's, both patients on it.
                in_array($todayReachable->id, $today, true)
                && in_array($todayUnreachable->id, $today, true)
                && ! in_array($tomorrowUnreachable->id, $today, true)

                /*
                 * The call list is TOMORROW's unreachable patients only. A
                 * call at 07:00 about an appointment at 09:00 the same
                 * morning is too late to be a reminder; a day's notice is
                 * what makes it worth making.
                 */
                && $calls === [$tomorrowUnreachable->id]
                && ! in_array($tomorrowReachable->id, $calls, true)
                && ! in_array($todayUnreachable->id, $calls, true);
        }
    );
});

it('marks unreachable patients on the schedule and lists their numbers to call', function () {
    $zone = config('clinic.timezone');
    $now = CarbonImmutable::parse('2026-06-09 07:00:00', $zone);
    CarbonImmutable::setTestNow($now);
    Carbon::setTestNow($now);

    $service = Service::active()->firstOrFail();
    $staff = User::query()->firstOrFail();

    $at = CarbonImmutable::parse('2026-06-10 11:00:00', $zone);

    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $staff->id,
        'starts_at' => $at->utc(),
        'ends_at' => $at->utc()->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
        'mode' => AppointmentMode::Online,
        'locale' => 'ar',
    ]);

    $appointment->patient->forceFill(['email' => null, 'name' => 'سلمى فؤاد'])->save();

    $notification = new DailySchedule(
        Carbon::instance($now->toDateTime())->setTimezone($zone),
        collect(),
        collect([$appointment->fresh()]),
    );

    $rendered = renderNotification($notification, 'ar');

    foreach (['html', 'text'] as $part) {
        $body = $rendered[$part];

        // A call list is a list to dial from: name, number, time, service.
        expect($body)->toContain(__('mail.daily_schedule.call_heading', locale: 'ar'));
        expect($body)->toContain('سلمى فؤاد');
        expect($body)->toContain(PhoneNumber::forDisplay($appointment->patient->phone));
        expect($body)->toContain($at->format('H:i'));
        expect($body)->toContain($appointment->service->getTranslation('name', 'ar'));
    }

    // And nothing clinical, because this is a list to dial from rather than a
    // file to read.
    expect($rendered['html'])->not->toContain(__('booking.goals.weight_management', locale: 'ar'));
});
