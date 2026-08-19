<?php

declare(strict_types=1);

namespace App\Services\Booking;

use RuntimeException;

/**
 * Somebody else booked this slot first.
 *
 * A distinct type rather than a generic failure, because the caller has to do
 * something very specific with it: refresh the calendar, say plainly what
 * happened, and KEEP EVERYTHING THE PATIENT TYPED. Losing a medical history
 * because another person clicked a second earlier is the worst outcome this
 * flow has, and it is entirely avoidable.
 *
 * Raised both by the pre-check and by the unique-index violation. The caller
 * cannot tell the difference and should not need to: from the patient's side
 * the two are the same event.
 */
class SlotTakenException extends RuntimeException {}
