<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\AppointmentMode;

/**
 * Sent about an hour before the appointment.
 *
 * Too late to rebook and not meant to be: this one exists so the appointment
 * is not missed while somebody is doing something else. For an online
 * consultation it is also the prompt to go and find somewhere quiet.
 */
class AppointmentReminder1h extends AppointmentNotification
{
    use Concerns\OnlyRemindsLiveAppointments;

    public function deliveryTemplate(): string
    {
        return 'reminder_1h';
    }

    protected function view(): string
    {
        return 'reminder-1h';
    }

    protected function subjectLine(): string
    {
        return __('mail.reminder_1h.subject', ['reference' => $this->appointment->reference]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->appointmentFacts() + [
            'isOnline' => $this->appointment->mode === AppointmentMode::Online,
        ];
    }
}
