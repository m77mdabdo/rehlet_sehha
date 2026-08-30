<?php

declare(strict_types=1);

namespace App\Filament\Resources\Appointments\Schemas;

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Models\Patient;
use App\Models\Service;
use App\Models\User;
use App\Services\Availability\AvailabilityEngine;
use App\Services\Availability\Slot;
use App\Support\PhoneNumber;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

/**
 * Booking from the clinic side.
 *
 * The slot list is produced by the SAME AvailabilityEngine the public wizard
 * uses, and the write goes through BookingService — see CreateAppointment. No
 * second booking path exists, so a staff booking cannot bypass lead time,
 * buffers, blocked slots or the unique index that stops double-booking.
 *
 * The patient section takes a phone number FIRST, because that is the key
 * Patient::findOrCreateByPhone matches on. A receptionist typing a returning
 * patient's number must land on that patient's existing file — with her
 * history, her past intake and her preferences — rather than opening a second
 * blank one that splits the record in two.
 */
class AppointmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الحجز')
                ->schema([
                    Select::make('service_id')
                        ->label('الباقة')
                        ->options(fn (): array => Service::query()
                            ->where('is_active', true)
                            ->pluck('name', 'id')
                            ->all())
                        ->required()
                        ->live()
                        ->native(false),

                    Select::make('staff_id')
                        ->label('الدكتورة')
                        ->options(fn (): array => User::query()->bookable()->pluck('name', 'id')->all())
                        ->required()
                        ->live()
                        ->native(false),

                    /*
                     * EVERY mode, not just the bookable ones.
                     *
                     * This used to offer bookableValues() — the modes a patient
                     * may choose TODAY, which is ['online'] since the practice
                     * went online-only. That made every appointment booked
                     * under the old clinic mode unsaveable: opening one showed
                     * the raw string "clinic" and refused to save with "The
                     * selected نوع الاستشارة is invalid", on a field nobody had
                     * touched. Half the appointments in the table were affected.
                     *
                     * AppointmentMode::options() exists for exactly this and
                     * says so in its own docblock — "includes modes that are no
                     * longer bookable, because an appointment booked last year
                     * still has to display". The form simply reached for the
                     * wrong helper.
                     *
                     * The booking wizard still offers bookableOptions(), which
                     * is where the restriction belongs: a patient cannot choose
                     * a retired mode, and the clinic can still edit a record
                     * that used one.
                     */
                    Select::make('mode')
                        ->label('نوع الاستشارة')
                        ->options(AppointmentMode::options())
                        ->default(AppointmentMode::Online->value)
                        ->required()
                        ->native(false),

                    /*
                     * Free slots only, from the availability engine. A staff
                     * member cannot type an arbitrary time into this form,
                     * which is the point: the calendar the clinic sees and the
                     * calendar the patient sees are the same calendar.
                     */
                    Select::make('slot')
                        ->label('الميعاد')
                        ->options(fn (Get $get): array => self::slotOptions($get))
                        ->required()
                        ->searchable()
                        ->native(false)
                        ->helperText('المواعيد المتاحة فقط، محسوبة زي ما المريضة بتشوفها بالظبط.')
                        ->hiddenOn('edit')
                        ->dehydrated(),
                ])
                ->columns(2),

            Section::make('المريضة')
                ->schema([
                    /*
                     * The phone comes first and is the identity. Typed in
                     * E.164 or in the local 01… form; PhoneNumber normalises
                     * either, and findOrCreateByPhone takes a row lock so two
                     * receptionists booking the same returning patient cannot
                     * race into two files.
                     */
                    TextInput::make('patient_phone')
                        ->label('رقم الموبايل')
                        ->tel()
                        ->required()
                        ->live(onBlur: true)
                        ->helperText('لو الرقم مسجل قبل كده، هيتفتح ملف المريضة الموجود.')
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            if ($state === null || ! PhoneNumber::isValid($state)) {
                                return;
                            }

                            $patient = Patient::query()
                                ->where('phone', PhoneNumber::e164($state))
                                ->first();

                            if ($patient === null) {
                                return;
                            }

                            /*
                             * Prefill from the existing file, so a receptionist
                             * can see at once that she has landed on a
                             * returning patient rather than starting a new one.
                             *
                             * Cosmetic only. The actual matching happens in
                             * Patient::findOrCreateByPhone during the write,
                             * which takes a row lock — so even if this never
                             * fired, the booking would still attach to the
                             * existing file rather than duplicating it.
                             */
                            $set('patient_name', $patient->name);
                            $set('patient_email', $patient->email);
                        }),

                    TextInput::make('patient_name')
                        ->label('الاسم')
                        ->required()
                        ->minLength(3)
                        ->maxLength(120),

                    TextInput::make('patient_email')
                        ->label('الإيميل')
                        ->email()
                        ->maxLength(190)
                        ->helperText('اختياري. من غيره مش هيوصلها تأكيد ولا تنبيهات، ولازم حد يكلمها.'),
                ])
                ->columns(2)
                /*
                 * The whole section goes on edit, not just its fields. Hiding
                 * the fields individually left an empty "المريضة" card sitting
                 * on the screen with nothing in it, which reads as something
                 * that failed to load.
                 *
                 * Patient details are not editable from the appointment at all:
                 * they belong to the patient FILE, which has its own screen.
                 * Editing a name here would silently rewrite it on every other
                 * appointment she has ever had.
                 */
                ->hiddenOn('edit'),

            Section::make('الحالة')
                ->schema([
                    Select::make('status')
                        ->label('حالة الحجز')
                        ->options(AppointmentStatus::options())
                        ->required()
                        ->native(false)
                        ->visibleOn('edit'),

                    Textarea::make('staff_notes')
                        ->label('ملاحظات العيادة')
                        ->rows(4)
                        ->columnSpanFull()
                        ->helperText('ملاحظات إدارية. مش دي الملاحظات الطبية بتاعة المريضة.'),
                ])
                ->columns(2)
                ->visibleOn('edit'),
        ]);
    }

    /**
     * @return array<string, string>
     */
    private static function slotOptions(Get $get): array
    {
        $serviceId = $get('service_id');
        $staffId = $get('staff_id');

        if (! $serviceId) {
            return [];
        }

        $service = Service::find($serviceId);

        if ($service === null) {
            return [];
        }

        $zone = config('clinic.timezone');
        $horizon = (int) config('clinic.booking.horizon_days', 30);

        return app(AvailabilityEngine::class)
            ->availableSlots(
                CarbonImmutable::now()->utc(),
                CarbonImmutable::now()->addDays($horizon)->endOfDay()->utc(),
                $staffId ? (int) $staffId : null,
                $service,
            )
            ->mapWithKeys(fn (Slot $slot): array => [
                $slot->key() => $slot->startsAtUtc->setTimezone($zone)->translatedFormat('l j F — H:i'),
            ])
            ->all();
    }
}
