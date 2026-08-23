<?php

declare(strict_types=1);

namespace App\Filament\Resources\Patients\RelationManagers;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\IntakeForm;
use App\Services\Clinical\ClinicalAccessLog;
use Filament\Actions\Action;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

/**
 * This patient's appointment history.
 *
 * Visible to all three roles: reception needs to see that someone has been
 * three times before and cancelled twice.
 *
 * The INTAKE of each past appointment is not. It sits behind a per-row action
 * that IntakeFormPolicy authorises, so the history is a list of dates and
 * outcomes for reception, and a clinical record for the doctor — from the same
 * screen, without a second page.
 *
 * Opening that action logs the read, with the reader's identity. See
 * ClinicalAccessLog.
 */
class AppointmentsRelationManager extends RelationManager
{
    protected static string $relationship = 'appointments';

    protected static ?string $title = 'تاريخ الحجوزات';

    public function table(Table $table): Table
    {
        $zone = config('clinic.timezone');

        return $table
            ->recordTitleAttribute('reference')
            ->defaultSort('starts_at', 'desc')
            ->columns([
                TextColumn::make('starts_at')
                    ->label('الميعاد')
                    ->dateTime('j F Y — H:i', timezone: $zone)
                    ->sortable(),

                TextColumn::make('reference')->label('رقم الحجز'),

                TextColumn::make('service.name')->label('الباقة'),

                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (AppointmentStatus $state): string => $state->label())
                    ->color(fn (AppointmentStatus $state): string => match ($state) {
                        AppointmentStatus::Pending => 'warning',
                        AppointmentStatus::Confirmed => 'success',
                        AppointmentStatus::Completed => 'gray',
                        AppointmentStatus::Cancelled, AppointmentStatus::NoShow => 'danger',
                    }),
            ])
            ->recordActions([
                Action::make('viewIntake')
                    ->label('المعلومات الطبية')
                    ->icon('heroicon-o-clipboard-document-list')
                    /*
                     * The gate. A receptionist never sees this button, and
                     * because the modal body is built inside the action, the
                     * content is not rendered — or queried — unless she is
                     * authorised. Nothing clinical reaches her payload.
                     */
                    ->authorize(fn (): bool => auth()->user()->can('viewAny', IntakeForm::class))
                    ->visible(fn (Appointment $record): bool => $record->intakeForm !== null)
                    ->modalHeading(fn (Appointment $record): string => 'المعلومات الطبية — '.$record->reference)
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('إغلاق')
                    ->action(function (): void {
                        // Read-only. The action exists to open the modal.
                    })
                    ->mountUsing(function (Appointment $record): void {
                        // Logged as the modal is opened, which is the moment a
                        // human actually looked at it.
                        if ($record->intakeForm !== null) {
                            ClinicalAccessLog::read($record->intakeForm, 'patient.history');
                        }
                    })
                    ->modalContent(fn (Appointment $record) => view('filament.clinical.intake-summary', [
                        'intake' => $record->intakeForm,
                    ])),
            ])
            ->emptyStateHeading('مفيش حجوزات في الملف ده');
    }
}
