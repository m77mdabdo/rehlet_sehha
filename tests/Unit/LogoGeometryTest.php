<?php

declare(strict_types=1);

/**
 * The logo is drawn twice: inline in a Blade component, and as a file in
 * public/brand/. Those two copies must not drift.
 *
 * They exist for different reasons and neither can replace the other. The
 * inline SVG is what the site renders — it has to inherit currentColor so the
 * mark inverts on the navy footer, which a file referenced by <img> cannot do.
 * The file is what goes to a printer, a designer or a social profile.
 *
 * So the shapes are duplicated, and duplicated shapes drift: someone nudges
 * the pulse in the Blade file to fix the hero, and six months later the
 * printed letterhead is a slightly different logo. Nothing catches that by
 * eye, because nobody ever sees the two side by side.
 *
 * This test compares the GEOMETRY only. Colour deliberately differs — the
 * component uses currentColor and a CSS token where the file uses literal hex,
 * and that difference is the whole point of having both.
 */

/**
 * Pull every drawable shape out of an SVG as a normalised geometry signature.
 *
 * Only positional and dimensional attributes are read. Fill and stroke colours
 * are ignored, as is the viewBox: the component reframes the mark on a square
 * so the two tiers swap at 48px without a size jump, while the exported file
 * is cropped tight because an asset should carry no padding. Framing is a
 * presentation choice; the shapes are the logo.
 *
 * @return list<string>
 */
function svgGeometry(string $markup): array
{
    $geometric = [
        'd', 'cx', 'cy', 'r', 'x', 'y', 'width', 'height', 'rx', 'ry',
        'stroke-width', 'stroke-dasharray', 'stroke-opacity',
        'stroke-linecap', 'stroke-linejoin', 'points',
    ];

    $shapes = [];

    // <circle …/>, <rect …/>, <path …/> — self-closing, as every one of these
    // files writes them.
    preg_match_all('/<(circle|rect|path|polygon|line|ellipse)\b([^>]*)>/i', $markup, $tags, PREG_SET_ORDER);

    foreach ($tags as $tag) {
        $name = strtolower($tag[1]);

        preg_match_all('/([a-zA-Z-]+)\s*=\s*"([^"]*)"/', $tag[2], $attributes, PREG_SET_ORDER);

        $kept = [];

        foreach ($attributes as $attribute) {
            $key = strtolower($attribute[1]);

            if (! in_array($key, $geometric, true)) {
                continue;
            }

            // Collapse whitespace so "M176 202 V174" and "M176 202  V174"
            // compare equal — a reformat is not a change to the drawing.
            $kept[$key] = trim((string) preg_replace('/\s+/', ' ', $attribute[2]));
        }

        ksort($kept);

        $shapes[] = $name.'['.http_build_query($kept).']';
    }

    return $shapes;
}

/**
 * @return array{0: list<string>, 1: list<string>}
 */
function geometryPair(string $component, string $brandFile): array
{
    return [
        svgGeometry(file_get_contents(resource_path('views/components/logo/'.$component))),
        svgGeometry(file_get_contents(public_path('brand/'.$brandFile))),
    ];
}

it('keeps the full mark identical to its brand file', function () {
    [$component, $file] = geometryPair('mark-full.blade.php', 'mark-navy.svg');

    expect($file)->not->toBeEmpty();

    expect($component)->toBe(
        $file,
        "components/logo/mark-full.blade.php has drifted from public/brand/mark-navy.svg.\n\n"
        ."The site would render one logo and the print/social assets another.\n"
        ."Whichever is right, make the other match it — do not delete this test.\n"
    );
});

it('keeps the icon mark identical to its brand file', function () {
    [$component, $file] = geometryPair('mark.blade.php', 'mark-icon-navy.svg');

    expect($file)->not->toBeEmpty();

    expect($component)->toBe(
        $file,
        "components/logo/mark.blade.php has drifted from public/brand/mark-icon-navy.svg.\n"
    );
});

it('draws the pulse only in the full mark', function () {
    // The tier difference IS the pulse. If the icon mark ever grows one, or
    // the full mark loses it, the 48px rule has stopped meaning anything.
    $full = file_get_contents(resource_path('views/components/logo/mark-full.blade.php'));
    $icon = file_get_contents(resource_path('views/components/logo/mark.blade.php'));

    expect(svgGeometry($full))->toHaveCount(6);
    expect(svgGeometry($icon))->toHaveCount(3);

    expect($full)->toContain('M124 202 H162 L176 176 L194 230 L206 202 H320');
    expect($icon)->not->toContain('M124 202');
});

it('centres both marks in their frame', function () {
    /*
     * Guards the correction this test file was written alongside: the icon
     * mark carried viewBox "-17 0 400 400", which pushed it 34 units RIGHT of
     * centre rather than centring it. Offsetting the frame is the opposite
     * sign to offsetting the content.
     *
     * Content bounds are measured values (getBBox, stroke included):
     *   icon  x 72 .. 362   -> centre 217    -> 400-wide frame starts at 17
     *   full  x 76.5 .. 349 -> centre 212.75 -> 400-wide frame starts at 12.75
     */
    $expected = [
        'mark.blade.php' => ['minX' => 17.0, 'centre' => 217.0],
        'mark-full.blade.php' => ['minX' => 12.75, 'centre' => 212.75],
    ];

    foreach ($expected as $file => $geometry) {
        $markup = file_get_contents(resource_path('views/components/logo/'.$file));

        expect(preg_match('/viewBox="([^"]+)"/', $markup, $match))->toBe(1);

        [$minX, $minY, $width, $height] = array_map('floatval', preg_split('/\s+/', trim($match[1])));

        expect($minX)->toBe($geometry['minX'], "{$file}: viewBox does not centre the mark.");
        expect($width)->toBe(400.0);
        expect($height)->toBe(400.0);

        // The frame's centre must land on the content's centre.
        expect($minX + $width / 2)->toBe($geometry['centre'], "{$file}: frame centre misses content centre.");

        // y is already centred at 200 for both marks.
        expect($minY)->toBe(0.0);
    }
});

it('uses currentColor for the body and the token for the gold', function () {
    foreach (['mark.blade.php', 'mark-full.blade.php'] as $file) {
        $markup = file_get_contents(resource_path('views/components/logo/'.$file));

        // A literal hex here would stop the mark inverting on the navy footer,
        // and would be a third copy of a colour the design tokens already own.
        expect($markup)->toContain('currentColor')
            ->toContain('var(--color-gold)');

        expect($markup)->not->toMatch('/(fill|stroke)="#[0-9a-fA-F]{3,6}"/');
    }
});
