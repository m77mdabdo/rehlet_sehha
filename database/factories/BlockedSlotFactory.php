<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\BlockedSlot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<BlockedSlot>
 */
class BlockedSlotFactory extends Factory
{
    protected $model = BlockedSlot::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startsAt = Carbon::now()->utc()->addDays(fake()->numberBetween(1, 30))->startOfHour();

        return [
            'staff_id' => null,
            'starts_at' => $startsAt,
            'ends_at' => $startsAt->clone()->addHours(fake()->numberBetween(1, 4)),
            'reason' => fake()->randomElement([
                'إجازة', 'مؤتمر طبي', 'صيانة العيادة', 'عطلة رسمية',
            ]),
        ];
    }
}
