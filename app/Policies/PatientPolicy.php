<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    /**
     * Soft delete only. The record stays, and Patient::findOrCreateByPhone()
     * revives it if the patient ever comes back.
     */
    public function delete(User $user, Patient $patient): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function restore(User $user, Patient $patient): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Nobody may force delete a patient. Not an administrator, not the doctor,
     * not through Filament, not through a bulk action.
     *
     * A patient file is a medical record. Destroying it would take the
     * appointment history with it (patient_id cascades), which removes the
     * clinical trail behind decisions the clinic has already acted on. It would
     * also free the phone number, so a later booking would silently open a
     * blank second file for the same person.
     *
     * This returns false unconditionally rather than checking a role, so that
     * adding a "superadmin" later cannot accidentally unlock it. If a genuine
     * erasure request ever arrives (a data-protection right-to-be-forgotten),
     * it should be handled by a deliberate, audited anonymisation routine that
     * scrubs identifying columns while preserving the clinical record — not by
     * a DELETE.
     */
    public function forceDelete(User $user, Patient $patient): bool
    {
        return false;
    }
}
