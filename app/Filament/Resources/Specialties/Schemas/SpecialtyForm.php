<?php

declare(strict_types=1);

namespace App\Filament\Resources\Specialties\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Arabic and English side by side, both required. See App\Filament\Support\Bilingual
 * for why the pair is on one row rather than behind language tabs.
 */
class SpecialtyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('المحتوى')
                ->schema([
                    Bilingual::text('name', 'الاسم'),
                    Bilingual::textarea('description', 'الوصف'),
                ]),

            Section::make('الإعدادات')
                ->schema([
                    TextInput::make('slug')
                        ->label('الرابط (slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr'])
                        ->helperText('بيتولد من الاسم العربي. تغييره بيكسر أي لينك قديم.'),
                    TextInput::make('icon')->label('الأيقونة')->maxLength(64),
                    Toggle::make('is_active')->label('ظاهرة على الموقع')->default(true),
                ])
                ->columns(2),
        ]);
    }
}
