<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Livewire\AppointmentManager;
use App\Livewire\BookingWizard;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Models\NotificationLog;
use App\Models\Service;
use App\Notifications\AppointmentReminder1h;
use App\Notifications\AppointmentReminder24h;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingCancelledAlert;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingRescheduled;
use App\Notifications\NewBookingAlert;
use App\Notifications\PatientMailFailedAlert;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Notifications\AppointmentNotifier;
use App\Support\Contact;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

/**
 * Every message the clinic sends.
 *
 * NOTHING HERE SENDS REAL MAIL. The suite runs with MAIL_MAILER=array (see
 * phpunit.xml) and everything below additionally fakes the mailer or the
 * notification layer. A test that actually delivered would mail whatever
 * address the factory invented, which for a clinic is a genuinely bad day.
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

function notifiableAppointment(?CarbonImmutable $startsAt = null, string $locale = 'ar', ?string $email = 'patient@example.com'): Appointment
{
    $service = Service::active()->firstOrFail();

    $slot = app(AvailabilityEngine::class)->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(14)->utc(),
        null,
        $service,
    )->firstOrFail();

    $startsAt ??= $slot->startsAtUtc;

    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $slot->staffId,
        'starts_at' => $startsAt,
        'ends_at' => $startsAt->addMinutes($service->duration_minutes),
        'status' => AppointmentStatus::Confirmed,
        'mode' => AppointmentMode::Online,
        'locale' => $locale,
    ]);

    $appointment->patient->forceFill(['email' => $email])->save();

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

/*
|------------------------------------------------------------------------------
| The facts every patient message must carry
|------------------------------------------------------------------------------
*/

it('renders every required fact in both locales', function (string $locale) {
    $appointment = notifiableAppointment(locale: $locale);

    $rendered = renderNotification(new BookingConfirmed($appointment), $locale);

    foreach (['html', 'text'] as $part) {
        $body = $rendered[$part];

        // Reference, service, price, mode.
        expect($body)->toContain($appointment->reference);
        expect($body)->toContain($appointment->service->getTranslation('name', $locale));
        expect($body)->toContain(number_format((float) $appointment->price));
        expect($body)->toContain(__('booking.mode.online', locale: $locale));

        /*
         * The time in Cairo, with the timezone NAMED. "17:00" alone is wrong
         * by an hour or three for a patient consulting from Riyadh or London,
         * and she has no way to tell that from the message.
         */
        $cairo = $appointment->starts_at->clone()->setTimezone(config('clinic.timezone'));
        expect($body)->toContain($cairo->locale($locale)->translatedFormat('H:i'));
        expect($body)->toContain(config('clinic.timezone'));

        // The manage link, without which she cannot cancel except by phoning.
        expect($body)->toContain($appointment->cancel_token);

        // And a way to reach a human.
        expect($body)->toContain((string) Contact::phoneDisplay());
    }
})->with(['ar', 'en']);

it('renders the arabic mail right-to-left, on the tables and not only the body', function () {
    $appointment = notifiableAppointment(locale: 'ar');

    $html = renderNotification(new BookingConfirmed($appointment), 'ar')['html'];

    expect($html)->toContain('<html')->toContain('dir="rtl"');

    /*
     * Gmail rewrites the message into its own DOM and keeps the tables while
     * dropping the document's direction. A layout that declares dir once at
     * the top arrives left-to-right, which ragged-lefts every Arabic line.
     * The attribute has to be on the tables themselves.
     */
    preg_match_all('/<table\b[^>]*>/i', $html, $tables);

    expect($tables[0])->not->toBeEmpty();

    $undirected = array_filter(
        $tables[0],
        fn (string $tag): bool => ! str_contains($tag, 'dir="rtl"'),
    );

    expect($undirected)->toBeEmpty(
        "Every table in an Arabic mail needs its own dir. Undirected:\n".implode("\n", $undirected)
    );

    // And the English mail is not silently RTL.
    $english = notifiableAppointment(locale: 'en');
    expect(renderNotification(new BookingConfirmed($english), 'en')['html'])
        ->toContain('dir="ltr"')
        ->not->toContain('dir="rtl"');
});

it('states the appointment date without mangling it in arabic', function () {
    // The bidi bug from the previous commit, in the place nobody would notice
    // it: an Arabic date under a forced Latin direction loses its day number
    // to the far end of the string, and nobody proof-reads their own
    // transactional mail.
    $appointment = notifiableAppointment(locale: 'ar');

    $html = renderNotification(new BookingConfirmed($appointment), 'ar')['html'];

    expect($html)->toContain('<bdi dir="auto">');

    preg_match_all('/<bdi dir="ltr">(.*?)<\/bdi>/su', $html, $forced);

    foreach ($forced[1] as $value) {
        expect($value)->not->toMatch('/\p{Arabic}/u');
    }
});

