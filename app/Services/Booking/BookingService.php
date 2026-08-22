<?php

declare(strict_types=1);

namespace App\Services\Booking;

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Enums\BookingSource;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Service;
use App\Services\Availability\AvailabilityEngine;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use LogicException;

/**
 * Writes a booking. Deliberately not inside the Livewire component.
 *
 * The transaction discipline here is the part of this feature that has to be
 * exactly right, and it should be testable without rendering anything, without
 * a session, and without a browser. A component that owned this logic could
 * only be tested through Livewire's test harness, which is a worse place to
 * reason about race conditions.
 */
class BookingService
{
    public function __construct(
        private readonly AvailabilityEngine $availability,
    ) {}

    /**
     * Book an appointment, or fail because the slot went.
     *
     * The whole thing is one transaction. If the intake form cannot be written
     * the appointment must not survive either — a booking with no intake is a
     * patient arriving for a consultation the clinician cannot prepare for,
     * and it would look completely normal in the calendar.
     *
     * @param  array{name: string, phone: string, email?: ?string, birth_date?: ?string}  $patientData
     * @param  array{goal?: ?string, medications?: ?string, conditions?: ?string, avoid_foods?: ?string, note?: ?string}  $intakeData
     *
     * @throws SlotTakenException
     */
    public function book(
        Service $service,
        CarbonImmutable $startsAtUtc,
        int $staffId,
        AppointmentMode $mode,
        array $patientData,
        array $intakeData,
        string $consentIp,
    ): Appointment {
        $this->guardMode($mode);
        $this->guardService($service);

        try {
            return DB::transaction(function () use (
                $service,
                $startsAtUtc,
                $staffId,
                $mode,
                $patientData,
                $intakeData,
                $consentIp,
            ): Appointment {
                /*
                 * 1. Re-check availability INSIDE the transaction.
                 *
                 * The form was rendered against a calendar that is by now
                 * minutes old. This closes the window to milliseconds; the
                 * unique index below closes it completely. Both are needed:
                 * without the re-check a patient can book a slot that was
                 * taken while they typed and only find out from a database
                 * error; without the index two simultaneous requests can both
                 * pass the re-check.
                 */
                if (! $this->availability->isSlotBookable($startsAtUtc, $staffId, $service)) {
                    throw new SlotTakenException('The selected slot is no longer available.');
                }

                /*
                 * 2. Resolve the patient. findOrCreateByPhone takes a row lock
                 * and handles the returning and soft-deleted cases, so a
                 * second booking from the same number waits here rather than
                 * racing to create a duplicate file.
                 */
                $patient = Patient::findOrCreateByPhone($patientData['phone'], $patientData);

                // 3. The appointment. slot_key is derived by the model's saving
                // hook, so the unique index sees it without this code touching it.
                $appointment = Appointment::create([
                    'reference' => Appointment::generateReference(),
                    'cancel_token' => Appointment::generateCancelToken(),
                    'patient_id' => $patient->id,
                    'service_id' => $service->id,
                    'staff_id' => $staffId,
                    'starts_at' => $startsAtUtc,
                    'ends_at' => $startsAtUtc->addMinutes($service->duration_minutes),
                    'mode' => $mode,
                    // Pending, not confirmed: Task 6 sends the confirmation,
                    // and the clinic decides what confirmation means.
                    'status' => AppointmentStatus::Pending,
                    'price' => $service->price,
                    'currency' => $service->currency,
                    'source' => BookingSource::Website,
                    /*
                     * The language this booking is being made in, captured
                     * here because this request is the only place that knows
                     * it. Every notification for this appointment is rendered
                     * in it — including reminders sent months later by a cron
                     * run that has no locale of its own.
                     */
                    'locale' => App::getLocale(),
                ]);

                // 4. The intake form, in the same transaction.
                $appointment->intakeForm()->create([
                    'goal' => $intakeData['goal'] ?? null,
                    'medications' => $intakeData['medications'] ?? null,
                    'conditions' => $intakeData['conditions'] ?? null,
                    'avoid_foods' => $intakeData['avoid_foods'] ?? null,
                    'note' => $intakeData['note'] ?? null,
                    // Consent is recorded at the moment of the write, from the
                    // server's clock and the request's IP — never from
                    // anything the client sent.
                    'consent_at' => now(),
                    'consent_ip' => $consentIp,
                ]);

                return $appointment;
            });
        } catch (QueryException $exception) {
            /*
             * The unique index on slot_key fired: two requests passed the
             * re-check and this one lost. Translated into the same exception
             * the pre-check raises, because from the patient's side these are
             * one event — "someone got there first" — and the interface should
             * not have two ways of saying it.
             */
            if ($this->isSlotCollision($exception)) {
                throw new SlotTakenException('The selected slot was taken while booking.', 0, $exception);
            }

            throw $exception;
        }
    }

