<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Review;
use Illuminate\Database\Eloquent\Collection;

/**
 * What the public is allowed to see about reviews.
 *
 * Two thresholds, both of which exist because a number shown too early is a
 * claim that misleads:
 *
 *   THREE approved before the section renders at all. A testimonials block
 *   with one quote in it advertises that almost nobody has said anything —
 *   worse than not having the section.
 *
 *   TEN approved before an aggregate rating appears. Three fives average to
 *   "5.0 out of 5", which reads as a fact about the practice and is really a
 *   fact about the sample size. Below the threshold nothing is displayed, and
 *   that is not a bug to be fixed by lowering it.
 *
 * The aggregate is COMPUTED, never stored. The 4.9 this replaced was a number
 * typed into a config file — the shape of mistake where nobody can later say
 * where it came from, because it came from nowhere.
 */
final class Reviews
{
    /**
     * @return Collection<int, Review>
     */
    public static function published(int $limit = 3): Collection
    {
        return PublicContent::approvedReviews()->take($limit);
    }

    public static function shouldDisplay(): bool
    {
        return PublicContent::approvedReviews()->count() >= Review::MINIMUM_TO_DISPLAY;
    }

    /**
     * The average rating, or null when there are not enough reviews for one to
     * mean anything.
     *
     * Null rather than zero, so a caller that forgets to check renders nothing
     * instead of "0.0 out of 5".
     */
    public static function aggregate(): ?float
    {
        $rated = PublicContent::approvedReviews()->whereNotNull('rating');

        if ($rated->count() < Review::MINIMUM_FOR_AGGREGATE) {
            return null;
        }

        return round((float) $rated->avg('rating'), 1);
    }

    public static function count(): int
    {
        return PublicContent::approvedReviews()->count();
    }
}
