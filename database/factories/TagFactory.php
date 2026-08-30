<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Tag>
 */
class TagFactory extends Factory
{
    protected $model = Tag::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $english = fake('en_US')->unique()->word();

        return [
            'slug' => Str::slug($english),
            'name' => ['ar' => 'وسم', 'en' => Str::title($english)],
        ];
    }
}
