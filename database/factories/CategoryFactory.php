<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $english = fake('en_US')->unique()->words(2, true);

        return [
            'slug' => Str::slug($english),
            'name' => ['ar' => 'تصنيف تجريبي', 'en' => Str::title($english)],
            'description' => ['ar' => 'وصف قصير للتصنيف.', 'en' => 'A short description of the category.'],
            'meta_description' => ['ar' => 'وصف للبحث.', 'en' => 'A description for search.'],
            'sort_order' => 0,
            'is_active' => true,
        ];
    }
}
