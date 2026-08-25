<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use LogicException;
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
        'title',
        'excerpt',
        'body',
        'cover_path',
        'category',
        'reading_minutes',
        'published_at',
        'reviewed_by',
        'reviewed_at',
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
    protected static function booted(): void
    {
        static::saving(function (self $post): void {
            if ($post->published_at === null) {
                return;
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
}