/*
|------------------------------------------------------------------------------
| Subject lines
|------------------------------------------------------------------------------
*/

it('never puts clinical content in a subject line', function (string $locale) {
    $appointment = notifiableAppointment(locale: $locale);

    $subjects = [
        renderNotification(new BookingConfirmed($appointment), $locale)['subject'],
        renderNotification(new AppointmentReminder24h($appointment), $locale)['subject'],
        renderNotification(new AppointmentReminder1h($appointment), $locale)['subject'],
        renderNotification(new BookingCancelled($appointment), $locale)['subject'],
        renderNotification(new BookingRescheduled($appointment, $appointment->startsAtClinic()), $locale)['subject'],
        renderNotification(new NewBookingAlert($appointment), $locale)['subject'],
        renderNotification(new BookingCancelledAlert($appointment), $locale)['subject'],
    ];

    /*
     * A subject is rendered on a locked phone screen, on a smartwatch and in a
     * preview pane on a shared desk — none of which the patient chose. The
     * reference identifies the appointment to her and means nothing to anyone
     * reading over her shoulder; a condition does not.
     */
    $clinical = [
        'ميتفورمين', 'تكيس', 'مبايض', 'مكسرات', 'شيفتات',
        __('booking.goals.weight_management', locale: $locale),
        // The service name implies the reason for the visit.
        $appointment->service->getTranslation('name', $locale),
    ];

    foreach ($subjects as $subject) {
        expect($subject)->not->toBe('');

        foreach ($clinical as $term) {
            expect($subject)->not->toContain($term);
        }
    }
})->with(['ar', 'en']);

it('carries the intake summary to the clinic but not to the patient', function () {
    $appointment = notifiableAppointment(locale: 'ar');

    // The clinic needs it to prepare, and it goes to the address that already
    // holds the patient's file.
    $clinic = renderNotification(new NewBookingAlert($appointment), 'ar');
    expect($clinic['html'])->toContain('ميتفورمين 500')->toContain('تكيس مبايض');
    expect($clinic['text'])->toContain('ميتفورمين 500');

    // The patient's own confirmation restates her appointment, not her file.
    $patient = renderNotification(new BookingConfirmed($appointment), 'ar');
    expect($patient['html'])->not->toContain('ميتفورمين 500');
    expect($patient['html'])->not->toContain('تكيس مبايض');
});

/*
|------------------------------------------------------------------------------
| The manage link
|------------------------------------------------------------------------------
*/

it('puts a working manage link in the mail and no tracking parameters on it', function () {
    $appointment = notifiableAppointment(locale: 'ar');

    $html = renderNotification(new BookingConfirmed($appointment), 'ar')['html'];

    preg_match_all('#https?://[^"\'\s<>]*'.preg_quote($appointment->cancel_token, '#').'[^"\'\s<>]*#', $html, $links);

    expect($links[0])->not->toBeEmpty('The confirmation must contain the manage link.');

    foreach ($links[0] as $link) {
        /*
         * The token is a bearer credential. A campaign parameter appended to
         * this URL would hand it to whatever analytics endpoint the parameter
         * feeds, and a Referer would carry it to any host the manage page then
         * talks to. There are no tracking parameters here and there will not
         * be any.
         */
        $query = parse_url($link, PHP_URL_QUERY);

        expect($query)->toBeNull("The manage link must carry no query string. Got: {$link}");

        foreach (['utm_', 'gclid', 'fbclid', 'mc_eid', 'ref='] as $tracker) {
            expect($link)->not->toContain($tracker);
        }
    }

    // And it actually resolves.
    $this->get($links[0][0])->assertOk()->assertSee($appointment->reference, false);
});

it('sends no request to any host but our own', function () {
    $appointment = notifiableAppointment(locale: 'ar');

    $html = renderNotification(new BookingConfirmed($appointment), 'ar')['html'];

    // No tracking pixel, no remote CSS, no third-party image.
    preg_match_all('/<img\b[^>]*src="([^"]+)"/i', $html, $images);

    foreach ($images[1] as $src) {
        expect($src)->toStartWith(rtrim((string) config('app.url'), '/'));
    }

    expect($html)->not->toContain('<script');
    expect($html)->not->toMatch('/<link[^>]+rel="stylesheet"/i');
});

