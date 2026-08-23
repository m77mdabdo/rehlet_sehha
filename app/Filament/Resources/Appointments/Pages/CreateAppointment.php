<?php

declare(strict_types=1);

namespace App\Filament\Resources\Appointments\Pages;

use App\Enums\AppointmentMode;
use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Service;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use App\Services\Booking\BookingService;
use App\Services\Booking\SlotTakenException;
use App\Services\Notifications\AppointmentNotifier;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

/**
 * Staff booking, through the public booking path.
 *
 * handleRecordCreation is overridden so Filament's default "mass-assign the
 * form and save" never runs. THERE IS ONE BOOKING PATH IN THIS APPLICATION and
 * it is BookingService::book(): the transaction, the in-transaction
 * availability re-check, the patient row lock, the intake row, and the unique
 * index on slot_key that arbitrates a collision.
 *
 * Writing a second path here would be the obvious shortcut and the worst kind
 * of bug: the two would agree for months and then diverge on exactly the case
 * that matters — two people booking the same hour at the same moment, one of
 * them at the reception desk.
 *
 * So a staff booking collides identically to a patient booking. The failure is
 * the same SlotTakenException, surfaced as a notification instead of an
 * inline form error.
 */
class CreateAppointment extends CreateRecord
{
    protected static string $resource = AppointmentResource::class;

    public function getTitle(): string
    {
        return 'حجز جديد';
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        $service = Service::findOrFail($data['service_id']);

        $slot = $this->resolveSlot($data, $service);

        $phone = (string) $data['patient_phone'];

        if (! PhoneNumber::isValid($phone)) {
            throw ValidationException::withMessages([
                'data.patient_phone' => __('booking.errors.phone_invalid'),
            ]);
        }

        try {
            $appointment = app(BookingService::class)->book(
                service: $service,
                startsAtUtc: $slot->startsAtUtc,
                staffId: $slot->staffId,
                mode: AppointmentMode::from($data['mode']),
                patientData: [
                    'name' => trim((string) $data['patient_name']),
                    // findOrCreateByPhone matches on this. A returning
                    // patient's number lands on her existing file rather than
                    // opening a duplicate — see the service.
                    'phone' => PhoneNumber::e164($phone),
                    'email' => ($data['patient_email'] ?? '') === '' ? null : $data['patient_email'],
                    'birth_date' => null,
                ],
                /*
                 * No intake. A patient writes her own history; reception does
                 * not take it down over the telephone, and the doctor asks in
                 * the room. An empty intake is honest about that.
                 */
                intakeData: [],
                consentIp: (string) request()->ip(),
            );
        } catch (SlotTakenException) {
            Notification::make()
                ->danger()
                ->title('الميعاد ده اتحجز')
                ->body('حد حجزه في نفس اللحظة. اختاري ميعاد تاني — البيانات اللي كتبتيها زي ما هي.')
                ->persistent()
                ->send();

            throw ValidationException::withMessages([
                'data.slot' => 'الميعاد ده مابقاش متاح.',
            ]);
        }

        /*
         * The same two messages a website booking sends. A patient booked over
         * the telephone gets her confirmation and her manage link exactly as
         * one who booked herself does — otherwise she has no written record
         * and no way to cancel without ringing back.
         */
        $notifier = app(AppointmentNotifier::class);
        $notifier->bookingConfirmed($appointment);
        $notifier->newBookingAlert($appointment);

        if (! $appointment->isReachableByEmail()) {
            Notification::make()
                ->warning()
                ->title('المريضة مالهاش إيميل')
                ->body('مش هيوصلها تأكيد ولا تنبيه. اكتبي رقم الحجز واديهولها، ولازم حد يكلمها قبل الميعاد.')
                ->persistent()
                ->send();
        }

        return $appointment;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveSlot(array $data, Service $service): Slot
    {
        $horizon = (int) config('clinic.booking.horizon_days', 30);

        $slot = app(AvailabilityEngine::class)
            ->availableSlots(
                CarbonImmutable::now()->utc(),
                CarbonImmutable::now()->addDays($horizon)->endOfDay()->utc(),
                (int) $data['staff_id'],
                $service,
            )
            ->first(fn (Slot $candidate): bool => $candidate->key() === $data['slot']);

        if ($slot === null) {
            /*
             * Gone between rendering the form and submitting it. Reported as a
             * validation error on the slot field rather than a crash, and the
             * rest of what was typed survives — the same courtesy the public
             * wizard extends after a collision.
             */
            throw ValidationException::withMessages([
                'data.slot' => 'الميعاد ده مابقاش متاح. اختاري ميعاد تاني.',
            ]);
        }

        return $slot;
    }
}
