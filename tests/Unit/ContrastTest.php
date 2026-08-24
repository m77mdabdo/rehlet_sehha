<?php

declare(strict_types=1);

/**
 * Every colour pair the site actually renders, checked against WCAG AA.
 *
 * This is not a checkbox. A nutrition clinic whose caseload includes diabetic
 * patients has a readership with a materially higher rate of visual impairment
 * than the general population — diabetic retinopathy, cataract and macular
 * changes are all more common here than in a random sample of web users. The
 * people most likely to struggle with 3.7:1 grey body text are, specifically,
 * the people this site exists to reach.
 *
 * The pair list below is HARDCODED on purpose. It is not derived from the CSS
 * and it does not scan the templates, so changing a token does not
 * automatically change what is tested — the list has to be edited by hand, by
 * someone who has looked at where that colour lands. A test that discovers its
 * own expectations cannot fail.
 *
 * Thresholds are WCAG 2.1 AA:
 *   4.5:1  normal body text (below 18.66px bold / 24px regular)
 *   3.0:1  large text, icons, and meaningful UI boundaries
 */

/**
 * Relative luminance, per WCAG 2.1 §relative-luminance.
 */
function relativeLuminance(string $hex): float
{
    $hex = ltrim($hex, '#');

    $channels = array_map(
        static function (string $pair): float {
            $value = hexdec($pair) / 255;

            return $value <= 0.04045
                ? $value / 12.92
                : (($value + 0.055) / 1.055) ** 2.4;
        },
        [substr($hex, 0, 2), substr($hex, 2, 2), substr($hex, 4, 2)],
    );

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function contrastRatio(string $foreground, string $background): float
{
    $a = relativeLuminance($foreground);
    $b = relativeLuminance($background);

    return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
}

/**
 * The palette, mirrored from the @theme block in resources/css/app.css.
 *
 * A second copy, deliberately: if someone edits a token they must edit this
 * too, and at that moment they are looking at a list of everywhere that colour
 * is used. That is the review this test exists to force.
 *
 * @return array<string, string>
 */
function palette(): array
{
    return [
        'ink' => '#0E2E4D',
        'ink-soft' => '#1C4A73',
        'accent' => '#1A6DA6',
        'accent-dark' => '#166A9F',
        'teal' => '#48BBD4',
        'gold' => '#E8A94A',
        'paper' => '#EEF3F8',
        'sage' => '#DCE7F1',
        'muted' => '#4A6684',
        'white' => '#FFFFFF',
        'line' => '#DBE0E6',

        /*
         * Several sections use bg-sage/50 over the paper background. The
         * browser composites that to a colour that is in neither token, and it
         * is the colour text is actually read against — so it is resolved here
         * rather than tested against the wrong background.
         */
        'sage-50-over-paper' => '#E5EDF4',
    ];
}

/**
 * Foreground, background, minimum ratio, and where it appears.
 *
 * @return list<array{0: string, 1: string, 2: float, 3: string}>
 */
function contrastPairs(): array
{
    return [
        /*
         * ---- Body text on the page background -------------------------------
         *
         * These three also carry the hero, which since Task 8.5 sits over a
         * background video.
         *
         * The video changes nothing about them, and that is the whole point of
         * the composition: the copy is on an OPAQUE paper panel and the case
         * card on opaque white, so every hero pair is still a solid colour on a
         * solid colour that can be checked here, at build time, without knowing
         * which frame is on screen.
         *
         * Verified against real composited pixels rather than assumed — the
         * rendered hero was screenshotted with the glyphs made transparent, and
         * every text box compared against the worst pixel actually behind it,
         * at 390/768/1440 in both locales, on the clip's brightest frame and
         * its darkest. 240 elements, none below its threshold, worst 5.23:1 at
         * the eyebrow. Not one of them moved by even 0.01 between the bright
         * frame and the dark one, which is the measurement that proves no text
         * overlaps the picture at any width.
         *
         * That result holds only while the panel is opaque. If it is ever given
         * an alpha, the hero's contrast becomes a property of the video instead
         * of the stylesheet and these lines stop describing it — so
         * HeroVideoTest fails the build if the panel becomes translucent.
         */
        ['ink', 'paper', 4.5, 'headings, body copy, and the hero headline on its panel'],
        ['muted', 'paper', 4.5, 'section leads, and the hero lead and credential chips'],
        ['accent', 'paper', 4.5, 'links on the page background'],
        ['accent-dark', 'paper', 4.5, 'section eyebrows — the worst pair in the hero, 5.23:1'],

        // ---- Body text on cards ----------------------------------------------
        ['ink', 'white', 4.5, 'card headings'],
        ['muted', 'white', 4.5, 'card descriptions, FAQ answers'],
        ['accent', 'white', 4.5, 'links inside cards'],
        ['accent-dark', 'white', 4.5, 'card eyebrows, read-more links'],

        // ---- Body text on the alternating band -------------------------------
        ['ink', 'sage-50-over-paper', 4.5, 'headings on packages/stories/FAQ'],
        ['muted', 'sage-50-over-paper', 4.5, 'leads on the alternating band'],
        ['accent-dark', 'sage-50-over-paper', 4.5, 'eyebrows on the alternating band'],

        // ---- Reversed out of the navy ----------------------------------------
        ['white', 'ink', 4.5, 'stats band and booking CTA'],
        ['paper', 'ink', 4.5, 'footer body copy'],

        // ---- Buttons ---------------------------------------------------------
        ['white', 'accent', 4.5, 'primary button label'],
        ['white', 'accent-dark', 4.5, 'primary button label, hover'],
        ['ink', 'white', 4.5, 'light button label'],
        ['ink', 'sage', 4.5, 'ghost button label, hover'],

        /*
         * ---- Icons and boundaries: 3:1 --------------------------------------
         *
         * accent on full sage measures 4.43:1, which would fail as body text.
         * It is only ever the specialty icon inside its sage chip — a graphic,
         * where AA asks 3:1. Listed explicitly at the lower threshold so the
         * exemption is a decision on the record rather than an omission.
         */
        ['accent', 'sage', 3.0, 'specialty icon in its chip'],
        ['gold', 'ink', 3.0, 'the mark on the navy footer'],
        ['teal', 'ink', 3.0, 'accents on navy'],

        // Large display text only needs 3:1.
        ['accent', 'white', 3.0, 'package price, 30px'],
        ['white', 'ink', 3.0, 'stats figures, 48px'],
    ];
}

it('meets WCAG AA on every colour pair the site renders', function () {
    $palette = palette();
    $failures = [];

    foreach (contrastPairs() as [$foreground, $background, $minimum, $usage]) {
        $ratio = contrastRatio($palette[$foreground], $palette[$background]);

        if ($ratio + 0.005 < $minimum) {
            $failures[] = sprintf(
                '%-12s on %-20s %5.2f:1  needs %.1f:1  — %s',
                $foreground,
                $background,
                $ratio,
                $minimum,
                $usage,
            );
        }
    }

    expect($failures)->toBeEmpty(
        "Colour pairs below WCAG AA.\n\n"
        ."This site's readers include diabetic patients, among whom visual\n"
        ."impairment is materially more common. Adjust the token in\n"
        ."resources/css/app.css — do not lower the threshold here.\n\n"
        .implode("\n", $failures)."\n"
    );
});

/**
 * Pairs that sit below the ratio rules on purpose, each with the reason.
 *
 * An exemption list rather than a quiet omission: leaving a failing pair out of
 * contrastPairs() would look identical to never having thought about it. Every
 * entry here has to carry a written justification, and the test below fails if
 * one does not.
 *
 * @return list<array{0: string, 1: string, 2: string}>
 */
function exemptContrastPairs(): array
{
    return [
        [
            'gold', 'white',
            'Rating stars, 2.06:1. WCAG 1.4.11 applies to graphics that are '
            .'REQUIRED to understand the content; the stars are marked '
            .'aria-hidden and the rating is written out beside them in muted '
            .'text at 5.96:1, so nothing is carried by the gold alone. If the '
            .'stars ever become the only expression of a rating again, this '
            .'exemption stops being true and the gold has to change.',
        ],
        [
            'gold', 'paper',
            'The gold dot in the logo mark, 1.96:1. Logotypes are explicitly '
            .'outside the contrast requirements, and the mark is never the only '
            .'route to anything — the wordmark next to it is real text.',
        ],
    ];
}

it('gives a written reason for every pair exempt from the ratio rules', function () {
    foreach (exemptContrastPairs() as [$foreground, $background, $reason]) {
        $ratio = contrastRatio(palette()[$foreground], palette()[$background]);

        // An exemption for a pair that actually passes is stale bookkeeping:
        // it should go back in the asserted list where it belongs.
        expect($ratio)->toBeLessThan(
            3.0,
            "{$foreground} on {$background} now measures {$ratio}:1 and no longer needs an exemption."
        );

        expect(strlen($reason))->toBeGreaterThan(
            80,
            "The exemption for {$foreground} on {$background} needs a real justification, not a note."
        );
    }
});

it('keeps the palette in this file in step with the stylesheet', function () {
    /*
     * The hardcoded list above is only useful if it still describes reality.
     * This reads the @theme block and fails when a token's value has moved
     * without the mirror being updated — which is the moment someone should be
     * reviewing the pairs.
     */
    $css = file_get_contents(resource_path('css/app.css'));

    foreach (palette() as $name => $expected) {
        // Composited and derived values have no token of their own.
        if (in_array($name, ['white', 'sage-50-over-paper', 'line'], true)) {
            continue;
        }

        expect(preg_match('/--color-'.preg_quote($name, '/').':\s*(#[0-9a-fA-F]{6})/', $css, $match))
            ->toBe(1, "No --color-{$name} in app.css.");

        expect(strtolower($match[1]))->toBe(
            strtolower($expected),
            "--color-{$name} is {$match[1]} in app.css but {$expected} in ContrastTest. "
            .'Update the mirror, then re-check every pair that uses it.'
        );
    }
});

it('computes known ratios correctly', function () {
    // Guards the guard: an arithmetic slip here would pass everything silently.
    expect(round(contrastRatio('#000000', '#FFFFFF'), 2))->toBe(21.0);
    expect(round(contrastRatio('#FFFFFF', '#FFFFFF'), 2))->toBe(1.0);

    // A published reference value: #767676 on white is the canonical 4.54:1
    // boundary case used in the WCAG examples.
    expect(round(contrastRatio('#767676', '#FFFFFF'), 2))->toBe(4.54);
});
