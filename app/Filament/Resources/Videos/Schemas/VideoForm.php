<?php

declare(strict_types=1);

namespace App\Filament\Resources\Videos\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Arabic and English side by side, both required. See App\Filament\Support\Bilingual
 * for why the pair is on one row rather than behind language tabs.
 */
class VideoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الفيديو')
                ->schema([
                    Bilingual::text('title', 'العنوان'),
                    Bilingual::textarea('description', 'الوصف', required: false, rows: 3),
                ]),

            Section::make('الإعدادات')
                ->schema([
                    TextInput::make('youtube_id')
                        ->label('كود اليوتيوب')
                        ->required()
                        ->extraInputAttributes(['dir' => 'ltr'])
                        ->helperText('الجزء اللي بعد v= في لينك اليوتيوب.'),
                    TextInput::make('duration_seconds')->label('المدة بالثواني')->numeric(),
                    TextInput::make('category')->label('التصنيف')->maxLength(64),
                    Toggle::make('is_active')->label('ظاهر على الموقع')->default(true),
                    Toggle::make('is_featured')->label('فيديو مميز'),
                ])
                ->columns(2),
        ]);
    }
}
