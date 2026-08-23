<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Today's appointments, in order, on the dashboard.
 *
 * The same information as the appointments list filtered to today — repeated
 * here on purpose, because the first question of the morning should not
 * require navigating anywhere or setting a filter.
 */
class TodaySchedule extends TableWidget
{
    protected static ?int $sort = 2;

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $zone = config('clinic.timezone');
        $today = now($zone)->startOfDay();

        return $table
            ->heading('مواعيد النهاردة')
            ->query(
                Appointment::query()
                    ->with(['patient', 'service'])
                    ->countsTowardWorkload()
                    ->whereBetween('starts_at', [
                        $today->clone()->utc(),
                        $today->clone()->endOfDay()->utc(),
                    ])
                    ->orderBy('starts_at')
            )
            ->columns([
                TextColumn::make('starts_at')
                    ->label('الساعة')
                    ->dateTime('H:i', timezone: $zone),

                TextColumn::make('patient.name')
                    ->label('المريضة')
                    ->description(fn (Appointment $record): string => $record->patient->phone),

                TextColumn::make('service.name')->label('الباقة'),

                IconColumn::make('reachable')
                    ->label('يوصلها تنبيه')
                    ->getStateUsing(fn (Appointment $record): bool => $record->isReachableByEmail())
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope')
                    ->falseIcon('heroicon-o-phone')
                    ->trueColor('gray')
                    ->falseColor('warning'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state): string => $state->label()),
            ])
            ->paginated(false)
            ->emptyStateHeading('مفيش مواعيد النهاردة');
    }
}
