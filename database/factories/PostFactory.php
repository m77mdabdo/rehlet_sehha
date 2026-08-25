<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Post;
use App\Models\User;
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
            'category' => fake()->randomElement([
                ['ar' => 'تغذية', 'en' => 'Nutrition'],
                ['ar' => 'صحة عامة', 'en' => 'General health'],
                ['ar' => 'وصفات', 'en' => 'Recipes'],
            ]),
            'reading_minutes' => fake()->numberBetween(2, 12),
            'published_at' => Carbon::now()->subDays(fake()->numberBetween(1, 120)),

            /*
             * A published article needs a named clinical reviewer, and the
             * model refuses to save one without it. A factory is a test
             * fixture rather than a publication, so it supplies a real doctor
             * user to stand in that role — which also means a test asserting
             * "the reviewer line names somebody" has somebody to name.
             */
            'reviewed_by' => User::factory()->state(['name' => 'د. رنا سالم']),
            'reviewed_at' => Carbon::now()->subDays(fake()->numberBetween(1, 120)),

            'is_featured' => false,
        ];
    }

    /**
     * Not published, and therefore not reviewed.
     *
     * Both are cleared together because that is the real shape of a draft: a
     * piece nobody has signed off because nobody has been asked to yet.
     */
    public function draft(): self
    {
        return $this->state(fn (): array => [
            'published_at' => null,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
    }

    /**
     * Written and dated, but nobody has checked it.
     *
     * The state that must never reach the public site. It exists so a test can
     * try to publish one and prove the model refuses.
     */
    public function unreviewed(): self
    {
        return $this->state(fn (): array => [
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);
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
