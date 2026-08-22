<?php

declare(strict_types=1);

namespace App\Notifications;

/**
 * Sent the moment a booking is written.
 *
 * The most important message the clinic sends. It is the patient's only proof
 * the booking exists, and it carries the manage link — without which she has
 * no way to cancel or move the appointment except by telephoning during
 * working hours. If this one fails, somebody has booked and does not know it
 * worked, which is why its failure raises a doctor alert.
 */
class BookingConfirmed extends AppointmentNotification
{
    public function deliveryTemplate(): string
    {
        return 'booking_confirmed';
    }

    protected function view(): string
    {
        return 'booking-confirmed';
    }

    protected function subjectLine(): string
    {
        return __('mail.confirmed.subject', ['reference' => $this->appointment->reference]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->appointmentFacts();
    }
}
