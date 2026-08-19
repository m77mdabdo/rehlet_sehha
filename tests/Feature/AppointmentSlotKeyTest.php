<?php

declare(strict_types=1);

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

function slotAt(string $time = '2026-09-01 10:00:00'): Carbon
{
    return Carbon::parse($time, 'UTC');
}

it('derives a slot key from the staff member and start time', function () {
    $staff = User::factory()->create();
    $slot = slotAt();

    $appointment = Appointment::factory()->at($slot)->create(['staff_id' => $staff->id]);

    expect($appointment->slot_key)->toBe("{$staff->id}-2026-09-01 10:00:00");
});

it('will not create an appointment without a practitioner', function () {
    /*
     * This used to assert the opposite: an unassigned appointment collapsed
     * onto the key "0-2026-09-01 10:00:00". That was correct with one doctor
     * and wrong in both directions with two — it refused a second unassigned
     * booking the clinic could have taken, and it locked nobody in particular.
     *
     * staff_id is NOT NULL now, so the collapse cannot happen. syncSlotKey
     * throws before the database has to, so the message explains the rule
     * instead of surfacing as a constraint violation.
     */
    expect(fn () => Appointment::factory()->at(slotAt())->create(['staff_id' => null]))
        ->toThrow(LogicException::class);
});

it('gives each practitioner their own key for the same instant', function () {
    $rana = User::factory()->create();
    $hala = User::factory()->create();
    $slot = slotAt();

    $first = Appointment::factory()->at($slot)->create(['staff_id' => $rana->id]);
    $second = Appointment::factory()->at($slot)->create(['staff_id' => $hala->id]);

    // Two people, one hour, two locks. Under the old collapsed key the second
    // insert would have been refused.
    expect($first->slot_key)->toBe("{$rana->id}-2026-09-01 10:00:00");
    expect($second->slot_key)->toBe("{$hala->id}-2026-09-01 10:00:00");
});

it('refuses a second active appointment in the same slot', function () {
    $staff = User::factory()->create();
    $slot = slotAt();

    Appointment::factory()->at($slot)->create(['staff_id' => $staff->id]);

    // The guard lives in the database, not in PHP: this is a unique-index
    // violation, which is what makes it safe against concurrent requests.
    expect(fn () => Appointment::factory()->at($slot)->create(['staff_id' => $staff->id]))
        ->toThrow(QueryException::class);

    expect(Appointment::count())->toBe(1);
});

it('allows the same slot for a different staff member', function () {
    $slot = slotAt();
    $first = User::factory()->create();
    $second = User::factory()->create();

    Appointment::factory()->at($slot)->create(['staff_id' => $first->id]);
    Appointment::factory()->at($slot)->create(['staff_id' => $second->id]);

    expect(Appointment::count())->toBe(2);
});

it('frees the slot when the appointment is cancelled and allows a rebooking', function () {
    $staff = User::factory()->create();
    $slot = slotAt();

    $original = Appointment::factory()->at($slot)->create(['staff_id' => $staff->id]);
    expect($original->slot_key)->not->toBeNull();

    $original->cancel('ظرف طارئ');

    expect($original->fresh()?->slot_key)->toBeNull()
        ->and($original->fresh()?->status)->toBe(AppointmentStatus::Cancelled)
        ->and(DB::table('appointments')->where('id', $original->id)->value('slot_key'))->toBeNull();

    // The hour is genuinely available again, not merely marked as free.
    $rebooked = Appointment::factory()->at($slot)->create(['staff_id' => $staff->id]);

    expect($rebooked->slot_key)->toBe("{$staff->id}-2026-09-01 10:00:00")
        ->and(Appointment::count())->toBe(2);
});

