<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\Post;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

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
                        ->helperText('سيبيها فاضية لو المقال لسه مسودة. مش هتقدري تنشري من غير مراجعة إكلينيكية.'),
                    Toggle::make('is_featured')->label('مقال مميز'),
                ])
                ->columns(2),

            /*
            |------------------------------------------------------------------
            | Clinical review
            |------------------------------------------------------------------
            |
            | DOCTOR AND ADMIN ONLY. The whole section is hidden from anybody
            | else — not merely disabled, because a disabled field still tells
            | a receptionist that signing an article off is a thing she is
            | nearly allowed to do, and the next step is asking somebody to
            | tick it for her.
            |
            | Visibility here is convenience. The rule that actually holds is
            | on the model: it refuses to SAVE a published article without a
            | named reviewer, whatever the form sends, and the same refusal
            | applies to seeders, imports and tinker.
            */
            Section::make('المراجعة الإكلينيكية')
                ->description('المقال مش هينشر من غير ما حد إكلينيكي يراجعه ويتسجل اسمه. ده مقال بيتنشر باسم دكتورة مقيّدة.')
                ->visible(fn (): bool => Auth::user()?->can('review', Post::class) ?? false)
                ->schema([
                    Select::make('reviewed_by')
                        ->label('راجعه')
                        ->relationship('reviewer', 'name')
                        ->searchable()
                        ->preload()
                        ->helperText('الاسم ده هيظهر للقارئة تحت العنوان.'),
                    DateTimePicker::make('reviewed_at')
                        ->label('اتراجع في')
                        ->helperText('تاريخ المراجعة، مش تاريخ النشر. الاتنين بيظهروا للقارئة.'),
                ])
                ->columns(2),
        ]);
    }
}
