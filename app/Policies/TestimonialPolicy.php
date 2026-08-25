<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Testimonial;
use App\Models\User;

/**
 * Site content: the clinic's own words on the public site.
 *
 * Admin and doctor. Not reception — a receptionist's job is the calendar and
 * the telephone, and publishing to a medical website is a clinical claim even
 * when it reads like marketing.
 */
class TestimonialPolicy
{
    /*
     * NOBODY MAY DO ANYTHING WITH A TESTIMONIAL ANY MORE.
     *
     * The three that used to live here were written by the clinic and read
     * like patients. They are gone, and this table takes no more: the only
     * quotes the site publishes now are reviews a patient wrote herself and
     * consented to publish — see App\Models\Review, which refuses to approve
     * anything without that consent.
     *
     * The Filament resource that used to edit this table has been removed for
     * the same reason. It carried a Create page, which is precisely the door
     * an invented testimonial walks through, and it was labelled «رأي» — the
     * same word as a real review, one menu item away from it.
     *
     * The model, table and factory are left in place because tests and the old
     * schema still reference them. They hold no rows and nothing renders them.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    public function view(User $user, Testimonial $testimonial): bool
    {
        return false;
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function update(User $user, Testimonial $testimonial): bool
    {
        return false;
    }

    public function delete(User $user, Testimonial $testimonial): bool
    {
        return false;
    }

    public function restore(User $user, Testimonial $testimonial): bool
    {
        return false;
    }

    /**
     * Content can genuinely be destroyed — an abandoned draft is not a record
     * of anything. Administrators only, because a hard delete of a published
     * page breaks whatever links to it.
     */
    public function forceDelete(User $user, Testimonial $testimonial): bool
    {
        return false;
    }
}
