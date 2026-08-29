<?php

namespace App\Filament\Resources\WorkingHours\Pages;

use App\Filament\Resources\WorkingHours\WorkingHourResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditWorkingHour extends EditRecord
{
    protected static string $resource = WorkingHourResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Deleting removes the only record of what the hours were.
            // Deactivating is almost always what was meant, and the form
            // offers it one field away.
            DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
