<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Services\Notifications\AppointmentNotifier;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Mails the clinic the day's appointments at 07:00 Cairo.
 *
 * The day is a CAIRO day, not a UTC one. Appointments are stored in UTC, so
 * "today" has to be resolved by converting the clinic's local midnight-to-
 * midnight into instants and querying on those — taking whereDate() against a
 * UTC column would put a 01:00 Cairo appointment on the previous day for half
 * the year and produce a schedule that silently drops the first appointment.
 */
class SendDailySchedule extends Command
{
    protected $signature = 'clinic:send-daily-schedule';

    protected $description = "Mail the clinic today's appointments.";

    public function handle(AppointmentNotifier $notifier): int
    {
        $zone = config('clinic.timezone');

        $startOfDay = Carbon::now($zone)->startOfDay();
        $endOfDay = $startOfDay->clone()->endOfDay();

        $appointments = Appointment::query()
            ->with(['patient', 'service', 'staff'])
            /*
             * countsTowardWorkload(), not holdingSlot(): this is a list of what
             * the practitioner is doing today, and a cancelled appointment is
             * not on it. No-shows are excluded too — at 07:00 nothing has been
             * marked a no-show yet, but a rerun later in the day should not
             * start listing them as work still to come.
             */
            ->countsTowardWorkload()
            ->whereBetween('starts_at', [$startOfDay->clone()->utc(), $endOfDay->clone()->utc()])
            ->orderBy('starts_at')
            ->get();

        /*
         * TOMORROW's unreachable patients — the call list.
         *
         * A patient who booked without an email address receives nothing: no
         * confirmation, no reminder the day before, no reminder an hour
         * before. That is a real gap and it is not one software can close, so
         * it is handed to the people who can: whoever is on reception gets a
         * list, the morning before, of exactly who needs a telephone call.
         *
         * Tomorrow rather than today on purpose. A call at 07:00 about an
         * appointment at 09:00 the same morning is too late to be a reminder
         * and too early to be useful — the patient has either set out or she
         * has not. A day's notice is what makes the call worth making.
         */
        $tomorrow = $startOfDay->clone()->addDay();

        $callList = Appointment::query()
            ->with(['patient', 'service'])
            ->countsTowardWorkload()
            ->whereBetween('starts_at', [
                $tomorrow->clone()->utc(),
                $tomorrow->clone()->endOfDay()->utc(),
            ])
            ->orderBy('starts_at')
            ->get()
            // Filtered in PHP rather than SQL: reachability is derived from the
            // patient record (see App\Enums\ContactPreference for why it is
            // not a column), and a day's appointments is a handful of rows.
            ->filter(fn (Appointment $appointment): bool => ! $appointment->isReachableByEmail())
            ->values();

        $notifier->dailySchedule($startOfDay, $appointments, $callList);

        $this->info(sprintf(
            'Queued the daily schedule for %s with %d appointment(s) and %d patient(s) to call.',
            $startOfDay->toDateString(),
            $appointments->count(),
            $callList->count(),
        ));

        return self::SUCCESS;
    }
}