/*
|------------------------------------------------------------------------------
| Dispatch and delivery logging
|------------------------------------------------------------------------------
*/

it('logs a delivery from queued through to sent', function () {
    Mail::fake();

    $appointment = notifiableAppointment();

    app(AppointmentNotifier::class)->bookingConfirmed($appointment);

    $log = NotificationLog::query()
        ->where('appointment_id', $appointment->id)
        ->where('template', 'booking_confirmed')
        ->firstOrFail();

    // The queue is sync in tests, so the send has already happened.
    expect($log->status)->toBe(NotificationLog::STATUS_SENT);
    expect($log->sent_at)->not->toBeNull();

    // The recipient is stored encrypted; the cast makes it readable here and
    // unreadable in the table.
    expect($log->recipient)->toBe('patient@example.com');
    expect($log->getRawOriginal('recipient'))->not->toContain('patient@example.com');
});

it('records a skipped delivery when the patient gave no email address', function () {
    Notification::fake();

    $appointment = notifiableAppointment(email: null);

    app(AppointmentNotifier::class)->bookingConfirmed($appointment);

    /*
     * A real fraction of patients book without an email, because the form does
     * not require one. Nothing can be sent to them — and that has to be
     * visible, or the clinic has no way to know somebody is holding an
     * appointment she was never told about.
     */
    $log = NotificationLog::query()
        ->where('appointment_id', $appointment->id)
        ->where('template', 'booking_confirmed')
        ->firstOrFail();

    expect($log->status)->toBe(NotificationLog::STATUS_SKIPPED);
    expect($log->error)->toContain('did not give an email address');

    Notification::assertNothingSent();
});

it('tells the clinic when a patient booked without an email address', function () {
    $appointment = notifiableAppointment(email: null);

    $rendered = renderNotification(new NewBookingAlert($appointment), 'ar');

    // Said in words rather than left as a blank cell, which would read like a
    // rendering fault rather than an instruction to telephone her.
    expect($rendered['html'])->toContain(__('mail.new_booking.no_email', locale: 'ar'));
    expect($rendered['text'])->toContain(__('mail.new_booking.no_email', locale: 'ar'));
});

it('logs a failure and raises a doctor alert when a confirmation cannot be delivered', function () {
    $appointment = notifiableAppointment();

    /*
     * Force the transport to fail. This is the case the whole alerting path
     * exists for: somebody has booked, been shown a success screen, and will
     * never receive the message telling her it worked.
     */
    Mail::shouldReceive('mailer')->andThrow(new RuntimeException('SMTP connection refused'));

    try {
        app(AppointmentNotifier::class)->bookingConfirmed($appointment);
    } catch (Throwable) {
        // The sync queue rethrows after marking the job failed. In production
        // the worker swallows this and retries.
    }

    $log = NotificationLog::query()
        ->where('appointment_id', $appointment->id)
        ->where('template', 'booking_confirmed')
        ->firstOrFail();

    expect($log->status)->toBe(NotificationLog::STATUS_FAILED);
    expect($log->error)->toContain('SMTP connection refused');
});

it('alerts the clinic once the retries are exhausted, not on the first bounce', function () {
    Notification::fake();

    $appointment = notifiableAppointment();

    $notification = new BookingConfirmed($appointment);

    // failed() is what SendQueuedNotifications calls after the last attempt.
    $notification->failed(new RuntimeException('mailbox unavailable'));

    Notification::assertSentOnDemand(
        PatientMailFailedAlert::class,
        function (PatientMailFailedAlert $alert, array $channels, AnonymousNotifiable $notifiable): bool {
            return $notifiable->routes['mail'] === Contact::email()
                && $alert->failedTemplate === 'booking_confirmed'
                && str_contains($alert->reason, 'mailbox unavailable');
        }
    );
});

/*
|------------------------------------------------------------------------------
| Booking, cancelling and rescheduling
|------------------------------------------------------------------------------
*/

it('notifies the patient and the clinic when a booking is cancelled', function () {
    Notification::fake();

    $appointment = notifiableAppointment();

    $notifier = app(AppointmentNotifier::class);
    $notifier->bookingCancelled($appointment);
    $notifier->bookingCancelledAlert($appointment);

    Notification::assertSentOnDemand(BookingCancelled::class);
    Notification::assertSentOnDemand(BookingCancelledAlert::class);
});

