<?php

declare(strict_types=1);

namespace App\Notifications\Concerns;

use App\Models\NotificationLog;

/**
 * Refuses to remind anyone about an appointment that is no longer happening.
 *
 * The sending command already filters on status, so why check again here?
 * Because of the gap between the two. The command selects, claims and queues;
 * the queue worker picks the job up on its next run, up to a minute later on
 * shared hosting, and possibly minutes later if a backlog has built. A patient
 * can cancel inside that window — in fact she is unusually likely to, since the
 * reminder itself is what prompts people to cancel.
 *
 * Sending anyway is not a cosmetic error. "Your appointment is tomorrow"
 * arriving after a patient has cancelled reads as the clinic having lost her
 * cancellation, and the next thing she does is ring up to check, or simply
 * turn up. This is the last point at which that can be prevented, because the
 * notification refetches the appointment when the queue deserialises it, so
 * the status read here is current rather than the one captured at dispatch.
 *
 * The log row is closed as `skipped` rather than left open. A row stuck at
 * `queued` forever is indistinguishable from a queue that died, and the whole
 * value of this table is that the two can be told apart.
 */
trait OnlyRemindsLiveAppointments
{
    public function shouldSend(object $notifiable, string $channel): bool
    {
        if ($this->appointment->isLive()) {
            return true;
        }

        $logId = $this->deliveryLogId();

        if ($logId !== null) {
            NotificationLog::query()
                ->whereKey($logId)
                ->update([
                    'status' => NotificationLog::STATUS_SKIPPED,
                    'error' => 'Appointment was '.$this->appointment->status->value.' by the time the reminder was due.',
                    'updated_at' => now(),
                ]);
        }

        return false;
    }
}
