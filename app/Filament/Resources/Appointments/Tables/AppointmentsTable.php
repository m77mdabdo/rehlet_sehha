<?php

declare(strict_types=1);

namespace App\Filament\Resources\Appointments\Tables;

use App\Enums\AppointmentMode;
use App\Enums\AppointmentStatus;
use App\Filament\Actions\AppointmentActions;
use App\Models\Appointment;
use App\Models\Service;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

/**
 * The day list.
 *
 * A list rather than a month calendar, and defaulting to today. The question
 * this screen answers first thing in the morning is "who is coming in, and
 * when" — a month grid answers "what does August look like", which nobody asks
 * at 08:00. The date filter opens the rest.
 */
class AppointmentsTable
{
    public static function configure(Table $table): Table
    {
        $zone = config('clinic.timezone');

        return $table
            ->defaultSort('starts_at')
            /*
             * Eager loaded because every row reads the patient (for the name,
             * the phone, and whether she can be reached at all) and the
             * service. Without this the day list is N+1 across three
             * relations.
             */
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with(['patient', 'service', 'staff']))
            ->columns([
                TextColumn::make('starts_at')
                    ->label('الميعاد')
                    ->dateTime('D j M — H:i', timezone: $zone)
                    ->sortable()
                    ->description(fn (Appointment $record): string => $record->reference),

                TextColumn::make('patient.name')
                    ->label('المريضة')
                    ->searchable()
                    ->description(fn (Appointment $record): string => $record->patient->phone),

                /*
                 * The unreachable badge, same fact the daily schedule email
                 * carries: this patient gave no email address, so she received
                 * no confirmation and will get no reminder. Whoever is on
                 * reception needs to see that on the row, not by opening it.
                 */
                IconColumn::make('reachable')
                    ->label('يوصلها تنبيه')
                    ->getStateUsing(fn (Appointment $record): bool => $record->isReachableByEmail())
                    ->boolean()
                    ->trueIcon('heroicon-o-envelope')
                    ->falseIcon('heroicon-o-phone')
                    ->trueColor('gray')
                    ->falseColor('warning')
                    ->tooltip(fn (Appointment $record): string => $record->isReachableByEmail()
                        ? 'هيوصلها تأكيد وتنبيهات بالإيميل'
                        : 'مفيش إيميل — لازم مكالمة'),

                TextColumn::make('service.name')
                    ->label('الباقة')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('staff.name')
                    ->label('الدكتورة')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mode')
                    ->label('نوع الاستشارة')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentMode $state): string => $state->label())
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state): string => $state->label())
                    ->color(fn (AppointmentStatus $state): string => match ($state) {
                        AppointmentStatus::Pending => 'warning',
                        AppointmentStatus::Confirmed => 'success',
                        AppointmentStatus::Completed => 'gray',
                        AppointmentStatus::Cancelled => 'danger',
                        AppointmentStatus::NoShow => 'danger',
                    }),
            ])
            ->filters([
                /*
                 * Defaults to TODAY. Filament applies default filter state on
                 * first load, so the screen opens on the day the clinic is
                 * working rather than on every appointment ever made.
                 */
                Filter::make('date_range')
                    ->label('التاريخ')
                    ->schema([
                        DatePicker::make('from')
                            ->label('من')
                            ->default(fn () => now(config('clinic.timezone'))->toDateString()),
                        DatePicker::make('until')
                            ->label('إلى')
                            ->default(fn () => now(config('clinic.timezone'))->toDateString()),
                    ])
                    ->query(function (Builder $query, array $data) use ($zone): Builder {
                        /*
                         * The filter takes CAIRO dates and the column stores
                         * UTC instants, so each bound is converted rather than
                         * compared directly. Comparing a Cairo date against a
                         * UTC column silently drops the first hours of the day
                         * for half the year.
                         */
                        return $query
                            ->when(
                                $data['from'] ?? null,
                                fn (Builder $q, string $from): Builder => $q->where(
                                    'starts_at',
                                    '>=',
                                    Carbon::parse($from, $zone)->startOfDay()->utc(),
                                ),
                            )
                            ->when(
                                $data['until'] ?? null,
                                fn (Builder $q, string $until): Builder => $q->where(
                                    'starts_at',
                                    '<=',
                                    Carbon::parse($until, $zone)->endOfDay()->utc(),
                                ),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['from'] ?? null) {
                            $indicators[] = 'من '.$data['from'];
                        }

                        if ($data['until'] ?? null) {
                            $indicators[] = 'إلى '.$data['until'];
                        }

                        return $indicators;
                    }),

                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(AppointmentStatus::options())
                    ->multiple(),

                SelectFilter::make('service_id')
                    ->label('الباقة')
                    ->options(fn (): array => Service::query()->pluck('name', 'id')->all())
                    ->multiple(),

                SelectFilter::make('mode')
                    ->label('نوع الاستشارة')
                    ->options(fn (): array => collect(AppointmentMode::cases())
                        ->mapWithKeys(fn (AppointmentMode $mode): array => [$mode->value => $mode->label()])
                        ->all()),
            ])
            ->recordActions(AppointmentActions::forTable())
            /*
             * No bulk actions. Every action here notifies a patient or changes
             * a clinical record, and neither is something to do to forty rows
             * at once by accident — least of all cancelling, which mails
             * everyone it touches.
             */
            ->toolbarActions([])
            ->emptyStateHeading('مفيش حجوزات في الفترة دي')
            ->emptyStateDescription('غيّري التاريخ من الفلتر فوق عشان تشوفي أيام تانية.');
    }
}
