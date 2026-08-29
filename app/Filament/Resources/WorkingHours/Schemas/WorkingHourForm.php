<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkingHours\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * One window of one practitioner's week.
 *
 * DAY NUMBERING IS CARBON'S: 0 = Sunday .. 6 = Saturday, which makes Friday 5.
 * The clinic works Saturday through Thursday, so Friday is simply absent — the
 * slot generator reads "no row" as closed and needs no row saying so.
 *
 * TIMES ARE CAIRO WALL-CLOCK and are stored as bare times with no date. They
 * are deliberately not cast to datetime on the model: bolting a date onto
 * "10:00" invites an accidental UTC conversion, which would move the whole
 * schedule by two or three hours depending on the season.
 */
class WorkingHourForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الميعاد')
                ->description('المواعيد دي بتحدد الساعات اللي الحجز بيعرضها، واللي بتظهر في الفوتر وفي بيانات جوجل.')
                ->schema([
                    Select::make('staff_id')
                        ->label('الدكتورة')
                        ->relationship(
                            'staff',
                            'name',
                            // Practitioners only. An administrator can act on
                            // the calendar but does not see patients, so giving
                            // one a schedule would advertise bookable hours
                            // nobody intends to work.
                            fn ($query) => $query->whereHas(
                                'roles',
                                fn ($roles) => $roles->where('name', 'doctor')
                            ),
                        )
                        ->default(fn (): ?int => User::query()
                            ->whereHas('roles', fn ($roles) => $roles->where('name', 'doctor'))
                            ->value('id'))
                        ->searchable()
                        ->preload()
                        ->required(),

                    Select::make('day_of_week')
                        ->label('اليوم')
                        ->options([
                            6 => 'السبت',
                            0 => 'الأحد',
                            1 => 'الاثنين',
                            2 => 'الثلاثاء',
                            3 => 'الأربعاء',
                            4 => 'الخميس',
                            5 => 'الجمعة',
                        ])
                        ->helperText('الجمعة إجازة. لو مفيش صف لليوم، يبقى مقفول.')
                        ->required(),

                    TimePicker::make('start_time')
                        ->label('من')
                        ->seconds(false)
                        ->required(),

                    TimePicker::make('end_time')
                        ->label('لحد')
                        ->seconds(false)
                        ->required()
                        // A window that ends before it starts silently produces
                        // no slots at all, which looks like a broken booking
                        // form rather than a typo.
                        ->after('start_time'),

                    TextInput::make('slot_minutes')
                        ->label('طول الميعاد (بالدقايق)')
                        ->numeric()
                        ->minValue(5)
                        ->maxValue(240)
                        ->default(60)
                        ->required()
                        ->helperText('ده بيحدد كل قد إيه يبدأ ميعاد جديد.'),

                    Toggle::make('is_active')
                        ->label('شغّال')
                        ->default(true)
                        ->helperText('اقفليه بدل ما تمسحي الصف: كده الميعاد بيقف من غير ما تضيع المواعيد القديمة.'),
                ])
                ->columns(2),
        ]);
    }
}
