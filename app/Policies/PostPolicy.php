<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Post;
use App\Models\User;

/**
 * Site content: the clinic's own words on the public site.
 *
 * Admin and doctor. Not reception — a receptionist's job is the calendar and
 * the telephone, and publishing to a medical website is a clinical claim even
 * when it reads like marketing.
 */
class PostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function view(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function update(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function delete(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function restore(User $user, Post $post): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * SIGN AN ARTICLE OFF AS CLINICALLY REVIEWED.
     *
     * Separate from update() on purpose, and narrower than it. Editing an
     * article is copywriting; marking one reviewed is a clinician putting
     * their name to a medical claim published under a licence.
     *
     * Doctor and admin only. A receptionist may not do this — not because the
     * calendar is their whole job, but because "reviewed by" has to mean
     * somebody qualified read it, and a review signed by the front desk is
     * worse than no review at all: it looks like the check happened.
     *
     * Admin is included because the practice runs with one clinician and an
     * administrator who is the same small team; if that stops being true, this
     * is the single line to narrow.
     */
    public function review(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Content can genuinely be destroyed — an abandoned draft is not a record
     * of anything. Administrators only, because a hard delete of a published
     * page breaks whatever links to it.
     */
    public function forceDelete(User $user, Post $post): bool
    {
        return $user->hasRole('admin');
    }
}
