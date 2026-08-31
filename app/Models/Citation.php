<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CitationConfidence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;
use Spatie\Translatable\HasTranslations;

/**
 * One reference behind one article.
 *
 * See the migration for why these are rows rather than a paragraph at the
 * bottom of the body.
 *
 * @property int $id
 * @property int $post_id
 * @property int $sort_order
 * @property array<string, string>|string $organisation
 * @property array<string, string>|string $title
 * @property int|null $year
 * @property string|null $url
 * @property CitationConfidence $confidence
 * @property string|null $note
 * @property int|null $verified_by
 * @property Carbon|null $verified_at
 */
class Citation extends Model
{
    use HasTranslations;

    /** @var list<string> */
    protected $fillable = [
        'post_id',
        'sort_order',
        'organisation',
        'title',
        'year',
        'url',
        'confidence',
        'note',
        'verified_by',
        'verified_at',
    ];

    /** @var array<int, string> */
    public array $translatable = ['organisation', 'title'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'confidence' => CitationConfidence::class,
            'verified_at' => 'datetime',
            'year' => 'integer',
            'sort_order' => 'integer',
        ];
    }

    /**
     * THE SAME GATE, FROM THE OTHER SIDE.
     *
     * Post::assertCitationsArePublishable() runs when the ARTICLE is saved,
     * which leaves an obvious hole: publish a clean article on Monday, attach
     * an unchecked reference to it on Tuesday, and the article never saves
     * again. The reference appears on a live page having passed nothing.
     *
     * So the rule is enforced wherever a citation changes as well. The two
     * hooks together mean there is no order of operations that gets an
     * unverified or low-confidence reference onto a published page.
     */
    protected static function booted(): void
    {
        static::saving(function (self $citation): void {
            $post = $citation->post;

            if ($post === null || $post->published_at === null) {
                return;
            }

            if (! $citation->confidence->isPublishable()) {
                throw new LogicException(
                    'This citation is marked '.$citation->confidence->value.', which means the draft was not '
                    .'confident it exists. It cannot be attached to the published article '.$post->describe().'. '
                    .'Delete it, and the sentence it supports.'
                );
            }

            if (! $citation->isVerified()) {
                throw new LogicException(
                    'A citation on a published article must be verified first. '
                    .'«'.$citation->getTranslation('organisation', 'en', false).'» on post '.$post->describe()
                    .' has no verified_by/verified_at. Unpublish the article, or verify this reference.'
                );
            }
        });
    }

    /**
     * @return BelongsTo<Post, $this>
     */
    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /**
     * Whoever opened the document and confirmed it says what we said it says.
     *
     * @return BelongsTo<User, $this>
     */
    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function isVerified(): bool
    {
        return $this->verified_by !== null && $this->verified_at !== null;
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeUnverified(Builder $query): void
    {
        $query->where(fn (Builder $q) => $q->whereNull('verified_by')->orWhereNull('verified_at'));
    }

    /**
     * The reference as a reader sees it: organisation, title, year.
     *
     * NO VOLUME, NO PAGE NUMBERS, NO DOI. Those were the fields most likely to
     * be invented by a draft written from memory, and they are not needed:
     * a named body, a document title and a year identify a guideline
     * unambiguously and are checkable by anybody with a search engine. A
     * fabricated DOI, by contrast, is the most convincing thing on the page.
     */
    public function reference(?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        $parts = array_filter([
            (string) $this->getTranslation('organisation', $locale),
            (string) $this->getTranslation('title', $locale),
            $this->year === null ? null : (string) $this->year,
        ], fn (string $part): bool => $part !== '');

        return implode('. ', $parts).'.';
    }
}
