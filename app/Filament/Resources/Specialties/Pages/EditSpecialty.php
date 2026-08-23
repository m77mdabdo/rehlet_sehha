<?php

declare(strict_types=1);

namespace App\Filament\Resources\Specialties\Pages;

use App\Filament\Concerns\EditsTranslations;
use App\Filament\Resources\Specialties\SpecialtyResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSpecialty extends EditRecord
{
    use EditsTranslations;

    protected static string $resource = SpecialtyResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->expandTranslations($data, $this->getRecord());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->foldTranslations($data);
    }
}
