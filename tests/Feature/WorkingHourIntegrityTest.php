<?php

declare(strict_types=1);

use App\Models\User;
use App\Models\WorkingHour;
use Database\Seeders\AdminUserSeeder;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Database\QueryException;

it('requires a staff member on every schedule block', function () {
    expect(fn () => WorkingHour::factory()->create(['staff_id' => null]))
        ->toThrow(QueryException::class);
});

it('refuses a duplicate day and opening time for the same staff member', function () {
    $staff = User::factory()->create();

    WorkingHour::factory()->create([
        'staff_id' => $staff->id,
        'day_of_week' => 6,
        'start_time' => '10:00:00',
    ]);

    // Every column in this unique index is NOT NULL, so unlike a nullable
    // composite it genuinely prevents the duplicate.
    expect(fn () => WorkingHour::factory()->create([
        'staff_id' => $staff->id,
        'day_of_week' => 6,
        'start_time' => '10:00:00',
    ]))->toThrow(QueryException::class);
});

it('allows a second block on the same day at a different time', function () {
    // A split day — morning and evening clinic — stays possible.
    $staff = User::factory()->create();

    WorkingHour::factory()->create(['staff_id' => $staff->id, 'day_of_week' => 6, 'start_time' => '10:00:00']);
    WorkingHour::factory()->create(['staff_id' => $staff->id, 'day_of_week' => 6, 'start_time' => '16:00:00']);

    expect(WorkingHour::where('staff_id', $staff->id)->count())->toBe(2);
});

it('allows two practitioners to share a day and opening time', function () {
    $first = User::factory()->create();
    $second = User::factory()->create();

    WorkingHour::factory()->create(['staff_id' => $first->id, 'day_of_week' => 6, 'start_time' => '10:00:00']);
    WorkingHour::factory()->create(['staff_id' => $second->id, 'day_of_week' => 6, 'start_time' => '10:00:00']);

    expect(WorkingHour::count())->toBe(2);
});

it('removes a schedule when its staff member is deleted', function () {
    $staff = User::factory()->create();
    $hours = WorkingHour::factory()->create(['staff_id' => $staff->id]);

    $staff->delete();

    $this->assertDatabaseMissing('working_hours', ['id' => $hours->id]);
});

it('seeds the clinic schedule onto the doctor and leaves friday closed', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);

    $doctor = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'doctor'))->firstOrFail();

    $days = WorkingHour::where('staff_id', $doctor->id)->pluck('day_of_week')->sort()->values()->all();

    // 0=Sun .. 6=Sat, so Friday is 5 — absent, meaning closed.
    expect($days)->toBe([0, 1, 2, 3, 4, 6])
        ->and(WorkingHour::whereNull('staff_id')->count())->toBe(0);

    $saturday = WorkingHour::where('staff_id', $doctor->id)->where('day_of_week', 6)->firstOrFail();

    expect($saturday->start_time)->toBe('10:00:00')
        ->and($saturday->end_time)->toBe('20:00:00')
        ->and($saturday->slot_minutes)->toBe(60)
        ->and($saturday->is_active)->toBeTrue();
});

it('does not give administrators a bookable schedule', function () {
    $this->seed(RoleSeeder::class);
    $this->seed(AdminUserSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);

    $admin = User::query()->whereHas('roles', fn ($q) => $q->where('name', 'admin'))->firstOrFail();

    expect(WorkingHour::where('staff_id', $admin->id)->count())->toBe(0);
});
