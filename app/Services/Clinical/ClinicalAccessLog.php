<?php

declare(strict_types=1);

namespace App\Services\Clinical;

use App\Models\ActivityLog;
use App\Models\IntakeForm;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Records who LOOKED at a patient's clinical content — and who tried to.
 *
 * Task 1 logs writes: who changed a record, when, and what it said before.
 * That answers "who altered this", which is the question an audit trail
 * usually exists for. It does not answer the question that actually gets asked
 * about medical records, which is "who looked at mine".
 *
 * A read log is standard wherever health data is held, and the reason is that
 * unauthorised READING is the realistic failure. Nobody edits a stranger's
 * intake form out of curiosity. People do open one — a colleague's, a
 * neighbour's, a public figure's — and without a read log that leaves no trace
 * at all, so the clinic could not answer the question even if asked directly.
 *
 * IT LOGS THE FACT, NEVER THE CONTENT. The row says that user 3 read intake 47
 * at 10:14. It does not restate the medications, because a log that copies the
 * record it protects has doubled the number of places that record exists — and
 * activity_log is retained longer than the intake itself and is read by more
 * people. Same discipline as the notification delivery log.
 */
class ClinicalAccessLog
{
    public const LOG_NAME = 'clinical_access';

    public const EVENT_READ = 'read';

    public const EVENT_DENIED = 'denied';

    /**
     * How long the same person reading the same record counts as one look.
     *
     * A doctor working through a consultation refreshes the page, switches
     * tabs, comes back, and Livewire remounts the component each time. Five
     * separate rows for one sitting is not five reads — it is one, recorded
     * badly, and it buries the rows that matter.
     *
     * FIVE MINUTES, chosen for what it does at each end: long enough to absorb
     * a page reload, a tab switch and a remount during a single sitting with a
     * patient; short enough that opening the same record again after lunch is
     * recorded separately, which is exactly what somebody auditing would want
     * to see. A window of an hour would hide a second visit; a window of
     * thirty seconds would not survive a slow page.
     *
     * Deduplication applies to PERMITTED reads only. See denied() for why.
     */
    private const DEDUPE_WINDOW_MINUTES = 5;

    /**
     * A permitted read.
     *
     * @param  string  $context  where the read happened, e.g. 'appointment.intake'
     */
    public static function read(IntakeForm $intakeForm, string $context): void
    {
        /** @var User|null $reader */
        $reader = Auth::user();

        if ($reader === null) {
            /*
             * No authenticated reader means this is not somebody looking at a
             * record — a queued job rendering a notification, say. Those are
             * covered by the delivery log.
             */
            return;
        }

        if (self::recentlyLogged($intakeForm, (int) $reader->getKey(), $context)) {
            return;
        }

        activity(self::LOG_NAME)
            ->performedOn($intakeForm)
            ->causedBy($reader)
            ->withProperties(self::properties($intakeForm, $context))
            ->event(self::EVENT_READ)
            ->log(self::EVENT_READ);
    }

    /**
     * A REFUSED attempt to read.
     *
     * Logged, and deliberately not deduplicated.
     *
     * A denied attempt on a medical record is more interesting than a
     * permitted one, not less. A permitted read repeated is the same person
     * doing the same job; a denied read repeated is somebody who was told no
     * and tried again, and the repetition IS the finding. Collapsing those
     * into one row would delete the only evidence that anyone probed.
     *
     * This fires where a refusal is a deliberate act — a request for the
     * clinical component itself — not on every page render where the panel
     * merely asks "should this tab exist for this user". A receptionist
     * opening an ordinary appointment is doing her job, and logging that as a
     * denial would fill the table with rows meaning nothing.
     */
    public static function denied(IntakeForm $intakeForm, string $context): void
    {
        /** @var User|null $reader */
        $reader = Auth::user();

        activity(self::LOG_NAME)
            ->performedOn($intakeForm)
            ->causedBy($reader)
            ->withProperties(self::properties($intakeForm, $context) + [
                // Recorded explicitly so the row is legible without having to
                // know which roles could read at the time it was written.
                'roles' => $reader?->getRoleNames()->all() ?? [],
            ])
            ->event(self::EVENT_DENIED)
            ->log(self::EVENT_DENIED);
    }

    /**
     * Facts about the access. Never about the record.
     *
     * @return array<string, mixed>
     */
    private static function properties(IntakeForm $intakeForm, string $context): array
    {
        return [
            'context' => $context,
            'appointment_id' => $intakeForm->appointment_id,
            // Recorded because "who read it" is only half the answer when
            // several people share a reception desk and one browser.
            'ip' => request()->ip(),
            /*
             * Whether there was anything to read. An erased record still logs
             * the access: somebody going looking AFTER the patient asked for
             * erasure is itself worth having.
             */
            'erased' => $intakeForm->isErased(),
        ];
    }

    /**
     * Has this reader already been recorded looking at this record just now?
     */
    private static function recentlyLogged(IntakeForm $intakeForm, int $readerId, string $context): bool
    {
        return ActivityLog::query()
            ->where('log_name', self::LOG_NAME)
            ->where('event', self::EVENT_READ)
            ->where('subject_type', IntakeForm::class)
            ->where('subject_id', $intakeForm->getKey())
            ->where('causer_id', $readerId)
            ->where('created_at', '>=', Carbon::now()->subMinutes(self::DEDUPE_WINDOW_MINUTES))
            /*
             * Context is part of the key: the same doctor reading the same
             * intake from the appointment screen and again from the patient's
             * history has looked at it twice, in two places, and both are
             * worth having. Only a repeat of the SAME view is collapsed.
             */
            ->where('properties->context', $context)
            ->exists();
    }
}
