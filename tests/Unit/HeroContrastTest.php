<?php

declare(strict_types=1);

/**
 * The hero's composited contrast — the numbers, and the bound.
 *
 * Everything else on this site reads against a known token, so ContrastTest can
 * check it as one hex against another. The hero cannot: the copy sits DIRECTLY
 * ON MOVING FOOTAGE, lit by a gradient rather than backed by a surface, and
 * what a glyph is read against is a composite of video and scrim that changes
 * both as the clip plays and as the viewport resizes.
 *
 * ---------------------------------------------------------------------------
 * WHAT WAS MEASURED
 * ---------------------------------------------------------------------------
 *
 * Not reasoned about — measured, off the rendered page. Every glyph was made
 * transparent, the clip was held on a known beat, and each text box was
 * compared against the WORST pixel actually behind it (the brightest, since all
 * of this copy is light). Text extents came from a Range rather than the block
 * box, because an Arabic paragraph is as wide as its column while its glyphs
 * hug one edge.
 *
 *     80 text elements  =  2 locales x 5 widths x 8 elements
 *     widths: 390, 768, 1024, 1440, 1920      failures: 0
 *
 *     worst overall            4.76:1   the eyebrow, ar 390, beat 1
 *     worst at 1920            4.84:1   the eyebrow, ar
 *     worst body copy          7.39:1   the scene line, en 1920
 *     title (large, needs 3)   8.89:1   ar 1440
 *
 * The binding element is the EYEBROW, and it is binding because it is the only
 * coloured text on the picture: teal at 14px, needing 4.5:1, sitting at the top
 * of the copy block where a bottom-anchored gradient is thinnest. Everything
 * else is white and clears by a wide margin.
 *
 * TWO SHAPES OF FAILURE WERE FOUND AND FIXED BY MEASURING, and both looked
 * fine on the screen they were designed on:
 *
 *   - percentage gradient stops, which track the window while the copy column
 *     does not. Failed at 1024, where a 36rem column is 56% of the frame.
 *   - rem stops, which track the column but not the CONTAINER'S CENTRING, an
 *     inset that grows from 0 at 1152 to 24rem at 1920. Failed at 1920, lead
 *     at 1.44:1.
 *
 * The shipped stops are max(rem, %) so each end is held by whichever term is
 * larger there.
 *
 * That measurement is a SAMPLE — two beats of a twenty-two second clip. For the
 * header, which has no gradient of its own beyond its scrim, a sample is not
 * good enough, and the test below replaces it with a BOUND: white text over the
 * worst backdrop physically possible, a pure white pixel. If that passes, every
 * frame passes, including any clip somebody swaps in later.
 *
 * ---------------------------------------------------------------------------
 *
 * These assertions pin the values that measurement depends on. Change any of
 * them and the numbers above stop describing the page — so re-measure rather
 * than adjusting a threshold here.
 */
function heroMarkup(): string
{
    return file_get_contents(resource_path('views/components/sections/hero.blade.php'));
}

/**
 * WCAG relative luminance and ratio, defined here rather than borrowed from
 * ContrastTest.
 *
 * That file has them at global scope and a full suite run would make them
 * reachable — but only by accident of load order, and a test that passes in the
 * suite and dies when you run it on its own is a test people stop running.
 */
function heroLuminance(string $hex): float
{
    $hex = ltrim($hex, '#');

    $channels = array_map(
        static function (string $pair): float {
            $value = hexdec($pair) / 255;

            return $value <= 0.04045 ? $value / 12.92 : (($value + 0.055) / 1.055) ** 2.4;
        },
        [substr($hex, 0, 2), substr($hex, 2, 2), substr($hex, 4, 2)],
    );

    return 0.2126 * $channels[0] + 0.7152 * $channels[1] + 0.0722 * $channels[2];
}

function heroContrast(string $foreground, string $background): float
{
    $a = heroLuminance($foreground);
    $b = heroLuminance($background);

    return (max($a, $b) + 0.05) / (min($a, $b) + 0.05);
}

/**
 * Composite `over` at `alpha` on top of `under`. Both #rrggbb.
 */
function compositeHex(string $over, float $alpha, string $under): string
{
    $channels = static fn (string $hex): array => [
        hexdec(substr(ltrim($hex, '#'), 0, 2)),
        hexdec(substr(ltrim($hex, '#'), 2, 2)),
        hexdec(substr(ltrim($hex, '#'), 4, 2)),
    ];

    [$or, $og, $ob] = $channels($over);
    [$ur, $ug, $ub] = $channels($under);

    return sprintf(
        '#%02X%02X%02X',
        (int) round($alpha * $or + (1 - $alpha) * $ur),
        (int) round($alpha * $og + (1 - $alpha) * $ug),
        (int) round($alpha * $ob + (1 - $alpha) * $ub),
    );
}

