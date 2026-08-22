<?php

declare(strict_types=1);

use App\Console\Commands\SendAppointmentReminders;
use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\Service;
use App\Models\User;
use App\Notifications\AppointmentReminder1h;
use App\Notifications\AppointmentReminder24h;
use App\Notifications\DailySchedule;
use Carbon\CarbonImmutable;
use Database\Seeders\DoctorUserSeeder;
use Database\Seeders\RoleSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;

/**
 * The reminder sweep, and the thing it must never do twice.
 *
 * On shared hosting the scheduler is an unsupervised cron running every
 * minute. Runs overlap when one is slow. The correctness of reminders rests
 * entirely on the database claim in SendAppointmentReminders::claim(), and
 * these tests exist to hold it to that.
 */
beforeEach(function () {
    $this->seed(RoleSeeder::class);
    $this->seed(DoctorUserSeeder::class);
    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
});

afterEach(function () {
    CarbonImmutable::setTestNow();
    Carbon::setTestNow();
});

/**
 * Freeze the clock at a Cairo wall-clock time.
 */
function freezeAt(string $cairoTime): CarbonImmutable
{
    $now = CarbonImmutable::parse($cairoTime, 'Africa/Cairo');

    CarbonImmutable::setTestNow($now);
    Carbon::setTestNow($now);

    return $now;
}

/**
 * An appointment at a given instant, booked far enough ahead that both
 * reminder windows apply.
 */
function remindableAppointment(
    CarbonImmutable $startsAt,
    ?CarbonImmutable $createdAt = null,
    AppointmentStatus $status = AppointmentStatus::Confirmed,
): Appointment {
    $service = Service::active()->firstOrFail();
    $staff = User::query()->firstOrFail();

    $appointment = Appointment::factory()->create([
        'service_id' => $service->id,
        'staff_id' => $staff->id,
        'starts_at' => $startsAt->utc(),
        'ends_at' => $startsAt->utc()->addMinutes($service->duration_minutes),
        'status' => $status,
        'mode' => AppointmentMode::Online,
        'locale' => 'ar',
    ]);

    $appointment->patient->forceFill(['email' => 'patient@example.com'])->save();

    // created_at decides whether a reminder window ever "opened" while the
    // booking existed — see the command's due() query.
    $appointment->forceFill([
        'created_at' => ($createdAt ?? $startsAt->subDays(3))->utc(),
    ])->saveQuietly();

    return $appointment->fresh();
}

function runReminders(): void
{
    Artisan::call('clinic:send-reminders');
}

/*
|------------------------------------------------------------------------------
| Windows
|------------------------------------------------------------------------------
*/

it('does not remind before the window opens', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    remindableAppointment($now->addHours(30));

    runReminders();

    // Thirty hours out is not "tomorrow", and it is certainly not "in an hour".
    Notification::assertNothingSent();
});

it('sends the 24-hour reminder once the appointment is inside a day', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    $appointment = remindableAppointment($now->addHours(30));

    // Seven hours later the appointment is 23 hours away.
    freezeAt('2026-06-08 13:00:00');

    runReminders();

    Notification::assertSentOnDemandTimes(AppointmentReminder24h::class, 1);
    Notification::assertNotSentTo(new AnonymousNotifiable, AppointmentReminder1h::class);

    expect($appointment->fresh()->reminder_24h_sent_at)->not->toBeNull();
    expect($appointment->fresh()->reminder_1h_sent_at)->toBeNull();
});

it('sends the 1-hour reminder in the last hour', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    $appointment = remindableAppointment($now->addHours(30));

    // Thirty minutes before it starts.
    freezeAt('2026-06-09 11:30:00');

    runReminders();

    // Both are due by now: the 24-hour window is still open (the appointment
    // has not started), and this is the first sweep to see either.
    Notification::assertSentOnDemandTimes(AppointmentReminder1h::class, 1);

    expect($appointment->fresh()->reminder_1h_sent_at)->not->toBeNull();
});

it('never reminds about an appointment that has already started', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    remindableAppointment($now->addHours(30));

    // One minute after it began.
    freezeAt('2026-06-09 12:01:00');

    runReminders();

    Notification::assertNothingSent();
});

