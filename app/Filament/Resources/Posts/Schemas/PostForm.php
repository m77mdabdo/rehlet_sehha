<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Schemas;

use App\Filament\Support\Bilingual;
use App\Models\Category;
use App\Models\Post;
use App\Models\Tag;
use Closure;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ViewField;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

/**
 * Arabic and English side by side, both required. See App\Filament\Support\Bilingual
 * for why the pair is on one row rather than behind language tabs.
 */
class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            /*
            |------------------------------------------------------------------
            | What is still standing between this article and the site
            |------------------------------------------------------------------
            |
            | THE SAME SENTENCES THE LIST SHOWS, on the screen where she can act
            | on them. Both read Post::publishBlockers(), so there is one place
            | that knows the rules and no way for the two screens to disagree.
            |
            | Only on an existing record: a brand-new article has no citations
            | and no reviewer by definition, and opening a blank form under a
            | red panel listing six failures teaches her to ignore the panel.
            */
            Section::make('جاهزية النشر')
                ->visible(fn (?Post $record): bool => $record !== null)
                ->schema([
                    Placeholder::make('blockers')
                        ->hiddenLabel()
                        ->content(function (?Post $record) {
                            if ($record === null) {
                                return '';
                            }

                            $blockers = $record->publishBlockers();

                            if ($blockers === []) {
                                return new HtmlString(
                                    '<p class="text-success-600 dark:text-success-400 font-medium">'
                                    .'المقال جاهز للنشر. كل المراجعات اتعملت ومفيش حاجة ناقصة.</p>'
                                );
                            }

                            $items = collect($blockers)
                                ->map(fn (string $b): string => '<li>'.e($b).'</li>')
                                ->implode('');

                            return new HtmlString(
                                '<p class="mb-2 font-medium text-danger-600 dark:text-danger-400">'
                                .'المقال ده مش هينشر لحد ما الحاجات دي تتظبط:</p>'
                                .'<ul class="list-disc space-y-1 ps-5 text-sm">'.$items.'</ul>'
                            );
                        }),
                ])
                ->collapsible(),

            Section::make('المقال')
                ->schema([
                    Bilingual::text('title', 'العنوان'),
                    /*
                     * NO `category` FIELD HERE, and there was one until now.
                     *
                     * It was a pair of REQUIRED bilingual text inputs bound to
                     * `posts.category` — a free-text column dropped months ago
                     * when categories became a relation. The migration removed
                     * the column and nobody removed the field, so the edit form
                     * has been rendering two mandatory boxes for an attribute
                     * that does not exist.
                     *
                     * The real category is the `category_id` select below, and
                     * it has been there the whole time; these were a second,
                     * broken way to answer the same question.
                     *
                     * Found by opening the form. Nothing else would have: no
                     * test rendered the Filament form, and PHPStan cannot know
                     * which strings are column names.
                     */
                    Bilingual::textarea('excerpt', 'المقدمة', rows: 3),
                    Bilingual::rich('body', 'النص'),
                ]),

            /*
            |------------------------------------------------------------------
            | The search snippet
            |------------------------------------------------------------------
            |
            | Optional, and separate from the excerpt on purpose. The excerpt
            | sits under a headline on the index and can run as long as it
            | reads; a meta description is cut at roughly 155 characters
            | mid-word. Leaving both blank keeps the previous behaviour exactly
            | — the title and the excerpt are used.
            */
            Section::make('العنوان والوصف في نتايج البحث')
                ->description('اختياري. سيبيهم فاضيين والموقع هيستخدم العنوان والمقدمة زي ما هما.')
                ->collapsed()
                ->schema([
                    Bilingual::text('meta_title', 'عنوان البحث', required: false),
                    Bilingual::textarea('meta_description', 'وصف البحث', required: false, rows: 2),
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

                    /*
                     * A PICTURE PICKER, NOT A FILENAME DROPDOWN.
                     *
                     * This was a searchable Select over the manifest slugs.
                     * «food-fruit-bowl» and «food-vegetables-overhead» are
                     * indistinguishable in a list, so choosing a photograph
                     * meant opening config/photos.php, reading `describes` and
                     * guessing — which is exactly how twelve articles ended up
                     * sharing generic covers chosen for other pieces.
                     *
                     * See resources/views/filament/forms/photo-picker.blade.php.
                     */
                    ViewField::make('cover_path')
                        ->label('صورة المقال')
                        ->view('filament.forms.photo-picker')
                        ->columnSpanFull()
                        ->helperText('الصورة اللي بتظهر فوق المقال وفي لينك المشاركة على واتساب.'),
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
