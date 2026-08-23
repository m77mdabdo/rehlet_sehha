<?php

declare(strict_types=1);

namespace App\Services\Clinical;

use App\Models\IntakeForm;
use Illuminate\Support\Facades\Auth;

/**
 * Records that somebody READ a patient's clinical content.
 *
 * Task 1 logs writes: who changed a record, when, and what it said before.
 * That answers "who altered this", which is the question an audit trail
 * usually exists for. It does not answer the question that actually gets asked
 * about medical records, which is "who looked at mine".
 *
 * A read log is standard practice wherever health data is held, and the reason
 * is that unauthorised READING is the realistic failure. Nobody edits a
 * stranger's intake form out of curiosity. People do open one — a colleague's,
 * a neighbour's, a public figure's — and without a read log that leaves no
 * trace at all, so the clinic could not answer the question even if asked
 * directly.
 *
 * IT LOGS THE FACT, NEVER THE CONTENT. The row says that user 3 read intake 47
 * at 10:14. It does not restate the medications, because a log that copies the
 * record it protects has doubled the number of places that record exists — and
 * activity_log is retained longer than the intake itself and is read by more
 * people.
 *
 * Cheap here: one insert on a screen a clinician opens a handful of times a
 * day, against a table that already exists and already prunes itself.
 */
class ClinicalAccessLog
{
    /**
     * @param  string  $context  where the read happened, e.g. 'appointment.intake'
     */
    public static function read(IntakeForm $intakeForm, string $context): void
    {
        $reader = Auth::user();

        if ($reader === null) {
            // No authenticated reader means this is not a panel read — a queued
            // job rendering a notification, say. Those are covered by the
            // delivery log and are not somebody looking at a record.
            return;
        }

        activity('clinical_access')
            ->performedOn($intakeForm)
            ->causedBy($reader)
            ->withProperties([
                'context' => $context,
                'appointment_id' => $intakeForm->appointment_id,
                // Recorded because "who read it" is only half the answer when
                // several people share a reception desk and one browser.
                'ip' => request()->ip(),
                // Whether there was anything to read. An erased record still
                // logs the attempt: the fact that someone went looking after
                // the patient asked for erasure is itself worth having.
                'erased' => $intakeForm->isErased(),
            ])
            ->event('read')
            ->log('read');
    }
}
