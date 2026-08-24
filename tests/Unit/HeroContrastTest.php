<?php

declare(strict_types=1);

/**
 * The hero's composited contrast — the numbers, and the bound.
 *
 * Everything else on this site reads against a known token, so ContrastTest can
 * check it as one hex against another. The hero cannot: since Task 8.6 the copy
 * sits on a TRANSLUCENT panel over moving footage, and the header is fully
 * transparent over it. What a glyph is read against is therefore a composite of
 * video, overlay, scrim and panel, and it changes as the clip plays.
 *
 * ---------------------------------------------------------------------------
 * WHAT WAS MEASURED
 * ---------------------------------------------------------------------------
 *
 * Not reasoned about — measured, off the rendered page. The hero and header
 * were screenshotted with every glyph made transparent, and each text box was
 * compared against the WORST pixel actually behind it:
 *
 *     444 text elements  =  2 locales x 3 widths (390/768/1440) x 3 frames
 *     failures: 0
 *
 *     worst at  390px   4.63:1   the eyebrow, on the panel
 *     worst at  768px   4.62:1   the eyebrow, on the panel
 *     worst at 1440px   4.65:1   the eyebrow, on the panel
 *     worst in the transparent header   5.55:1
 *
 * The binding constraint is accent-dark on the panel, not anything over bare
 * video. The three frames were the clip's brightest, its darkest, and one from
 * its second scene; across them no single ratio on the panel moved by more than
 * a rounding error, because at this opacity only about 7% of the backdrop
 * reaches through and backdrop-blur flattens what does.
 *
 * That measurement is a SAMPLE — three frames of a hundred and twenty-eight.
 * The panel is safe by margin and by the clip's evenness (luminance 120.4 to
 * 129.1 out of 255 across every frame). The header has no panel to hide behind,
 * so for the header a sample is not good enough, and the test below replaces it
 * with a BOUND: white text on the scrim over the worst backdrop physically
 * possible, a pure white pixel. If that passes, every frame passes, including
 * any clip somebody swaps in later.
 *
 * ---------------------------------------------------------------------------
 *
 * These assertions pin the three values that measurement depends on. Change any
 * of them and the numbers above stop describing the page — so re-measure rather
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

it('keeps the panel opaque enough for the measurement above to hold', function () {
    preg_match('/bg-paper\/\[([0-9.]+)\]/', heroMarkup(), $match);

    expect($match)->not->toBeEmpty('The hero panel no longer declares an explicit opacity.');

    /*
     * Measured, not chosen. At 0.90 the credential chips came out at 4.48:1
     * against the footage behind them, which fails AA; 0.93 is the first value
     * where all 444 measurements clear it. Lowering this means re-running the
     * sweep, not adjusting the number.
     */
    expect((float) $match[1])->toBeGreaterThanOrEqual(0.93, 'The hero panel is more transparent than measurement allows.');

    // And the blur, which is what stops one dark pixel dragging one glyph under.
    expect(heroMarkup())->toContain('backdrop-blur');
});

it('keeps the overlay at the density the panel opacity was measured against', function () {
    expect(heroMarkup())->toContain('bg-ink/[0.38]');
});

it('keeps white header text legible over any possible frame, not just the sampled ones', function () {
    /*
     * The bound rather than the sample.
     *
     * Worst case for white text is the brightest backdrop, and the brightest a
     * pixel can be is white. So: white video pixel, then the 38% ink overlay,
     * then the scrim. If white text clears AA against THAT, it clears it
     * against every frame of this clip and of any clip that replaces it.
     *
     * Checked at the scrim's weakest point over the header — its bottom edge,
     * where the gradient has fallen to roughly the via stop — rather than at the
     * top where it is nearly solid ink and the answer is easy.
     */
    $ink = '#0E2E4D';

    $overlaid = compositeHex($ink, 0.38, '#FFFFFF');

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
    preg_match('/<div[^>]*data-hero-scrim[^>]*>/s', heroMarkup(), $scrim);

    expect($scrim)->not->toBeEmpty('The header scrim is gone. The transparent header has nothing to sit on.');
    expect($scrim[0])->toContain('from-ink/85');
    expect($scrim[0])->toContain('via-ink/45');
});
