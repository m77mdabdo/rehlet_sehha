<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Appointment;
use App\Models\User;

/**
 * Appointments are the shared surface: all three roles work here.
 *
 * A receptionist schedules, confirms, moves and cancels — that is the job.
 * What she must not reach is the intake attached to the appointment, and that
 * boundary lives in IntakeFormPolicy rather than here, so that "may I open
 * this booking" and "may I read her medical history" stay separate questions
 * with separate answers.
 */
class AppointmentPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    public function view(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    public function update(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    /**
     * The clinical outcome actions — completing a consultation, recording a
     * no-show — are the practitioner's to make. A receptionist may cancel and
     * reschedule, because those are scheduling facts she is told over the
     * phone; she may not record that a consultation happened.
     */
    public function complete(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function markNoShow(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function confirm(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    public function cancel(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    public function reschedule(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor', 'receptionist']);
    }

    /**
     * Soft delete only, and not for reception.
     *
     * Cancelling is the operation reception needs; it releases the slot and
     * leaves the record. Deleting removes the clinic's evidence that the hour
     * was ever used.
     */
    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function restore(User $user, Appointment $appointment): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Nobody, ever. Same reasoning as PatientPolicy::forceDelete(): the row is
     * the clinic's record that an hour was consumed, and destroying it takes
     * the intake with it by cascade.
     */
    public function forceDelete(User $user, Appointment $appointment): bool
    {
        return false;
    }
}
