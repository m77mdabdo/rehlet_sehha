<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Concerns\EditsTranslations;
use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class EditPost extends EditRecord
{
    use EditsTranslations;

    protected static string $resource = PostResource::class;

    /**
     * @return array<int, Action>
     */
    protected function getHeaderActions(): array
    {
        return [
            /*
             * Preview opens the live article page in a new tab. Only offered
             * once it is published, because an unpublished slug 404s — the
             * clinical review gate applies to preview exactly as it does to
             * everything else, and a "preview" that bypassed it would be a
             * way to read unreviewed clinical content on the public site.
             */
            Action::make('preview')
                ->label('معاينة')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(function (): string {
                    $post = $this->getRecord();

                    return $post instanceof Post
                        ? route('posts.show', ['locale' => 'ar', 'slug' => $post->slug])
                        : route('articles', ['locale' => 'ar']);
                })
                ->openUrlInNewTab()
                ->visible(function (): bool {
                    $post = $this->getRecord();

                    return $post instanceof Post && $post->published_at !== null;
                }),

            DeleteAction::make(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $this->expandTranslations($data, $this->getRecord());
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return $this->foldTranslations($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        try {
            return parent::handleRecordUpdate($record, $data);
        } catch (LogicException $e) {
            $this->handleUnreviewedPublish($e);

            /*
             * Halt explicitly rather than through $this->halt(). Both throw the
             * same exception and stop the save; only this form tells a static
             * analyser that the method never falls through, which is true.
             */
            throw new Halt;
        }
    }

    /**
     * The backstop behind the form's validation rule.
     *
     * The rule on published_at states the requirement in words and is what
     * Rana will normally meet. This catches the case where the model's own
     * guard fires anyway — a record whose reviewer was deleted between load
     * and save, a state the form did not anticipate, anything reaching save()
     * by another route.
     *
     * The exception is the right mechanism and stays exactly as it is: it is
     * what stops a seeder or an import publishing unreviewed clinical content.
     * What is wrong is a person meeting it. Unhandled it produced a 500 — a
     * stack trace locally, and in production a blank "Server Error" page that
     * tells her nothing about what to do next.
     */
    protected function handleUnreviewedPublish(LogicException $e): void
    {
        report($e);

        Notification::make()
            ->danger()
            ->title('المقال ده محتاج مراجعة إكلينيكية')
            ->body(
                'مش هينفع ينشر من غير ما حد إكلينيكي يراجعه ويتسجل اسمه. '
                .'اختاري مين راجعه وتاريخ المراجعة، أو سيبي تاريخ النشر فاضي عشان يفضل مسودة.'
            )
            ->persistent()
            ->send();
    }
}