it('does not send a 24-hour reminder for a booking made inside the window', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');

    /*
     * Booked three hours before the appointment. There was never a moment 24
     * hours ahead at which this booking existed, so "your appointment is
     * tomorrow" would arrive seconds after the confirmation screen said the
     * same thing — about an appointment later today.
     */
    $appointment = remindableAppointment(
        startsAt: $now->addHours(3),
        createdAt: $now,
    );

    runReminders();

    Notification::assertNotSentTo(new AnonymousNotifiable, AppointmentReminder24h::class);
    expect($appointment->fresh()->reminder_24h_sent_at)->toBeNull();

    // The 1-hour reminder still applies: by then the booking is two hours old
    // and the patient has moved on to something else.
    freezeAt('2026-06-08 08:30:00');
    runReminders();

    Notification::assertSentOnDemandTimes(AppointmentReminder1h::class, 1);
});

/*
|------------------------------------------------------------------------------
| Cancelled appointments
|------------------------------------------------------------------------------
*/

it('never reminds about a cancelled appointment', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    $appointment = remindableAppointment($now->addHours(30), status: AppointmentStatus::Cancelled);

    freezeAt('2026-06-08 13:00:00');
    runReminders();

    freezeAt('2026-06-09 11:30:00');
    runReminders();

    Notification::assertNothingSent();
    expect($appointment->fresh()->reminder_24h_sent_at)->toBeNull();
});

it('drops a reminder whose appointment was cancelled after it was queued', function () {
    /*
     * The gap this closes: the command selects, claims and queues; the worker
     * picks the job up a minute later. A patient can cancel inside that
     * window — and the reminder is itself what prompts many cancellations.
     *
     * "Your appointment is tomorrow", arriving after she cancelled, reads as
     * the clinic having lost her cancellation.
     */
    $now = freezeAt('2026-06-08 06:00:00');
    $appointment = remindableAppointment($now->addHours(30));

    $log = NotificationLog::factory()->create([
        'appointment_id' => $appointment->id,
        'template' => 'reminder_24h',
        'status' => NotificationLog::STATUS_QUEUED,
        'sent_at' => null,
    ]);

    $notification = new AppointmentReminder24h($appointment);
    $notification->setDeliveryLogId($log->id);

    // Cancelled between dispatch and delivery. The queued notification
    // refetches the appointment, so the status it sees here is current.
    $appointment->cancel('changed my mind');

    $fresh = new AppointmentReminder24h($appointment->fresh());
    $fresh->setDeliveryLogId($log->id);

    expect($fresh->shouldSend(new AnonymousNotifiable, 'mail'))->toBeFalse();

    // Closed as skipped rather than left at queued, which would be
    // indistinguishable from a worker that died.
    expect($log->fresh()->status)->toBe(NotificationLog::STATUS_SKIPPED);
    expect($log->fresh()->error)->toContain('cancelled');
});

/*
|------------------------------------------------------------------------------
| Idempotency
|------------------------------------------------------------------------------
*/

it('does not resend when a later cron run sees the same appointment', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    remindableAppointment($now->addHours(30));

    freezeAt('2026-06-08 13:00:00');

    /*
     * Two runs, one after the other — the ordinary case, where the second
     * run's query simply no longer matches the stamped row.
     *
     * Note what this does NOT prove. Run sequentially, the SELECT does all the
     * work: whereNull($column) already excludes the row, so this test passes
     * even with the conditional UPDATE in claim() replaced by an unconditional
     * one. Overlapping runs are a different problem and are covered below.
     */
    runReminders();
    runReminders();

    Notification::assertSentOnDemandTimes(AppointmentReminder24h::class, 1);
});

it('lets only one of two overlapping runs claim the same reminder', function () {
    $now = freezeAt('2026-06-08 06:00:00');
    $appointment = remindableAppointment($now->addHours(30));

    freezeAt('2026-06-08 13:00:00');

    /*
     * The real race, which sequential runs cannot reproduce.
     *
     * Two cron runs overlap when one is slow. Both execute their SELECT before
     * either has written a stamp, so both hold the same appointment and both
     * believe it needs reminding. What separates them is the conditional
     * UPDATE: set the stamp WHERE it is still NULL, and read the affected-row
     * count. The database arbitrates in a single statement, so there is no
     * window between checking and setting for the other run to slip into.
     *
     * Calling claim() twice on one row is exactly that situation.
     */
    $command = app(SendAppointmentReminders::class);
    $stamp = Carbon::now();

    expect($command->claim($appointment, 'reminder_24h_sent_at', $stamp))->toBeTrue();

    // The second run lost. It must send nothing.
    expect($command->claim($appointment, 'reminder_24h_sent_at', $stamp))->toBeFalse();

    // And the winner's stamp is the one that stands.
    expect($appointment->fresh()->reminder_24h_sent_at)->not->toBeNull();
});

