<?php

declare(strict_types=1);

namespace App\Notifications\Contracts;

use App\Listeners\RecordNotificationDelivery;
use App\Models\Appointment;
use App\Services\Notifications\AppointmentNotifier;

/**
 * A notification whose delivery is recorded in notification_logs.
 *
 * The point of the log is to answer one question after the fact: did the
 * message actually reach the patient? A clinic that cannot answer that has no
 * way to tell a genuine no-show from someone who was never told their
 * appointment existed — and those two call for opposite responses.
 *
 * The row is created when the notification is DISPATCHED, not when it is
 * delivered, and that ordering is the whole design. If the row only appeared
 * on success, a queue that never ran would leave no trace at all: no row, no
 * error, nothing to look at. Silence would be indistinguishable from success,
 * which is exactly the failure this table exists to make impossible.
 *
 * @see RecordNotificationDelivery for the state transitions.
 * @see AppointmentNotifier for where rows are opened.
 */
interface LogsDelivery
{
    /**
     * A stable identifier for this kind of message, e.g. 'booking_confirmed'.
     *
     * Written to notification_logs.template and used to find the open row for
     * this delivery. Stable because it is read by humans looking at a log
     * weeks later, and by the failure alert deciding whether a patient is
     * sitting there not knowing their booking worked.
     */
    public function deliveryTemplate(): string;

    /**
     * The appointment this message is about, if any.
     *
     * Null for messages that are not about one appointment — the daily
     * schedule covers a whole day, so it has no single row to hang from.
     */
    public function deliveryAppointment(): ?Appointment;

    /**
     * The notification_logs row opened for this delivery.
     *
     * Set by the notifier before dispatch, carried through the queue with the
     * serialised notification, and read by the delivery listener so it can
     * update the row it already created rather than guessing which of several
     * similar rows this event belongs to.
     */
    public function deliveryLogId(): ?int;

    public function setDeliveryLogId(int $id): void;
}
