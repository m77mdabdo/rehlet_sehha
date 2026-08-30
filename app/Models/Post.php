<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use App\Support\Locales;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use LogicException;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $slug
 * @property array<string, string>|string $title
 * @property array<string, string>|string|null $excerpt
 * @property array<string, string>|string $body
 * @property string|null $cover_path
 * @property int|null $reading_minutes
 * @property int|null $category_id
 * @property Carbon|null $published_at
 * @property Carbon|null $content_updated_at
 * @property int|null $reviewed_by
 * @property Carbon|null $reviewed_at
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
        'category_id',
        'title',
        'excerpt',
        'body',
        'cover_path',
        'reading_minutes',
        'published_at',
        'reviewed_by',
        'reviewed_at',
        'is_featured',
    ];

    /** @var array<int, string> */
    public array $translatable = ['title', 'excerpt', 'body'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
            'content_updated_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'is_featured' => 'boolean',
            'reading_minutes' => 'integer',
        ];
    }

    /**
     * NO ARTICLE PUBLISHES WITHOUT A NAMED CLINICAL REVIEWER.
     *
     * Enforced here, at save, rather than in the admin form — because a rule
     * that lives in a form holds only until somebody writes a seeder, runs an
     * import, or fixes a typo in tinker. Every one of those paths goes through
     * this hook.
     *
     * WHY THIS IS NOT A STYLE RULE. These articles carry a byline. A visitor
     * reading about PCOS on a clinic's site is reading what she reasonably
     * takes to be advice from the practitioner she is about to book with, and
     * a wrong sentence is wrong in that practitioner's name and against her
     * licence. "Nobody checked it" is not a defence a licensed professional
     * can offer for something published under her own name.
     *
     * A DRAFT NEEDS NO REVIEWER. published_at is what makes an article public,
     * so it is what the requirement attaches to. Writing is unrestricted;
     * publishing is not.
     *
     * UNPUBLISHING IS ALWAYS ALLOWED. Taking something down must never be
     * blocked by the rule that governs putting it up — otherwise the fastest
     * way to remove a dangerous article is to delete the row, and the record
     * of what was said goes with it.
     */
    /**
     * The marker standing in for a sentence only the clinician can write.
     *
     * Every article on this site is drafted with its structure, framing and
     * transitions complete and its clinical content ABSENT. Where a specific
     * recommendation, a target, a quantity or an "eat X to lower Y" belongs,
     * the draft carries this marker and a one-line prompt naming what is
     * needed — so Dr. Rana answers a question rather than writing an article.
     *
     * Publishing one is the failure this guards: an article that reaches a
     * patient reading CLINICAL_INPUT — what do you tell someone at week three
     * is worse than a page that never existed, because it looks like advice
     * and is a stage direction.
     */
    public const CLINICAL_MARKER = 'CLINICAL_INPUT';

    protected static function booted(): void
    {
        static::saving(function (self $post): void {
            /*
             * Reading time, when nobody has set one. Left alone if an editor
             * typed a number: a piece with a long table reads slower than its
             * word count says, and the person who noticed that is right.
             */
            if ($post->reading_minutes === null && filled($post->getTranslation('body', 'ar', false))) {
                $post->reading_minutes = $post->estimatedReadingMinutes();
            }

            if ($post->published_at === null) {
                return;
            }

            /*
             * NO UNANSWERED CLINICAL PROMPT REACHES A READER.
             *
             * Checked in every locale, because a draft finished in Arabic and
             * forgotten in English is the likely shape of this mistake, and an
             * English reader would be the one who found it.
             */
            foreach (Locales::all() as $locale) {
                foreach (['title', 'excerpt', 'body'] as $field) {
                    $value = (string) $post->getTranslation($field, $locale, false);

                    if (str_contains($value, self::CLINICAL_MARKER)) {
                        throw new LogicException(
                            'An article cannot be published while it still asks the clinician a question. '
                            .self::CLINICAL_MARKER." is present in {$field} ({$locale}) on post "
                            .($post->slug !== '' ? $post->slug : (string) ($post->id ?? 'new')).'. '
                            .'Answer every prompt, or clear published_at to keep it as a draft.'
                        );
                    }
                }
            }

            if ($post->reviewed_by !== null && $post->reviewed_at !== null) {
                return;
            }

            $missing = [];

            if ($post->reviewed_by === null) {
                $missing[] = 'reviewed_by';
            }

            if ($post->reviewed_at === null) {
                $missing[] = 'reviewed_at';
            }

            throw new LogicException(
                'An article cannot be published without a named clinical reviewer. '
                .implode(' and ', $missing).' is null on post '
                .($post->slug !== '' ? $post->slug : (string) ($post->id ?? 'new')).'. '
                .'Clear published_at to keep it as a draft.'
            );
        });
    }

    /**
     * Published means: has a publish date, that date has passed, AND a named
     * clinician signed it off.
     *
     * The review condition is here as well as in the saving hook on purpose.
     * The hook governs writes; this governs reads. If a row ever reaches the
     * table unreviewed — a raw SQL insert, a restored backup from before this
     * rule existed, a migration that copied rows — the site still will not
     * serve it.
     *
     * @param  Builder<self>  $query
     */
    public function scopePublished(Builder $query): void
    {
        $query->whereNotNull('published_at')
            ->where('published_at', '<=', Carbon::now())
            ->whereNotNull('reviewed_by')
            ->whereNotNull('reviewed_at')
            ->orderByDesc('published_at');
    }

    /**
     * The clinician who signed this article off.
     *
     * @return BelongsTo<User, $this>
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Other articles a reader of this one might want.
     *
     * Same category first, then anything sharing a tag. Ordered by how much
     * they overlap rather than by date, because "most recent" on a clinic blog
     * surfaces whatever was written last, not whatever is closest.
     *
     * @return Collection<int, self>
     */
    public function relatedPosts(int $limit = 2): Collection
    {
        $tagIds = $this->tags->pluck('id');

        return self::query()
            ->published()
            ->whereKeyNot($this->getKey())
            ->with('category')
            ->where(function (Builder $query) use ($tagIds): void {
                $query->where('category_id', $this->category_id)
                    ->orWhereHas('tags', fn (Builder $tags) => $tags->whereIn('tags.id', $tagIds));
            })
            ->get()
            ->sortByDesc(function (self $post) use ($tagIds): int {
                $shared = $post->tags->pluck('id')->intersect($tagIds)->count();

                return ($post->category_id === $this->category_id ? 10 : 0) + $shared;
            })
            ->take($limit)
            ->values();
    }

    /**
     * Reading time, computed rather than typed.
     *
     * 180 words a minute is deliberately slower than the 200-250 usually
     * quoted: this is Arabic medical prose read by somebody who is anxious
     * about the subject, not English marketing copy skimmed on a commute.
     * Rounding up means the estimate is never optimistic.
     *
     * Only filled when nobody has set it by hand, so an editor can override
     * for a piece with a long table or a lot of headings.
     */
    public function estimatedReadingMinutes(): int
    {
        $words = str_word_count(strip_tags((string) $this->getTranslation('body', 'ar', false)), 0, 'أبتثجحخدذرزسشصضطظعغفقكلمنهوىيءآأؤإئة');

        // str_word_count is Latin-centric; for Arabic the whitespace count is
        // the honest measure.
        $words = max($words, count(preg_split('/\s+/u', trim(strip_tags((string) $this->getTranslation('body', 'ar', false)))) ?: []));

        return max(1, (int) ceil($words / 180));
    }
}
