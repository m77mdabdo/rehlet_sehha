<?php

declare(strict_types=1);

namespace App\Filament\Resources\Appointments;

use App\Filament\Resources\Appointments\Pages\CreateAppointment;
use App\Filament\Resources\Appointments\Pages\EditAppointment;
use App\Filament\Resources\Appointments\Pages\ListAppointments;
use App\Filament\Resources\Appointments\RelationManagers\IntakeRelationManager;
use App\Filament\Resources\Appointments\Schemas\AppointmentForm;
use App\Filament\Resources\Appointments\Tables\AppointmentsTable;
use App\Models\Appointment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * The clinic's main screen.
 *
 * Everything here goes through AppointmentPolicy — Filament calls it for every
 * page, every row action and every bulk action, so there are no controller
 * checks and no `@if (auth()->user()->hasRole(...))` in a Blade file deciding
 * who sees what.
 *
 * The intake form is a RELATION on this resource rather than a resource of its
 * own, for two reasons. A patient's medical history has no meaning detached
 * from the appointment it was written for, so a top-level "Intake" list would
 * be a browsable index of everyone's conditions. And attaching it here means
 * the boundary is one policy on one relation, instead of a second navigation
 * item somebody has to remember to hide from reception.
 */
class AppointmentResource extends Resource
{
    protected static ?string $model = Appointment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?int $navigationSort = 1;

    public static function getModelLabel(): string
    {
        return 'حجز';
    }

    public static function getPluralModelLabel(): string
    {
        return 'الحجوزات';
    }

    public static function getNavigationLabel(): string
    {
        return 'الحجوزات';
    }

    /**
     * How many appointments today, on the sidebar.
     *
     * The number a receptionist actually wants at a glance when she opens the
     * panel, and the reason the badge counts TODAY rather than everything
     * pending: a total of 340 tells nobody anything.
     */
    public static function getNavigationBadge(): ?string
    {
        $zone = config('clinic.timezone');
        $start = now($zone)->startOfDay();

        $count = Appointment::query()
            ->countsTowardWorkload()
            ->whereBetween('starts_at', [
                $start->clone()->utc(),
                $start->clone()->endOfDay()->utc(),
            ])
            ->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function form(Schema $schema): Schema
    {
        return AppointmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AppointmentsTable::configure($table);
    }

    /**
     * @return array<class-string>
     */
    public static function getRelations(): array
    {
        return [
            IntakeRelationManager::class,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function getPages(): array
    {
        return [
            'index' => ListAppointments::route('/'),
            'create' => CreateAppointment::route('/create'),
            'edit' => EditAppointment::route('/{record}/edit'),
        ];
    }
}
