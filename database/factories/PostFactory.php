<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @extends Factory<Post>
 */
class PostFactory extends Factory
{
    protected $model = Post::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $english = fake('en_US')->sentence(5);

        return [
            'slug' => Str::slug($english).'-'.fake()->unique()->numberBetween(1, 99999),
            'title' => [
                'ar' => 'كيف تبني عادة غذائية تستمر معك',
                'en' => rtrim($english, '.'),
            ],
            'excerpt' => [
                'ar' => 'ثلاث خطوات عملية تساعدك على الالتزام بخطتك دون حرمان.',
                'en' => 'Three practical steps that help you stick to your plan without deprivation.',
            ],
            'body' => [
                'ar' => 'العادة الغذائية الناجحة لا تبدأ بقرار كبير، بل بخطوة صغيرة تتكرر يومياً حتى تصبح جزءاً من روتينك.',
                'en' => 'A habit that lasts does not start with a big decision. It starts with a small step repeated daily until it becomes routine.',
            ],
            'cover_path' => null,
            'category' => fake()->randomElement(['تغذية', 'صحة عامة', 'وصفات']),
            'reading_minutes' => fake()->numberBetween(2, 12),
            'published_at' => Carbon::now()->subDays(fake()->numberBetween(1, 120)),
            'is_featured' => false,
        ];
    }

    public function draft(): self
    {
        return $this->state(fn (): array => ['published_at' => null]);
    }

    public function scheduled(): self
    {
        return $this->state(fn (): array => [
            'published_at' => Carbon::now()->addDays(7),
        ]);
    }

    public function featured(): self
    {
        return $this->state(fn (): array => ['is_featured' => true]);
    }
}
