<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\BlockedSlot;
use App\Models\Service;
use App\Models\User;
use App\Models\WorkingHour;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * The availability engine.
 *
 * Everything after this task depends on these rules being right, and almost
 * none of it is verifiable by looking at a screen: a calendar that is wrong by
 * one buffer, or by one hour on two nights a year, looks exactly like a
 * calendar that is right.
 */
const CAIRO = 'Africa/Cairo';

function engine(): AvailabilityEngine
{
    return new AvailabilityEngine;
}

/**
 * A practitioner with a schedule built for the test rather than for the clinic.
 *
 * @param  list<array{day: int, start: string, end: string, slot?: int}>  $windows
 */
function practitionerWith(array $windows): User
{
    $staff = User::factory()->create();

    foreach ($windows as $window) {
        WorkingHour::query()->create([
            'staff_id' => $staff->id,
            'day_of_week' => $window['day'],
            'start_time' => $window['start'],
            'end_time' => $window['end'],
            'slot_minutes' => $window['slot'] ?? 60,
            'is_active' => true,
        ]);
    }

    return $staff;
}

/**
 * A service of a given length.
 *
 * is_active is false so Service's saving guard — which refuses an ACTIVE
 * service longer than the shortest slot — stays out of the way. These tests
 * deliberately use durations that do not divide neatly into the grid, which is
 * exactly the case that guard exists to prevent in production.
 */
function serviceOf(int $minutes): Service
{
    return Service::factory()->create([
        'duration_minutes' => $minutes,
        'is_active' => false,
    ]);
}

/**
 * Cairo wall-clock times of the returned slots, for readable assertions.
 *
 * @param  Collection<int, Slot>  $slots
 * @return list<string>
 */
function cairoTimes($slots): array
{
    return $slots->map(fn (Slot $slot): string => $slot->cairoTime())->values()->all();
}

beforeEach(function () {
    // Buffer and lead time are set per-test where they matter; these are the
    // clinic's real values, so a test that does not care inherits reality.
    config()->set('clinic.booking.lead_time_hours', 2);
    config()->set('clinic.booking.horizon_days', 30);
    config()->set('clinic.booking.buffer_minutes', 15);
});

afterEach(function () {
    Carbon::setTestNow();
    CarbonImmutable::setTestNow();
});

/*
|------------------------------------------------------------------------------
| DST
|------------------------------------------------------------------------------
|
| Transition dates come from the IANA tz database shipped with PHP (tzdata
| 2025.2), read directly rather than assumed:
|
|   2026-04-24 (Fri)  00:00 local -> 01:00   spring forward, 00:00–00:59 gone
|   2026-10-29 (Thu)  24:00 local -> 23:00   fall back,      23:00–23:59 twice
|   2027-04-30 (Fri)  00:00 local -> 01:00
|   2027-10-28 (Thu)  24:00 local -> 23:00
|
| Egypt's rule since DST returned in 2023 is: start last Friday of April, end
| last Thursday of October. It matches neither the EU nor the US, which is
| precisely why it is read from tzdata and not derived.
|
| NOTE: the clinic's real hours are 10:00–20:00, so NEITHER transition falls
| inside them, and both spring-forward dates are Fridays, which are closed.
| These tests therefore invent late-night schedules — otherwise they would pass
| while exercising nothing.
|
*/

