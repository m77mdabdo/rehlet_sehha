<?php

declare(strict_types=1);

namespace App\Services\Availability;

use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\Service;
use App\Models\WorkingHour;
use Carbon\CarbonImmutable;
use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Support\Collection;

/**
 * Which moments a patient may actually book.
 *
 * READ ONLY. Nothing here writes, locks, or reserves. Booking is Task 5's job
 * and happens inside a transaction that relies on the UNIQUE index over
 * appointments.slot_key to settle races. This class answers "what looks free
 * right now"; the database answers "who got it". Those are different questions
 * and conflating them is how double bookings happen — a check-then-insert in
 * application code has a window between the two steps no matter how short.
 *
 * ────────────────────────────────────────────────────────────────────────────
 * TIME HANDLING
 *
 * working_hours stores CAIRO WALL-CLOCK times with no date attached: "Saturday
 * 10:00–20:00" is a statement about clocks on a wall, not about instants. An
 * instant only exists once a wall-clock time is paired with a calendar date and
 * resolved through a timezone — and in Egypt that resolution is not always a
 * function, because the country observes DST.
 *
 * Every conversion in this file goes through DateTimeZone. There is not a
 * single fixed hour offset anywhere, and there must never be: +2 is right for
 * roughly seven months of the year and quietly wrong for the other five.
 *
 * Around the two transitions a local time can fail to be a single instant:
 *
 *   SPRING FORWARD (last Friday of April, 00:00 -> 01:00 local)
 *     Local times in the skipped hour NEVER HAPPEN. PHP resolves them by
 *     silently rolling forward, which would invent an appointment at a time no
 *     clock in Cairo displayed. Such slots are SKIPPED, not shifted.
 *
 *   FALL BACK (last Thursday of October, 24:00 -> 23:00 local)
 *     Local times in the repeated hour happen TWICE, an hour apart. PHP
 *     resolves them to the SECOND occurrence (standard time). We deliberately
 *     take the FIRST — the earlier instant, still on summer time.
 *
 *     Why first: the clinic's day runs forwards. If a patient is shown "23:30"
 *     and two instants answer to that name, the one they will turn up for is
 *     the first time their phone shows 23:30, not the repeat an hour later.
 *     Choosing the second would also place the appointment after a slot the
 *     engine had already offered at an apparently later wall-clock time, so
 *     the day's list would no longer be in chronological order.
 *
 * With the clinic's current 10:00–20:00 schedule NEITHER transition falls
 * inside working hours — both happen around midnight, and both spring-forward
 * dates land on a Friday, which is closed. So today this code path is
 * unreachable in production. It is written and tested anyway because working
 * hours are data: one late-evening clinic added through an admin panel makes
 * it live, and it would go wrong exactly twice a year, at night, in a way
 * nobody would reproduce on a Tuesday afternoon.
 */
class AvailabilityEngine
{
    /**
     * NOT CACHED, and that is the decision.
     *
     * Availability is invalidated by every booking, every cancellation, every
     * blocked slot, and by the passage of time itself — the lead-time floor
     * moves continuously, so an entry cached at 09:00 is already wrong at
     * 09:01 for the slots nearest the boundary. Any cache would need busting
     * on all four, and the one that cannot be bust is the clock.
     *
     * The asymmetry of the failure decides it. A slow calendar costs a
     * spinner. A stale calendar shows a slot that is already taken, the
     * patient picks it, fills in the form, and the insert fails on the unique
     * index — so they are told at the last step that the time they chose was
     * never available. That is the worst moment in the flow to fail, and it
     * arrives after they have handed over their phone number.
     *
     * The cost of not caching is small and bounded: three indexed queries
     * covering a thirty-day window, then arithmetic in memory. There is no N+1
     * here — no query runs inside a loop over dates or slots.
     *
     * If this ever needs to be faster, the answer is a materialised
     * availability table maintained by the booking transaction, not a cache
     * with a TTL.
     */
    public function __construct(
        private readonly DateTimeZone $clinicTimezone = new DateTimeZone('Africa/Cairo'),
    ) {}

