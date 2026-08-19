<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Appointment>
 */
class AppointmentFactory extends Factory
{
    protected $model = Appointment::class;

    /**
     * Every appointment holds a UNIQUE slot_key derived from its start time, so
     * two factory-made appointments landing on the same random hour would fail
     * the insert. This counter walks each new appointment forward one hour so
     * that Appointment::factory()->count(20)->create() just works; pass an
     * explicit starts_at when a test needs a specific instant.
     */
    private static int $slotCursor = 0;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::now()
            ->utc()
            ->startOfHour()
            ->addHours(++self::$slotCursor);

        return [
            'reference' => Appointment::generateReference(),
            'cancel_token' => Appointment::generateCancelToken(),
            'patient_id' => Patient::factory(),
            'service_id' => Service::factory(),
            // Every appointment names a practitioner: staff_id is NOT NULL so
            // that slot_key locks one person's hour. Pass an explicit staff_id
            // when a test needs two appointments to share, or to avoid, a
            // practitioner.
            'staff_id' => User::factory(),
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->clone()->addMinutes(45),
            'mode' => fake()->randomElement(AppointmentMode::cases()),
            'status' => AppointmentStatus::Pending,
            'price' => fake()->randomElement([400, 600, 1500, 3900]),
            'currency' => 'EGP',
            'source' => fake()->randomElement(BookingSource::cases()),
            'staff_notes' => null,
            'confirmed_at' => null,
            'cancelled_at' => null,
            'cancellation_reason' => null,
        ];
    }

    public function confirmed(): self
    {
        return $this->state(fn (): array => [
            'status' => AppointmentStatus::Confirmed,
            'confirmed_at' => Carbon::now()->subDay(),
        ]);
    }

    public function completed(): self
    {
        return $this->state(fn (): array => [
            'status' => AppointmentStatus::Completed,
            'confirmed_at' => Carbon::now()->subDays(3),
        ]);
    }

    public function cancelled(): self
    {
        return $this->state(fn (): array => [
            'status' => AppointmentStatus::Cancelled,
            'cancelled_at' => Carbon::now()->subDay(),
            'cancellation_reason' => fake()->randomElement([
                'ظرف طارئ', 'تغيير الموعد', 'سفر',
            ]),
        ]);
    }

    public function noShow(): self
    {
        return $this->state(fn (): array => ['status' => AppointmentStatus::NoShow]);
    }

    public function past(): self
    {
        return $this->state(function (): array {
            $startsAt = Carbon::now()->utc()->startOfHour()->subHours(++self::$slotCursor);

            return [
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->clone()->addMinutes(45),
            ];
        });
    }

    public function at(Carbon $startsAt, int $durationMinutes = 45): self
    {
        return $this->state(fn (): array => [
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->clone()->addMinutes($durationMinutes),
        ]);
    }
}
