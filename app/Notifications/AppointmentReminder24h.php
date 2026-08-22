<?php

declare(strict_types=1);

namespace App\Notifications;

/**
 * Sent roughly a day before the appointment.
 *
 * The reminder that can still change something. Twenty-four hours is enough
 * notice for a patient to move the appointment AND for the clinic to offer the
 * released hour to somebody else, which is why the copy says so rather than
 * only telling her to turn up.
 */
class AppointmentReminder24h extends AppointmentNotification
{
    use Concerns\OnlyRemindsLiveAppointments;

    public function deliveryTemplate(): string
    {
        return 'reminder_24h';
    }

    protected function view(): string
    {
        return 'reminder-24h';
    }

    protected function subjectLine(): string
    {
        return __('mail.reminder_24h.subject', ['reference' => $this->appointment->reference]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->appointmentFacts();
    }
}