    /**
     * Every bookable slot between two instants.
     *
     * $from and $to are instants; they are converted to Cairo to decide which
     * calendar days to walk. The range is additionally clamped to the lead
     * time and the horizon, so a caller asking for the next five years gets
     * the next thirty days rather than a timeout.
     *
     * When $staffId is null, slots are generated for every practitioner who
     * has active working hours, each tagged with whose slot it is. Callers
     * wanting one entry per wall-clock time should group by cairoTime(); the
     * engine will not collapse two practitioners into one row, because which
     * practitioner is free is information the booking flow needs.
     *
     * @return Collection<int, Slot> ordered by instant, then staff
     */
    public function availableSlots(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?int $staffId,
        Service $service,
    ): Collection {
        $now = CarbonImmutable::now('UTC');

        $earliest = $now->addHours($this->leadTimeHours());
        $latest = $now->addDays($this->horizonDays());

        // Clamp before doing any work: rule 7 and rule 8 are cheaper as bounds
        // on the loop than as a filter over slots we bothered to build.
        $rangeStart = $from->utc()->max($earliest);
        $rangeEnd = $to->utc()->min($latest);

        if ($rangeStart->greaterThanOrEqualTo($rangeEnd)) {
            return collect();
        }

        $schedule = $this->workingHours($staffId);

        if ($schedule === []) {
            return collect();
        }

        /*
         * Three queries, all outside the loop. Widened by the buffer on both
         * sides so an appointment or block that starts just before the window
         * and reaches into it is still seen.
         */
        $margin = $this->bufferMinutes() + $service->duration_minutes;

        $appointments = $this->occupyingAppointments(
            $rangeStart->subMinutes($margin),
            $rangeEnd->addMinutes($margin),
            $staffId,
        );

        $blocks = $this->blockedSlots(
            $rangeStart->subMinutes($margin),
            $rangeEnd->addMinutes($margin),
            $staffId,
        );

        $slots = [];

        foreach ($this->cairoDatesCovering($rangeStart, $rangeEnd) as $date) {
            // Carbon's dayOfWeek matches working_hours.day_of_week: 0 = Sunday.
            $dayOfWeek = (int) $date->dayOfWeek;

            foreach ($schedule[$dayOfWeek] ?? [] as $window) {
                foreach ($this->candidatesFor($date, $window, $service) as $slot) {
                    if ($slot->startsAtUtc->lessThan($rangeStart) || $slot->startsAtUtc->greaterThan($rangeEnd)) {
                        continue;
                    }

                    if ($this->collidesWithBlock($slot, $blocks)) {
                        continue;
                    }

                    if ($this->collidesWithAppointment($slot, $appointments)) {
                        continue;
                    }

                    $slots[] = $slot;
                }
            }
        }

        usort(
            $slots,
            fn (Slot $a, Slot $b): int => [$a->startsAtUtc->getTimestamp(), $a->staffId ?? 0]
                <=> [$b->startsAtUtc->getTimestamp(), $b->staffId ?? 0],
        );

        return collect($slots);
    }

    /**
     * Re-check one instant against all eight rules.
     *
     * The booking form renders a calendar and then, minutes later, receives a
     * submission. In between, somebody else may have taken the slot, the
     * clinic may have blocked the afternoon, or the lead-time floor may simply
     * have moved past it. Validating only at render time means trusting a
     * decision made against a world that no longer exists.
     *
     * This is still not a reservation. It closes the window from minutes to
     * milliseconds; the unique index closes it entirely.
     */
    public function isSlotBookable(
        CarbonImmutable $startsAtUtc,
        ?int $staffId,
        Service $service,
    ): bool {
        $instant = $startsAtUtc->utc();

        /*
         * Ask for a one-second window around the instant rather than
         * reimplementing the rules. Two implementations of "is this bookable"
         * would drift, and the drift would show up as a calendar offering
         * something the submit handler rejects — the exact failure this method
         * exists to prevent.
         */
        return $this->availableSlots(
            $instant,
            $instant->addSecond(),
            $staffId,
            $service,
        )->contains(
            fn (Slot $slot): bool => $slot->startsAtUtc->equalTo($instant)
                && $slot->staffId === $staffId,
        );
    }