    /**
     * Move an existing appointment to a new instant.
     *
     * Same discipline as booking. The update is what re-derives slot_key, so
     * the unique index arbitrates the move exactly as it arbitrates a new
     * booking — two people rescheduling into the same free hour cannot both
     * win.
     *
     * @throws SlotTakenException
     */
    public function reschedule(Appointment $appointment, CarbonImmutable $startsAtUtc): Appointment
    {
        try {
            return DB::transaction(function () use ($appointment, $startsAtUtc): Appointment {
                /*
                 * Lock the row first. Without this, two concurrent reschedules
                 * of the SAME appointment could both read the old time and
                 * both proceed, and the loser's slot_key update would silently
                 * overwrite the winner's.
                 */
                /** @var Appointment $locked */
                $locked = Appointment::query()
                    ->whereKey($appointment->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if (! $this->availability->isSlotBookable($startsAtUtc, $locked->staff_id, $locked->service)) {
                    throw new SlotTakenException('The selected slot is no longer available.');
                }

                /*
                 * Written through the attribute setters rather than assigned
                 * to the typed properties: the model casts these to Carbon,
                 * not CarbonImmutable, and assigning the immutable instance
                 * directly is a type mismatch that happens to work at runtime.
                 */
                $locked->setAttribute('starts_at', Carbon::instance($startsAtUtc));
                $locked->setAttribute(
                    'ends_at',
                    Carbon::instance($startsAtUtc->addMinutes($locked->service->duration_minutes)),
                );
                $locked->save();

                return $locked;
            });
        } catch (QueryException $exception) {
            if ($this->isSlotCollision($exception)) {
                throw new SlotTakenException('The selected slot was taken while rescheduling.', 0, $exception);
            }

            throw $exception;
        }
    }

    /**
     * A mode that is not currently offered must not be bookable, however the
     * payload arrived.
     *
     * The enum still contains Clinic so historical rows render; config decides
     * what may be chosen today. Re-checked here rather than only in the
     * component, because this is the last point before the write.
     */
    private function guardMode(AppointmentMode $mode): void
    {
        if (! $mode->isBookable()) {
            throw new LogicException(sprintf(
                'Appointment mode "%s" is not currently offered. Bookable modes are: %s.',
                $mode->value,
                implode(', ', AppointmentMode::bookableValues()),
            ));
        }
    }

    /**
     * An inactive service is one the clinic has withdrawn. It may still be
     * referenced by an old link or a tampered payload, and neither should be
     * able to sell it.
     */
    private function guardService(Service $service): void
    {
        if (! $service->is_active) {
            throw new LogicException(sprintf(
                'Service "%s" is not active and cannot be booked.',
                $service->slug,
            ));
        }
    }

    /**
     * Is this the slot_key unique index, rather than some other constraint?
     *
     * Checked by name as well as by SQLSTATE, because a duplicate-key error on
     * the patients.phone index means something completely different — a race
     * between two first-time bookings from the same number — and swallowing
     * that as "slot taken" would tell the patient a lie and hide a real bug.
     */
    private function isSlotCollision(QueryException $exception): bool
    {
        if (($exception->errorInfo[1] ?? null) !== 1062) {
            return false;
        }

        return str_contains($exception->getMessage(), 'slot_key');
    }
}
