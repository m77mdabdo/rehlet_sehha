<?php

declare(strict_types=1);

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Model;

/**
 * Side-by-side Arabic and English editing for spatie-translated fields.
 *
 * The models store translations as a JSON column and expose the ACTIVE
 * locale's value through the accessor, so `$service->name` is a string, not a
 * pair. A form bound straight to `name` would therefore edit whichever
 * language the panel happens to be in and silently discard the other — which,
 * since the panel is Arabic-only, means every English translation on the site
 * would be destroyed the first time someone edited a service.
 *
 * So the form works on flat `name_ar` / `name_en` fields, expanded on the way
 * in and folded back on the way out. Both directions live here rather than in
 * each resource, because getting one side right and the other wrong loses
 * content without erroring.
 *
 * BOTH LANGUAGES ARE REQUIRED BEFORE PUBLISH. That rule is on the fields
 * themselves in each resource; this trait only moves the values.
 */
trait EditsTranslations
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function expandTranslations(array $data, Model $record): array
    {
        foreach ($this->translatableFields() as $field) {
            /*
             * getTranslations() comes from spatie's HasTranslations TRAIT.
             * The package ships no interface for it, so there is no type that
             * expresses "a Model that is translatable" — hence the narrow
             * ignore rather than a fictional intersection type. Every model
             * reaching this trait is a content model that uses it; the
             * translatableFields() list is empty for anything that does not,
             * so the loop never runs.
             */
            /** @phpstan-ignore-next-line method.notFound */
            $translations = $record->getTranslations($field);

            foreach (['ar', 'en'] as $locale) {
                $data[$field.'_'.$locale] = $translations[$locale] ?? null;
            }

            // The flat pair replaces the single value entirely, so nothing
            // downstream can write the accessor's string back over the JSON.
            unset($data[$field]);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function foldTranslations(array $data): array
    {
        foreach ($this->translatableFields() as $field) {
            $ar = $data[$field.'_ar'] ?? null;
            $en = $data[$field.'_en'] ?? null;

            unset($data[$field.'_ar'], $data[$field.'_en']);

            /*
             * Written as an array so spatie sets BOTH locales in one go. Doing
             * it with two setTranslation calls would work too, but this way a
             * missing key cannot leave a stale value from a previous edit
             * sitting in the other language.
             */
            $data[$field] = ['ar' => $ar, 'en' => $en];
        }

        return $data;
    }

    /**
     * @return array<int, string>
     */
    protected function translatableFields(): array
    {
        /** @var class-string<Model> $model */
        $model = static::getResource()::getModel();

        $instance = new $model;

        /*
         * $translatable is a public property declared by each content model
         * (see Service, Post, Faq…). Absent on anything else, which is exactly
         * what the null coalesce answers.
         */
        /** @phpstan-ignore-next-line property.notFound */
        return $instance->translatable ?? [];
    }
}
