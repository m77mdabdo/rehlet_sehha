<?php

declare(strict_types=1);

namespace App\Services\Availability;

use Carbon\CarbonImmutable;
use JsonSerializable;

/**
 * One bookable moment, carrying BOTH representations of itself.
 *
 * The UTC instant is what gets stored and compared; the Cairo wall-clock time
 * is what a patient reads. Every caller needs both, and every caller deriving
 * the second from the first is a caller that can get the conversion wrong —
 * around a DST boundary, or by reaching for a fixed +2 offset, or by
 * converting in Blade where nobody would think to look for a bug.
 *
 * So the engine converts once, here, and hands over the answer. Nothing
 * downstream should ever call setTimezone() on these values again.
 *
 * Immutable by construction: a slot is a fact about a moment, and a caller
 * that could mutate one could quietly desynchronise the two representations.
 */
final readonly class Slot implements JsonSerializable
{
    public function __construct(
        /** The instant, in UTC. What goes in appointments.starts_at. */
        public CarbonImmutable $startsAtUtc,

        /** The same instant, in Africa/Cairo. What the patient is shown. */
        public CarbonImmutable $startsAtCairo,

        /** End of the appointment itself, UTC. Excludes the buffer. */
        public CarbonImmutable $endsAtUtc,

        /** The practitioner this slot belongs to. */
        public ?int $staffId,

        /** Minutes of the service, without the buffer. */
        public int $durationMinutes,
    ) {}

    /**
     * A stable identifier for the slot, safe to put in a form field or a URL.
     *
     * Built from the UTC instant rather than the local time, because two
     * different instants can share a local time on the night the clocks go
     * back — and a form that round-trips the local string would book the wrong
     * one of them.
     */
    public function key(): string
    {
        return sprintf('%d-%s', $this->staffId ?? 0, $this->startsAtUtc->format('Y-m-d\TH:i:s\Z'));
    }

    /**
     * The Cairo date this slot belongs to, for grouping a calendar by day.
     *
     * Deliberately the CAIRO date, not the UTC one: a 22:00 Cairo slot is on
     * the previous day in UTC, and grouping by UTC would scatter one clinic
     * day across two columns.
     */
    public function cairoDate(): string
    {
        return $this->startsAtCairo->format('Y-m-d');
    }

    /**
     * HH:MM as the patient reads it.
     */
    public function cairoTime(): string
    {
        return $this->startsAtCairo->format('H:i');
    }

    /**
     * Whether this slot falls inside a DST offset that differs from the
     * clinic's standard time. Not used for logic — it exists so a calendar can
     * be inspected during the two weeks a year when a support call about
     * "the times look wrong" is plausible.
     */
    public function utcOffsetHours(): float
    {
        return $this->startsAtCairo->getOffset() / 3600;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'key' => $this->key(),
            'starts_at_utc' => $this->startsAtUtc->toIso8601ZuluString(),
            'ends_at_utc' => $this->endsAtUtc->toIso8601ZuluString(),
            'date' => $this->cairoDate(),
            'time' => $this->cairoTime(),
            'staff_id' => $this->staffId,
            'duration_minutes' => $this->durationMinutes,
        ];
    }
}
