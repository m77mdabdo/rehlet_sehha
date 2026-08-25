<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\EditReview;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/**
 * The moderation queue.
 *
 * REVIEWS DO NOT PUBLISH THEMSELVES. Somebody reads every one before it is on
 * the internet, because a patient may write a diagnosis into a public box, or
 * another person's name, or something she will want back — and that is caught
 * here or not at all.
 *
 * NOTHING CAN BE CREATED HERE. ReviewPolicy::create() returns false for
 * everybody. A review typed into the admin is a testimonial the clinic wrote
 * about itself, which is exactly what this system replaced.
 *
 * CONSENT IS NOT EDITABLE. It is shown, never changed: it is the patient's
 * decision, recorded at the moment she made it, and an admin who could toggle
 * it could publish something she declined to publish. The model throws if an
 * approval is saved without it.
 */
class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|\UnitEnum|null $navigationGroup = 'محتوى الموقع';

    protected static ?int $navigationSort = 13;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    public static function getModelLabel(): string
    {
        return 'تقييم';
    }

    public static function getPluralModelLabel(): string
    {
        return 'تقييمات المرضى';
    }

    public static function getNavigationLabel(): string
    {
        return 'تقييمات المرضى';
    }

    /** The queue length: what is waiting to be read. */
    public static function getNavigationBadge(): ?string
    {
        $pending = Review::pending()->count();

        return $pending > 0 ? (string) $pending : null;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('ما كتبته المريضة')
                ->description(
                    'اقرأي التقييم كامل قبل الموافقة. لو فيه تشخيص أو أسماء أدوية أو نتايج تحاليل أو '
                    .'اسم شخص تاني — ارفضيه أو اطلبي تعديله. ده معلومات صحية خاصة، ونشرها على الموقع '
                    .'حاجة صعب ترجع فيها.'
                )
                ->schema([
                    Placeholder::make('rating_display')
                        ->label('التقييم')
                        ->content(fn (?Review $record): string => $record?->rating ? $record->rating.' / 5' : '—'),

                    Placeholder::make('comment_display')
                        ->label('النص')
                        ->content(fn (?Review $record): string => $record?->comment ?: '—'),

                    Placeholder::make('consent_display')
                        ->label('موافقة النشر')
                        ->content(fn (?Review $record): string => $record?->consented_at
                            ? 'وافقت في '.$record->consented_at->format('Y-m-d')
                            : 'لم توافق — لا يمكن نشره'),
                ]),

            Section::make('ما يظهر على الموقع')
                ->schema([
                    TextInput::make('display_name')
                        ->label('الاسم المعروض')
                        ->helperText('الاسم الأول وحرف بيكفي. الاسم الكامل جنب كلام عن علاج طبي تعريف أكتر مما تقصده.')
                        ->maxLength(60),

                    /*
                     * Approval is a checkbox rather than a hidden timestamp so
                     * it is a deliberate act, and it is DISABLED when consent
                     * is absent — the moderator sees why they cannot publish
                     * rather than clicking and getting an exception.
                     */
                    Checkbox::make('is_approved')
                        ->label('اعتمدي النشر على الموقع')
                        ->helperText('مش هيتنشر غير لو المريضة وافقت.')
                        ->disabled(fn (?Review $record): bool => $record?->consented_at === null)
                        ->dehydrated(false)
                        ->afterStateHydrated(fn (Checkbox $component, ?Review $record) => $component->state($record?->approved_at !== null)),

                    Textarea::make('moderation_note')
                        ->label('ملاحظة داخلية')
                        ->helperText('مش بتظهر للمريضة ولا على الموقع.')
                        ->rows(2),
                ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('display_name')->label('الاسم')->searchable(),

                TextColumn::make('rating')->label('التقييم')->badge()
                    ->formatStateUsing(fn (?int $state): string => $state ? $state.'/5' : '—'),

                TextColumn::make('comment')->label('النص')->limit(60)->wrap(),

                IconColumn::make('consented_at')->label('وافقت')->boolean(),

                IconColumn::make('approved_at')->label('منشور')->boolean(),

                TextColumn::make('submitted_at')->label('اتكتب')->dateTime('Y-m-d')->sortable(),
            ])
            ->filters([
                Filter::make('pending')
                    ->label('في انتظار المراجعة')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('submitted_at')->whereNull('approved_at'))
                    ->default(),
            ])
            ->recordActions([EditAction::make()])
            ->toolbarActions([BulkActionGroup::make([])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReviews::route('/'),
            'edit' => EditReview::route('/{record}/edit'),
        ];
    }
}
