<?php

declare(strict_types=1);

namespace App\Filament\Resources\Testimonials\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

/**
 * Arabic and English side by side, both required. See App\Filament\Support\Bilingual
 * for why the pair is on one row rather than behind language tabs.
 */
class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('الرأي')
                ->schema([
                    Bilingual::text('context', 'السياق'),
                    Bilingual::textarea('quote', 'النص'),
                ]),

            Section::make('صاحبة الرأي')
                ->schema([
                    TextInput::make('name')->label('الاسم')->required()->maxLength(120),
                    TextInput::make('initials')->label('الحروف الأولى')->maxLength(4),
                    TextInput::make('rating')->label('التقييم')->numeric()->minValue(1)->maxValue(5),
                    Toggle::make('is_active')->label('ظاهر على الموقع')->default(true),
                ])
                ->columns(2),
        ]);
    }
}
