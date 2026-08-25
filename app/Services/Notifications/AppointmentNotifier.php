<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationChannel;
use App\Models\Appointment;
use App\Models\NotificationLog;
use App\Models\Review;
use App\Notifications\AppointmentReminder1h;
use App\Notifications\AppointmentReminder24h;
use App\Notifications\BookingCancelled;
use App\Notifications\BookingCancelledAlert;
use App\Notifications\BookingConfirmed;
use App\Notifications\BookingRescheduled;
use App\Notifications\Contracts\LogsDelivery;
use App\Notifications\DailySchedule;
use App\Notifications\NewBookingAlert;
use App\Notifications\PatientMailFailedAlert;
use App\Notifications\ReviewRequested;
use App\Support\Contact;
use Illuminate\Notifications\Notification as BaseNotification;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Notification;
use Throwable;

/**
 * The only place notifications are dispatched.
 *
 * Everything goes through here so that three things cannot be forgotten: the
 * language the patient booked in, the log row that makes a silent failure
 * visible, and the check that there is somewhere to send to at all.
 *
 * THE MISSING EMAIL ADDRESS. The booking form does not require an email, and
 * it never will: a real share of patients here do not use email, and demanding
 * one would cost the clinic those bookings outright. So a real fraction of
 * appointments cannot be emailed anything, and this class is where that fact
 * lands.
 *
 * It is not papered over at any layer:
 *
 *   - The patient is told before she books, in step 3, exactly what will not
 *     arrive, and chooses to continue anyway (BookingWizard::$showNoEmailNotice).
 *   - Her confirmation screen becomes the record instead of a receipt, with
 *     the reference and manage link copyable and a WhatsApp action to send
 *     them to herself.
 *   - A SKIPPED row is written here, so the delivery log shows the message
 *     was never sent rather than showing nothing at all.
 *   - The clinic's new-booking alert prints "no email — call her" in place of
 *     the address, and the daily schedule carries tomorrow's unreachable
 *     patients as a call list.
 *
 * What remains is a workflow the clinic performs by telephone, which is the
 * honest answer: software cannot deliver to an address that does not exist.
 */
class AppointmentNotifier
{
    /*
    |--------------------------------------------------------------------------
    | Patient
    |--------------------------------------------------------------------------
    */

    public function bookingConfirmed(Appointment $appointment): void
    {
        $this->toPatient($appointment, new BookingConfirmed($appointment));
    }

    public function reminder24h(Appointment $appointment): void
    {
        $this->toPatient($appointment, new AppointmentReminder24h($appointment));
    }

    public function reminder1h(Appointment $appointment): void
    {
        $this->toPatient($appointment, new AppointmentReminder1h($appointment));
    }

    public function bookingCancelled(Appointment $appointment): void
    {
        $this->toPatient($appointment, new BookingCancelled($appointment));
    }

    public function bookingRescheduled(Appointment $appointment, Carbon $previousStartsAt): void
    {
        $this->toPatient($appointment, new BookingRescheduled($appointment, $previousStartsAt));
    }

    /**
     * Invite a patient to review a visit that has already happened.
     *
     * Through the same door as everything else, for the same three reasons —
     * and one more that is specific to this message. A review invitation goes
     * to a patient who may well have no email address, and the invitation is
     * the only thing that carries her review token. If it is not delivered,
     * the token exists and she never learns of it; the SKIPPED row is what
     * tells the clinic to stop expecting a reply.
     */
    public function reviewRequested(Appointment $appointment, Review $review): void
    {
        $this->toPatient($appointment, new ReviewRequested($appointment, $review));
    }

    /*
    |--------------------------------------------------------------------------
    | Clinic
    |--------------------------------------------------------------------------
    */

    public function newBookingAlert(Appointment $appointment): void
    {
        $this->toClinic($appointment, new NewBookingAlert($appointment));
    }

    public function bookingCancelledAlert(Appointment $appointment): void
    {
        $this->toClinic($appointment, new BookingCancelledAlert($appointment));
    }

    /**
     * @param  Collection<int, Appointment>  $appointments  today's schedule
     * @param  Collection<int, Appointment>  $callList  tomorrow's patients with no email
     */
    public function dailySchedule(Carbon $date, Collection $appointments, ?Collection $callList = null): void
    {
        $this->toClinic(null, new DailySchedule($date, $appointments, $callList ?? new Collection));
    }

    public function alertClinicOfFailedDelivery(
        Appointment $appointment,
        string $failedTemplate,
        Throwable $exception,
    ): void {
        $this->toClinic($appointment, new PatientMailFailedAlert(
            $appointment,
            $failedTemplate,
            $exception->getMessage(),
        ));
    }

    /*
    |--------------------------------------------------------------------------
    | Dispatch
    |--------------------------------------------------------------------------
    */

    /**
     * Send to the patient, in the language they booked in.
     */
    private function toPatient(Appointment $appointment, BaseNotification&LogsDelivery $notification): void
    {
        $email = $appointment->patient->email;

        if ($email === null || trim($email) === '') {
            /*
             * Recorded, not swallowed. A patient with no email address is a
             * patient the clinic must reach another way, and the only way that
             * happens is if somebody can see that the message was never sent.
             */
            $this->openLog($appointment, $notification->deliveryTemplate(), '—', NotificationLog::STATUS_SKIPPED)
                ->update(['error' => 'The patient did not give an email address, so nothing could be sent.']);

            return;
        }

        $log = $this->openLog($appointment, $notification->deliveryTemplate(), $email);
        $notification->setDeliveryLogId($log->id);

        Notification::route('mail', $email)
            /*
             * The locale is pinned to the booking, not taken from the ambient
             * request. Reminders are rendered by a cron run that has no locale
             * at all, and the default would quietly mail an English-speaking
             * patient in Arabic.
             */
            ->notify($notification->locale($appointment->locale));
    }

    /**
     * Send to the clinic, in the clinic's own language.
     *
     * Deliberately NOT the patient's locale. These messages are read by the
     * practitioner and reception, who work in Arabic; rendering the clinic's
     * own daily schedule in English because one patient happened to book in
     * English would be absurd.
     */
    private function toClinic(?Appointment $appointment, BaseNotification&LogsDelivery $notification): void
    {
        $email = Contact::email();

        if ($email === null) {
            // Nothing is configured to receive this. Nothing to do, and
            // nothing to log against — there is no recipient to record.
            return;
        }

        $log = $this->openLog($appointment, $notification->deliveryTemplate(), $email);
        $notification->setDeliveryLogId($log->id);

        Notification::route('mail', $email)
            ->notify($notification->locale(config('app.locale')));
    }

    /**
     * Open the delivery row BEFORE dispatch.
     *
     * The ordering is the point. A row written only on success cannot describe
     * a queue that never ran, and "no row" would be indistinguishable from
     * "delivered". The listener moves this row to sent or failed; if it is
     * still queued a week later, that itself is the finding.
     */
    private function openLog(
        ?Appointment $appointment,
        string $template,
        string $recipient,
        string $status = NotificationLog::STATUS_QUEUED,
    ): NotificationLog {
        return NotificationLog::create([
            'appointment_id' => $appointment?->id,
            'channel' => NotificationChannel::Mail,
            // Encrypted by the model cast; this table would otherwise become a
            // plaintext directory of patient contact details.
            'recipient' => $recipient,
            'template' => $template,
            'status' => $status,
        ]);
    }
}
