<?php

declare(strict_types=1);

namespace App\Filament\Resources\Posts\Pages;

use App\Filament\Concerns\EditsTranslations;
use App\Filament\Resources\Posts\PostResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;
use LogicException;

class CreatePost extends CreateRecord
{
    use EditsTranslations;

    protected static string $resource = PostResource::class;

    /**
     * Fold the flat ar/en fields back into the translation arrays spatie
     * stores. See the trait for why the form cannot bind to the field itself.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return $this->foldTranslations($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleRecordCreation(array $data): Model
    {
        try {
            return parent::handleRecordCreation($data);
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
