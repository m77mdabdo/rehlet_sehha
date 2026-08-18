<?php

declare(strict_types=1);

use App\Enums\Gender;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

it('creates a patient when the phone number is unknown', function () {
    $patient = Patient::findOrCreateByPhone('+201012345678', [
        'name' => 'منى عبد الرحمن',
        'email' => 'mona@example.com',
    ]);

    expect($patient->exists)->toBeTrue()
        ->and($patient->wasRecentlyCreated)->toBeTrue()
        ->and($patient->phone)->toBe('+201012345678')
        ->and($patient->name)->toBe('منى عبد الرحمن');

    expect(Patient::count())->toBe(1);
});

it('returns the existing patient and fills only missing attributes', function () {
    $existing = Patient::factory()->create([
        'phone' => '+201012345678',
        'name' => 'منى عبد الرحمن',
        'email' => null,
        'gender' => null,
    ]);

    $resolved = Patient::findOrCreateByPhone('+201012345678', [
        // A booking form must never overwrite a name the clinic corrected by hand.
        'name' => 'MONA A R',
        'email' => 'mona@example.com',
        'gender' => Gender::Female,
    ]);

    expect($resolved->id)->toBe($existing->id)
        ->and($resolved->wasRecentlyCreated)->toBeFalse()
        ->and($resolved->name)->toBe('منى عبد الرحمن')
        ->and($resolved->email)->toBe('mona@example.com')
        ->and($resolved->gender)->toBe(Gender::Female);

    expect(Patient::count())->toBe(1);
});

it('restores a soft deleted patient instead of creating a second file', function () {
    $original = Patient::factory()->create([
        'phone' => '+201012345678',
        'name' => 'منى عبد الرحمن',
    ]);
    $appointment = Appointment::factory()->create(['patient_id' => $original->id]);

    $original->delete();

    expect($original->fresh()?->trashed())->toBeTrue()
        ->and(Patient::count())->toBe(0)
        ->and(Patient::withTrashed()->count())->toBe(1);

    $resolved = Patient::findOrCreateByPhone('+201012345678', [
        'name' => 'منى عبد الرحمن',
        'email' => 'mona@example.com',
    ]);

    // Same row, revived — not a new one.
    expect($resolved->id)->toBe($original->id)
        ->and($resolved->trashed())->toBeFalse()
        ->and($resolved->deleted_at)->toBeNull()
        ->and($resolved->email)->toBe('mona@example.com')
        ->and(Patient::count())->toBe(1)
        ->and(Patient::withTrashed()->count())->toBe(1);

    // And their history came back with them.
    expect($resolved->appointments()->pluck('id')->all())->toContain($appointment->id);
});

it('overwrites attributes on a restored patient', function () {
    // Unlike the live-patient branch, a returning patient's newly supplied
    // details are treated as the current truth.
    $original = Patient::factory()->create([
        'phone' => '+201012345678',
        'email' => 'old@example.com',
    ]);
    $original->delete();

    $resolved = Patient::findOrCreateByPhone('+201012345678', ['email' => 'new@example.com']);

    expect($resolved->email)->toBe('new@example.com');
});

it('never lets the phone number be changed through the attributes array', function () {
    $existing = Patient::factory()->create(['phone' => '+201012345678']);

    $resolved = Patient::findOrCreateByPhone('+201012345678', ['phone' => '+201099999999']);

    expect($resolved->id)->toBe($existing->id)
        ->and($resolved->phone)->toBe('+201012345678');
});

it('ignores attributes that are not fillable', function () {
    $patient = Patient::findOrCreateByPhone('+201012345678', [
        'name' => 'منى',
        'id' => 9999,
        'deleted_at' => now(),
    ]);

    expect($patient->id)->not->toBe(9999)
        ->and($patient->trashed())->toBeFalse();
});

it('takes a row lock inside a transaction so two bookings cannot race', function () {
    Patient::factory()->create(['phone' => '+201012345678']);

    $outerLevel = DB::transactionLevel();
    $observed = [];

    DB::listen(function ($query) use (&$observed, $outerLevel): void {
        if (str_contains(strtolower($query->sql), 'select') && str_contains(strtolower($query->sql), 'patients')) {
            $observed[] = [
                'sql' => strtolower($query->sql),
                'nested' => DB::transactionLevel() > $outerLevel,
            ];
        }
    });

    Patient::findOrCreateByPhone('+201012345678', ['name' => 'منى']);

    $lookup = collect($observed)->first(fn (array $q): bool => str_contains($q['sql'], 'for update'));

    // SELECT ... FOR UPDATE, issued inside its own transaction. On a unique
    // index this also gap-locks a missing row, so a concurrent booking for the
    // same number waits instead of racing to a duplicate insert.
    expect($lookup)->not->toBeNull()
        ->and($lookup['sql'])->toContain('where `phone` =')
        ->and($lookup['nested'])->toBeTrue();
});

it('still refuses a duplicate phone number at the database level', function () {
    // The lock stops us reaching this; the unique index is the last-resort
    // backstop for any code path that bypasses findOrCreateByPhone().
    Patient::factory()->create(['phone' => '+201012345678']);

    expect(fn () => Patient::factory()->create(['phone' => '+201012345678']))
        ->toThrow(QueryException::class);
});

it('keeps a soft deleted patient holding their phone number', function () {
    // This is why findOrCreateByPhone exists: the unique index deliberately
    // covers soft-deleted rows, so a naive create() would fail.
    $patient = Patient::factory()->create(['phone' => '+201012345678']);
    $patient->delete();

    expect(fn () => Patient::factory()->create(['phone' => '+201012345678']))
        ->toThrow(QueryException::class);
});

it('casts gender to an enum and keeps it nullable', function () {
    $withGender = Patient::factory()->create(['gender' => Gender::Female]);
    $without = Patient::factory()->create(['gender' => null]);

    expect($withGender->fresh()?->gender)->toBe(Gender::Female)
        ->and($withGender->fresh()?->gender?->label())->toBe('أنثى')
        ->and($without->fresh()?->gender)->toBeNull();
});

it('forbids force deleting a patient for every role', function (string $role) {
    $this->seed(RoleSeeder::class);

    $user = User::factory()->create();
    $user->assignRole($role);

    $patient = Patient::factory()->create();

    expect($user->can('forceDelete', $patient))->toBeFalse()
        // ...while ordinary soft delete stays available to clinical staff.
        ->and($user->can('view', $patient))->toBeTrue();
})->with(['admin', 'doctor', 'receptionist']);

it('allows soft delete but not force delete for the doctor', function () {
    $this->seed(RoleSeeder::class);

    $doctor = User::factory()->create();
    $doctor->assignRole('doctor');
    $patient = Patient::factory()->create();

    expect($doctor->can('delete', $patient))->toBeTrue()
        ->and($doctor->can('restore', $patient))->toBeTrue()
        ->and($doctor->can('forceDelete', $patient))->toBeFalse();
});
