<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Faq;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Faq>
 */
class FaqFactory extends Factory
{
    protected $model = Faq::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'question' => [
                'ar' => 'هل الاستشارة متاحة أونلاين؟',
                'en' => 'Is the consultation available online?',
            ],
            'answer' => [
                'ar' => 'نعم، يمكنك اختيار الاستشارة عن بُعد عند الحجز وستصلك رسالة بالرابط.',
                'en' => 'Yes. Choose the remote option when booking and you will receive the link by message.',
            ],
            'sort_order' => fake()->numberBetween(0, 20),
            'is_active' => true,
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
