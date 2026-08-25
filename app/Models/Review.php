<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\ReviewFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use LogicException;

/**
 * A review written by a real patient.
 *
 * TWO GATES, AND THE MODEL ENFORCES BOTH.
 *
 * The clinic may approve whatever it likes; without the patient's consent
 * nothing is publishable, and that is checked HERE rather than only in the
 * form or only in the admin. A rule that lives in a UI is a rule that holds
 * until somebody writes a seeder, a console command, or a second admin screen.
 *
 * @property int $id
 * @property string $token
 * @property int $appointment_id
 * @property int $patient_id
 * @property int|null $rating
 * @property string|null $comment
 * @property string|null $display_name
 * @property Carbon|null $invited_at
 * @property Carbon|null $submitted_at
 * @property Carbon|null $consented_at
 * @property Carbon|null $approved_at
 * @property int|null $approved_by
 * @property string|null $moderation_note
 *
 * @method static \Database\Factories\ReviewFactory factory($count = null, $state = [])
 */
class Review extends Model
{
    use FlushesPublicContentCache;

    /** @use HasFactory<ReviewFactory> */
    use HasFactory;

    /**
     * How long after a completed appointment the invitation goes out.
     *
     * Three days: long enough that the session is not still being digested,
     * short enough to be remembered. Same day reads as a shop asking before
     * you have left.
     */
    public const INVITE_AFTER_DAYS = 3;

    /**
     * How many approved reviews before an aggregate rating means anything.
     *
     * Below ten, an average is noise presented as a fact — three fives reads
     * as "5.0 out of 5" and tells a patient nothing except that very few
     * people have answered. Nothing is displayed until the number can carry
     * the weight of being displayed.
     */
    public const MINIMUM_FOR_AGGREGATE = 10;

    /**
     * How many approved reviews before the section renders at all.
     *
     * An empty or nearly-empty testimonials block is worse than none: it
     * advertises that almost nobody has said anything.
     */
    public const MINIMUM_TO_DISPLAY = 3;

    /** @var list<string> */
    protected $fillable = [
        'token',
        'appointment_id',
        'patient_id',
        'rating',
        'comment',
        'display_name',
        'invited_at',
        'submitted_at',
        'consented_at',
        'approved_at',
        'approved_by',
        'moderation_note',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'rating' => 'integer',
            'invited_at' => 'datetime',
            'submitted_at' => 'datetime',
            'consented_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        /*
         * THE RULE, AT THE LOWEST LEVEL THERE IS.
         *
         * A review may not carry an approval unless the patient consented to
         * publication. Enforced on save rather than in the form or the admin,
         * because either of those can be bypassed by the next person who
         * writes a command or a seeder — and the thing being bypassed is
         * somebody's decision about their own medical care being made public.
         *
         * A LogicException rather than a silent unset: quietly dropping the
         * approval would leave a moderator believing they had published
         * something.
         */
        static::saving(function (self $review): void {
            if ($review->approved_at !== null && $review->consented_at === null) {
                throw new LogicException(
                    'A review cannot be approved without the patient\'s consent to publish. '
                    .'consented_at is null on review '.($review->id ?? 'new').'.'
                );
            }
        });
    }

    public static function newToken(): string
    {
        // 64 characters of the same alphabet the cancel token uses. This is a
        // bearer credential: holding it lets you write as that patient.
        return Str::random(64);
    }

    /**
     * Publishable: the patient agreed AND the clinic checked.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeApproved(Builder $query): Builder
    {
        return $query
            ->whereNotNull('consented_at')
            ->whereNotNull('approved_at')
            ->whereNotNull('comment')
            ->orderByDesc('approved_at');
    }

    /**
     * Submitted but not yet moderated — the queue.
     *
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopePending(Builder $query): Builder
    {
        return $query
            ->whereNotNull('submitted_at')
            ->whereNull('approved_at')
            ->orderBy('submitted_at');
    }

    /** @return BelongsTo<Appointment, $this> */
    public function appointment(): BelongsTo
    {
        return $this->belongsTo(Appointment::class);
    }

    /** @return BelongsTo<Patient, $this> */
    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    /** @return BelongsTo<User, $this> */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPublishable(): bool
    {
        return $this->consented_at !== null
            && $this->approved_at !== null
            && $this->comment !== null;
    }
}
