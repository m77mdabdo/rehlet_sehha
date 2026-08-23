<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Specialty;
use App\Models\User;

/**
 * Site content: the clinic's own words on the public site.
 *
 * Admin and doctor. Not reception — a receptionist's job is the calendar and
 * the telephone, and publishing to a medical website is a clinical claim even
 * when it reads like marketing.
 */
class SpecialtyPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function view(User $user, Specialty $specialty): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function update(User $user, Specialty $specialty): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function delete(User $user, Specialty $specialty): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function restore(User $user, Specialty $specialty): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Content can genuinely be destroyed — an abandoned draft is not a record
     * of anything. Administrators only, because a hard delete of a published
     * page breaks whatever links to it.
     */
    public function forceDelete(User $user, Specialty $specialty): bool
    {
        return $user->hasRole('admin');
    }
}
