<?php

declare(strict_types=1);

namespace App\Notifications;

/**
 * Sent when an appointment is cancelled, whichever side cancelled it.
 *
 * Deliberately sent in both directions. A patient who cancels needs the
 * confirmation that it worked — otherwise she is left wondering whether to
 * turn up anyway — and a patient whose appointment the clinic cancelled needs
 * to be told before she travels for it. The message reads the same either way
 * because the fact is the same; what she does next differs, so it offers a
 * route back to the calendar rather than assuming she wants one.
 */
class BookingCancelled extends AppointmentNotification
{
    public function deliveryTemplate(): string
    {
        return 'booking_cancelled';
    }

    protected function view(): string
    {
        return 'booking-cancelled';
    }

    protected function subjectLine(): string
    {
        return __('mail.cancelled.subject', ['reference' => $this->appointment->reference]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->appointmentFacts() + [
            'bookingUrl' => route('booking', ['locale' => $this->appointment->locale]),
        ];
    }
}