it('skips a local time that does not exist on the spring-forward night', function (string $date, int $dayOfWeek) {
    CarbonImmutable::setTestNow(CarbonImmutable::parse($date.' 00:00:00', CAIRO)->subDays(3));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    config()->set('clinic.booking.buffer_minutes', 0);

    $staff = practitionerWith([['day' => $dayOfWeek, 'start' => '00:00', 'end' => '03:00', 'slot' => 30]]);
    $service = serviceOf(15);

    $slots = engine()->availableSlots(
        CarbonImmutable::parse($date.' 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse($date.' 23:59:59', CAIRO)->utc(),
        $staff->id,
        $service,
    );

    // 00:00 and 00:30 never happen: no clock in Cairo showed them. They are
    // dropped, NOT rolled forward into 01:00 and 01:30 — which would have
    // created two appointments at instants nobody asked for, and a duplicate
    // of the real 01:00 slot.
    // The grid steps 30 minutes from 00:00, so 02:30 is the last candidate
    // that starts on it — 02:45 would fit in the window but is not on the grid.
    expect(cairoTimes($slots))->toBe(['01:00', '01:30', '02:00', '02:30']);

    foreach ($slots as $slot) {
        expect($slot->startsAtCairo->format('Y-m-d H:i'))
            ->toBe($slot->startsAtUtc->setTimezone(CAIRO)->format('Y-m-d H:i'));
    }
})->with([
    'spring 2026' => ['2026-04-24', 5],
    'spring 2027' => ['2027-04-30', 5],
]);

it('takes the first occurrence of a local time that happens twice', function (string $date, int $dayOfWeek) {
    CarbonImmutable::setTestNow(CarbonImmutable::parse($date.' 00:00:00', CAIRO)->subDays(3));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    config()->set('clinic.booking.buffer_minutes', 0);

    $staff = practitionerWith([['day' => $dayOfWeek, 'start' => '22:00', 'end' => '23:59', 'slot' => 30]]);
    $service = serviceOf(15);

    $slots = engine()->availableSlots(
        CarbonImmutable::parse($date.' 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse($date.' 23:59:59', CAIRO)->utc(),
        $staff->id,
        $service,
    );

    expect(cairoTimes($slots))->toBe(['22:00', '22:30', '23:00', '23:30']);

    $byTime = $slots->keyBy(fn (Slot $slot): string => $slot->cairoTime());

    // 22:00 and 22:30 are still on summer time, +03:00.
    expect($byTime['22:00']->startsAtUtc->format('H:i'))->toBe('19:00');
    expect($byTime['22:30']->startsAtUtc->format('H:i'))->toBe('19:30');

    /*
     * 23:00 and 23:30 each name two instants an hour apart. We take the FIRST
     * — still +03:00, so 20:00 and 20:30 UTC. PHP's own default is the SECOND
     * (21:00 / 21:30 UTC, on +02:00), so this assertion is the one that proves
     * the engine overrode it rather than inheriting it.
     */
    expect($byTime['23:00']->startsAtUtc->format('H:i'))->toBe('20:00');
    expect($byTime['23:30']->startsAtUtc->format('H:i'))->toBe('20:30');

    expect($byTime['23:00']->utcOffsetHours())->toBe(3.0);

    // And the day is still in chronological order, which choosing the second
    // occurrence would have broken.
    $timestamps = $slots->map(fn (Slot $s): int => $s->startsAtUtc->getTimestamp())->all();
    expect($timestamps)->toBe(array_values(collect($timestamps)->sort()->all()));
})->with([
    'autumn 2026' => ['2026-10-29', 4],
    'autumn 2027' => ['2027-10-28', 4],
]);

it('never derives the offset by adding a fixed number of hours', function () {
    // A slot in winter and a slot in summer must carry different offsets. If
    // anything in the engine hard-coded +2 or +3 this fails.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-01-10 08:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([['day' => 0, 'start' => '10:00', 'end' => '20:00']]);
    $service = serviceOf(45);

    $winter = engine()->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(20)->utc(),
        $staff->id,
        $service,
    )->first();

    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-10 08:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $summer = engine()->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(20)->utc(),
        $staff->id,
        $service,
    )->first();

    expect($winter->cairoTime())->toBe('10:00');
    expect($summer->cairoTime())->toBe('10:00');

    expect($winter->utcOffsetHours())->toBe(2.0);
    expect($summer->utcOffsetHours())->toBe(3.0);

    // Same wall-clock time, different instants — an hour apart.
    expect($winter->startsAtUtc->format('H:i'))->toBe('08:00');
    expect($summer->startsAtUtc->format('H:i'))->toBe('07:00');
});

/*
|------------------------------------------------------------------------------
| Window fitting, buffer, and the grid
|------------------------------------------------------------------------------
*/