it('sends one reminder when an overlapping run interleaves with a full sweep', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    $appointment = remindableAppointment($now->addHours(30));

    freezeAt('2026-06-08 13:00:00');

    /*
     * Run A gets as far as selecting the appointment and then stalls. Run B
     * starts, completes the whole sweep, and queues the reminder. Run A wakes
     * up and tries to claim the row it is still holding.
     */
    $stalledRunHolds = $appointment;

    runReminders(); // Run B, start to finish.

    $command = app(SendAppointmentReminders::class);

    expect($command->claim($stalledRunHolds, 'reminder_24h_sent_at', Carbon::now()))
        ->toBeFalse('The stalled run must discover that the reminder is already claimed.');

    Notification::assertSentOnDemandTimes(AppointmentReminder24h::class, 1);
});

it('sends exactly one reminder across many sweeps over the whole window', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    remindableAppointment($now->addHours(30));

    /*
     * The scheduler runs this every minute. Over the twenty-three hours the
     * 24-hour window stays open that is well over a thousand sweeps, and every
     * one of them re-runs the same query. The stamp is what makes that safe.
     */
    foreach (['13:00:00', '13:01:00', '14:00:00', '20:00:00'] as $time) {
        freezeAt('2026-06-08 '.$time);
        runReminders();
    }

    foreach (['2026-06-09 06:00:00', '2026-06-09 11:00:00', '2026-06-09 11:59:00'] as $moment) {
        freezeAt($moment);
        runReminders();
    }

    Notification::assertSentOnDemandTimes(AppointmentReminder24h::class, 1);
    Notification::assertSentOnDemandTimes(AppointmentReminder1h::class, 1);
});

it('claims the reminder before queueing it, not after delivery', function () {
    Notification::fake();

    $now = freezeAt('2026-06-08 06:00:00');
    $appointment = remindableAppointment($now->addHours(30));

    freezeAt('2026-06-08 13:00:00');
    runReminders();

    /*
     * The stamp is written by the sweep itself. Were it written on delivery
     * instead, the row would stay claimable for the entire life of the queue
     * job — which is precisely the window in which the next minute's cron
     * looks at it again.
     */
    expect($appointment->fresh()->reminder_24h_sent_at)->not->toBeNull();
});

/*
|------------------------------------------------------------------------------
| The clinic's daily schedule
|------------------------------------------------------------------------------
*/

it('lists the appointments for a cairo day, not a utc one', function () {
    Notification::fake();

    // 08:00 Cairo on the 9th is 05:00 UTC — a query against the UTC column
    // would put an early appointment on the previous day for half the year.
    freezeAt('2026-06-09 07:00:00');

    $today = remindableAppointment(CarbonImmutable::parse('2026-06-09 08:00:00', 'Africa/Cairo'));
    $tomorrow = remindableAppointment(CarbonImmutable::parse('2026-06-10 08:00:00', 'Africa/Cairo'));

    Artisan::call('clinic:send-daily-schedule');

    Notification::assertSentOnDemand(
        DailySchedule::class,
        function (DailySchedule $notification) use ($today, $tomorrow): bool {
            $ids = $notification->appointments->pluck('id')->all();

            return in_array($today->id, $ids, true) && ! in_array($tomorrow->id, $ids, true);
        }
    );
});

it('mails the clinic even when the day is empty', function () {
    Notification::fake();

    freezeAt('2026-06-09 07:00:00');

    Artisan::call('clinic:send-daily-schedule');

    /*
     * A digest that only arrives when there is something in it teaches the
     * reader nothing on the mornings it does not arrive: she cannot tell a
     * quiet Tuesday from a cron that died three weeks ago. The empty message
     * is a heartbeat.
     */
    Notification::assertSentOnDemand(
        DailySchedule::class,
        fn (DailySchedule $notification): bool => $notification->appointments->isEmpty(),
    );
});

it('leaves cancelled appointments off the daily schedule', function () {
    Notification::fake();

    freezeAt('2026-06-09 07:00:00');

    $cancelled = remindableAppointment(
        CarbonImmutable::parse('2026-06-09 08:00:00', 'Africa/Cairo'),
        status: AppointmentStatus::Cancelled,
    );

    Artisan::call('clinic:send-daily-schedule');

    Notification::assertSentOnDemand(
        DailySchedule::class,
        fn (DailySchedule $notification): bool => ! $notification->appointments
            ->pluck('id')
            ->contains($cancelled->id),
    );
});
