<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\FoodGroup;
use App\Models\PlateFood;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PlateFood>
 */
class PlateFoodFactory extends Factory
{
    protected $model = PlateFood::class;

    /**
     * NO NUMERIC ATTRIBUTE HERE EITHER — see App\Models\PlateFood. A factory
     * that invented a calorie figure would put one into every test fixture and
     * make the rule look optional.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $group = fake()->randomElement(FoodGroup::cases());

        return [
            'name' => [
                'ar' => fake('ar_SA')->word(),
                'en' => fake('en_US')->word(),
            ],
            'group' => $group,
            'emoji' => fake()->randomElement(['🥗', '🫘', '🥖', '🫒', '🍊', '🧀']),
            'sort_order' => fake()->numberBetween(0, 50),
            'is_active' => true,
        ];
    }
}