it('excludes a slot whose service plus buffer would run past closing time', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    // Monday 10:00–20:00, hourly grid, 45-minute service, 15-minute buffer.
    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '20:00', 'slot' => 60]]);

    $slots = engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        serviceOf(45),
    );

    /*
     * 19:00 fits: 19:00 + 45 = 19:45, + 15 buffer = 20:00 exactly.
     * 20:00 does not: it would end at 20:45 with the buffer running to 21:00.
     * The last legal start is close minus duration minus buffer, inclusive.
     */
    expect(cairoTimes($slots))->toBe([
        '10:00', '11:00', '12:00', '13:00', '14:00',
        '15:00', '16:00', '17:00', '18:00', '19:00',
    ]);
});

it('offers nothing when the service cannot fit in the window at all', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    // A one-hour clinic cannot host a 90-minute consultation, buffer or not.
    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '11:00', 'slot' => 30]]);

    expect(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        serviceOf(90),
    ))->toBeEmpty();
});

it('lets the buffer alone remove the slot after an appointment', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    // 15-minute grid, so a slot can start exactly when the appointment ends.
    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '13:00', 'slot' => 15]]);
    $service = serviceOf(45);

    $start = CarbonImmutable::parse('2026-06-08 10:00:00', CAIRO)->utc();

    Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_id' => $service->id,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(45),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $times = cairoTimes(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        $service,
    ));

    /*
     * The appointment runs 10:00–10:45. 10:45 would not overlap it at all on
     * its own — they are back to back — but the 15-minute buffer on each side
     * does, so 10:45 goes. 11:00 is the first slot clear of it, which is the
     * "blocks until 11:00" rule stated exactly.
     */
    expect($times)->not->toContain('10:45');
    expect($times)->toContain('11:00');

    // Everything from 09:45 through 10:45 is gone; nothing before is affected.
    expect($times)->not->toContain('10:00', '10:15', '10:30');
});

it('treats adjacent appointments as not overlapping', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    config()->set('clinic.booking.buffer_minutes', 0);

    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '13:00', 'slot' => 60]]);
    $service = serviceOf(60);

    $start = CarbonImmutable::parse('2026-06-08 10:00:00', CAIRO)->utc();

    Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_id' => $service->id,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(60),
        'status' => AppointmentStatus::Confirmed,
    ]);

    // With no buffer, 11:00 begins exactly as the 10:00 appointment ends.
    // Half-open intervals: touching is not overlapping, or every adjacent pair
    // in a grid would collide and the calendar would halve.
    expect(cairoTimes(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        $service,
    )))->toBe(['11:00', '12:00']);
});

it('returns nothing on a day the clinic does not open', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    // The seeded clinic works Saturday–Thursday. Friday simply has no row —
    // absence is how "closed" is expressed, not is_active = false.
    $staff = practitionerWith([
        ['day' => 4, 'start' => '10:00', 'end' => '20:00'],
        ['day' => 6, 'start' => '10:00', 'end' => '20:00'],
    ]);

    $friday = CarbonImmutable::parse('2026-06-12 00:00:00', CAIRO);
    expect($friday->dayOfWeek)->toBe(5);

    expect(engine()->availableSlots(
        $friday->utc(),
        $friday->endOfDay()->utc(),
        $staff->id,
        serviceOf(45),
    ))->toBeEmpty();
});

/*
|------------------------------------------------------------------------------
| Blocks and appointments
|------------------------------------------------------------------------------
*/

it('removes a slot that a blocked period only partially overlaps', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '14:00', 'slot' => 60]]);

    // Ten minutes clipping the tail of the 11:00 slot. A block that overlaps
    // at all makes the slot unbookable — there is no such thing as most of an
    // appointment.
    BlockedSlot::query()->create([
        'staff_id' => $staff->id,
        'starts_at' => CarbonImmutable::parse('2026-06-08 11:35:00', CAIRO)->utc(),
        'ends_at' => CarbonImmutable::parse('2026-06-08 11:45:00', CAIRO)->utc(),
        'reason' => 'call',
    ]);

    $times = cairoTimes(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        serviceOf(45),
    ));

    expect($times)->not->toContain('11:00');
    expect($times)->toContain('10:00', '12:00', '13:00');
});

