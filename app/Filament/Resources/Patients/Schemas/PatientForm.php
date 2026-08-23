<?php

declare(strict_types=1);

namespace App\Filament\Resources\Patients\Schemas;

use App\Enums\Gender;
use App\Models\Patient;
use App\Support\PhoneNumber;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * A patient file.
 *
 * The phone number is the identity in this system — Patient::findOrCreateByPhone
 * matches on it, and appointments hang off the row it returns. So it is unique,
 * normalised to E.164 before it is stored, and validated against the existing
 * rows here as well as at the database, because a receptionist who creates a
 * second file for a returning patient splits that patient's history in two and
 * nothing later reunites it.
 *
 * `notes` is administrative — "prefers afternoons", "calls from her sister's
 * phone". It is NOT clinical content: what the patient wrote about her health
 * lives in intake_forms, behind IntakeFormPolicy, and reception can read this
 * field while never reaching that one.
 */
class PatientForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('بيانات التواصل')
                ->schema([
                    TextInput::make('name')
                        ->label('الاسم')
                        ->required()
                        ->minLength(3)
                        ->maxLength(120),

                    TextInput::make('phone')
                        ->label('رقم الموبايل')
                        ->tel()
                        ->required()
                        ->extraInputAttributes(['dir' => 'ltr'])
                        /*
                         * Normalised BEFORE the unique check runs, so "01012345678"
                         * and "+201012345678" are recognised as the same number
                         * rather than passing as two different strings.
                         */
                        ->dehydrateStateUsing(fn (string $state): string => PhoneNumber::e164($state))
                        ->rule(fn (): \Closure => function (string $attribute, mixed $value, \Closure $fail): void {
                            if (! PhoneNumber::isValid((string) $value)) {
                                $fail(__('booking.errors.phone_invalid'));
                            }
                        })
                        ->unique(
                            table: Patient::class,
                            column: 'phone',
                            ignoreRecord: true,
                            modifyRuleUsing: fn (object $rule, mixed $state) => $rule->where(
                                'phone',
                                PhoneNumber::isValid((string) $state) ? PhoneNumber::e164((string) $state) : $state,
                            ),
                        )
                        ->helperText('الرقم ده هو هوية الملف. لو موجود قبل كده، افتحي الملف الموجود بدل ما تعملي واحد جديد.'),

                    TextInput::make('email')
                        ->label('الإيميل')
                        ->email()
                        ->maxLength(190)
                        ->extraInputAttributes(['dir' => 'ltr'])
                        ->helperText('من غيره مش هيوصلها تأكيد ولا تنبيهات.'),

                    DatePicker::make('birth_date')
                        ->label('تاريخ الميلاد')
                        ->maxDate(now()),

                    Select::make('gender')
                        ->label('النوع')
                        ->options(fn (): array => collect(Gender::cases())
                            ->mapWithKeys(fn (Gender $g): array => [$g->value => $g->label()])
                            ->all())
                        ->native(false),
                ])
                ->columns(2),

            Section::make('ملاحظات إدارية')
                ->schema([
                    Textarea::make('notes')
                        ->label('ملاحظات')
                        ->rows(4)
                        ->columnSpanFull()
                        /*
                         * Said out loud on the screen, because the field is
                         * next to a patient's name and the temptation to type
                         * a diagnosis into it is real. Anything clinical
                         * belongs in the intake, which is policy-gated; this
                         * box is readable by reception.
                         */
                        ->helperText('ملاحظات تنظيمية زي "بتفضل مواعيد بعد الضهر". متكتبيش هنا أي حاجة طبية — دي بتتقرا من الاستقبال.'),
                ]),
        ]);
    }
}
