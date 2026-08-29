<?php

namespace App\Filament\Resources\WorkingHours;

use App\Filament\Resources\WorkingHours\Pages\CreateWorkingHour;
use App\Filament\Resources\WorkingHours\Pages\EditWorkingHour;
use App\Filament\Resources\WorkingHours\Pages\ListWorkingHours;
use App\Filament\Resources\WorkingHours\Schemas\WorkingHourForm;
use App\Filament\Resources\WorkingHours\Tables\WorkingHoursTable;
use App\Models\WorkingHour;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

/**
 * THE SCHEDULE, EDITABLE BY THE PERSON WHO WORKS IT.
 *
 * This table decided which slots the booking form offered, which hours the
 * JSON-LD advertised and what the footer told a patient — and it had no admin
 * screen at all. Changing Saturday's closing time meant running a seeder or
 * opening the database by hand, which in practice means it never changed, or
 * it changed and nobody could say who did it.
 *
 * Doctor and admin only; see WorkingHourPolicy for why reception is excluded.
 *
 * SAVING INVALIDATES THE PUBLIC CACHE automatically: the model uses
 * FlushesPublicContentCache, so a corrected time is live on the next request
 * rather than on the next TTL boundary. PublicContentCacheTest asserts exactly
 * that, and asserted it before this screen existed.
 */
class WorkingHourResource extends Resource
{
    protected static ?string $model = WorkingHour::class;

    protected static string|\UnitEnum|null $navigationGroup = 'الإعدادات';

    protected static ?int $navigationSort = 5;

    public static function getModelLabel(): string
    {
        return 'ميعاد عمل';
    }

    public static function getPluralModelLabel(): string
    {
        return 'مواعيد العمل';
    }

    public static function getNavigationLabel(): string
    {
        return 'مواعيد العمل';
    }

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    public static function form(Schema $schema): Schema
    {
        return WorkingHourForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return WorkingHoursTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListWorkingHours::route('/'),
            'create' => CreateWorkingHour::route('/create'),
            'edit' => EditWorkingHour::route('/{record}/edit'),
        ];
    }
}