it('applies a clinic-wide block to every practitioner', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '14:00', 'slot' => 60]]);

    // staff_id null means the clinic is shut, not that nobody in particular
    // is busy.
    BlockedSlot::query()->create([
        'staff_id' => null,
        'starts_at' => CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        'ends_at' => CarbonImmutable::parse('2026-06-09 00:00:00', CAIRO)->utc(),
        'reason' => 'public holiday',
    ]);

    expect(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        serviceOf(45),
    ))->toBeEmpty();
});

it('frees the slot when an appointment is cancelled but not when it is a no-show', function (string $status, bool $shouldBeFree) {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '14:00', 'slot' => 60]]);
    $service = serviceOf(45);

    $start = CarbonImmutable::parse('2026-06-08 11:00:00', CAIRO)->utc();

    Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_id' => $service->id,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(45),
        'status' => AppointmentStatus::from($status),
    ]);

    $times = cairoTimes(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        $service,
    ));

    /*
     * A no-show does NOT release its hour. The clinic spent that time; the
     * unique index on slot_key still holds it, and if the calendar disagreed
     * the booking form would offer a slot the insert then refuses. This is why
     * the engine filters on holdingSlot() rather than active() — the two
     * scopes differ on exactly this case.
     */
    expect(in_array('11:00', $times, true))->toBe($shouldBeFree);
})->with([
    'pending holds it' => ['pending', false],
    'confirmed holds it' => ['confirmed', false],
    'completed holds it' => ['completed', false],
    'no-show holds it' => ['no_show', false],
    'cancelled releases it' => ['cancelled', true],
]);

it('does not let one practitioner block another', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $mine = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '14:00', 'slot' => 60]]);
    $theirs = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '14:00', 'slot' => 60]]);

    $service = serviceOf(45);
    $start = CarbonImmutable::parse('2026-06-08 11:00:00', CAIRO)->utc();

    Appointment::factory()->create([
        'staff_id' => $theirs->id,
        'service_id' => $service->id,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(45),
        'status' => AppointmentStatus::Confirmed,
    ]);

    expect(cairoTimes(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $mine->id,
        $service,
    )))->toContain('11:00');
});

it('treats an unassigned appointment as consuming the practitioner hour', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '14:00', 'slot' => 60]]);
    $service = serviceOf(45);

    $start = CarbonImmutable::parse('2026-06-08 11:00:00', CAIRO)->utc();

    // staff_id null. syncSlotKey collapses these to "0-<time>", and with one
    // practitioner an unassigned booking still uses up her hour.
    Appointment::factory()->create([
        'staff_id' => null,
        'service_id' => $service->id,
        'starts_at' => $start,
        'ends_at' => $start->addMinutes(45),
        'status' => AppointmentStatus::Confirmed,
    ]);

    expect(cairoTimes(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        $service,
    )))->not->toContain('11:00');
});

/*
|------------------------------------------------------------------------------
| Lead time and horizon, on the exact boundary
|------------------------------------------------------------------------------
*/

it('includes a slot exactly on the lead-time boundary and excludes the one before', function () {
    // 08:00 Cairo, two-hour lead time: 10:00 is the first bookable slot.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 08:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    config()->set('clinic.booking.lead_time_hours', 2);

    $staff = practitionerWith([['day' => 1, 'start' => '09:00', 'end' => '14:00', 'slot' => 60]]);

    $times = cairoTimes(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        serviceOf(45),
    ));

    expect($times)->not->toContain('09:00');
    expect($times[0])->toBe('10:00');

    // One minute later and the 10:00 slot is gone too: the floor is now + lead
    // time exactly, not the start of the hour containing it.
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 08:00:01', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    expect(cairoTimes(engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        $staff->id,
        serviceOf(45),
    ))[0])->toBe('11:00');
});

it('includes a slot exactly on the horizon and excludes the one past it', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 10:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    config()->set('clinic.booking.horizon_days', 7);

    // Every day open, so the horizon is the only thing bounding the answer.
    $staff = practitionerWith(array_map(
        fn (int $day): array => ['day' => $day, 'start' => '10:00', 'end' => '12:00', 'slot' => 60],
        range(0, 6),
    ));

    $slots = engine()->availableSlots(
        CarbonImmutable::now()->utc(),
        CarbonImmutable::now()->addDays(60)->utc(),
        $staff->id,
        serviceOf(45),
    );

    $last = $slots->last();
    $horizon = CarbonImmutable::now()->addDays(7);

    expect($last->startsAtUtc->lessThanOrEqualTo($horizon))->toBeTrue();

    // The horizon lands at 10:00 on day seven, so that slot is the last one in
    // and the 11:00 after it is out.
    expect($last->startsAtCairo->format('Y-m-d H:i'))->toBe(
        $horizon->setTimezone(CAIRO)->format('Y-m-d H:i')
    );
});

