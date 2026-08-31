<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\CitationConfidence;
use App\Models\Concerns\FlushesPublicContentCache;
use App\Support\ArticleBody;
use App\Support\Locales;
use Database\Factories\PostFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
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

    /**
     * The marker standing in for a sentence only SHE can say.
     *
     * Distinct from CLINICAL_INPUT, and the distinction is the whole point.
     *
     * CLINICAL_INPUT is a gap in the medicine: a dose, a target, a directed
     * instruction. It could in principle be filled by any competent clinician
     * working from the same guidelines.
     *
     * PRACTITIONER_VOICE is a gap in the AUTHORSHIP. It marks a first-person
     * clinical observation — "what I see in patients who…", "the sentence I
     * actually use" — and those cannot be written by anybody else, because
     * they are not claims about the literature. They are claims about what one
     * named clinician has personally observed across her own patients.
     *
     * Writing one and attributing it to her would be inventing a professional
     * memory and signing her name to it. It is also, unhappily, the easiest
     * thing in the world to do convincingly, which is exactly why it needs a
     * marker and a gate rather than a rule in a style guide.
     *
     * These sentences are also what makes the blog hers rather than a
     * competent translation of somebody else's. They are worth waiting for.
     */
    public const PRACTITIONER_MARKER = 'PRACTITIONER_VOICE';

    /**
     * Both markers, for the places that treat them identically.
     *
     * @return list<string>
     */
    public static function markers(): array
    {
        return [self::CLINICAL_MARKER, self::PRACTITIONER_MARKER];
    }

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

                    foreach (self::markers() as $marker) {
                        if (str_contains($value, $marker)) {
                            throw new LogicException(
                                $marker === self::CLINICAL_MARKER
                                    ? 'An article cannot be published while it still asks the clinician a question. '
                                        .$marker." is present in {$field} ({$locale}) on post ".$post->describe().'. '
                                        .'Answer every prompt, or clear published_at to keep it as a draft.'
                                    : 'An article cannot be published while a sentence in it is still waiting for '
                                        .'the practitioner to say it herself. '
                                        .$marker." is present in {$field} ({$locale}) on post ".$post->describe().'. '
                                        .'Nobody else may write these — see Post::PRACTITIONER_MARKER.'
                            );
                        }
                    }
                }
            }

            $missing = [];

            if ($post->reviewed_by === null) {
                $missing[] = 'reviewed_by';
            }

            if ($post->reviewed_at === null) {
                $missing[] = 'reviewed_at';
            }

            if ($missing !== []) {
                throw new LogicException(
                    'An article cannot be published without a named clinical reviewer. '
                    .implode(' and ', $missing).' is null on post '.$post->describe().'. '
                    .'Clear published_at to keep it as a draft.'
                );
            }

            $post->assertCitationsArePublishable();
        });
    }

    /**
     * How this post is named in an exception a human has to act on.
     *
     * The slug, because that is what the person reading the error will search
     * for. An id is only useful to somebody already in the database.
     */
    public function describe(): string
    {
        return $this->slug !== '' ? $this->slug : (string) ($this->id ?? 'new');
    }

    /**
     * @return HasMany<Citation, $this>
     */
    public function citations(): HasMany
    {
        return $this->hasMany(Citation::class)->orderBy('sort_order');
    }

    /**
     * NO ARTICLE PUBLISHES ON A CITATION NOBODY HAS CHECKED.
     *
     * The third gate, and the one specific to how these articles were made.
     * They were drafted without internet access, from memory, and they report
     * what the ADA, WHO, NICE, ESPEN and Cochrane say. That is a legitimate
     * way to draft and an indefensible way to publish: a fabricated reference
     * in a medical article under a licensed practitioner's name is worse than
     * no article at all, because it borrows an institution's authority to
     * make a claim that institution never made.
     *
     * "Somebody will check them before we go live" is not a control. This is.
     *
     * TWO SEPARATE FAILURES, kept separate because the fix differs:
     *
     *   - A LOW-confidence citation was never eligible for checking. It should
     *     not have been written down, and the answer is to delete it and the
     *     sentence it supports, not to go looking for it.
     *   - An UNVERIFIED citation is fine and simply is not done yet. Somebody
     *     with library access has to open the document and confirm it says
     *     what we said it says.
     *
     * Called from the saving hook, and again from Citation's own hook, so a
     * citation cannot be attached to an already-published article after the
     * fact.
     */
    public function assertCitationsArePublishable(): void
    {
        if ($this->exists === false) {
            // A row that does not exist yet cannot have citations pointing at
            // it. There is nothing to check, and querying would match every
            // orphan in the table.
            return;
        }

        $citations = $this->citations()->get();

        $unpublishable = $citations->filter(
            fn (Citation $citation): bool => ! $citation->confidence->isPublishable()
        );

        if ($unpublishable->isNotEmpty()) {
            throw new LogicException(
                'An article cannot be published while it cites something the draft was not sure exists. '
                .$unpublishable->count().' citation(s) on post '.$this->describe().' are marked '
                .CitationConfidence::Low->value.': '
                .$unpublishable->map(fn (Citation $c): string => (string) $c->getTranslation('title', 'en', false))->implode('; ')
                .'. Delete them, and the sentences they support, rather than publishing around them.'
            );
        }

        $unverified = $citations->filter(fn (Citation $citation): bool => ! $citation->isVerified());

        if ($unverified->isNotEmpty()) {
            throw new LogicException(
                'An article cannot be published on citations nobody has checked. '
                .$unverified->count().' of '.$citations->count().' citation(s) on post '.$this->describe()
                .' have no verified_by/verified_at: '
                .$unverified->map(fn (Citation $c): string => (string) $c->getTranslation('organisation', 'en', false))->implode('; ')
                .'. See docs/content/citations-to-verify.md.'
            );
        }
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
            /*
             * And not one unchecked reference on it.
             *
             * The read-side half of assertCitationsArePublishable(). If a row
             * ever reaches the table in a state the write hook would have
             * refused — a raw insert, a restored backup taken before this rule
             * existed, a migration that copied rows — the site still will not
             * serve it.
             */
            ->whereDoesntHave('citations', function (Builder $citations): void {
                $citations->whereNull('verified_by')
                    ->orWhereNull('verified_at')
                    ->orWhere('confidence', CitationConfidence::Low->value);
            })
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
        /*
         * The link syntax is stripped first, so [[booking|احجزي دلوقتي]]
         * counts as the two words a reader sees rather than the four the
         * column stores. Otherwise every internal link would inflate the
         * reading estimate, and an article full of them would claim to take
         * a minute longer than it does.
         */
        $text = ArticleBody::plain(strip_tags((string) $this->getTranslation('body', 'ar', false)));

        /*
         * WHITESPACE, NOT str_word_count(). THIS USED TO BE WRONG.
         *
         * str_word_count() is byte-based and Latin-centric. Handed UTF-8
         * Arabic it splits multi-byte characters and reports far more "words"
         * than exist — measured at 1,965 against a true 1,270 on one of these
         * articles, an inflation of about 55%. The old code took the LARGER of
         * the two counts, which meant the inflated figure always won and every
         * Arabic article claimed to take half again as long to read as it does.
         *
         * It went unnoticed while the drafts were a few hundred words and the
         * error was a minute. At 1,200 words it is four.
         *
         * A whitespace-delimited count is the honest measure for Arabic, and
         * this method only ever reads the Arabic body.
         */
        $words = count(preg_split('/\s+/u', trim($text)) ?: []);

        return max(1, (int) ceil($words / 180));
    }
}
