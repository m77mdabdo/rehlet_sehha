<?php

declare(strict_types=1);

namespace App\Filament\Resources\Patients\Tables;

use App\Models\Patient;
use App\Support\PhoneNumber;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PatientsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('phone')
                    ->label('الموبايل')
                    ->formatStateUsing(fn (string $state): string => PhoneNumber::forDisplay($state))
                    /*
                     * Searchable on the STORED value, which is E.164. A
                     * receptionist types what is on the caller display —
                     * "01012345678" — so the query is normalised before it
                     * runs, otherwise searching for the number a patient
                     * actually gave never finds her.
                     */
                    ->searchable(query: function (Builder $query, string $search): Builder {
                        $normalised = PhoneNumber::isValid($search)
                            ? PhoneNumber::e164($search)
                            : $search;

                        return $query->where('phone', 'like', '%'.$normalised.'%')
                            ->orWhere('phone', 'like', '%'.$search.'%');
                    }),

                TextColumn::make('email')
                    ->label('الإيميل')
                    ->placeholder('—')
                    ->toggleable(),

                IconColumn::make('reachable')
                    ->label('يوصلها تنبيه')
                    ->getStateUsing(fn (Patient $record): bool => filled($record->email))
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope')
                    ->falseIcon('heroicon-o-phone')
                    ->trueColor('gray')
                    ->falseColor('warning'),

                TextColumn::make('appointments_count')
                    ->label('عدد الحجوزات')
                    ->counts('appointments')
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('email')
                    ->label('عندها إيميل')
                    ->nullable()
                    ->attribute('email'),
            ])
            ->recordActions([
                EditAction::make()->label('فتح الملف'),
            ])
            // No bulk delete: a patient file is a medical record, and
            // PatientPolicy refuses a force delete outright.
            ->toolbarActions([]);
    }
}
