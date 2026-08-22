<?php

declare(strict_types=1);

namespace App\Services\Notifications;

use App\Enums\NotificationChannel;
use App\Models\Appointment;
use App\Models\NotificationLog;
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
 * THE MISSING EMAIL ADDRESS. The booking form does not require an email — it
 * asks for a name and a mobile number, and email is optional. That was the
 * right call for the form and it means a real fraction of patients cannot be
 * emailed anything. This class does not paper over that: it records a SKIPPED
 * row saying so, and the clinic's own new-booking alert prints "no email —
 * call her" in place of the address. The clinic can then reach her the way she
 * actually gave us, which is her phone.
 *
 * That is a workaround, not a solution, and it is flagged as such: a patient
 * who books without an email gets no confirmation, no reminder and no manage
 * link, and nothing on the booking form tells her that.
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
     * @param  Collection<int, Appointment>  $appointments
     */
    public function dailySchedule(Carbon $date, Collection $appointments): void
    {
        $this->toClinic(null, new DailySchedule($date, $appointments));
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
