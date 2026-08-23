<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Mail\AppointmentMailable;
use App\Models\Appointment;
use App\Notifications\Contracts\LogsDelivery;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Notifications\Notification;

/**
 * Tells the clinic that a message to a patient could not be delivered.
 *
 * Raised only after every retry is exhausted, from the notification's failed()
 * hook — not on the first bounce. A transient failure that the second attempt
 * fixes is not something anybody needs to be told about, and an alert channel
 * that cries wolf is one the clinic learns to ignore.
 *
 * The case this exists for is the confirmation. Somebody has completed a
 * booking, been shown a success screen, and been told to expect an email that
 * is never going to arrive. She has a real appointment in the calendar and no
 * knowledge of it, and no manage link to cancel with. Nothing else in the
 * system would ever surface that — the appointment looks perfectly normal —
 * so the clinic is told to pick up the phone.
 *
 * It is deliberately NOT itself a LogsDelivery failure trigger: if this alert
 * fails, it fails quietly to the application log rather than raising another
 * alert about the alert.
 */
class PatientMailFailedAlert extends Notification implements LogsDelivery, ShouldQueue
{
    use Queueable;

    /**
     * One attempt at a message about a failure.
     *
     * If the clinic's own mailbox is unreachable too, the mail transport is
     * down as a whole and retrying this will not fix it. The exhausted-retry
     * path writes to the application log, which is where a total outage
     * belongs.
     */
    public int $tries = 1;

    /**
     * Discard the job if the models it carries are gone. See
     * AppointmentNotification::$deleteWhenMissingModels for the reasoning; the
     * failure mode is identical and the alternative is a permanently
     * unactionable row in failed_jobs.
     */
    public bool $deleteWhenMissingModels = true;

    public ?int $deliveryLogId = null;

    public function __construct(
        public readonly Appointment $appointment,
        public readonly string $failedTemplate,
        public readonly string $reason,
    ) {}

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
            templateName: 'delivery-failed',
            subjectLine: __('mail.delivery_failed.subject', [
                'reference' => $this->appointment->reference,
            ]),
            payload: [
                'appointment' => $this->appointment,
                'reference' => $this->appointment->reference,
                'startsAt' => $this->appointment->startsAtClinic(),
                'timezone' => config('clinic.timezone'),
                'patient' => $this->appointment->patient,
                'failedTemplate' => $this->failedTemplate,
                'reason' => $this->reason,
            ],
            replyToClinic: false,
        ))->to($address);
    }

    public function deliveryTemplate(): string
    {
        return 'patient_mail_failed_alert';
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
}
