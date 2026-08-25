<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Review;
use App\Models\User;

/**
 * Patient reviews: moderation.
 *
 * DOCTOR AND ADMIN ONLY, not reception. Deciding that a patient's words go on
 * a public medical website is a clinical and reputational judgement — the
 * moderator has to be able to recognise a diagnosis somebody has written into
 * a public box and take it out.
 *
 * NOBODY MAY CREATE ONE. A review is written by a patient through her own
 * invitation link, or it does not exist. A clinic-authored review is the exact
 * thing this whole system replaced.
 */
class ReviewPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function view(User $user, Review $review): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Never, for anybody.
     *
     * Not an oversight and not a permission to loosen: a review typed in the
     * admin is a testimonial the clinic wrote about itself, which is what the
     * invented three were.
     */
    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Review $review): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function delete(User $user, Review $review): bool
    {
        return $user->hasRole('admin');
    }
}