    /**
     * Candidate slots for one Cairo date within one working-hours window.
     *
     * @return list<Slot>
     */
    private function candidatesFor(CarbonImmutable $date, WorkingHour $window, Service $service): array
    {
        $step = max(1, $window->slot_minutes);

        /*
         * Rule 3: the service AND its buffer must fit before closing time.
         *
         * Worked through, because it is easy to be off by one buffer here: a
         * 45-minute service with a 15-minute buffer inside a window ending at
         * 20:00 may start at 19:00 (ends 19:45, buffer to 20:00) but not at
         * 19:15 (buffer would run to 20:15). The last legal start is therefore
         * end_time minus duration minus buffer, and the comparison is
         * inclusive.
         */
        $required = $service->duration_minutes + $this->bufferMinutes();

        $openMinutes = $this->minutesOfDay($window->start_time);
        $closeMinutes = $this->minutesOfDay($window->end_time);

        $lastStart = $closeMinutes - $required;

        if ($lastStart < $openMinutes) {
            // The service cannot fit in this window at all. Not an error —
            // a 45-minute service simply has no home in a 30-minute clinic.
            return [];
        }

        $slots = [];
        $dateString = $date->format('Y-m-d');

        for ($minutes = $openMinutes; $minutes <= $lastStart; $minutes += $step) {
            $local = sprintf('%s %02d:%02d:00', $dateString, intdiv($minutes, 60), $minutes % 60);

            $instant = $this->resolveCairoWallClock($local);

            // Rule 4: the local time does not exist on this date. Skipped, not
            // shifted — see the class docblock.
            if ($instant === null) {
                continue;
            }

            $startsAtUtc = CarbonImmutable::instance($instant)->utc();

            $slots[] = new Slot(
                startsAtUtc: $startsAtUtc,
                startsAtCairo: $startsAtUtc->setTimezone($this->clinicTimezone),
                endsAtUtc: $startsAtUtc->addMinutes($service->duration_minutes),
                staffId: $window->staff_id,
                durationMinutes: $service->duration_minutes,
            );
        }

        return $slots;
    }

    /**
     * Resolve a Cairo wall-clock string to a single instant.
     *
     * Returns null when the local time does not exist (spring forward), and
     * the FIRST of the two instants when it happens twice (fall back).
     *
     * Both cases are detected by asking PHP what it did, rather than by
     * hard-coding transition dates: the tz database is updated when a
     * government changes its mind, and Egypt has changed its mind about DST
     * several times in the last fifteen years.
     */
    private function resolveCairoWallClock(string $local): ?DateTimeImmutable
    {
        $resolved = DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $local, $this->clinicTimezone);

        if ($resolved === false) {
            return null;
        }

        /*
         * NONEXISTENT: PHP rolls a skipped local time forward across the gap,
         * so the parsed value no longer formats back to what was asked for.
         * That mismatch is the detection.
         */
        if ($resolved->format('Y-m-d H:i:s') !== $local) {
            return null;
        }

