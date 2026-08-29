<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\User;
use App\Models\WorkingHour;

/**
 * THE SCHEDULE IS CLINICAL CAPACITY, NOT SITE CONTENT.
 *
 * Every row here decides which slots the availability engine offers, which
 * hours the JSON-LD advertises, and what the footer tells a patient. Widening
 * a window puts appointments in the diary; narrowing one takes them out of
 * reach. That is the practitioner's own working life.
 *
 * Doctor and admin. NOT reception — a receptionist manages the calendar within
 * the hours she is given, and deciding what those hours are is a different
 * decision made by a different person.
 */
class WorkingHourPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function view(User $user, WorkingHour $workingHour): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function update(User $user, WorkingHour $workingHour): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Deleting a window is how a day comes off the schedule entirely, so it is
     * allowed — but the row is the only record of what the hours were, and
     * deactivating is almost always what was meant. The table offers both.
     */
    public function delete(User $user, WorkingHour $workingHour): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function restore(User $user, WorkingHour $workingHour): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function forceDelete(User $user, WorkingHour $workingHour): bool
    {
        return $user->hasRole('admin');
    }
}
