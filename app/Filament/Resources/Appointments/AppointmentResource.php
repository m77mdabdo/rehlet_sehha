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
     * How many bookings nobody has acted on, on the sidebar. Any date.
     *
     * THIS USED TO COUNT TODAY, and that is the defect rather than a style
     * choice. The old note argued that today is "the number a receptionist
     * actually wants at a glance", because a running total of everything
     * pending tells nobody anything. The first half was right and the second
     * half answered a question nobody asked: `pending` is not a backlog of
     * everything, it is the set of bookings waiting for a human — which for
     * this clinic is normally nought, occasionally one.
     *
     * Counting today meant the badge was blank on every morning that mattered.
     * A booking made on 26 September for the 27th showed a badge on exactly one
     * day, which nobody happened to look at, and then went quiet while still
     * unconfirmed. Its date passed. A second did the same two weeks later.
     *
     * Today's number has not been lost: ClinicOverview carries it on the
     * dashboard and TodaySchedule lists the appointments themselves. The
     * sidebar is now the only place that answers "is anything waiting for me",
     * and it answers it regardless of when the appointment happens to fall.
     */
    public static function getNavigationBadge(): ?string
    {
        $count = Appointment::query()->awaitingConfirmation()->count();

        return $count > 0 ? (string) $count : null;
    }

    /**
     * Never grey. A booking waiting on a human is the one thing on this
     * sidebar that is asking for something, and it should not look like a
     * count of how busy today is.
     */
    public static function getNavigationBadgeColor(): ?string
    {
        return Appointment::query()->awaitingConfirmation()->count() > 0 ? 'warning' : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'حجوزات مستنية تأكيد من العيادة';
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
