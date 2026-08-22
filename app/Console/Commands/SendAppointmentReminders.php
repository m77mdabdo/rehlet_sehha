<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\Notifications\AppointmentNotifier;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * Sends the 24-hour and 1-hour reminders.
 *
 * Runs every minute from the scheduler, which on shared hosting means an
 * unsupervised cron that can overlap itself when a run is slow. The
 * correctness of this command rests entirely on how it claims work, described
 * at claim() below.
 */
class SendAppointmentReminders extends Command
{
    protected $signature = 'clinic:send-reminders';

    protected $description = 'Send 24-hour and 1-hour appointment reminders.';

    public function handle(AppointmentNotifier $notifier): int
    {
        $now = Carbon::now();

        $sent24h = $this->sweep(
            column: 'reminder_24h_sent_at',
            leadHours: 24,
            now: $now,
            send: fn (Appointment $appointment) => $notifier->reminder24h($appointment),
        );

        $sent1h = $this->sweep(
            column: 'reminder_1h_sent_at',
            leadHours: 1,
            now: $now,
            send: fn (Appointment $appointment) => $notifier->reminder1h($appointment),
        );

        $this->info(sprintf('Queued %d 24-hour and %d 1-hour reminders.', $sent24h, $sent1h));

        return self::SUCCESS;
    }

    /**
     * One reminder window.
     *
     * @param  callable(Appointment): void  $send
     */
    private function sweep(string $column, int $leadHours, Carbon $now, callable $send): int
    {
        $due = $this->due($column, $leadHours, $now)->get();

        $count = 0;

        foreach ($due as $appointment) {
            if (! $this->claim($appointment, $column, $now)) {
                // Another run took it between the SELECT and here.
                continue;
            }

            $send($appointment);
            $count++;
        }

        return $count;
    }

    /**
     * Appointments whose reminder is due and unsent.
     *
     * @return Builder<Appointment>
     */
    private function due(string $column, int $leadHours, Carbon $now): Builder
    {
        return Appointment::query()
            ->with(['patient', 'service'])
            ->whereNull($column)
            // Never remind about something that is not going to happen. Checked
            // again at send time, because a patient can cancel in the gap
            // between this query and the queue worker picking the job up.
            ->whereIn('status', [
                AppointmentStatus::Pending->value,
                AppointmentStatus::Confirmed->value,
            ])
            // The appointment is still ahead...
            ->where('starts_at', '>', $now)
            // ...and inside the reminder window.
            ->where('starts_at', '<=', $now->clone()->addHours($leadHours))
            /*
             * And the booking already existed when the window opened.
             *
             * Without this, booking an appointment for tomorrow afternoon
             * would fire the "your appointment is tomorrow" reminder within
             * the minute — the appointment IS inside the 24-hour window, it
             * just has been for no time at all. Comparing against created_at
             * asks the question that actually matters: was there a moment
             * $leadHours before this appointment at which the booking already
             * existed? If not, the patient has just been told all of this on
             * the confirmation screen and does not need reminding of it.
             */
            ->whereRaw(
                sprintf('created_at <= DATE_SUB(starts_at, INTERVAL %d HOUR)', $leadHours)
            );
    }

    /**
     * Take ownership of one reminder, or discover that someone else has.
     *
     * A conditional UPDATE — set the stamp WHERE it is still NULL — and the
     * affected-row count is the answer. The database decides, in one
     * statement, which of two overlapping cron runs owns this reminder; there
     * is no window between checking and setting for the other run to slip
     * into.
     *
     * This is why the stamp is written BEFORE the notification is queued
     * rather than after it is delivered. Writing it after would leave the row
     * claimable for the whole life of the queue job, which is exactly the
     * period during which the next minute's cron will look at it again.
     *
     * The cost of this ordering is that a reminder lost by a crashing worker
     * is not retried by the next sweep. That is the right trade: the
     * alternative risks sending the same patient the same reminder twice,
     * and a duplicate reminder erodes trust in every message the clinic
     * sends, while a missed one is invisible.
     *
     * PUBLIC so it can be tested directly. Two sequential runs of this command
     * prove nothing about concurrency — the second run's SELECT already
     * excludes the stamped row, so the test passes even with the claim
     * removed. The only way to exercise the actual race is to call this twice
     * on a row two overlapping runs both selected, which is what
     * AppointmentReminderTest does.
     */
    public function claim(Appointment $appointment, string $column, Carbon $now): bool
    {
        return Appointment::query()
            ->whereKey($appointment->getKey())
            ->whereNull($column)
            ->update([$column => $now]) === 1;
    }
}
