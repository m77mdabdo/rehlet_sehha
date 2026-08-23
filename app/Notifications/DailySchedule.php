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
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The day's appointments, mailed to the clinic at 07:00 Cairo.
 *
 * Sent even when the day is empty, and that is deliberate. A digest that only
 * arrives when there is something in it teaches the reader nothing on the
 * mornings it does not arrive — she cannot tell "no appointments" from "the
 * cron stopped running three weeks ago". An explicit "no appointments today"
 * is a heartbeat as much as a schedule, and it costs one email.
 *
 * Does not extend AppointmentNotification: this message is about a DAY, not an
 * appointment, so there is no single row for the log to hang from and no
 * manage link to include.
 */
class DailySchedule extends Notification implements LogsDelivery, ShouldQueue
{
    use Queueable;

    public int $tries = 3;

    /**
     * Discard the job if the models it carries are gone. See
     * AppointmentNotification::$deleteWhenMissingModels for the reasoning; the
     * failure mode is identical and the alternative is a permanently
     * unactionable row in failed_jobs.
     */
    public bool $deleteWhenMissingModels = true;

    public ?int $deliveryLogId = null;

    /**
     * @param  Collection<int, Appointment>  $appointments  today's schedule
     * @param  Collection<int, Appointment>  $callList  tomorrow's patients with no email
     */
    public function __construct(
        public readonly Carbon $date,
        public readonly Collection $appointments,
        public readonly Collection $callList = new Collection,
    ) {}

    /**
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
            templateName: 'daily-schedule',
            subjectLine: __('mail.daily_schedule.subject', [
                'date' => $this->date->translatedFormat('j F Y'),
            ]),
            payload: [
                'date' => $this->date,
                'appointments' => $this->appointments,
                /*
                 * Tomorrow's patients who cannot be reached electronically.
                 * They receive no reminder at all, so a person has to ring
                 * them — and this list is the only place that need surfaces.
                 */
                'callList' => $this->callList,
                'callDate' => $this->date->clone()->addDay(),
                'timezone' => config('clinic.timezone'),
            ],
            // The clinic is the recipient; a Reply-To back to the clinic is noise.
            replyToClinic: false,
        ))->to($address);
    }

    public function deliveryTemplate(): string
    {
        return 'daily_schedule';
    }

    /**
     * A whole day, not one appointment.
     */
    public function deliveryAppointment(): ?Appointment
    {
        return null;
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
