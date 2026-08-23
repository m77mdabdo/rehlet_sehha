<?php

declare(strict_types=1);

namespace App\Filament\Resources\Services\Pages;

use App\Filament\Concerns\EditsTranslations;
use App\Filament\Resources\Services\ServiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateService extends CreateRecord
{
    use EditsTranslations;

    protected static string $resource = ServiceResource::class;

    /**
     * Fold the flat ar/en fields back into the translation arrays spatie
     * stores. See the trait for why the form cannot bind to the field itself.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->foldTranslations($data);
    }
}
