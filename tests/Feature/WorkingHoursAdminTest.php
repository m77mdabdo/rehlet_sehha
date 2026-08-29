<?php

declare(strict_types=1);

use App\Filament\Resources\WorkingHours\Pages\EditWorkingHour;
use App\Filament\Resources\WorkingHours\Pages\ListWorkingHours;
use App\Models\User;
use App\Models\WorkingHour;
use App\Support\PublicContent;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;
use Livewire\Livewire;
use Spatie\Permission\PermissionRegistrar;

/**
 * THE SCHEDULE HAD NO EDITING PATH AT ALL.
 *
 * working_hours decides which slots the booking form offers, which hours the
 * JSON-LD advertises and what the footer tells a patient — and there was no
 * admin screen for it. Changing Saturday's closing time meant running a seeder
 * or opening the database by hand. In practice that means it never changes, or
 * it changes and nobody can say who did it.
 *
 * Worse, PublicContentCacheTest already asserted that a schedule change is live
 * on the next request. It was testing an invalidation path behind a door that
 * did not exist.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);

    app(PermissionRegistrar::class)->forgetCachedPermissions();
});

/**
 * A staff user with one role.
 *
 * Named for this file rather than generically: helpers declared in a Pest test
 * file are global, so a plain staffWithRole() collides with the identical
 * helper in AdminPanelAccessTest and the whole suite fails to load.
 */
function scheduleStaff(string $role): User
{
    $user = User::factory()->create();
    $user->assignRole($role);

    return $user;
}

it('lets the doctor and the admin reach the schedule, and nobody else', function (string $role, bool $allowed) {
    $user = staffWithRole($role);

    $response = $this->actingAs($user)->get('/admin/working-hours');

    expect($response->status())->toBe($allowed ? 200 : 403, sprintf(
        'A %s %s be able to open the schedule.',
        $role,
        $allowed ? 'should' : 'must not',
    ));
})->with([
    ['doctor', true],
    ['admin', true],
    ['receptionist', false],
]);

it('shows every window that exists', function () {
    $rows = WorkingHour::query()->count();

    expect($rows)->toBeGreaterThan(0, 'The seeder produced no schedule to list.');

    Livewire::actingAs(scheduleStaff('doctor'))
        ->test(ListWorkingHours::class)
        ->assertCanSeeTableRecords(WorkingHour::all());
});

it('changes an opening time and the site says so on the next request', function () {
    /*
     * The whole point of the screen. Reception changes Saturday's hours, and
     * the site keeps announcing the old ones until somebody clears a cache —
     * that is the failure this closes. The model flushes the public content
     * cache on save, so the correction is live immediately.
     */
    $saturday = WorkingHour::query()->where('day_of_week', 6)->firstOrFail();

    // Warm the cache with the OLD value so the flush has something to undo.
    $this->get('/ar')->assertOk();
    expect(PublicContent::openingHours()->firstWhere('day_of_week', 6)->end_time)->toStartWith('20:00');

    Livewire::actingAs(scheduleStaff('doctor'))
        ->test(EditWorkingHour::class, ['record' => $saturday->getKey()])
        ->fillForm(['end_time' => '18:00'])
        ->call('save')
        ->assertHasNoFormErrors();

    expect(PublicContent::openingHours()->firstWhere('day_of_week', 6)->end_time)
        ->toStartWith('18:00', 'The cached schedule still holds the old closing time.');

    $content = $this->get('/ar')->assertOk()->getContent();

    expect(str_contains($content, '"closes":"18:00"'))->toBeTrue(
        'The JSON-LD still advertises the old closing time.'
    );
});

it('refuses a window that ends before it starts', function () {
    /*
     * A backwards window produces no slots at all, silently. To a patient that
     * looks like a broken booking form rather than a typo, and to the clinic
     * it looks like nobody wants an appointment that day.
     */
    $saturday = WorkingHour::query()->where('day_of_week', 6)->firstOrFail();

    Livewire::actingAs(scheduleStaff('doctor'))
        ->test(EditWorkingHour::class, ['record' => $saturday->getKey()])
        ->fillForm(['start_time' => '18:00', 'end_time' => '09:00'])
        ->call('save')
        ->assertHasFormErrors(['end_time']);

    expect($saturday->fresh()->start_time)->toStartWith('10:00', 'The invalid window was saved anyway.');
});

it('offers deactivation as well as deletion', function () {
    /*
     * Deleting a row destroys the only record of what the hours were.
     * Deactivating stops the day being offered and keeps the history, and is
     * almost always what was meant.
     */
    $saturday = WorkingHour::query()->where('day_of_week', 6)->firstOrFail();

    Livewire::actingAs(scheduleStaff('doctor'))
        ->test(EditWorkingHour::class, ['record' => $saturday->getKey()])
        ->fillForm(['is_active' => false])
        ->call('save')
        ->assertHasNoFormErrors();

    expect($saturday->fresh()->is_active)->toBeFalse();

    // And an inactive window is out of the public schedule immediately.
    expect(PublicContent::openingHours()->firstWhere('day_of_week', 6))->toBeNull();
});
