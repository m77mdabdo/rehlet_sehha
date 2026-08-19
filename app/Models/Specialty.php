<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\SpecialtyFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * A clinical area the practice works in — NOT a bookable service.
 *
 * See the specialties migration for why these are two tables. The short
 * version: a Service has a price and becomes an appointment; a Specialty is
 * what an appointment is *about*. Nothing here is purchasable, and nothing
 * here should ever grow a price column.
 *
 * @property int $id
 * @property string $slug
 * @property array<string, string>|string $name
 * @property array<string, string>|string|null $description
 * @property string $icon
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\SpecialtyFactory factory($count = null, $state = [])
 */
class Specialty extends Model
{
    use FlushesPublicContentCache;

    /** @use HasFactory<SpecialtyFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'is_active',
        'sort_order',
    ];

    /** @var array<int, string> */
    public array $translatable = ['name', 'description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