it('keeps holding the slot for a no-show', function () {
    // A no-show already consumed the clinic's hour, so unlike a cancellation it
    // must not hand that hour back to the booking calendar.
    $staff = User::factory()->create();
    $slot = slotAt();

    $appointment = Appointment::factory()->at($slot)->noShow()->create(['staff_id' => $staff->id]);

    expect($appointment->slot_key)->not->toBeNull();

    expect(fn () => Appointment::factory()->at($slot)->create(['staff_id' => $staff->id]))
        ->toThrow(QueryException::class);
});

it('releases the slot when the appointment is soft deleted', function () {
    $staff = User::factory()->create();
    $slot = slotAt();

    $appointment = Appointment::factory()->at($slot)->create(['staff_id' => $staff->id]);
    $appointment->delete();

    expect(DB::table('appointments')->where('id', $appointment->id)->value('slot_key'))->toBeNull();

    // Without this the deleted row would hold its hour hostage forever, because
    // the unique index does not care that the row is soft deleted.
    $replacement = Appointment::factory()->at($slot)->create(['staff_id' => $staff->id]);

    expect($replacement->slot_key)->not->toBeNull();
});

it('moves the slot key when the appointment is rescheduled', function () {
    $staff = User::factory()->create();

    $appointment = Appointment::factory()->at(slotAt())->create(['staff_id' => $staff->id]);

    $appointment->update([
        'starts_at' => slotAt('2026-09-01 14:00:00'),
        'ends_at' => slotAt('2026-09-01 14:45:00'),
    ]);

    expect($appointment->fresh()?->slot_key)->toBe("{$staff->id}-2026-09-01 14:00:00");

    // And the vacated 10:00 is bookable again.
    $filler = Appointment::factory()->at(slotAt())->create(['staff_id' => $staff->id]);
    expect($filler->slot_key)->toBe("{$staff->id}-2026-09-01 10:00:00");
});

it('cannot have its slot key set through mass assignment', function () {
    $staff = User::factory()->create();

    $appointment = Appointment::factory()->at(slotAt())->create(['staff_id' => $staff->id]);
    $appointment->fill(['slot_key' => null])->save();

    // slot_key is derived state, not input — freeing an occupied slot must not
    // be reachable from a request body.
    expect($appointment->fresh()?->slot_key)->toBe("{$staff->id}-2026-09-01 10:00:00");
});

it('separates the workload question from the is-this-hour-free question', function () {
    $staff = User::factory()->create();

    Appointment::factory()->at(slotAt('2026-09-01 10:00:00'))->create(['staff_id' => $staff->id]);
    Appointment::factory()->at(slotAt('2026-09-01 11:00:00'))->confirmed()->create(['staff_id' => $staff->id]);
    Appointment::factory()->at(slotAt('2026-09-01 12:00:00'))->cancelled()->create(['staff_id' => $staff->id]);
    Appointment::factory()->at(slotAt('2026-09-01 13:00:00'))->noShow()->create(['staff_id' => $staff->id]);

    // Workload: the clinic did two appointments. The cancellation never
    // happened and the no-show did not consume the practitioner's attention.
    expect(Appointment::countsTowardWorkload()->count())->toBe(2);

    // Occupancy: three hours are still spoken for. The no-show's hour was
    // spent whether or not the patient turned up, and slot_key still holds it.
    expect(Appointment::holdingSlot()->count())->toBe(3);

    // The one-row difference between the two scopes IS the no-show. Anything
    // reaching for the wrong one gets exactly this off-by-one, silently.
    expect(Appointment::holdingSlot()->count() - Appointment::countsTowardWorkload()->count())->toBe(1);
});

it('scopes upcoming appointments to the future in start order', function () {
    $staff = User::factory()->create();

    Appointment::factory()->past()->create(['staff_id' => $staff->id]);
    $soon = Appointment::factory()->at(Carbon::now()->utc()->addDay()->startOfHour())->create(['staff_id' => $staff->id]);
    $later = Appointment::factory()->at(Carbon::now()->utc()->addDays(3)->startOfHour())->create(['staff_id' => $staff->id]);

    expect(Appointment::upcoming()->pluck('id')->all())->toBe([$soon->id, $later->id]);
});
