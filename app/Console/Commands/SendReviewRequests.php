<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Review;
use App\Services\Notifications\AppointmentNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Invites patients to review, three days after a completed appointment.
 *
 * WHY A COMMAND AND NOT A MODEL EVENT. The trigger is elapsed TIME, not a
 * state change: the appointment is marked completed on the day, and the
 * invitation goes out three days later. Nothing happens at the moment of
 * completion, so there is nothing for an event to hook.
 *
 * IT WILL NOT INVITE TWICE. reviews.appointment_id is unique, and this only
 * considers appointments with no review row at all — so a scheduler that runs
 * twice, or a status flipped back and forth by a receptionist, produces one
 * invitation.
 *
 * IT WILL NOT INVITE A PATIENT WHO CANNOT BE REACHED. A patient with no email
 * and no phone gets a review row created but no notification attempted; the
 * row exists so the link works if it is ever handed over another way, and so
 * this command does not reconsider her every hour forever.
 */
class SendReviewRequests extends Command
{
    protected $signature = 'clinic:send-review-requests {--dry-run : List who would be invited without sending}';

    protected $description = 'Invite patients to review, three days after a completed appointment';

    /**
     * A floor on how far back to look.
     *
     * Without it, switching this feature on would email every patient the
     * practice has ever seen, all at once, about appointments they had
     * forgotten. Fourteen days is recent enough to be a reasonable thing to
     * receive.
     */
    private const LOOK_BACK_DAYS = 14;

    public function handle(AppointmentNotifier $notifier): int
    {
        $cutoff = Carbon::now()->subDays(Review::INVITE_AFTER_DAYS);
        $floor = Carbon::now()->subDays(self::LOOK_BACK_DAYS);

        $appointments = Appointment::query()
            ->where('status', AppointmentStatus::Completed)
            ->whereBetween('ends_at', [$floor, $cutoff])
            ->whereDoesntHave('review')
            ->with(['patient', 'service'])
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No appointments are due a review invitation.');

            return self::SUCCESS;
        }

        foreach ($appointments as $appointment) {
            if ($this->option('dry-run')) {
                $this->line("  would invite {$appointment->reference} ({$appointment->patient->name})");

                continue;
            }

            $review = Review::create([
                'token' => Review::newToken(),
                'appointment_id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'invited_at' => Carbon::now(),
                /*
                 * A default the patient can edit on the form. First name plus
                 * an initial, because a full name beside a description of
                 * medical care is more exposure than most people intend when
                 * they tick a box.
                 */
                'display_name' => self::defaultDisplayName((string) $appointment->patient->name),
            ]);

            /*
             * Through the notifier, never straight at the patient. It is what
             * pins the locale to the booking, writes the delivery row, and
             * handles the patient who gave no email address — all three of
             * which this command runs from cron and cannot check for itself.
             */
            $notifier->reviewRequested($appointment, $review);

            $this->line("  invited {$appointment->reference}");
        }

        $this->info(sprintf('%d invitation(s) %s.', $appointments->count(), $this->option('dry-run') ? 'would be sent' : 'queued'));

        return self::SUCCESS;
    }

    /**
     * "Rana Mohamed Salem" becomes "Rana M." — a name she is recognisable by
     * to herself, and not to a stranger searching for her.
     */
    public static function defaultDisplayName(string $name): string
    {
        $parts = preg_split('/\s+/u', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        if ($parts === []) {
            return '';
        }

        if (count($parts) === 1) {
            return $parts[0];
        }

        return $parts[0].' '.mb_substr($parts[1], 0, 1).'.';
    }
}
