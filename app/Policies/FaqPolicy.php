<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Faq;
use App\Models\User;

/**
 * Site content: the clinic's own words on the public site.
 *
 * Admin and doctor. Not reception — a receptionist's job is the calendar and
 * the telephone, and publishing to a medical website is a clinical claim even
 * when it reads like marketing.
 */
class FaqPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function view(User $user, Faq $faq): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function update(User $user, Faq $faq): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function delete(User $user, Faq $faq): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function restore(User $user, Faq $faq): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Content can genuinely be destroyed — an abandoned draft is not a record
     * of anything. Administrators only, because a hard delete of a published
     * page breaks whatever links to it.
     */
    public function forceDelete(User $user, Faq $faq): bool
    {
        return $user->hasRole('admin');
    }
}
