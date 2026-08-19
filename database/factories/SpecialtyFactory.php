<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Specialty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Specialty>
 */
class SpecialtyFactory extends Factory
{
    protected $model = Specialty::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'slug' => str($name)->slug()->value(),
            'name' => [
                'ar' => 'تخصص '.fake()->word(),
                'en' => ucfirst($name),
            ],
            'description' => [
                'ar' => fake()->sentence(),
                'en' => fake()->sentence(),
            ],
            // A key the x-icon component knows; a made-up one would render the
            // fallback and quietly pass a test that meant to check the icon.
            'icon' => fake()->randomElement([
                'stethoscope', 'target', 'heart', 'bolt',
                'smile', 'cycle', 'flask', 'briefcase',
            ]),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(1, 20),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
