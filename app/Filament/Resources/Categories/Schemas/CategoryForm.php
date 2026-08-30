<?php

declare(strict_types=1);

namespace App\Filament\Resources\Categories\Schemas;

use App\Filament\Support\Bilingual;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CategoryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('التصنيف')
                ->schema([
                    Bilingual::text('name', 'الاسم'),
                    Bilingual::textarea('description', 'الوصف', rows: 2),
                ]),

            Section::make('البحث والترتيب')
                ->description('الوصف ده بيظهر لجوجل في نتيجة البحث على صفحة التصنيف.')
                ->schema([
                    Bilingual::textarea('meta_description', 'وصف البحث', rows: 2),

                    TextInput::make('slug')
                        ->label('الرابط (slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->helperText('بالإنجليزي. ده بيدخل في الرابط، ولما يتغيّر الرابط القديم بيبقى مكسور.')
                        ->extraInputAttributes(['dir' => 'ltr']),

                    TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),

                    Toggle::make('is_active')
                        ->label('ظاهر')
                        ->default(true)
                        ->helperText('اقفليه بدل ما تمسحي: المسح بيسيب المقالات من غير تصنيف.'),
                ])
                ->columns(2),
        ]);
    }
}
