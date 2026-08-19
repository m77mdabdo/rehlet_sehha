<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property array<string, string>|string $title
 * @property array<string, string>|string|null $excerpt
 * @property array<string, string>|string $body
 * @property string|null $cover_path
 * @property array<string, string>|string|null $category
 * @property int|null $reading_minutes
 * @property Carbon|null $published_at
 * @property bool $is_featured
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\PostFactory factory($count = null, $state = [])
 */
class Post extends Model
{
    use FlushesPublicContentCache;

    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'title',
        'excerpt',
        'body',
        'cover_path',
        'category',
        'reading_minutes',
        'published_at',
        'is_featured',
    ];

    /** @var array<int, string> */
    public array $translatable = ['title', 'excerpt', 'body', 'category'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'is_featured' => 'boolean',
            'reading_minutes' => 'integer',
        ];
    }

    /**
     * Published means: has a publish date, and that date has passed. A future
     * published_at is a scheduled post and must stay hidden.
     *
     * @param  Builder<self>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->orderByDesc('published_at');
    }
}
