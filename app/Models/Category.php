<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Translatable\HasTranslations;

/**
 * An editorial grouping for articles: one per article, its own index page.
 *
 * @property int $id
 * @property string $slug
 * @property array<string, string>|string $name
 * @property array<string, string>|string|null $description
 * @property array<string, string>|string|null $meta_description
 * @property int $sort_order
 * @property bool $is_active
 *
 * @method static \Database\Factories\CategoryFactory factory($count = null, $state = [])
 */
class Category extends Model
{
    use FlushesPublicContentCache;

    /** @use HasFactory<CategoryFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    protected $fillable = ['slug', 'name', 'description', 'meta_description', 'sort_order', 'is_active'];

    /** @var array<int, string> */
    public array $translatable = ['name', 'description', 'meta_description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return ['sort_order' => 'integer', 'is_active' => 'boolean'];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }
}
