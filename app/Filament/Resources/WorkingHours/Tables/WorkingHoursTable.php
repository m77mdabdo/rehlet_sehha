<?php

declare(strict_types=1);

namespace App\Filament\Resources\WorkingHours\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class WorkingHoursTable
{
    /**
     * Ordered as the week is worked — Saturday first, Friday last — rather
     * than by Carbon's numbering, which would open the list on Sunday and put
     * the weekend in the middle.
     */
    private const DAY_ORDER = [6 => 0, 0 => 1, 1 => 2, 2 => 3, 3 => 4, 4 => 5, 5 => 6];

    private const DAY_LABELS = [
        6 => 'السبت',
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
    ];

    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('day_of_week')
                    ->label('اليوم')
                    ->formatStateUsing(fn (int $state): string => self::DAY_LABELS[$state] ?? (string) $state)
                    ->sortable(),

                TextColumn::make('staff.name')->label('الدكتورة')->sortable(),

                TextColumn::make('start_time')
                    ->label('من')
                    ->formatStateUsing(fn (?string $state): string => substr((string) $state, 0, 5)),

                TextColumn::make('end_time')
                    ->label('لحد')
                    ->formatStateUsing(fn (?string $state): string => substr((string) $state, 0, 5)),

                TextColumn::make('slot_minutes')->label('طول الميعاد')->suffix(' د'),

                IconColumn::make('is_active')->label('شغّال')->boolean(),
            ])
            ->defaultSort(fn ($query) => $query
                ->orderByRaw('FIELD(day_of_week, '.implode(',', array_keys(self::DAY_ORDER)).')')
                ->orderBy('start_time'))
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
