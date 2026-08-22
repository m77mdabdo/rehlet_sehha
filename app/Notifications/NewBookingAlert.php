<?php

declare(strict_types=1);

namespace App\Notifications;

/**
 * Tells the clinic a booking came in, with the intake summary.
 *
 * This one DOES carry clinical content in the body, and should: the
 * practitioner needs the intake answers to prepare, and it is going to the
 * address that already holds the patient's file. The subject still does not,
 * because the clinic's own inbox is read on a phone like everybody else's.
 *
 * It is also the only notice the clinic gets when a patient books without an
 * email address — see the template, which says so explicitly rather than
 * leaving a blank row that reads like a rendering bug.
 */
class NewBookingAlert extends AppointmentNotification
{
    public function deliveryTemplate(): string
    {
        return 'new_booking_alert';
    }

    protected function view(): string
    {
        return 'new-booking-alert';
    }

    protected function subjectLine(): string
    {
        return __('mail.new_booking.subject', ['reference' => $this->appointment->reference]);
    }

    /**
     * The recipient is the clinic, so a Reply-To pointing at the clinic would
     * aim its own mailbox at itself.
     */
    protected function repliesReachAHuman(): bool
    {
        return false;
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        $intake = $this->appointment->intakeForm;

        return $this->appointmentFacts() + [
            'patient' => $this->appointment->patient,
            'intake' => $intake,
            'goalLabel' => $intake?->goal === null ? null : __('booking.goals.'.$intake->goal),
            'bookedAt' => $this->appointment->created_at?->clone()->setTimezone(config('clinic.timezone')),
        ];
    }
}
