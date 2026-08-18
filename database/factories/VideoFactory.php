<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Video>
 */
class VideoFactory extends Factory
{
    protected $model = Video::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // YouTube ids are 11 characters; generating the real shape keeps
            // any future embed-url building honest.
            'youtube_id' => Str::random(11),
            'title' => [
                'ar' => 'ثلاث أخطاء شائعة في الرجيم',
                'en' => 'Three common dieting mistakes',
            ],
            'description' => [
                'ar' => 'شرح مبسط لأكثر الأخطاء التي تعطل نزول الوزن.',
                'en' => 'A simple breakdown of the mistakes that stall weight loss.',
            ],
            'duration_seconds' => fake()->numberBetween(60, 1200),
            'category' => fake()->randomElement(['تغذية', 'تمارين', 'أسئلة شائعة']),
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 20),
        ];
    }

    public function featured(): self
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }

    public function inactive(): self
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
