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

        $notifier->dailySchedule($startOfDay, $appointments);

        $this->info(sprintf(
            'Queued the daily schedule for %s with %d appointment(s).',
            $startOfDay->toDateString(),
            $appointments->count(),
        ));

        return self::SUCCESS;
    }
}
