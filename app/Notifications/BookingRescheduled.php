<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Appointment;
use Illuminate\Support\Carbon;

/**
 * Sent when an appointment moves, stating BOTH times.
 *
 * The old time is in the message on purpose. A patient who has three
 * confirmation emails in her inbox cannot tell which one is current from a
 * message that only names a new time, and "your appointment is now Tuesday
 * 10:00" is indistinguishable from the original booking if she has forgotten
 * what she booked. Naming what it moved FROM is what makes the message
 * self-evidently about a change.
 */
class BookingRescheduled extends AppointmentNotification
{
    /**
     * @param  Carbon  $previousStartsAt  the old time, already in clinic time
     */
    public function __construct(
        Appointment $appointment,
        public readonly Carbon $previousStartsAt,
    ) {
        parent::__construct($appointment);
    }

    public function deliveryTemplate(): string
    {
        return 'booking_rescheduled';
    }

    protected function view(): string
    {
        return 'booking-rescheduled';
    }

    protected function subjectLine(): string
    {
        return __('mail.rescheduled.subject', ['reference' => $this->appointment->reference]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->appointmentFacts() + [
            'previousStartsAt' => $this->previousStartsAt,
        ];
    }
}