        /*
         * AMBIGUOUS: PHP resolves a repeated local time to the SECOND
         * occurrence. If the instant exactly 3600 seconds earlier renders as
         * the SAME local time, we are inside the repeated hour, and that
         * earlier instant is the first occurrence — the one we want.
         *
         * On any ordinary day the instant an hour earlier renders an hour
         * earlier, so this cannot false-positive.
         *
         * sub(PT1H), NOT modify('-1 hour'). The two are different operations
         * and the difference only shows up here:
         *
         *   modify('-1 hour')  is WALL-CLOCK arithmetic. From 23:30 +02:00 it
         *                      produces 22:30 +03:00 — the displayed hour goes
         *                      down by one, but the instant moves by two.
         *   sub(PT1H)          is ABSOLUTE arithmetic. From 23:30 +02:00 it
         *                      produces 23:30 +03:00 — exactly 3600 seconds
         *                      earlier, which on this night is the same
         *                      wall-clock time again.
         *
         * Only the absolute one can detect a repeated hour, because the whole
         * property being tested is "two instants, one local time". The
         * wall-clock version silently never matches and the engine would
         * inherit PHP's second-occurrence default while looking like it had
         * overridden it.
         */
        $anHourEarlier = $resolved->sub(new DateInterval('PT1H'));

        if ($anHourEarlier->format('Y-m-d H:i:s') === $local) {
            return $anHourEarlier;
        }

