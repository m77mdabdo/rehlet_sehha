<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Pages;

use App\Filament\Concerns\EditsTranslations;
use App\Filament\Resources\Faqs\FaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    use EditsTranslations;

    protected static string $resource = FaqResource::class;

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
