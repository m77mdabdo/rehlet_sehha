<?php

declare(strict_types=1);

use App\Support\Locales;
use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;

/**
 * The ar and en translation files must carry identical key structures.
 *
 * A missing key does not throw in Laravel — __('home.hero.title') simply
 * renders the literal string "home.hero.title". So the failure mode of an
 * untranslated key is not an error anyone sees in development; it is a raw
 * dotted identifier sitting in the middle of a live page, in the language the
 * developer happened not to be testing. On a bilingual site where Arabic is the
 * primary language and English is the one we read while building, that means
 * the Arabic page is the one most likely to break unnoticed.
 *
 * This test makes it a CI failure instead, and reports the missing keys by
 * name rather than just declaring a mismatch.
 */

/**
 * Flatten a nested translation array to dotted keys.
 *
 * Only the SHAPE is compared, never the values — the whole point is that the
 * values differ. A key whose value is an array in one locale and a string in
 * the other is a genuine mismatch and surfaces as one, because the two flatten
 * to different key sets.
 *
 * @param  array<array-key, mixed>  $translations
 * @return list<string>
 */
function flattenTranslationKeys(array $translations, string $prefix = ''): array
{
    $keys = [];

    foreach ($translations as $key => $value) {
        $dotted = $prefix === '' ? (string) $key : $prefix.'.'.$key;

        if (is_array($value) && $value !== []) {
            $keys = [...$keys, ...flattenTranslationKeys($value, $dotted)];

            continue;
        }

        $keys[] = $dotted;
    }

    return $keys;
}

/**
 * Every dotted key defined for a locale, across all of its files, namespaced
 * by the file it came from — exactly how __() addresses them.
 *
 * @return list<string>
 */
function translationKeysFor(string $locale): array
{
    $keys = [];

    foreach (Finder::create()->files()->in(lang_path($locale))->name('*.php')->depth(0) as $file) {
        $group = $file->getBasename('.php');

        /** @var array<array-key, mixed> $translations */
        $translations = require $file->getRealPath();

        foreach (flattenTranslationKeys($translations) as $key) {
            $keys[] = $group.'.'.$key;
        }
    }

    sort($keys);

    return $keys;
}

it('defines every locale directory named in the allow-list', function () {
    foreach (Locales::all() as $locale) {
        expect(is_dir(lang_path($locale)))->toBeTrue(
            "config('app.supported_locales') offers `{$locale}` but lang/{$locale}/ does not exist. "
            .'A locale that routes but has no translations serves a page of raw keys.'
        );
    }
});

it('keeps the same translation files in every locale', function () {
    $filesFor = function (string $locale): array {
        $names = collect(Finder::create()->files()->in(lang_path($locale))->name('*.php')->depth(0))
            ->map(fn ($file): string => $file->getBasename())
            ->values()
            ->all();

        sort($names);

        return $names;
    };

    $reference = Locales::DEFAULT;

    foreach (Locales::all() as $locale) {
        if ($locale === $reference) {
            continue;
        }

        expect($filesFor($locale))->toBe(
            $filesFor($reference),
            "lang/{$locale}/ and lang/{$reference}/ do not contain the same files."
        );
    }
});

it('keeps identical key structures across locales', function () {
    $reference = Locales::DEFAULT;
    $referenceKeys = translationKeysFor($reference);

    expect($referenceKeys)->not->toBeEmpty();

    foreach (Locales::all() as $locale) {
        if ($locale === $reference) {
            continue;
        }

        $keys = translationKeysFor($locale);

        $missing = array_values(array_diff($referenceKeys, $keys));
        $extra = array_values(array_diff($keys, $referenceKeys));

        $report = [];

        if ($missing !== []) {
            $report[] = "Missing from lang/{$locale}/ (present in {$reference}):\n  ".implode("\n  ", $missing);
        }

        if ($extra !== []) {
            $report[] = "Present in lang/{$locale}/ but not in {$reference}:\n  ".implode("\n  ", $extra);
        }

        expect($report)->toBeEmpty(
            "Translation keys are out of step between `{$reference}` and `{$locale}`.\n\n"
            .'A missing key renders as its own dotted name on a live page rather than '
            ."throwing,\nso this has to fail here instead.\n\n"
            .implode("\n\n", $report)."\n"
        );
    }
});

it('has no empty translation values', function () {
    foreach (Locales::all() as $locale) {
        foreach (Finder::create()->files()->in(lang_path($locale))->name('*.php')->depth(0) as $file) {
            /** @var array<array-key, mixed> $translations */
            $translations = require $file->getRealPath();

            $group = $file->getBasename('.php');

            foreach (Arr::dot($translations) as $key => $value) {
                expect(trim((string) $value))->not->toBe(
                    '',
                    "lang/{$locale}/{$group}.php: `{$key}` is empty. An empty string is a "
                    .'placeholder that renders as a blank gap — leave the key out or translate it.'
                );
            }
        }
    }
});
