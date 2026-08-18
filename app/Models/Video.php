<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $youtube_id
 * @property array<string, string>|string $title
 * @property array<string, string>|string|null $description
 * @property int|null $duration_seconds
 * @property string|null $category
 * @property bool $is_featured
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\VideoFactory factory($count = null, $state = [])
 */
class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    protected $fillable = [
        'youtube_id',
        'title',
        'description',
        'duration_seconds',
        'category',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    /** @var array<int, string> */
    public array $translatable = ['title', 'description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }
}
