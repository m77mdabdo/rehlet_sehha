<?php

declare(strict_types=1);

namespace App\Notifications;

/**
 * Tells the clinic a patient cancelled, and that the hour is free again.
 *
 * Sent only for patient-initiated cancellations. Mailing the clinic to report
 * a cancellation the clinic just performed would be noise, and noise in an
 * alert channel is how real alerts stop being read.
 */
class BookingCancelledAlert extends AppointmentNotification
{
    public function deliveryTemplate(): string
    {
        return 'booking_cancelled_alert';
    }

    protected function view(): string
    {
        return 'booking-cancelled-alert';
    }

    protected function subjectLine(): string
    {
        return __('mail.cancelled_alert.subject', ['reference' => $this->appointment->reference]);
    }

    protected function repliesReachAHuman(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->appointmentFacts() + [
            'patient' => $this->appointment->patient,
            'reason' => $this->appointment->cancellation_reason,
        ];
    }
}
