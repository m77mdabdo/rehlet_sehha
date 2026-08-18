<?php

declare(strict_types=1);

use App\Models\Service;
use App\Models\WorkingHour;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;

/**
 * The booking system's double-booking guarantee rests on
 * appointments.slot_key, which is unique per (staff member, start instant).
 * That stops two appointments SHARING a start time. It does not stop them
 * OVERLAPPING.
 *
 * Overlap is impossible today only because every service is shorter than the
 * working-hours slot grid. Nothing in the schema says so. These tests say so.
 */
it('slot grid invariant', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);

    $shortestSlot = WorkingHour::query()->where('is_active', true)->min('slot_minutes');

    expect($shortestSlot)->not->toBeNull(
        'No active working hours exist, so no slot grid is defined and the '
        .'invariant cannot be checked. Seed WorkingHoursSeeder first.'
    );

    $services = Service::query()->where('is_active', true)->get();

    expect($services)->not->toBeEmpty('No active services to check.');

    foreach ($services as $service) {
        expect($service->duration_minutes)->toBeLessThanOrEqual(
            (int) $shortestSlot,
            sprintf(
                "SLOT GRID INVARIANT BROKEN.\n\n"
                ."Service \"%s\" lasts %d minutes, but the shortest active working-hours slot is %d minutes.\n\n"
                ."Why this matters: double-booking is prevented by a UNIQUE index on appointments.slot_key,\n"
                ."which is derived from (staff_id, starts_at). That guarantees no two active appointments\n"
                ."SHARE A START INSTANT. It does NOT guarantee they never OVERLAP.\n\n"
                ."With a service longer than one slot, an appointment at 10:00 running %d minutes and a\n"
                ."second appointment in the very next slot produce two DIFFERENT slot_keys, so both insert\n"
                ."successfully — and the practitioner is double-booked with no error anywhere.\n\n"
                ."Do not widen this test. Adding a longer service requires replacing slot_key with real\n"
                ."overlap detection first: a transaction plus SELECT ... FOR UPDATE range check over\n"
                ."[starts_at, ends_at), with an index on (staff_id, starts_at).\n"
                .'See docs/architecture/booking-concurrency.md.',
                $service->slug,
                $service->duration_minutes,
                (int) $shortestSlot,
                $service->duration_minutes,
            ),
        );
    }
});

it('refuses to save an active service longer than the slot grid', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);

    // The model guard closes the application path, so a future admin panel
    // cannot create the offending row in one click.
    expect(fn () => Service::factory()->create([
        'slug' => 'intensive-90',
        'duration_minutes' => 90,
        'is_active' => true,
    ]))->toThrow(LogicException::class, 'is 90 minutes long');
});

it('allows a service exactly as long as the slot grid', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);

    $service = Service::factory()->create(['duration_minutes' => 60, 'is_active' => true]);

    expect($service->exists)->toBeTrue();
});

it('does not block an inactive service that is too long', function () {
    // An archived service cannot be booked, so it cannot cause an overlap.
    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);

    $service = Service::factory()->inactive()->create(['duration_minutes' => 120]);

    expect($service->exists)->toBeTrue();
});

it('blocks reactivating a service that no longer fits the grid', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);

    $service = Service::factory()->inactive()->create(['duration_minutes' => 120]);

    expect(fn () => $service->update(['is_active' => true]))->toThrow(LogicException::class);
});

it('skips the guard when no schedule exists yet', function () {
    // A fresh install mid-seed has no working hours; there is no grid to
    // violate, and the invariant test covers the seeded end state.
    expect(WorkingHour::count())->toBe(0);

    $service = Service::factory()->create(['duration_minutes' => 240, 'is_active' => true]);

    expect($service->exists)->toBeTrue();
});
