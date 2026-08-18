<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\User;
use App\Models\WorkingHour;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WorkingHour>
 */
class WorkingHourFactory extends Factory
{
    protected $model = WorkingHour::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // staff_id is NOT NULL: every schedule block belongs to a
            // practitioner, so the factory makes one rather than leaving it open.
            'staff_id' => User::factory(),
            // 5 is Friday, the clinic's closed day, so it is never generated.
            'day_of_week' => fake()->randomElement([0, 1, 2, 3, 4, 6]),
            'start_time' => '10:00:00',
            'end_time' => '20:00:00',
            'slot_minutes' => 60,
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
