<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\IntakeForm;
use App\Models\User;
use App\Services\Clinical\ClinicalAccessLog;

/**
 * THE BOUNDARY THIS PANEL EXISTS TO ENFORCE.
 *
 * A receptionist schedules appointments and telephones patients. She has no
 * clinical role, and she must not read what a patient wrote about her own
 * body: the medications she takes, the conditions she lives with, why she came.
 *
 * That is not a courtesy. It is the difference between a clinic where a
 * patient can write "أنا حامل" or name a psychiatric medication in a booking
 * form, and one where she learns to leave the field blank — at which point the
 * form stops being worth having and the doctor starts consulting blind.
 *
 * ENFORCED HERE, NOT BY HIDING FIELDS. A hidden Filament field is still
 * resolved server-side and still serialised into the Livewire payload that
 * reaches the browser; anyone who opens developer tools reads it. Every path
 * to intake content — the relation manager, the infolist, the patient
 * profile's history — asks this policy, and the query itself is not run when
 * the answer is no.
 *
 * @see ClinicalAccessLog for the read audit trail.
 */
class IntakeFormPolicy
{
    /**
     * Clinical staff only. Deliberately no 'receptionist' anywhere in this file.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function view(User $user, IntakeForm $intakeForm): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Intake is written by the patient, through the booking form. Staff do not
     * create it, and there is no screen that offers to.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * The doctor may correct what she and the patient discussed. Reception
     * cannot, and neither can anyone after the consultation has happened —
     * that rule lives on the model (IntakeForm::isCorrectable) and applies to
     * staff for the same reason it applies to patients: a record read during a
     * consultation must not change afterwards.
     */
    public function update(User $user, IntakeForm $intakeForm): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Erasure is the patient's right, exercised from her own token page. Staff
     * do not erase clinical content on her behalf through the panel: doing so
     * would leave no record of who asked for it or whether she did.
     */
    public function delete(User $user, IntakeForm $intakeForm): bool
    {
        return false;
    }

    public function forceDelete(User $user, IntakeForm $intakeForm): bool
    {
        return false;
    }
}
