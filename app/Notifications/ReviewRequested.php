<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\Review;

/**
 * The invitation to leave a review, three days after a completed appointment.
 *
 * WHY THREE DAYS. Same day reads as a shop asking before you have left the
 * building, and it arrives while the session is still being digested. Three
 * days is long enough for the plan to have met a real week, short enough to
 * still be remembered.
 *
 * WHY IT ASKS RATHER THAN ASSUMES. The link opens a form where consent to
 * publish is a separate, unticked box. A patient may want to tell the clinic
 * something and not want it on the internet, and those are different
 * decisions — the model refuses to publish anything without the second.
 *
 * The token in the link is a BEARER CREDENTIAL: whoever holds it can write as
 * that patient. The page it opens is noindex, no-store and no-referrer, the
 * same discipline as the cancellation link.
 */
class ReviewRequested extends AppointmentNotification
{
    public function __construct(
        Appointment $appointment,
        private readonly Review $review,
    ) {
        parent::__construct($appointment);
    }

    public function deliveryTemplate(): string
    {
        return 'review_requested';
    }

    protected function view(): string
    {
        return 'review-requested';
    }

    protected function subjectLine(): string
    {
        return __('mail.review_requested.subject');
    }

    /**
     * @return array<string, mixed>
     */
    protected function payload(): array
    {
        return $this->appointmentFacts() + [
            /*
             * The locale is passed explicitly here and nowhere else: this URL
             * is built inside a QUEUED JOB, where the URL generator's default
             * is whatever locale the worker happens to be in rather than the
             * one the patient booked in.
             */
            'reviewUrl' => route('review.show', [
                'locale' => $this->appointment->locale ?? app()->getLocale(),
                'token' => $this->review->token,
            ]),
        ];
    }
}
