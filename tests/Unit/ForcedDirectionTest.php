<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/**
 * Nothing forced to a Latin direction may contain Arabic-rendering content.
 *
 * Forcing `dir="ltr"` is right for values that are entirely Latin or numeric —
 * a booking reference, a phone number, a price, an email, a URL. It is wrong
 * for anything containing Arabic, and it fails in a way nobody notices in
 * review.
 *
 * A date is the case that keeps recurring. "21 أغسطس 2026 — 00:57" mixes
 * scripts, and under a forced LTR direction the digits that follow the Arabic
 * month are reclassified from European to Arabic numerals (UAX #9, rule W2).
 * That raises their embedding level and reverses the tail of the string around
 * the leading day number, stranding the day at the far end away from its own
 * month. The English page looks perfect throughout. The Arabic one — the
 * primary language of this clinic — shows the patient a mangled appointment
 * time on the confirmation screen and the manage screen.
 *
 * The fix in every case is `dir="auto"`: the direction comes from the first
 * strong character of the content, so an Arabic date reads right-to-left and
 * an English one left-to-right without either being told which it is.
 *
 * This is a rule that decays silently, and it has already decayed once: the
 * pattern was correct in some views and wrong in three others for two commits,
 * with the tests green the whole time. Nothing else catches it — Pint formats,
 * PHPStan types, neither reads Blade, and a reviewer sees `dir="ltr"` on a date
 * and reads it as care rather than as the bug.
 *
 * There is a companion RENDERED check in tests/Feature/ManageAppointmentTest.php
 * asserting against the real output of the exported record. The two catch
 * different things and neither replaces the other: this one covers every Blade
 * file, including those no test ever renders; that one catches Arabic arriving
 * through a helper whose name this file cannot know.
 */

/**
 * Content that renders Arabic and therefore must never be forced to LTR.
 *
 * @return array<string, string> regex fragment => what it matches
 */
function arabicRenderingContent(): array
{
    return [
        '\p{Arabic}' => 'literal Arabic text',

        // Month and day names come out translated — this is the case that broke.
        'translatedFormat\s*\(' => 'a translated date (translatedFormat)',

        // Translation output is Arabic under the ar locale by definition.
        '__\s*\(' => 'a translation string (__)',
        '@lang\b' => 'a translation string (@lang)',
        '(?<![\w>])trans\s*\(' => 'a translation string (trans)',
        'trans_choice\s*\(' => 'a translation string (trans_choice)',
    ];
}

/**
 * Every element that forces a Latin direction, with the content it wraps.
 *
 * Matches `dir="ltr"` and the `ltr` helper class the exported record uses,
 * since both do the same thing by different means.
 *
 * Non-greedy to the first matching close tag, so a same-tag nesting would be
 * under-captured. That direction of error is deliberate: it can only miss a
 * violation, never invent one, and these wrappers are short inline spans in
 * practice. `(?:[^>]|->)*` lets an attribute contain a Blade arrow without the
 * `>` in `->` ending the tag early.
 *
 * @return array<int, array{tag: string, content: string, offset: int}>
 */
function forcedLatinDirectionElements(string $source): array
{
    $pattern = '/<(?P<tag>[a-z][a-z0-9]*)\b(?:[^>]|->)*?'
        .'(?:dir\s*=\s*"ltr"|class\s*=\s*"[^"]*\bltr\b[^"]*")'
        .'(?:[^>]|->)*>(?P<content>.*?)<\/(?P=tag)\s*>/su';

    if (preg_match_all($pattern, $source, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE) === 0) {
        return [];
    }

    return array_map(fn (array $match): array => [
        'tag' => $match['tag'][0],
        'content' => $match['content'][0],
        'offset' => $match[0][1],
    ], $matches);
}

