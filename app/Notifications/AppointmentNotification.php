<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\AppointmentMailable;
use App\Models\Appointment;
use App\Notifications\Contracts\LogsDelivery;
use App\Services\Notifications\AppointmentNotifier;
use App\Support\Contact;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

/**
 * Everything the clinic's eight notifications have in common.
 *
 * QUEUED, ALWAYS. Nothing here is sent inline. A booking request must not wait
 * on an SMTP handshake with a third-party host — on shared hosting that
 * handshake can take several seconds and can fail outright, and a patient who
 * has just typed in her medical history should never watch the form hang, or
 * worse, see it error, because a mail server was slow. The write is committed
 * and the message is queued; delivery is a separate concern with its own
 * retries and its own log.
 *
 * @see AppointmentNotifier for dispatch and logging.
 */
abstract class AppointmentNotification extends Notification implements LogsDelivery, ShouldQueue
{
    use Queueable;

    /**
     * Three attempts, as specified.
     *
     * Enough to ride out the ordinary failures — a momentary DNS blip, a
     * greylisting delay, the mail host restarting — and few enough that a
     * genuinely undeliverable address is declared dead within minutes rather
     * than being retried for a day. What happens after the third attempt
     * matters more than the number itself: see failed() below.
     */
    public int $tries = 3;

    /**
     * Set by the notifier before dispatch and carried through the queue.
     */
    public ?int $deliveryLogId = null;

    public function __construct(
        public readonly Appointment $appointment,
    ) {}

    /**
     * Waits between attempts, in seconds.
     *
     * Backoff rather than three immediate retries. The failures worth retrying
     * are almost all transient-but-not-instant — a greylist wants a minute, a
     * restarting mail host wants longer — and hammering an SMTP server three
     * times in a second is both useless and a good way to get the sending
     * address rate-limited by the very host we depend on.
     *
     * @return list<int>
     */
    public function backoff(): array
    {
        return [60, 300];
    }

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): Mailable
    {
        /** @var string $address */
        $address = $notifiable->routeNotificationFor('mail', $this);

        return (new AppointmentMailable(
            templateName: $this->view(),
            subjectLine: $this->subjectLine(),
            payload: $this->payload(),
            replyToClinic: $this->repliesReachAHuman(),
        ))->to($address);
    }

    public function deliveryAppointment(): ?Appointment
    {
        return $this->appointment;
    }

    public function deliveryLogId(): ?int
    {
        return $this->deliveryLogId;
    }

    public function setDeliveryLogId(int $id): void
    {
        $this->deliveryLogId = $id;
    }

    /**
     * The Blade view under resources/views/emails, without extension.
     */
    abstract protected function view(): string;

    /**
     * The subject line.
     *
     * NEVER contains clinical content — no goal, no condition, no medication.
     * Subjects are rendered on locked phone screens and in preview panes on
     * shared desks, neither of which the patient consented to. The copy lives
     * in the mail translation files.
     */
    abstract protected function subjectLine(): string;

    /**
     * View data. Facts only; the templates do the wording.
     *
     * @return array<string, mixed>
     */
    abstract protected function payload(): array;

    /**
     * Every retry is spent and the message will not be delivered.
     *
     * For a patient message this is the moment somebody is left with an
     * appointment they do not know about, so the clinic is told to telephone
     * them. Nothing else would ever surface it: the appointment itself looks
     * completely normal in the calendar, and the only evidence is a log row
     * nobody is watching.
     *
     * Called by SendQueuedNotifications once the job has exhausted $tries —
     * deliberately not on each failed attempt, because an alert raised on the
     * first transient bounce, then resolved silently by the second attempt, is
     * how an alert channel becomes something the clinic ignores.
     */
    public function failed(\Throwable $exception): void
    {
        if (! $this->alertsClinicOnFailure()) {
            return;
        }

        app(AppointmentNotifier::class)
            ->alertClinicOfFailedDelivery($this->appointment, $this->deliveryTemplate(), $exception);
    }

    /**
     * Does a permanent failure of this message warrant telephoning someone?
     *
     * True for patient mail. False for the clinic's own alerts — a failed
     * internal notice is a problem for the application log, and mailing the
     * clinic to say that mail to the clinic is not working would be its own
     * kind of joke.
     */
    protected function alertsClinicOnFailure(): bool
    {
        return $this->repliesReachAHuman();
    }

    /**
     * Should a reply to this message reach a person?
     *
     * True for anything sent to a patient. Overridden to false only where the
     * recipient IS the clinic, since a Reply-To pointing the clinic's own
     * mailbox back at itself is noise.
     */
    protected function repliesReachAHuman(): bool
    {
        return true;
    }

    /**
     * The facts every patient mail states, in the language it was booked in.
     *
     * Gathered here rather than in each template so that no message can
     * quietly omit one. A confirmation without the manage link is a
     * confirmation that traps the patient; a reminder without the timezone is
     * a reminder that might be wrong by an hour for anyone travelling.
     *
     * @return array<string, mixed>
     */
    protected function appointmentFacts(): array
    {
        $appointment = $this->appointment;

        return [
            'appointment' => $appointment,
            'reference' => $appointment->reference,
            'service' => $appointment->service->name,
            'startsAt' => $appointment->startsAtClinic(),
            'timezone' => config('clinic.timezone'),
            'mode' => __('booking.mode.'.$appointment->mode->value),
            'price' => $appointment->price,
            'currency' => $appointment->currency,
            'manageUrl' => $appointment->manageUrl(),
            'clinicPhone' => Contact::phoneDisplay(),
            'patientName' => $appointment->patient->name,
        ];
    }
}
