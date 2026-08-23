<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;

/**
 * Staff accounts. Administrators only.
 *
 * This is the one thing the doctor cannot do, and it is deliberate rather than
 * an oversight: user management is where privilege is granted, and a role that
 * can grant itself privilege is not a role, it is a superuser with extra steps.
 * Keeping it to admin means the person who decides who may read medical
 * records is a separate decision from the person who reads them.
 */
class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    public function create(User $user): bool
    {
        return $user->hasRole('admin');
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole('admin');
    }

    /**
     * An administrator may not delete their own account.
     *
     * Not vanity — it is the only account that can restore access. Deleting
     * the last admin locks the clinic out of its own panel, and the recovery
     * is a developer with database access, which on a Sunday is nobody.
     */
    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('admin') && $user->isNot($model);
    }

    /**
     * Never. A staff account is the causer on every activity_log row it wrote;
     * destroying the row would orphan the audit trail behind real clinical
     * decisions.
     */
    public function forceDelete(User $user, User $model): bool
    {
        return false;
    }
}
