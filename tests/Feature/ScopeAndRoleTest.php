<?php

declare(strict_types=1);

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Enums\NotificationChannel;
use App\Models\Appointment;
use App\Models\Post;
use App\Models\Service;
use App\Models\User;
use Database\Seeders\RoleSeeder;

it('only returns active services in sort order', function () {
    $second = Service::factory()->create(['is_active' => true, 'sort_order' => 2]);
    $first = Service::factory()->create(['is_active' => true, 'sort_order' => 1]);
    Service::factory()->inactive()->create(['sort_order' => 0]);

    expect(Service::active()->pluck('id')->all())->toBe([$first->id, $second->id]);
});

it('treats drafts and future dates as unpublished', function () {
    $published = Post::factory()->create();
    Post::factory()->draft()->create();
    Post::factory()->scheduled()->create();

    expect(Post::published()->pluck('id')->all())->toBe([$published->id]);
});

it('assigns the three clinic roles to staff users', function () {
    $this->seed(RoleSeeder::class);

    $admin = User::factory()->create();
    $doctor = User::factory()->create();
    $receptionist = User::factory()->create();

    $admin->assignRole('admin');
    $doctor->assignRole('doctor');
    $receptionist->assignRole('receptionist');

    expect($admin->hasRole('admin'))->toBeTrue()
        ->and($doctor->hasRole('doctor'))->toBeTrue()
        ->and($receptionist->hasRole('receptionist'))->toBeTrue()
        ->and($receptionist->hasRole('admin'))->toBeFalse();
});

it('lists only admins and doctors as bookable staff', function () {
    $this->seed(RoleSeeder::class);

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');

    $receptionist = User::factory()->create();
    $receptionist->assignRole('receptionist');

    $ids = User::bookable()->pluck('id')->all();

    expect($ids)->toContain($doctor->id)
        ->and($ids)->not->toContain($receptionist->id);
});

it('casts the enum columns back to enum instances', function () {
    $appointment = Appointment::factory()->create([
        'mode' => AppointmentMode::Online,
        'status' => AppointmentStatus::Confirmed,
        'source' => BookingSource::Phone,
    ]);

    $fresh = $appointment->fresh();

    expect($fresh?->mode)->toBe(AppointmentMode::Online)
        ->and($fresh?->status)->toBe(AppointmentStatus::Confirmed)
        ->and($fresh?->source)->toBe(BookingSource::Phone);
});

it('gives every enum case an arabic label', function (string $enum) {
    foreach ($enum::cases() as $case) {
        expect($case->label())->toBeString()->not->toBe('')
            ->and(preg_match('/\p{Arabic}/u', $case->label()))->toBe(1);
    }
})->with([
    AppointmentStatus::class,
    AppointmentMode::class,
    BookingSource::class,
    NotificationChannel::class,
]);

it('stores prices with two decimal places', function () {
    $service = Service::factory()->create(['price' => 600]);

    expect($service->fresh()?->price)->toBe('600.00');
});
