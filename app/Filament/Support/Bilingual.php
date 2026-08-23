<?php

declare(strict_types=1);

namespace App\Filament\Support;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Fieldset;

/**
 * An Arabic field and its English twin, side by side.
 *
 * Side by side rather than in language tabs, deliberately. Tabs let an editor
 * finish the Arabic, publish, and never look at the English — which is how a
 * bilingual site ends up half-translated with nobody noticing, since the
 * person editing only ever reads one of the two languages. Putting the pair on
 * one row makes the empty box impossible to miss.
 *
 * Arabic comes first because the clinic writes in Arabic and the English is
 * the translation, not the other way round. The slug is derived from the
 * Arabic title for the same reason.
 *
 * BOTH ARE REQUIRED. A service, article or FAQ that exists in one language is
 * a page that 404s — or worse, silently falls back and shows Arabic to an
 * English reader — for half the site's visitors.
 */
class Bilingual
{
    public static function text(string $field, string $label, bool $required = true): Fieldset
    {
        return Fieldset::make($label)
            ->schema([
                TextInput::make($field.'_ar')
                    ->label($label.' (عربي)')
                    ->required($required)
                    ->maxLength(255),
                TextInput::make($field.'_en')
                    ->label($label.' (English)')
                    ->required($required)
                    ->maxLength(255)
                    ->extraInputAttributes(['dir' => 'ltr']),
            ])
            ->columns(2);
    }

    public static function textarea(string $field, string $label, bool $required = true, int $rows = 4): Fieldset
    {
        return Fieldset::make($label)
            ->schema([
                Textarea::make($field.'_ar')
                    ->label($label.' (عربي)')
                    ->required($required)
                    ->rows($rows),
                Textarea::make($field.'_en')
                    ->label($label.' (English)')
                    ->required($required)
                    ->rows($rows)
                    ->extraInputAttributes(['dir' => 'ltr']),
            ])
            ->columns(2);
    }

    public static function rich(string $field, string $label, bool $required = true): Fieldset
    {
        return Fieldset::make($label)
            ->schema([
                RichEditor::make($field.'_ar')
                    ->label($label.' (عربي)')
                    ->required($required),
                RichEditor::make($field.'_en')
                    ->label($label.' (English)')
                    ->required($required),
            ])
            ->columns(1);
    }
}
