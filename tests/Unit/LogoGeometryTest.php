<?php

declare(strict_types=1);

/**
 * THERE IS ONE MARK, AND EVERY SURFACE DRAWS IT.
 *
 * The logo is written out in many places: inline in a Blade component, as flat
 * files for print and social, as mono files for single-colour reproduction, on
 * a navy plate for the favicon and app icons, and embedded inside the lockup
 * files. Those copies exist for reasons none of the others can serve — the
 * inline SVG has to inherit currentColor so the mark inverts on the navy
 * footer, which a file referenced by <img> cannot do; the exported file is what
 * goes to a printer.
 *
 * Duplicated shapes drift. Somebody nudges the pulse in the Blade file to fix
 * the hero, and six months later the letterhead is a slightly different logo.
 * Nothing catches that by eye, because nobody ever sees them side by side.
 *
 * public/brand/mark-navy.svg is the reference. Every other surface is compared
 * against it.
 *
 * WHAT THIS TEST NO LONGER CHECKS. It used to assert that a second, pulse-less
 * mark existed for use below 48px, and that the two tiers differed by exactly
 * the pulse. That variant is gone: one mark now renders at every size, from
 * the 16px favicon up. The trade-off was measured and then overridden
 * deliberately — see the note in mark-full.blade.php before reintroducing one.
 *
 * Colour is deliberately NOT compared. The component uses currentColor and a
 * CSS token where the files use literal hex, the mono set collapses gold into
 * the body colour, and the tiles invert to white. That is the whole point of
 * having more than one file.
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

/**
 * Every place the mark is drawn.
 *
 * Names only, no path helpers: Pest builds a dataset at collection time,
 * before the application has booted, and resource_path() is not available
 * yet. markSurfacePath() resolves them once a test is actually running.
 *
 * @return list<string>
 */
function markSurfaces(): array
{
    return [
        'components/logo/mark-full.blade.php',
        'brand/mark-white.svg',
        'brand/mark-mono-navy.svg',
        'brand/mark-mono-white.svg',
        'brand/mark-mono-black.svg',
        'brand/favicon.svg',
        'brand/icon-tile-navy.svg',
        'brand/lockup-h-ar-dark.svg',
        'brand/lockup-h-ar-light.svg',
        'brand/lockup-h-en-dark.svg',
        'brand/lockup-h-en-light.svg',
        'brand/lockup-v-ar-dark.svg',
        'brand/lockup-v-ar-light.svg',
    ];
}

function markSurfacePath(string $name): string
{
    return str_starts_with($name, 'brand/')
        ? public_path($name)
        : resource_path('views/'.$name);
}

it('draws the same mark on every surface', function (string $name) {
    $reference = svgGeometry(file_get_contents(public_path('brand/mark-navy.svg')));

    expect($reference)->toHaveCount(6);

    $markup = file_get_contents(markSurfacePath($name));

    /*
     * The tiles and the lockups wrap the mark in furniture — a rounded plate
     * behind it, a wordmark beside it. Only the mark's own shapes are
     * compared, matched by taking the run that starts at the plate ring.
     */
    $shapes = svgGeometry($markup);
    $offset = null;

    foreach ($shapes as $i => $shape) {
        if ($shape === $reference[0]) {
            $offset = $i;

            break;
        }
    }

    expect($offset)->not->toBeNull("{$name}: no plate ring matching mark-navy.svg was found at all.");

    expect(array_values(array_slice($shapes, $offset, 6)))->toBe(
        $reference,
        "{$name} has drifted from public/brand/mark-navy.svg.\n\n"
        ."The site would render one logo and this surface another.\n"
        ."There is ONE mark; whichever is right, make the other match it.\n"
    );
})->with(markSurfaces());

it('keeps the pulse on every surface, including the favicon', function (string $name) {
    /*
     * The specific regression this guards. There used to be a pulse-less mark
     * for small sizes, and the favicon used it. One mark now runs everywhere,
     * which was a deliberate override of a measured legibility rule — so the
     * way it would silently come undone is somebody quietly dropping the pulse
     * from the favicon again because it looks like mush at 16px. It does. That
     * was accepted.
     */
    $markup = file_get_contents(markSurfacePath($name));

    expect(str_contains($markup, 'M124 202 H162 L176 176 L194 230 L206 202 H320'))->toBeTrue(
        "{$name} has no pulse. Every surface carries the full mark."
    );
})->with(markSurfaces());

it('has no pulse-less mark left to reach for', function () {
    /*
     * Deleting the component is not enough on its own — the exported icon
     * variants were the other half of the retired tier, and leaving them on
     * disk is an invitation to point a <link rel="icon"> back at one.
     */
    foreach (['mark-icon-navy.svg', 'mark-icon-white.svg'] as $retired) {
        expect(file_exists(public_path('brand/'.$retired)))->toBeFalse(
            "public/brand/{$retired} is back. The reduced variant was retired deliberately."
        );
    }

    expect(file_exists(resource_path('views/components/logo/mark.blade.php')))->toBeFalse(
        'The icon-tier component is back. There is one mark.'
    );
});

it('centres the mark in its frame', function () {
    /*
     * Guards a correction this file was written alongside: a mark once carried
     * viewBox "-17 0 400 400", which pushed it 34 units RIGHT of centre rather
     * than centring it. Offsetting the frame is the opposite sign to
     * offsetting the content.
     *
     * Content bounds are measured (getBBox, stroke included): x 76.5 .. 349,
     * centre 212.75, so a 400-wide frame starts at 12.75.
     */
    $markup = file_get_contents(resource_path('views/components/logo/mark-full.blade.php'));

    expect(preg_match('/viewBox="([^"]+)"/', $markup, $match))->toBe(1);

    [$minX, $minY, $width, $height] = array_map('floatval', preg_split('/\s+/', trim($match[1])));

    expect($minX)->toBe(12.75, 'mark-full.blade.php: viewBox does not centre the mark.');
    expect($width)->toBe(400.0);
    expect($height)->toBe(400.0);

    // The frame's centre must land on the content's centre.
    expect($minX + $width / 2)->toBe(212.75, 'mark-full.blade.php: frame centre misses content centre.');

    // y is already centred at 200.
    expect($minY)->toBe(0.0);
});

it('uses currentColor for the body and the token for the gold', function () {
    foreach (['mark-full.blade.php'] as $file) {
        $markup = file_get_contents(resource_path('views/components/logo/'.$file));

        // A literal hex here would stop the mark inverting on the navy footer,
        // and would be a third copy of a colour the design tokens already own.
        expect($markup)->toContain('currentColor')
            ->toContain('var(--color-gold)');

        expect($markup)->not->toMatch('/(fill|stroke)="#[0-9a-fA-F]{3,6}"/');
    }
});
