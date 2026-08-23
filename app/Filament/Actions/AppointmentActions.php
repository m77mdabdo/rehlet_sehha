<?php

declare(strict_types=1);

namespace App\Filament\Actions;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use App\Services\Booking\BookingService;
use App\Services\Booking\SlotTakenException;
use App\Services\Notifications\AppointmentNotifier;
use Carbon\CarbonImmutable;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

/**
 * What the clinic does to an appointment.
 *
 * EVERY ACTION FIRES THE NOTIFICATION THE PATIENT FLOW WOULD FIRE. A booking
 * cancelled by reception must reach the patient exactly as one she cancelled
 * herself does — she cannot tell the difference from where she is sitting, and
 * a cancellation she is never told about is a wasted journey.
 *
 * Authorisation is per action via AppointmentPolicy, so a receptionist simply
 * does not see "اكتملت" or "لم تحضر": recording that a consultation happened
 * is a clinical statement, not a scheduling one.
 */
class AppointmentActions
{
    /**
     * @return array<int, Action|ActionGroup>
     */
    public static function forTable(): array
    {
        return [
            ActionGroup::make([
                EditAction::make()->label('فتح'),
                self::confirm(),
                self::reschedule(),
                self::complete(),
                self::markNoShow(),
                self::cancel(),
            ])->label('إجراءات'),
        ];
    }

    public static function confirm(): Action
    {
        return Action::make('confirm')
            ->label('تأكيد الحجز')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->authorize(fn (Appointment $record): bool => auth()->user()->can('confirm', $record))
            ->visible(fn (Appointment $record): bool => $record->status === AppointmentStatus::Pending)
            ->requiresConfirmation()
            ->modalHeading('تأكيد الحجز')
            ->modalDescription('الحجز هيتحول لمؤكد. المريضة مش هيوصلها رسالة جديدة — التأكيد الأصلي اتبعت وقت الحجز.')
            ->action(function (Appointment $record): void {
                $record->confirm();

                Notification::make()->success()->title('الحجز اتأكد')->send();
            });
    }

    /**
     * Completing a consultation. Clinical staff only.
     */
    public static function complete(): Action
    {
        return Action::make('complete')
            ->label('اكتملت')
            ->icon('heroicon-o-clipboard-document-check')
            ->color('gray')
            ->authorize(fn (Appointment $record): bool => auth()->user()->can('complete', $record))
            ->visible(fn (Appointment $record): bool => in_array(
                $record->status,
                [AppointmentStatus::Pending, AppointmentStatus::Confirmed],
                true,
            ))
            ->requiresConfirmation()
            ->modalHeading('تسجيل إن الجلسة تمت')
            ->action(function (Appointment $record): void {
                $record->status = AppointmentStatus::Completed;
                $record->save();

                Notification::make()->success()->title('اتسجلت كمكتملة')->send();
            });
    }

    public static function markNoShow(): Action
    {
        return Action::make('markNoShow')
            ->label('لم تحضر')
            ->icon('heroicon-o-user-minus')
            ->color('danger')
            ->authorize(fn (Appointment $record): bool => auth()->user()->can('markNoShow', $record))
            ->visible(fn (Appointment $record): bool => in_array(
                $record->status,
                [AppointmentStatus::Pending, AppointmentStatus::Confirmed],
                true,
            ))
            ->requiresConfirmation()
            ->modalHeading('تسجيل عدم حضور')
            /*
             * Said plainly, because it is the one status change with a
             * consequence people do not expect: a no-show does NOT hand the
             * hour back. The clinic spent that time.
             */
            ->modalDescription('الميعاد مش هيرجع للكالندر — العيادة استهلكت الوقت ده فعلًا.')
            ->action(function (Appointment $record): void {
                $record->status = AppointmentStatus::NoShow;
                $record->save();

                Notification::make()->warning()->title('اتسجلت كعدم حضور')->send();
            });
    }

