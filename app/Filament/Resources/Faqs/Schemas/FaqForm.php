<?php

declare(strict_types=1);

namespace App\Filament\Resources\Faqs\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Arabic and English side by side, both required. See App\Filament\Support\Bilingual
 * for why the pair is on one row rather than behind language tabs.
 */
class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('السؤال')
                ->schema([
                    Bilingual::text('question', 'السؤال'),
                    Bilingual::textarea('answer', 'الإجابة'),
                ]),

            Section::make('الإعدادات')
                ->schema([
                    Toggle::make('is_active')->label('ظاهر على الموقع')->default(true),
                ]),
        ]);
    }
}
