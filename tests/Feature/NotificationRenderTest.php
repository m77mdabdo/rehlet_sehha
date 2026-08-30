<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Review;
use App\Notifications\AppointmentReminder1h;
use App\Notifications\AppointmentReminder24h;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingCancelledAlert;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingRescheduled;
use App\Notifications\DailySchedule;
use App\Notifications\NewBookingAlert;
use App\Notifications\PatientMailFailedAlert;
use App\Notifications\ReviewRequested;
use App\Support\Locales;
use Database\Seeders\ServiceSeeder;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use Illuminate\Support\Facades\View;

/**
 * EVERY EMAIL IS ACTUALLY RENDERED HERE. BOTH PARTS. BOTH LOCALES.
 *
 * The review invitation was broken for its entire existence and nothing
 * noticed: emails/text/review-requested.blade.php was never written, so every
 * send threw "View [emails.text.review-requested] not found", failed three
 * times and landed in the delivery log. No patient ever reached a review form.
 *
 * The reason the suite missed it is the shape of the tests around it. They all
 * used Notification::fake(), which records that a notification was DISPATCHED
 * and never builds the message — so a missing view, a broken payload, an
 * undefined variable in a template and a translation key that does not exist
 * are all invisible to them. They prove the code was called, not that the
 * thing works.
 *
 * That was the fifth silent failure of this shape in the project. This file is
 * the answer to the whole class: it constructs each notification for real,
 * asks it for its Mailable, and RENDERS both the HTML and the plain-text
 * bodies. Anything that would throw at send time throws here instead.
 *
 * A mail with no text part is not a style choice. Text/plain is what a screen
 * reader, a watch, a low-bandwidth client and a spam filter read first, and
 * Laravel raises rather than degrading when the view is missing — so half a
 * template is a total failure, not a partial one.
 */
beforeEach(function () {
    $this->seed(ServiceSeeder::class);
});

/**
 * A confirmed appointment with everything a template might reach for.
 */
function renderableAppointment(string $locale = 'ar'): Appointment
{
    return Appointment::factory()->create([
        'status' => AppointmentStatus::Confirmed,
        'locale' => $locale,
        'starts_at' => Carbon::now()->addDays(2)->setTime(11, 0),
        'ends_at' => Carbon::now()->addDays(2)->setTime(11, 45),
    ]);
}

/**
 * Every notification the application can send, built for real.
 *
 * Listed by name rather than discovered by scanning the directory, because a
 * class this test does not know how to construct must be an explicit decision
 * rather than something a glob quietly skips.
 *
 * @return array<string, callable(string): Notification>
 */
function everyNotification(): array
{
    return [
        'BookingConfirmed' => fn (string $l) => new BookingConfirmed(renderableAppointment($l)),
        'BookingCancelled' => fn (string $l) => new BookingCancelled(renderableAppointment($l)),
        'BookingRescheduled' => fn (string $l) => new BookingRescheduled(
            renderableAppointment($l),
            Carbon::now()->addDay()->setTime(9, 0),
        ),
        'AppointmentReminder24h' => fn (string $l) => new AppointmentReminder24h(renderableAppointment($l)),
        'AppointmentReminder1h' => fn (string $l) => new AppointmentReminder1h(renderableAppointment($l)),
        'NewBookingAlert' => fn (string $l) => new NewBookingAlert(renderableAppointment($l)),
        'BookingCancelledAlert' => fn (string $l) => new BookingCancelledAlert(renderableAppointment($l)),
        'ReviewRequested' => function (string $l) {
            $appointment = renderableAppointment($l);

            return new ReviewRequested(
                $appointment,
                Review::factory()->create([
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                ]),
            );
        },
        'DailySchedule' => fn (string $l) => new DailySchedule(
            Carbon::now()->addDay(),
            new Collection([renderableAppointment($l)]),
            new Collection([renderableAppointment($l)]),
        ),
        'PatientMailFailedAlert' => fn (string $l) => new PatientMailFailedAlert(
            renderableAppointment($l),
            'booking_confirmed',
            'Connection could not be established with host smtp.example.com',
        ),
    ];
}

it('renders both parts of every notification, in both locales', function (string $name) {
    foreach (Locales::all() as $locale) {
        app()->setLocale($locale);

        $notification = everyNotification()[$name]($locale);

        /*
         * An on-demand notifiable, exactly as AppointmentNotifier uses. Going
         * through toMail() rather than reaching for the Mailable directly
         * means the payload, the subject and the reply-to are all built the
         * way a real send builds them.
         */
        $notifiable = NotificationFacade::route('mail', 'patient@example.com');

        $mailable = $notification->toMail($notifiable);

        $content = $mailable->content();
        $data = $mailable->buildViewData() + $content->with;

        /*
         * BOTH VIEWS MUST EXIST BEFORE EITHER IS RENDERED. Asserted separately
         * so the failure names the missing file — Laravel's own error for a
         * missing text view arrives from deep inside the mailer and does not
         * say which notification asked for it.
         */
        foreach (['markdown' => $content->markdown, 'text' => $content->text] as $kind => $view) {
            expect($view)->not->toBeNull("{$name} declares no {$kind} view.");

            expect(View::exists($view))->toBeTrue(
                "{$name} ({$locale}) is missing its {$kind} view: {$view}.\n\n"
                .'A mail with only half its template does not degrade — Laravel throws, '
                .'the job fails three times, and the patient is never told anything.'
            );
        }

        // And now actually build them. An undefined variable, a broken
        // component or a bad translation key surfaces here.
        $html = $mailable->render();

        expect(trim($html))->not->toBe('', "{$name} ({$locale}) rendered an empty HTML body.");

        $text = view($content->text, $data)->render();

        expect(trim($text))->not->toBe('', "{$name} ({$locale}) rendered an empty text body.");

        /*
         * A rendered template still holding a translation key means the key is
         * missing: Laravel returns the key itself rather than raising.
         */
        foreach (['html' => $html, 'text' => $text] as $kind => $body) {
            expect($body)->not->toMatch(
                '/\b(mail|booking|common)\.[a-z_]+\.[a-z_]+\b/',
                "{$name} ({$locale}) {$kind} body contains an unresolved translation key."
            );
        }
    }
})->with(array_keys(everyNotification()));

it('gives every mail template both halves', function () {
    /*
     * The directory-level counterpart. The test above only covers templates
     * some notification reaches for; this catches an HTML view added with no
     * text twin before anything sends it.
     */
    $html = collect(glob(resource_path('views/emails/*.blade.php')))
        ->map(fn (string $p): string => basename($p, '.blade.php'))
        ->sort()
        ->values();

    $text = collect(glob(resource_path('views/emails/text/*.blade.php')))
        ->map(fn (string $p): string => basename($p, '.blade.php'))
        ->sort()
        ->values();

    expect($html)->not->toBeEmpty();

    expect($html->diff($text)->values()->all())->toBe(
        [],
        'These templates have an HTML view and no plain-text view: '
        .$html->diff($text)->implode(', ')
    );

    expect($text->diff($html)->values()->all())->toBe(
        [],
        'These templates have a plain-text view and no HTML view: '
        .$text->diff($html)->implode(', ')
    );
});