    public static function cancel(): Action
    {
        return Action::make('cancel')
            ->label('إلغاء')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->authorize(fn (Appointment $record): bool => auth()->user()->can('cancel', $record))
            ->visible(fn (Appointment $record): bool => $record->isLive())
            ->requiresConfirmation()
            ->modalHeading('إلغاء الحجز')
            ->modalDescription('الميعاد هيرجع للكالندر، وهنبعت للمريضة رسالة بالإلغاء لو عندها إيميل.')
            ->action(function (Appointment $record): void {
                $record->cancel('ألغته العيادة');

                /*
                 * The same pair the patient's own cancellation sends. She is
                 * told, and the clinic's alert records that the hour is free
                 * again — see AppointmentManager::cancel().
                 */
                $notifier = app(AppointmentNotifier::class);
                $notifier->bookingCancelled($record);
                $notifier->bookingCancelledAlert($record);

                Notification::make()->success()->title('الحجز اتلغى واتبعت رسالة للمريضة')->send();
            });
    }

    /**
     * Moving an appointment.
     *
     * Goes through BookingService::reschedule — the SAME path the patient's
     * own reschedule uses. There is no second booking path in this
     * application: the row is locked, availability is re-checked inside the
     * transaction, and the unique index on slot_key arbitrates. Staff booking
     * a slot that has just gone therefore fails exactly as a patient's does,
     * with the same exception and the same message.
     */
    public static function reschedule(): Action
    {
        return Action::make('reschedule')
            ->label('تغيير الميعاد')
            ->icon('heroicon-o-arrow-path')
            ->color('warning')
            ->authorize(fn (Appointment $record): bool => auth()->user()->can('reschedule', $record))
            ->visible(fn (Appointment $record): bool => $record->isLive())
            ->schema([
                Select::make('slot')
                    ->label('المواعيد المتاحة')
                    ->options(fn (Appointment $record): array => self::slotOptions($record))
                    ->searchable()
                    ->required()
                    ->helperText('المواعيد دي محسوبة من نفس محرك الأوقات اللي بيشوفه المرضى.'),
            ])
            ->action(function (Appointment $record, array $data): void {
                $slot = collect(self::slots($record))
                    ->first(fn (Slot $candidate): bool => $candidate->key() === $data['slot']);

                if ($slot === null) {
                    Notification::make()->danger()
                        ->title('الميعاد ده راح')
                        ->body('حد حجزه قبلك. اختاري ميعاد تاني.')
                        ->send();

                    return;
                }

                $previousStartsAt = $record->startsAtClinic();

                try {
                    app(BookingService::class)->reschedule($record, $slot->startsAtUtc);
                } catch (SlotTakenException) {
                    Notification::make()->danger()
                        ->title('الميعاد ده راح')
                        ->body('حد حجزه في نفس اللحظة. اختاري ميعاد تاني.')
                        ->send();

                    return;
                }

                $moved = $record->fresh();

                if ($moved !== null) {
                    app(AppointmentNotifier::class)->bookingRescheduled($moved, $previousStartsAt);
                }

                Notification::make()->success()->title('الميعاد اتغير واتبعت رسالة للمريضة')->send();
            });
    }

    /**
     * @return array<int, Slot>
     */
    private static function slots(Appointment $appointment): array
    {
        $horizon = (int) config('clinic.booking.horizon_days', 30);

        return app(AvailabilityEngine::class)->availableSlots(
            CarbonImmutable::now()->utc(),
            CarbonImmutable::now()->addDays($horizon)->endOfDay()->utc(),
            $appointment->staff_id,
            $appointment->service,
        )->all();
    }

    /**
     * @return array<string, string>
     */
    private static function slotOptions(Appointment $appointment): array
    {
        $zone = config('clinic.timezone');

        return collect(self::slots($appointment))
            ->mapWithKeys(fn (Slot $slot): array => [
                $slot->key() => $slot->startsAtUtc->setTimezone($zone)->translatedFormat('l j F — H:i'),
            ])
            ->all();
    }
}