it('never forces a latin direction onto arabic-rendering content in blade templates', function () {
    $forbidden = arabicRenderingContent();
    $violations = [];

    foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
        $source = $file->getContents();
        $relative = str_replace(base_path().'/', '', $file->getRealPath());

        foreach (forcedLatinDirectionElements($source) as $element) {
            foreach ($forbidden as $fragment => $description) {
                if (preg_match('/'.$fragment.'/u', $element['content']) !== 1) {
                    continue;
                }

                $violations[] = sprintf(
                    '%s:%d  <%s> forced to LTR wraps %s',
                    $relative,
                    substr_count(substr($source, 0, $element['offset']), "\n") + 1,
                    $element['tag'],
                    $description,
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Arabic content forced to a Latin direction in Blade templates.\n\n"
        ."Under a forced LTR direction, digits following an Arabic word are\n"
        ."reclassified as Arabic numerals (UAX #9, rule W2) and the string is\n"
        ."reordered around them — so an Arabic date renders with its day torn\n"
        ."away from its month, while the English page looks fine.\n\n"
        ."Use dir=\"auto\" instead: the direction follows the content. Keep\n"
        ."dir=\"ltr\" only for values that are entirely Latin or numeric — a\n"
        ."reference, a phone number, a price, an email, a URL.\n\n"
        .implode("\n", $violations)."\n"
    );
});

it('detects forced-latin arabic content when it is introduced', function () {
    /*
     * Guards the guard. A regex that quietly stopped matching would leave the
     * real test green forever while checking nothing — which is exactly the
     * failure mode that let this bug live in three views.
     */
    $shouldFlag = [
        // The three that actually shipped broken.
        '<bdi dir="ltr">{{ $cairo->translatedFormat(\'l j F Y — H:i\') }}</bdi>',
        '<bdi dir="ltr">{{ $slot->startsAtCairo->translatedFormat(\'D j M — H:i\') }}</bdi>',
        "<bdi dir=\"ltr\">\n    {{ \$a->starts_at->translatedFormat('l j F Y') }}\n</bdi>",

        // The same mistake by other means.
        '<span class="ltr">{{ $date->translatedFormat(\'j F Y\') }}</span>',
        '<bdi dir="ltr">{{ __(\'booking.confirmation.when\') }}</bdi>',
        '<span dir="ltr">مرحبا</span>',
        '<div dir="ltr" class="text-end">@lang(\'common.brand\')</div>',
        '<bdi class="tabular-nums ltr">{{ trans(\'common.minutes\') }}</bdi>',
        // Attribute carrying a Blade arrow, which naive [^>]* would truncate.
        '<bdi dir="ltr" title="{{ $x->y }}">{{ $d->translatedFormat(\'j F Y\') }}</bdi>',
    ];

    $shouldNotFlag = [
        // Legitimately LTR: entirely Latin or numeric.
        '<bdi dir="ltr">{{ $appointment->reference }}</bdi>',
        '<bdi dir="ltr">{{ number_format((float) $service->price) }}</bdi>',
        '<bdi dir="ltr" class="tabular-nums">{{ Contact::phoneDisplay() }}</bdi>',
        '<bdi dir="ltr">{{ $email }}</bdi>',
        '<span class="ltr">{{ config(\'app.url\') }}</span>',

        // Correctly resolved: direction follows the content.
        '<bdi dir="auto">{{ $cairo->translatedFormat(\'l j F Y — H:i\') }}</bdi>',
        '<bdi dir="auto">{{ __(\'booking.confirmation.when\') }}</bdi>',

        // Arabic that was never forced to a direction at all.
        '<span>{{ $date->translatedFormat(\'j F Y\') }}</span>',
        '<p class="text-muted">{{ __(\'booking.rights.lead\') }}</p>',

        // Void inputs are legitimately LTR and wrap nothing.
        '<input id="phone" type="tel" dir="ltr" inputmode="tel">',

        // "ltr" must be matched as a class token, not inside another word.
        '<div class="filtry">{{ __(\'common.brand\') }}</div>',
    ];

    $forbidden = arabicRenderingContent();

    $flags = function (string $sample) use ($forbidden): bool {
        foreach (forcedLatinDirectionElements($sample) as $element) {
            foreach ($forbidden as $fragment => $description) {
                if (preg_match('/'.$fragment.'/u', $element['content']) === 1) {
                    return true;
                }
            }
        }

        return false;
    };

    foreach ($shouldFlag as $sample) {
        expect($flags($sample))->toBeTrue("expected to flag: {$sample}");
    }

    foreach ($shouldNotFlag as $sample) {
        expect($flags($sample))->toBeFalse("false positive on: {$sample}");
    }
});