        return $resolved;
    }

    /**
     * Rule 6, with the buffer applied on both sides.
     *
     * An appointment from 10:00 to 10:45 with a 15-minute buffer occupies
     * 09:45 to 11:00 as far as a new booking is concerned: the buffer after it
     * protects the practitioner's notes, and the buffer before it protects the
     * same thing for the appointment being placed.
     *
     * @param  Collection<int, Appointment>  $appointments
     */
    private function collidesWithAppointment(Slot $slot, Collection $appointments): bool
    {
        $buffer = $this->bufferMinutes();

        $slotStart = $slot->startsAtUtc->subMinutes($buffer);
        $slotEnd = $slot->endsAtUtc->addMinutes($buffer);

        foreach ($appointments as $appointment) {
            if (! $this->appliesToStaff($appointment->staff_id, $slot->staffId)) {
                continue;
            }

            $existingStart = CarbonImmutable::instance($appointment->starts_at)->utc();
            $existingEnd = CarbonImmutable::instance($appointment->ends_at)->utc();

            if ($this->overlaps($slotStart, $slotEnd, $existingStart, $existingEnd)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Rule 5. No buffer here: a block is an explicit statement about a period
     * of time, and padding it would silently remove slots the clinic believed
     * it had left open.
     *
     * @param  Collection<int, BlockedSlot>  $blocks
     */
    private function collidesWithBlock(Slot $slot, Collection $blocks): bool
    {
        foreach ($blocks as $block) {
            if (! $this->appliesToStaff($block->staff_id, $slot->staffId)) {
                continue;
            }

            $blockStart = CarbonImmutable::instance($block->starts_at)->utc();
            $blockEnd = CarbonImmutable::instance($block->ends_at)->utc();

            if ($this->overlaps($slot->startsAtUtc, $slot->endsAtUtc, $blockStart, $blockEnd)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Half-open interval overlap: [aStart, aEnd) against [bStart, bEnd).
     *
     * Half-open on purpose. An appointment ending at exactly 11:00 and one
     * starting at exactly 11:00 do not overlap — they are back to back, which
     * is what a slot grid is for. Using closed intervals would make every
     * adjacent pair collide and halve the calendar.
     */
    private function overlaps(
        CarbonImmutable $aStart,
        CarbonImmutable $aEnd,
        CarbonImmutable $bStart,
        CarbonImmutable $bEnd,
    ): bool {
        return $aStart->lessThan($bEnd) && $bStart->lessThan($aEnd);
    }

    /**
     * Whether a row belonging to $rowStaffId constrains a slot for $slotStaffId.
     *
     * A null on the ROW means clinic-wide. That is now only reachable for
     * BLOCKED SLOTS — a block with no staff is the clinic being shut, which is
     * a real and useful thing to record. Appointments cannot be clinic-wide:
     * staff_id is NOT NULL, so each one constrains exactly its own
     * practitioner.
     */
    private function appliesToStaff(?int $rowStaffId, ?int $slotStaffId): bool
    {
        return $rowStaffId === null || $rowStaffId === $slotStaffId;
    }

    /**
     * Active working hours, grouped by day of week.
     *
     * A plain array rather than a grouped Collection: groupBy() on an Eloquent
     * collection produces a nested collection keyed int|string, which does not
     * describe what this actually is — the keys are always day numbers.
     *
     * @return array<int, list<WorkingHour>>
     */
    private function workingHours(?int $staffId): array
    {
        $windows = WorkingHour::query()
            ->where('is_active', true)
            ->when($staffId !== null, fn ($query) => $query->where('staff_id', $staffId))
            ->orderBy('start_time')
            ->get();

        $byDay = [];

        foreach ($windows as $window) {
            $byDay[$window->day_of_week][] = $window;
        }

        return $byDay;
    }

    /**
     * Appointments that still hold their slot.
     *
     * NOT scopeActive(). That scope excludes both cancelled and no-show, but
     * only cancellation releases a slot — AppointmentStatus::releasesSlot()
     * and Appointment::syncSlotKey() agree on that, and the unique index is
     * built on it. A no-show is a record of something that occupied that hour;
     * offering the hour again would produce a calendar that contradicts the
     * database, and an insert that fails on the index at the last step.
     *
     * @return Collection<int, Appointment>
     */
    private function occupyingAppointments(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?int $staffId,
    ): Collection {
        return Appointment::query()
            ->holdingSlot()
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            // No orWhereNull: appointments.staff_id is NOT NULL, so every
            // appointment belongs to exactly one practitioner and can only
            // ever constrain that one.
            ->when($staffId !== null, fn ($query) => $query->where('staff_id', $staffId))
            ->get(['id', 'staff_id', 'starts_at', 'ends_at', 'status']);
    }

    /**
     * @return Collection<int, BlockedSlot>
     */
    private function blockedSlots(
        CarbonImmutable $from,
        CarbonImmutable $to,
        ?int $staffId,
    ): Collection {
        return BlockedSlot::query()
            ->where('starts_at', '<', $to)
            ->where('ends_at', '>', $from)
            ->when(
                $staffId !== null,
                fn ($query) => $query->where(function ($inner) use ($staffId): void {
                    $inner->where('staff_id', $staffId)->orWhereNull('staff_id');
                }),
            )
            ->get(['id', 'staff_id', 'starts_at', 'ends_at']);
    }

    /**
     * Every Cairo calendar date touched by a UTC range.
     *
     * Walked in Cairo rather than UTC because a clinic day is a local thing: a
     * range ending at 21:00 UTC still includes the Cairo evening of the
     * following date in summer.
     *
     * @return list<CarbonImmutable>
     */
    private function cairoDatesCovering(CarbonImmutable $from, CarbonImmutable $to): array
    {
        $cursor = $from->setTimezone($this->clinicTimezone)->startOfDay();
        $end = $to->setTimezone($this->clinicTimezone)->startOfDay();

        $dates = [];

        while ($cursor->lessThanOrEqualTo($end)) {
            $dates[] = $cursor;

            /*
             * addDay() on a zoned CarbonImmutable adds a calendar day and
             * re-resolves the offset, so this stays on midnight local across a
             * transition. Adding 86400 seconds would drift by an hour and
             * eventually skip or repeat a date.
             */
            $cursor = $cursor->addDay()->startOfDay();
        }

        return $dates;
    }

    /**
     * "10:00:00" -> 600. working_hours times are bare wall-clock strings with
     * no date and deliberately no cast; parsing them as datetimes would bolt a
     * meaningless date on and invite an accidental UTC conversion.
     */
    private function minutesOfDay(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return $hours * 60 + $minutes;
    }

    private function leadTimeHours(): int
    {
        return (int) config('clinic.booking.lead_time_hours', 2);
    }

    private function horizonDays(): int
    {
        return (int) config('clinic.booking.horizon_days', 30);
    }

    private function bufferMinutes(): int
    {
        return (int) config('clinic.booking.buffer_minutes', 15);
    }
}
