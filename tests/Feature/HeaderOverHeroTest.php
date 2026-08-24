<?php

declare(strict_types=1);

/**
 * The header that goes transparent over the hero.
 *
 * THE DEFAULT IS SOLID, AND THAT IS THE ENTIRE SAFETY ARGUMENT.
 *
 * The markup ships the solid treatment — ink on paper, as it has always been.
 * header.js only ever ADDS the transparent state, and only on a page that has
 * a hero with media behind it. So every failure mode lands on a readable
 * header: no JavaScript, a script error, a page with no hero, a hero with the
 * video suppressed for Save-Data.
 *
 * The alternative — shipping transparent and letting a script add solid — reads
 * identically in a browser and puts white-on-white in front of anybody whose
 * script did not run. It is worth being explicit about which way round this is,
 * because "fixing" it the other way would look like a simplification.
 *
 * Contrast in BOTH states was measured against real composited pixels rather
 * than reasoned about; the numbers live in HeroContrastTest's docblock.
 */
it('ships a solid header, so a page with no script is still readable', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('/<header\b[^>]*>/s', $html, $match);

    expect($match)->not->toBeEmpty('No header rendered.');

    $header = $match[0];

    // The resting state, present in the markup with no script involved.
    expect($header)->toContain('bg-paper/80');
    expect($header)->toContain('text-ink');
    expect($header)->toContain('border-line');

    // The transparent state exists only as a conditional variant.
    expect($header)->toContain('data-transparent:bg-transparent');
    expect($header)->toContain('data-transparent:text-white');

    // And is NOT applied server-side.
    expect(preg_match('/<header\b[^>]*\sdata-transparent(\s|=|>)/', $html))->toBe(
        0,
        'The header ships in the transparent state. A visitor without JavaScript '
        .'would get white nav links on the page background.'
    );
})->with(['ar', 'en']);

it('gives the header a hero to observe and something to be transparent over', function () {
    $html = $this->get('/ar')->assertOk()->getContent();

    expect($html)->toContain('data-header');
    expect($html)->toContain('data-hero');
    expect($html)->toContain('data-hero-scrim');

    /*
     * The scrim is not decoration. White nav links over the top of this
     * footage do not clear AA on their own — the plate is near-white where the
     * logo sits. A gradient rather than a bar, because a transparent header
     * with a visible edge is just a solid header that is hard to read.
     */
    preg_match('/<div[^>]*data-hero-scrim[^>]*>/s', $html, $scrim);

    expect($scrim)->not->toBeEmpty();
    expect($scrim[0])->toContain('bg-linear-to-b');
    expect($scrim[0])->toContain('from-ink/');
});

it('pulls the hero up under the sticky header', function () {
    /*
     * position:sticky occupies flow space. Without this the hero begins BELOW
     * the header, the transparent header shows the page background instead of
     * the footage, and the white wordmark lands on paper — it measured 1.20:1
     * that way, which is invisible.
     */
    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match('/<section[^>]*data-hero\b[^>]*>/s', $html, $section);

    expect($section)->not->toBeEmpty();
    expect($section[0])->toContain('-mt-18');
});

it('does not go transparent on a hero with no media', function () {
    /*
     * Save-Data suppresses the video, and the poster goes with it. There is
     * then nothing behind the header but a flat colour, and white-on-that is a
     * guess rather than a measurement — so the script leaves it solid. The
     * server does not decide this, the script does, but the marker it keys on
     * has to be absent for that to work.
     */
    $html = $this->withHeader('Save-Data', 'on')->get('/ar')->assertOk()->getContent();

    expect(str_contains($html, 'data-hero-video'))->toBeFalse();

    $script = file_get_contents(resource_path('js/header.js'));

    expect($script)->toContain('data-hero-poster');
    expect($script)->toContain('data-transparent');
});

it('keeps the logo inheriting its colour rather than pinning it', function () {
    /*
     * The lockup used to hardcode text-ink. The footer worked around it by
     * passing text-white and winning a specificity race; the header could not
     * work around it at all, and the wordmark stayed navy on navy over the
     * video. Inheriting means the surface decides once.
     */
    $lockup = file_get_contents(resource_path('views/components/logo/lockup.blade.php'));

    expect(str_contains($lockup, 'items-center gap-3 text-ink'))->toBeFalse(
        'The logo lockup pins its own colour again, so it cannot invert over the hero.'
    );
});
