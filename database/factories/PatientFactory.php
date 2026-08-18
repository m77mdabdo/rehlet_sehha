<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Patient;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Patient>
 */
class PatientFactory extends Factory
{
    protected $model = Patient::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake('ar_EG')->name(),
            'phone' => $this->egyptianMobile(),
            'email' => fake()->boolean(70) ? fake('en_US')->safeEmail() : null,
            'birth_date' => fake()->boolean(60)
                ? fake()->dateTimeBetween('-60 years', '-18 years')->format('Y-m-d')
                : null,
            'gender' => fake()->randomElement(['female', 'male', null]),
            'notes' => null,
        ];
    }

    public function withNotes(): self
    {
        return $this->state(fn (): array => [
            'notes' => fake()->randomElement([
                'تفضل المتابعة عبر واتساب.',
                'حساسية من اللاكتوز.',
                'تعمل بنظام ورديات ليلية.',
            ]),
        ]);
    }

    /**
     * Egyptian mobile numbers in E.164: +20 followed by 10, 11, 12 or 15 and
     * eight digits. Faker has no ar_EG phone provider, so we build it here to
     * keep seeded data plausible for a Cairo clinic.
     */
    private function egyptianMobile(): string
    {
        return sprintf(
            '+20%s%08d',
            fake()->randomElement(['10', '11', '12', '15']),
            fake()->numberBetween(0, 99999999),
        );
    }
}
