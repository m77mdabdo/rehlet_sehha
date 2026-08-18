<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Service;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Service>
 */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $arabic = fake()->randomElement([
            'استشارة تغذية', 'متابعة شهرية', 'برنامج إنقاص وزن', 'تحليل التركيب الجسمي',
        ]);
        $english = fake()->randomElement([
            'Nutrition Consultation', 'Monthly Follow-up', 'Weight Loss Programme', 'Body Composition Analysis',
        ]);

        return [
            'slug' => Str::slug($english).'-'.fake()->unique()->numberBetween(1, 99999),
            'name' => ['ar' => $arabic, 'en' => $english],
            'subtitle' => [
                'ar' => 'خطة غذائية مبنية على حالتك',
                'en' => 'A plan built around your case',
            ],
            'description' => [
                'ar' => 'جلسة تقييم شاملة تشمل مراجعة العادات الغذائية ووضع خطة عملية قابلة للتطبيق.',
                'en' => 'A full assessment covering your eating habits and a practical plan you can actually follow.',
            ],
            'features' => [
                'ar' => ['تقييم كامل', 'خطة مكتوبة', 'متابعة عبر واتساب'],
                'en' => ['Full assessment', 'Written plan', 'WhatsApp follow-up'],
            ],
            'price' => fake()->randomElement([400, 600, 1500, 3900]),
            'currency' => 'EGP',
            'duration_minutes' => fake()->randomElement([25, 45]),
            'sessions_count' => fake()->randomElement([1, 4, 12]),
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
