<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Tables;

use App\Enums\PostStatus;
use App\Models\Category;
use App\Models\Post;
use App\Support\Locales;
use App\Support\Photo;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * THE LIST IS WHERE THE BLOG IS ACTUALLY RUN.
 *
 * Dr. Rana edits her own articles. The thing she will do most often is not
 * writing — it is looking at fourteen rows and deciding which are ready, and
 * occasionally taking one down in a hurry. Both of those have to work without
 * opening a form.
 *
 * THE FASTEST WAY TO PULL A BAD ARTICLE MUST NEVER BE DELETING THE ROW. That
 * is the rule this file is built around. Unpublishing is one click, needs no
 * confirmation, and cannot fail — there is no gate on taking something down,
 * only on putting it up. Deleting is behind a bulk menu and takes the record
 * of what was said with it.
 *
 * THE GATES ARE NOT BYPASSED HERE, they are EXPLAINED here. Post::booted()
 * still throws on any attempt to save a published article that is not ready.
 * What this file adds is that she is told which of the six reasons applies,
 * in Arabic, before anything throws — see Post::publishBlockers().
 *
 * Articles are the one content type that is NOT drag-ordered. They have no
 * sort_order column and should not have one: articles are ordered by when they
 * were published, which is a fact rather than a preference, and a blog whose
 * order is hand-arranged stops being a chronology.
 */
class PostsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                /*
                 * The cover, small. She chose these images per article and the
                 * fastest way to notice one is wrong is to see it beside its
                 * own title.
                 */
                ImageColumn::make('cover_path')
                    ->label('الصورة')
                    ->getStateUsing(fn (Post $record): ?string => $record->cover_path && Photo::has($record->cover_path)
                        ? Photo::url($record->cover_path, 'sm')
                        : null)
                    ->height(40)
                    ->extraImgAttributes(['class' => 'rounded-md object-cover'])
                    ->toggleable(),

                TextColumn::make('title')
                    ->label('العنوان')
                    ->searchable()
                    ->wrap()
                    ->description(fn (Post $record): string => $record->slug),

                /*
                 * STATUS AT A GLANCE, and derived rather than stored — see
                 * PostStatus for why there is no status column in the table.
                 */
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->getStateUsing(fn (Post $record): string => $record->status()->label())
                    ->color(fn (Post $record): string => $record->status()->colour())
                    ->icon(fn (Post $record): string => $record->status()->icon()),

                /*
                 * WHAT IS STILL MISSING, counted.
                 *
                 * The single most useful column on this screen: it answers
                 * "which of these can I actually publish today" without opening
                 * anything. Empty is the good state, so the column is quiet
                 * when there is nothing to say.
                 */
                TextColumn::make('readiness')
                    ->label('ناقص')
                    ->badge()
                    ->color(fn (Post $record): string => $record->isReadyToPublish() ? 'success' : 'danger')
                    ->getStateUsing(function (Post $record): string {
                        if ($record->isReadyToPublish()) {
                            return 'جاهز';
                        }

                        $markers = $record->unansweredMarkers();
                        $parts = [];

                        if ($markers['clinical'] > 0) {
                            $parts[] = $markers['clinical'].' إكلينيكي';
                        }

                        if ($markers['practitioner'] > 0) {
                            $parts[] = $markers['practitioner'].' بصوتك';
                        }

                        $unverified = $record->citations->filter(fn ($c): bool => ! $c->isVerified())->count();

                        if ($unverified > 0) {
                            $parts[] = $unverified.' مصدر';
                        }

                        if ($record->reviewed_by === null || $record->reviewed_at === null) {
                            $parts[] = 'مراجعة';
                        }

                        return $parts === [] ? 'ناقص' : implode(' · ', $parts);
                    })
                    ->tooltip(fn (Post $record): ?string => $record->isReadyToPublish()
                        ? null
                        : implode("\n", $record->publishBlockers())),

                TextColumn::make('category.name')->label('التصنيف')->toggleable()->sortable(),

                TextColumn::make('locales')
                    ->label('اللغات')
                    ->badge()
                    ->color(fn (Post $record): string => $record->missingLocales() === [] ? 'success' : 'warning')
                    ->getStateUsing(fn (Post $record): string => $record->missingLocales() === []
                        ? 'عربي + إنجليزي'
                        : 'ناقص '.implode(' و', array_map(
                            fn (string $l): string => $l === 'ar' ? 'العربي' : 'الإنجليزي',
                            $record->missingLocales(),
                        )))
                    ->toggleable(),

                TextColumn::make('published_at')
                    ->label('اتنشر في')
                    ->dateTime('j F Y — H:i', timezone: config('clinic.timezone'))
                    ->placeholder('—')
                    ->sortable()
                    ->toggleable(),

                IconColumn::make('is_featured')->label('مميز')->boolean()->toggleable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(collect(PostStatus::cases())
                        ->mapWithKeys(fn (PostStatus $s): array => [$s->value => $s->label()])
                        ->all())
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            PostStatus::Draft->value => $query->whereNull('published_at'),
                            PostStatus::Scheduled->value => $query->whereNotNull('published_at')
                                ->where('published_at', '>', Carbon::now()),
                            PostStatus::Published->value => $query->whereNotNull('published_at')
                                ->where('published_at', '<=', Carbon::now()),
                            default => $query,
                        };
                    }),

                SelectFilter::make('category_id')
                    ->label('التصنيف')
                    ->relationship('category', 'slug')
                    ->getOptionLabelFromRecordUsing(fn (Category $record): string => (string) $record->name)
                    ->preload(),

                /*
                 * LOCALE COMPLETENESS, in SQL rather than in PHP.
                 *
                 * Filtering a page of results in PHP filters the page, not the
                 * table — the rows that were paginated away are simply never
                 * examined, and the filter appears to work while quietly
                 * lying. So this asks the database.
                 *
                 * A missing translation is either a null JSON key or an empty
                 * string, and spatie/translatable writes both, so both are
                 * checked.
                 */
                TernaryFilter::make('locale_complete')
                    ->label('مكتمل باللغتين')
                    ->placeholder('الكل')
                    ->trueLabel('عربي وإنجليزي')
                    ->falseLabel('ناقص لغة')
                    ->queries(
                        true: fn (Builder $query): Builder => self::whereLocalesComplete($query, true),
                        false: fn (Builder $query): Builder => self::whereLocalesComplete($query, false),
                        blank: fn (Builder $query): Builder => $query,
                    ),

                TernaryFilter::make('is_featured')->label('مميز'),
            ])
            ->recordActions([
                self::toggleAction(),
                EditAction::make()->label('تعديل'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    self::bulkPublish(),
                    self::bulkUnpublish(),
                    DeleteBulkAction::make()
                        ->label('حذف')
                        ->modalDescription(
                            'الحذف بيشيل المقال وسجله بالكامل. لو عايزة تشيليه من الموقع بس، '
                            .'استخدمي «إخفاء» — بيرجع مسودة في ثانية وتقدري تنشريه تاني من غير ما تكتبيه من الأول.'
                        ),
                ]),
            ]);
    }

    /**
     * ONE CLICK, BOTH DIRECTIONS, AND THEY ARE NOT SYMMETRICAL.
     *
     * Unpublishing needs no confirmation and cannot fail. Publishing checks
     * every gate first and, if it cannot proceed, says which one — because the
     * alternative is Post::booted() throwing a LogicException into a Livewire
     * request, which in production is a bare "Server Error" and no information
     * whatsoever.
     */
    private static function toggleAction(): Action
    {
        return Action::make('togglePublished')
            ->label(fn (Post $record): string => $record->published_at === null ? 'نشر' : 'إخفاء')
            ->icon(fn (Post $record): string => $record->published_at === null ? 'heroicon-o-globe-alt' : 'heroicon-o-eye-slash')
            ->color(fn (Post $record): string => $record->published_at === null ? 'success' : 'gray')
            ->visible(fn (): bool => Auth::user()?->can('review', Post::class) ?? false)
            ->action(function (Post $record): void {
                if ($record->published_at !== null) {
                    /*
                     * TAKING SOMETHING DOWN IS NEVER GATED. If an article is
                     * wrong, the fastest path off the site must be one click
                     * with nothing standing in the way — otherwise the fastest
                     * path becomes deleting the row, and the record of what was
                     * published goes with it.
                     */
                    $record->update(['published_at' => null]);

                    Notification::make()
                        ->title('اترفع من الموقع')
                        ->body('المقال بقى مسودة. الكلام كله موجود زي ما هو، وتقدري تنشريه تاني في أي وقت.')
                        ->success()
                        ->send();

                    return;
                }

                $blockers = $record->publishBlockers();

                if ($blockers !== []) {
                    Notification::make()
                        ->title('المقال ده لسه مش جاهز للنشر')
                        ->body('• '.implode("\n• ", $blockers))
                        ->danger()
                        ->persistent()
                        ->send();

                    return;
                }

                $record->update(['published_at' => Carbon::now()]);

                Notification::make()
                    ->title('اتنشر')
                    ->body('المقال بقى على الموقع باللغتين.')
                    ->success()
                    ->send();
            });
    }

    private static function bulkPublish(): BulkAction
    {
        return BulkAction::make('publishSelected')
            ->label('نشر المحدد')
            ->icon('heroicon-o-globe-alt')
            ->color('success')
            ->visible(fn (): bool => Auth::user()?->can('review', Post::class) ?? false)
            ->action(function (Collection $records): void {
                $published = 0;
                $refused = [];

                foreach ($records as $record) {
                    if (! $record instanceof Post || $record->published_at !== null) {
                        continue;
                    }

                    $blockers = $record->publishBlockers();

                    if ($blockers !== []) {
                        $refused[] = $record->getTranslation('title', 'ar', false).' — '.$blockers[0];

                        continue;
                    }

                    $record->update(['published_at' => Carbon::now()]);
                    $published++;
                }

                /*
                 * PARTIAL SUCCESS IS REPORTED AS PARTIAL. A bulk action that
                 * publishes four of nine and says "done" is how somebody
                 * believes an article is live for a fortnight.
                 */
                if ($refused === []) {
                    Notification::make()
                        ->title("اتنشر {$published} مقال")
                        ->success()
                        ->send();

                    return;
                }

                Notification::make()
                    ->title($published > 0 ? "اتنشر {$published}، واتساب ".count($refused) : 'محصلش نشر')
                    ->body('• '.implode("\n• ", array_slice($refused, 0, 8)))
                    ->warning()
                    ->persistent()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    private static function bulkUnpublish(): BulkAction
    {
        return BulkAction::make('unpublishSelected')
            ->label('إخفاء المحدد')
            ->icon('heroicon-o-eye-slash')
            ->color('gray')
            ->visible(fn (): bool => Auth::user()?->can('review', Post::class) ?? false)
            ->action(function (Collection $records): void {
                // No gate, no confirmation, no partial failure. See toggleAction().
                $count = 0;

                foreach ($records as $record) {
                    if (! $record instanceof Post || $record->published_at === null) {
                        continue;
                    }

                    $record->update(['published_at' => null]);
                    $count++;
                }

                Notification::make()
                    ->title("اترفع {$count} مقال من الموقع")
                    ->body('كلهم بقوا مسودات. مفيش حاجة اتمسحت.')
                    ->success()
                    ->send();
            })
            ->deselectRecordsAfterCompletion();
    }

    /**
     * @param  Builder<Post>  $query
     * @return Builder<Post>
     */
    private static function whereLocalesComplete(Builder $query, bool $complete): Builder
    {
        $incomplete = function (Builder $query): void {
            foreach (Locales::all() as $locale) {
                foreach (['title', 'excerpt', 'body'] as $field) {
                    $query->orWhereNull("{$field}->{$locale}")
                        ->orWhere("{$field}->{$locale}", '');
                }
            }
        };

        return $complete
            ? $query->whereNot($incomplete)
            : $query->where($incomplete);
    }
}