it('returns nothing when the requested range falls entirely outside the bounds', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 10:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '20:00']]);
    $service = serviceOf(45);

    // Entirely in the past.
    expect(engine()->availableSlots(
        CarbonImmutable::now()->subDays(10)->utc(),
        CarbonImmutable::now()->subDay()->utc(),
        $staff->id,
        $service,
    ))->toBeEmpty();

    // Entirely beyond the horizon.
    expect(engine()->availableSlots(
        CarbonImmutable::now()->addDays(90)->utc(),
        CarbonImmutable::now()->addDays(120)->utc(),
        $staff->id,
        $service,
    ))->toBeEmpty();
});

/*
|------------------------------------------------------------------------------
| isSlotBookable must agree with availableSlots
|------------------------------------------------------------------------------
*/

it('agrees with availableSlots for every slot it returned', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-07 08:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([
        ['day' => 0, 'start' => '10:00', 'end' => '20:00', 'slot' => 60],
        ['day' => 1, 'start' => '10:00', 'end' => '20:00', 'slot' => 60],
        ['day' => 2, 'start' => '12:00', 'end' => '16:00', 'slot' => 30],
        ['day' => 3, 'start' => '10:00', 'end' => '20:00', 'slot' => 60],
    ]);

    $service = serviceOf(45);

    // Some noise, so the two code paths have something to disagree about.
    $taken = CarbonImmutable::parse('2026-06-08 13:00:00', CAIRO)->utc();
    Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_id' => $service->id,
        'starts_at' => $taken,
        'ends_at' => $taken->addMinutes(45),
        'status' => AppointmentStatus::Confirmed,
    ]);

    BlockedSlot::query()->create([
        'staff_id' => $staff->id,
        'starts_at' => CarbonImmutable::parse('2026-06-09 14:00:00', CAIRO)->utc(),
        'ends_at' => CarbonImmutable::parse('2026-06-09 15:30:00', CAIRO)->utc(),
        'reason' => 'admin',
    ]);

    $from = CarbonImmutable::now()->utc();
    $to = CarbonImmutable::now()->addDays(7)->utc();

    $slots = engine()->availableSlots($from, $to, $staff->id, $service);

    expect($slots)->not->toBeEmpty();

    foreach ($slots as $slot) {
        expect(engine()->isSlotBookable($slot->startsAtUtc, $staff->id, $service))->toBeTrue(
            'availableSlots offered '.$slot->startsAtCairo->format('Y-m-d H:i').' but isSlotBookable refused it.'
        );
    }
});

it('refuses every instant availableSlots did not offer', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-07 08:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([
        ['day' => 1, 'start' => '10:00', 'end' => '20:00', 'slot' => 60],
        ['day' => 2, 'start' => '12:00', 'end' => '16:00', 'slot' => 30],
    ]);

    $service = serviceOf(45);

    $taken = CarbonImmutable::parse('2026-06-08 13:00:00', CAIRO)->utc();
    Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_id' => $service->id,
        'starts_at' => $taken,
        'ends_at' => $taken->addMinutes(45),
        'status' => AppointmentStatus::Confirmed,
    ]);

    $from = CarbonImmutable::now()->utc();
    $to = CarbonImmutable::now()->addDays(4)->utc();

    $offered = engine()->availableSlots($from, $to, $staff->id, $service)
        ->map(fn (Slot $slot): string => $slot->startsAtUtc->toIso8601ZuluString())
        ->all();

    expect($offered)->not->toBeEmpty();

    /*
     * Walk the whole range on a fifteen-minute grid and assert the two methods
     * never disagree — in either direction. This is the property that matters:
     * the form validates on submit with isSlotBookable, and any instant it
     * accepts that the calendar would not have offered is a booking nobody
     * could have made through the interface.
     */
    $checked = 0;
    $cursor = $from->startOfHour();

    while ($cursor->lessThan($to)) {
        $expected = in_array($cursor->toIso8601ZuluString(), $offered, true);

        expect(engine()->isSlotBookable($cursor, $staff->id, $service))->toBe(
            $expected,
            'Disagreement at '.$cursor->setTimezone(CAIRO)->format('Y-m-d H:i').' Cairo.'
        );

        $checked++;
        $cursor = $cursor->addMinutes(15);
    }

    expect($checked)->toBeGreaterThan(300);
});

