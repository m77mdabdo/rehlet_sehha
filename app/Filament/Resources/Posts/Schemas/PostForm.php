<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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

            Section::make('التصنيف والوسوم')
                ->schema([
                    Select::make('category_id')
                        ->label('التصنيف')
                        ->relationship('category', 'slug')
                        ->getOptionLabelFromRecordUsing(fn (Category $record): string => (string) $record->name)
                        ->searchable()
                        ->preload()
                        ->required()
                        ->helperText('المقال بيظهر في صفحة التصنيف ده.'),

                    Select::make('tags')
                        ->label('الوسوم')
                        ->relationship('tags', 'slug')
                        ->getOptionLabelFromRecordUsing(fn (Tag $record): string => (string) $record->name)
                        ->multiple()
                        ->preload()
                        ->helperText('اختياري. الوسم بيجمع مقالات من تصنيفات مختلفة بتتكلم عن نفس الموضوع.'),

                    Select::make('cover_path')
                        ->label('الصورة')
                        ->options(function (): array {
                            /** @var array<string, array{describes: string}> $manifest */
                            $manifest = require resource_path('photos-manifest.php');

                            $options = [];

                            foreach ($manifest as $slug => $entry) {
                                $options[$slug] = $slug.' — '.Str::limit($entry['describes'], 60);
                            }

                            return $options;
                        })
                        ->searchable()
                        ->helperText('من مكتبة الصور المعالجة. الوصف بيساعدك تختاري صورة تخص المقال فعلاً.'),
                ])
                ->columns(2),

            Section::make('النشر')
                ->schema([
                    TextInput::make('slug')
                        ->label('الرابط (slug)')
                        ->required()
                        ->unique(ignoreRecord: true)
                        ->extraInputAttributes(['dir' => 'ltr']),
                    TextInput::make('reading_minutes')->label('دقايق القراءة')->numeric()->minValue(1),
                    /*
                     * PUBLISHING IS REFUSED HERE, IN WORDS, BEFORE THE MODEL
                     * REFUSES IT WITH AN EXCEPTION.
                     *
                     * Post::booted() throws a LogicException on any attempt to
                     * save a published article with no named reviewer, which
                     * is right and stays. But an exception is not a message: it
                     * reached Filament unhandled and produced a 500 — a raw
                     * stack trace in local, and a bare "Server Error" page in
                     * production, where APP_DEBUG is off and she would be told
                     * nothing whatsoever.
                     *
                     * So the same rule is stated as validation, attached to
                     * the field she is actually editing.
                     */
                    DateTimePicker::make('published_at')
                        ->label('اتنشر في')
                        ->helperText('سيبيها فاضية لو المقال لسه مسودة. مش هتقدري تنشري من غير مراجعة إكلينيكية.')
                        ->rules([
                            fn (Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                                if (blank($value)) {
                                    return;
                                }

                                if (blank($get('reviewed_by')) || blank($get('reviewed_at'))) {
                                    $fail(
                                        'المقال ده مش هينشر من غير مراجعة إكلينيكية. '
                                        .'اختاري مين راجعه وتاريخ المراجعة تحت، أو سيبي تاريخ النشر فاضي عشان يفضل مسودة.'
                                    );
                                }
                            },
                        ]),
                    /*
                     * Scheduling is published_at itself, not a separate flag.
                     * Post::published() requires the date to have PASSED, so a
                     * future date is a scheduled article and 404s until then —
                     * one field, one meaning, and no way for a "scheduled"
                     * boolean to disagree with the date beside it.
                     */
                    DateTimePicker::make('content_updated_at')
                        ->label('اتحدّث في')
                        ->helperText('سيبيها فاضية إلا لو غيّرتي كلام فعلاً. ده بيظهر للقارئة وبيتبعت لجوجل — تصحيح إملائي مش تحديث.'),

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