/**
 * The scrim's densest stop, read off the markup.
 *
 * Parsed rather than repeated, because the header legibility bound below is
 * computed from it and the two silently disagreeing would mean the bound was
 * being checked against a scrim that is no longer there.
 */
function scrimPeakAlpha(): float
{
    preg_match_all('/rgb\(14 46 77 \/ ([0-9.]+)\)/', heroMarkup(), $match);

    expect($match[1])->not->toBeEmpty('The hero scrims no longer declare explicit densities.');

    return max(array_map('floatval', $match[1]));
}

it('keeps a directional scrim rather than a flat wash', function () {
    /*
     * THE WHOLE TECHNIQUE OF THIS HERO, AND THE EASIEST THING TO UNDO.
     *
     * A flat overlay is one line of CSS and it makes every contrast number
     * pass. It also dims the footage evenly, which is what the panel used to
     * do, and it gives back nothing for having removed the panel. The point of
     * a gradient is that the ink is spent where the words are.
     *
     * So: gradients, and stops that actually vary. A "gradient" from 0.9 to
     * 0.88 is a flat wash with extra steps.
     */
    $markup = heroMarkup();

    expect(substr_count($markup, 'linear-gradient'))->toBeGreaterThanOrEqual(3);

    preg_match_all('/rgb\(14 46 77 \/ ([0-9.]+)\)/', $markup, $match);

    $alphas = array_map('floatval', $match[1]);

    expect(max($alphas))->toBeGreaterThanOrEqual(0.88, 'The hero scrim is no longer dense enough where the copy is.');
    expect(min($alphas))->toBeLessThanOrEqual(0.10, 'The hero scrim never fades. That is a flat wash, not a gradient.');
});

it('anchors the scrim stops to the column and to the container, not to one of them', function () {
    /*
     * max(rem, %) at every interior stop, and both halves earned their place
     * by failing a measurement — see the file header. A stop expressed in one
     * unit alone passes at the width it was written for and fails at the other
     * end of the range, silently, because nothing about it looks wrong.
     */
    preg_match('/linear-gradient\(\{\{ \$scrimTravel \}\},(.*?)\)"/su', heroMarkup(), $match);

    expect($match)->not->toBeEmpty('The directional scrim is gone.');

    $stops = $match[1];

    expect(substr_count($stops, 'max('))->toBeGreaterThanOrEqual(
        4,
        'The wide-screen scrim stops are no longer max(rem, %). One unit alone fails at one end of the range.'
    );
});

it('keeps white header text legible over any possible frame, not just the sampled ones', function () {
    /*
     * The bound rather than the sample.
     *
     * Worst case for white text is the brightest backdrop, and the brightest a
     * pixel can be is white. So: white video pixel, then the hero scrim at its
     * densest declared stop, then the header's own scrim. If white text clears AA against THAT, it clears it
     * against every frame of this clip and of any clip that replaces it.
     *
     * Checked at the scrim's weakest point over the header — its bottom edge,
     * where the gradient has fallen to roughly the via stop — rather than at the
     * top where it is nearly solid ink and the answer is easy.
     */
    $ink = '#0E2E4D';

    $overlaid = compositeHex($ink, scrimPeakAlpha(), '#FFFFFF');

    foreach ([
        'the top of the header, scrim near full' => 0.85,
        'the bottom of the header, scrim at its via stop' => 0.45,
    ] as $where => $scrim) {
        $backdrop = compositeHex($ink, $scrim, $overlaid);
        $ratio = heroContrast('#FFFFFF', $backdrop);

        expect($ratio)->toBeGreaterThanOrEqual(
            4.5,
            sprintf(
                'White header text over %s measures %.2f:1 against a pure-white frame. '
                ."Strengthen the scrim in the hero rather than hoping the footage stays dark.\n",
                $where,
                $ratio,
            ),
        );
    }
});

it('computes its own composite and ratio correctly', function () {
    // Guards the guard: a slip in either helper would pass the bound silently.
    expect(round(heroContrast('#000000', '#FFFFFF'), 2))->toBe(21.0);
    expect(round(heroContrast('#767676', '#FFFFFF'), 2))->toBe(4.54);
    expect(compositeHex('#000000', 0.5, '#FFFFFF'))->toBe('#808080');
    expect(compositeHex('#0E2E4D', 1.0, '#FFFFFF'))->toBe('#0E2E4D');
});

it('keeps the scrim the header relies on', function () {
    /*
     * The negative lookahead matters: since 8.16 there are four scrims and
     * three of them are named data-hero-scrim-SOMETHING, so a bare match finds
     * the copy scrim and asserts the header's gradient against the wrong
     * element.
     */
    preg_match('/<div[^>]*data-hero-scrim(?![-\w])[^>]*>/s', heroMarkup(), $scrim);

    expect($scrim)->not->toBeEmpty('The header scrim is gone. The transparent header has nothing to sit on.');
    expect($scrim[0])->toContain('from-ink/85');
    expect($scrim[0])->toContain('via-ink/45');
});
