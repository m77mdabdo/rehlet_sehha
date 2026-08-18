<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Testimonial;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Testimonial>
 */
class TestimonialFactory extends Factory
{
    protected $model = Testimonial::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake('ar_EG')->name();

        return [
            'name' => $name,
            'initials' => mb_substr($name, 0, 1),
            'context' => [
                'ar' => 'متابعة ثلاثة أشهر',
                'en' => 'Three-month programme',
            ],
            'quote' => [
                'ar' => 'الخطة كانت واقعية ومناسبة لمواعيد عملي، ونزلت وزني بثبات دون حرمان.',
                'en' => 'The plan was realistic and fit my work schedule. I lost weight steadily without feeling deprived.',
            ],
            'rating' => fake()->numberBetween(4, 5),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