it('refuses a slot that was taken between rendering and submitting', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '14:00', 'slot' => 60]]);
    $service = serviceOf(45);

    $wanted = CarbonImmutable::parse('2026-06-08 11:00:00', CAIRO)->utc();

    expect(engine()->isSlotBookable($wanted, $staff->id, $service))->toBeTrue();

    // Somebody else books it while the form is open.
    Appointment::factory()->create([
        'staff_id' => $staff->id,
        'service_id' => $service->id,
        'starts_at' => $wanted,
        'ends_at' => $wanted->addMinutes(45),
        'status' => AppointmentStatus::Confirmed,
    ]);

    expect(engine()->isSlotBookable($wanted, $staff->id, $service))->toBeFalse();
});

it('refuses an instant that is not on the grid', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $staff = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '14:00', 'slot' => 60]]);
    $service = serviceOf(45);

    // 11:07 is not a slot the clinic offers, however free the hour looks.
    expect(engine()->isSlotBookable(
        CarbonImmutable::parse('2026-06-08 11:07:00', CAIRO)->utc(),
        $staff->id,
        $service,
    ))->toBeFalse();
});

/*
|------------------------------------------------------------------------------
| Multiple practitioners
|------------------------------------------------------------------------------
*/

it('covers every practitioner when no staff member is named', function () {
    CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-06-08 06:00:00', CAIRO));
    Carbon::setTestNow(CarbonImmutable::getTestNow());

    $first = practitionerWith([['day' => 1, 'start' => '10:00', 'end' => '12:00', 'slot' => 60]]);
    $second = practitionerWith([['day' => 1, 'start' => '15:00', 'end' => '17:00', 'slot' => 60]]);

    $slots = engine()->availableSlots(
        CarbonImmutable::parse('2026-06-08 00:00:00', CAIRO)->utc(),
        CarbonImmutable::parse('2026-06-08 23:59:59', CAIRO)->utc(),
        null,
        serviceOf(45),
    );

    // Each slot is tagged with whose it is, rather than the two schedules being
    // merged into an anonymous list the booking flow could not act on.
    expect($slots->pluck('staffId')->unique()->sort()->values()->all())
        ->toBe([$first->id, $second->id]);

    expect(cairoTimes($slots))->toBe(['10:00', '11:00', '15:00', '16:00']);
});

/*
|------------------------------------------------------------------------------
| Modes
|------------------------------------------------------------------------------
*/

it('rejects a disabled mode for new bookings while still rendering an existing one', function () {
    config()->set('clinic.booking.modes', ['online']);

    $online = AppointmentMode::Online;
    $clinic = AppointmentMode::Clinic;

    // Selectable today: online only.
    expect($online->isBookable())->toBeTrue();
    expect($clinic->isBookable())->toBeFalse();
    expect(array_keys(AppointmentMode::bookableOptions()))->toBe(['online']);

    /*
     * But the enum still knows about clinic mode, and a row carrying it must
     * still load and render. Deleting the case would have made this row throw
     * on cast and taken down every page that displays an appointment.
     */
    $existing = Appointment::factory()->create([
        'mode' => $clinic,
        'status' => AppointmentStatus::Completed,
    ]);

    $reloaded = Appointment::query()->findOrFail($existing->id);

    expect($reloaded->mode)->toBe($clinic);
    expect($reloaded->mode->label())->toBe('في العيادة');
    expect(array_keys(AppointmentMode::options()))->toBe(['online', 'clinic']);

    // And turning it back on is a config edit, not a migration.
    config()->set('clinic.booking.modes', ['online', 'clinic']);
    expect($clinic->isBookable())->toBeTrue();
});
