<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * What actually happened to a message, as far as the request that sent it knows.
 *
 * This exists so a screen can stop guessing. The booking confirmation is sent
 * inside the patient's own request now, which means the outcome is knowable at
 * the moment we render the page that tells her about it — and a page that says
 * "check your inbox" when the send threw is worse than one that says nothing,
 * because she will wait for a message instead of writing the reference down.
 *
 * NOT the same thing as NotificationLog::STATUS_*. Those describe the delivery
 * row over its whole life, and the row keeps changing after this request ends:
 * a Queued outcome here becomes `sent` in the log a minute later when the
 * worker runs. This enum is a snapshot of what the request can honestly claim,
 * and nothing more.
 */
enum DeliveryOutcome: string
{
    /**
     * Handed to the mail server before the response was returned.
     *
     * The strongest thing we can truthfully say. It is still not proof of
     * arrival — SMTP acceptance is not delivery, and a bounce arrives later or
     * never — so the copy that renders this must not promise an inbox.
     */
    case Sent = 'sent';

    /**
     * Not sent in this request; it is on the queue and will go when the worker
     * runs. Either the message is scheduled by nature (a reminder), or an
     * immediate send failed and this is the fallback.
     */
    case Queued = 'queued';

    /**
     * Nothing was sent and nothing will be: there is no address to send to.
     * The clinic reaches this patient by telephone.
     */
    case Skipped = 'skipped';
}
