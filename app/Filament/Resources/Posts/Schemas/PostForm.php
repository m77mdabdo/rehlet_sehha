<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Arabic and English side by side, both required. See App\Filament\Support\Bilingual
 * for why the pair is on one row rather than behind language tabs.
 */
class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('المقال')
                ->schema([
                    Bilingual::text('title', 'العنوان'),
                    Bilingual::text('category', 'التصنيف'),
                    Bilingual::textarea('excerpt', 'المقدمة', rows: 3),
                    Bilingual::rich('body', 'النص'),
                ]),

            Section::make('النشر')
                ->schema([
                    TextInput::make('slug')
                        ->label('الرابط (slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('reading_minutes')->label('دقايق القراءة')->numeric()->minValue(1),
                    DateTimePicker::make('published_at')
                        ->label('اتنشر في')
                        ->helperText('سيبيها فاضية لو المقال لسه مسودة.'),
                    Toggle::make('is_featured')->label('مقال مميز'),
                ])
                ->columns(2),
        ]);
    }
}
