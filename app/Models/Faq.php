<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\FaqFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property array<string, string>|string $question
 * @property array<string, string>|string $answer
 * @property int $sort_order
 * @property bool $is_active
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\FaqFactory factory($count = null, $state = [])
 */
class Faq extends Model
{
    use FlushesPublicContentCache;

    /** @use HasFactory<FaqFactory> */
    use HasFactory;

    use HasTranslations;

    protected $table = 'faqs';

    /** @var list<string> */
    protected $fillable = [
        'question',
        'answer',
        'category',
        'sort_order',
        'is_active',
    ];

    /**
     * The general questions, which is what the homepage shows.
     *
     * Buying questions live on the packages page and are deliberately not
     * mixed in here: someone reading the homepage has not decided to buy
     * anything yet, and a cancellation policy answered before she knows what
     * the clinic does is an answer to a question she has not asked.
     */
    public const CATEGORY_GENERAL = 'general';

    /** Questions about purchasing a package: switching, paying, cancelling. */
    public const CATEGORY_BUYING = 'buying';

    /** @var array<int, string> */
    public array $translatable = ['question', 'answer'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }
}