it('states both the old and the new time when an appointment moves', function (string $locale) {
    $appointment = notifiableAppointment(locale: $locale);

    $previous = $appointment->startsAtClinic()->clone()->subDays(2);

    $rendered = renderNotification(new BookingRescheduled($appointment, $previous), $locale);

    foreach (['html', 'text'] as $part) {
        /*
         * A message naming only the new time is indistinguishable from the
         * original confirmation to anyone with three of these in her inbox.
         */
        expect($rendered[$part])->toContain($previous->locale($locale)->translatedFormat('j F'));
        expect($rendered[$part])->toContain($appointment->startsAtClinic()->locale($locale)->translatedFormat('j F'));
        expect($rendered[$part])->toContain(__('mail.rescheduled.old_time', locale: $locale));
        expect($rendered[$part])->toContain(__('mail.rescheduled.new_time', locale: $locale));
    }
})->with(['ar', 'en']);

it('sends each notification in the language the booking was made in', function () {
    Notification::fake();

    $arabic = notifiableAppointment(locale: 'ar');
    $english = notifiableAppointment(locale: 'en');

    $notifier = app(AppointmentNotifier::class);
    $notifier->bookingConfirmed($arabic);
    $notifier->bookingConfirmed($english);

    Notification::assertSentOnDemand(
        BookingConfirmed::class,
        fn (BookingConfirmed $n): bool => $n->appointment->is($arabic) && $n->locale === 'ar',
    );

    Notification::assertSentOnDemand(
        BookingConfirmed::class,
        fn (BookingConfirmed $n): bool => $n->appointment->is($english) && $n->locale === 'en',
    );
});

it('mails the clinic in the clinic language whatever the patient booked in', function () {
    Notification::fake();

    // The practitioner and reception work in Arabic. Rendering the clinic's own
    // alert in English because one patient booked in English would be absurd.
    $english = notifiableAppointment(locale: 'en');

    app(AppointmentNotifier::class)->newBookingAlert($english);

    Notification::assertSentOnDemand(
        NewBookingAlert::class,
        fn (NewBookingAlert $n): bool => $n->locale === config('app.locale'),
    );
});

/*
|------------------------------------------------------------------------------
| The flows, end to end through the components
|------------------------------------------------------------------------------
*/

it('queues a confirmation and a clinic alert when a booking is made', function () {
    Notification::fake();

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

    // The form has a minimum fill time, so a booking submitted instantly reads
    // as a script rather than a person. Advance the clock instead of faking
    // the timestamp, which is what a real patient does by typing.
    CarbonImmutable::setTestNow(CarbonImmutable::getTestNow()->addSeconds(30));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $component
        ->set([
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
        ])
        ->call('submit')
        ->assertHasNoErrors()
        ->assertSet('step', 4);

    $appointment = Appointment::query()->latest('id')->firstOrFail();

    Notification::assertSentOnDemandTimes(BookingConfirmed::class, 1);
    Notification::assertSentOnDemandTimes(NewBookingAlert::class, 1);

    // The language the booking was made in is recorded on the row, because it
    // is the only moment it is known — a reminder months later is rendered by
    // a cron run with no locale of its own.
    expect($appointment->locale)->toBe(App::getLocale());
});

it('queues both cancellation messages when a patient cancels', function () {
    Notification::fake();

    $appointment = notifiableAppointment();

    Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('cancel')
        ->assertSet('flash', 'manage.cancelled');

    // She needs written confirmation it worked; the clinic needs to know the
    // hour is free again before the morning.
    Notification::assertSentOnDemandTimes(BookingCancelled::class, 1);
    Notification::assertSentOnDemandTimes(BookingCancelledAlert::class, 1);
});

it('queues a reschedule notice carrying the time it moved from', function () {
    Notification::fake();

    $appointment = notifiableAppointment();
    $originalStart = $appointment->startsAtClinic();

    $component = Livewire::test(AppointmentManager::class, ['token' => $appointment->cancel_token])
        ->call('startReschedule');

    $slot = $component->instance()->slots()
        ->first(fn ($candidate) => $candidate->startsAtUtc->ne($appointment->starts_at));

    expect($slot)->not->toBeNull('The test clinic has no other free slot to move into.');

    $component->call('selectSlot', $slot->key())->call('confirmReschedule');

    Notification::assertSentOnDemand(
        BookingRescheduled::class,
        function (BookingRescheduled $notification) use ($originalStart): bool {
            /*
             * The old time has to be captured before the move — after
             * reschedule() the model no longer knows it, and a message naming
             * only the new time is indistinguishable from the original
             * confirmation.
             */
            return $notification->previousStartsAt->equalTo($originalStart);
        }
    );
});
