<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Models\NotificationLog;
use App\Notifications\Contracts\LogsDelivery;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Notifications\Events\NotificationSent;
use Throwable;

/**
 * Moves a delivery row from queued to sent or failed.
 *
 * The row itself is created at dispatch by AppointmentNotifier — see that
 * class for why it cannot wait until delivery. This listener only ever
 * updates: it never creates, so an event arriving for a notification nobody
 * logged (a framework notification, a package's own mail) passes straight
 * through instead of littering the clinic's delivery log.
 *
 * Registered on the events rather than wired into the notifications so that
 * the logging cannot be forgotten when a ninth notification is added. Adding
 * one means implementing LogsDelivery, and the rest happens on its own.
 */
class RecordNotificationDelivery
{
    public function sent(NotificationSent $event): void
    {
        $log = $this->logFor($event->notification);

        $log?->update([
            'status' => NotificationLog::STATUS_SENT,
            'sent_at' => now(),
            // Cleared, because a row that succeeded on its third attempt should
            // not still be displaying the error from its first.
            'error' => null,
        ]);
    }

    public function failed(NotificationFailed $event): void
    {
        $log = $this->logFor($event->notification);

        if ($log === null) {
            return;
        }

        /*
         * This fires on EVERY failed attempt, not only the last one, so the
         * row tracks the most recent outcome rather than the final verdict.
         * A message that fails once and succeeds on retry ends up `sent` with
         * the error cleared, which is the honest description of what happened.
         *
         * The alert to the clinic is deliberately not raised here — it belongs
         * to the notification's failed() hook, which runs only once the
         * retries are genuinely exhausted.
         */
        $log->update([
            'status' => NotificationLog::STATUS_FAILED,
            'error' => $this->describe($event->data['exception'] ?? null),
        ]);
    }

    private function logFor(object $notification): ?NotificationLog
    {
        if (! $notification instanceof LogsDelivery) {
            return null;
        }

        $id = $notification->deliveryLogId();

        return $id === null ? null : NotificationLog::query()->find($id);
    }

    /**
     * A short, storable description of what went wrong.
     *
     * Truncated because an SMTP failure can carry a multi-kilobyte server
     * response, and the useful part is always at the front. The full trace
     * goes to the application log via the queue's own failure handling; this
     * column exists so somebody reading the delivery log can see at a glance
     * whether an address bounced or the whole transport was down.
     */
    private function describe(mixed $exception): string
    {
        if (! $exception instanceof Throwable) {
            return 'Delivery failed for an unrecorded reason.';
        }

        $message = trim($exception->getMessage());

        if ($message === '') {
            $message = $exception::class;
        }

        // One line: SMTP responses arrive with embedded newlines that make the
        // log unreadable in a terminal.
        $message = (string) preg_replace('/\s+/u', ' ', $message);

        return mb_substr($message, 0, 500);
    }
}
