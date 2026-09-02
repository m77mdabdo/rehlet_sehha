<?php

declare(strict_types=1);

use App\Support\Locales;

/**
 * THE SCENE-SYNCED HERO COPY, AND THE THREE PROMISES IT MAKES.
 *
 * One: a screen reader gets ONE sentence, not five. The four cycling lines are
 * decorative captions on a decorative picture; announcing a new one every five
 * and a half seconds would be an interruption, not information.
 *
 * Two: the static line is in the DOM before any JavaScript runs. It is what
 * reduced-motion, Save-Data, 2g/3g, no-JS and failed-video visitors read, and
 * a line injected by a script is a line four of those five groups never see.
 *
 * Three: nothing here can shift the layout. Every line shares one grid cell,
 * so the box is as tall as its tallest child before a script exists — which is
 * also why the reserved height is correct in both locales without anybody
 * measuring anything, since each locale lays out its own words.
 */
it('puts the static line in the markup, not in a script', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    $static = __('home.hero.beats.'.config('hero.static_beat'), [], $locale);

    expect($html)->toContain('data-hero-beat-static');
    expect($html)->toContain($static);

    /*
     * The assertion that actually means "before JavaScript": the sentence is
     * in the HTML the server sent. No test here executes a script, so if it is
     * in this string it was never injected.
     */
    expect(substr_count($html, $static))->toBeGreaterThanOrEqual(1);
})->with(['ar', 'en']);

it('leaves exactly one of the five lines in the accessibility tree', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    preg_match('/<div[^>]*data-hero-beats\b.*?<\/div>/su', $html, $match);

    expect($match)->not->toBeEmpty('The hero beat block did not render.');

    $block = $match[0];

    // Four cycling lines, every one of them hidden from assistive technology.
    expect(substr_count($block, 'data-hero-beat="'))->toBe(4);
    expect(substr_count($block, 'aria-hidden="true"'))->toBe(4);

    /*
     * And the static line is NOT hidden. This is the assertion that would fail
     * if somebody "tidied up" by putting aria-hidden on the container, which
     * would take the last sentence out of the tree and leave a screen reader
     * with nothing at all.
     */
    preg_match('/<p[^>]*data-hero-beat-static[^>]*>/', $block, $staticTag);

    expect($staticTag)->not->toBeEmpty();
    expect($staticTag[0])->not->toContain('aria-hidden');
})->with(['ar', 'en']);

it('stacks every line in one grid cell so a swap cannot move anything', function () {
    /*
     * The layout-shift guarantee, asserted structurally rather than by
     * measuring a number that would only be right for one locale.
     *
     * All five lines are in the same grid cell, so the container is already
     * the height of the longest before anything runs — and the longest differs
     * between Arabic and English, which is exactly why no pixel value is
     * written down anywhere.
     */
    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match('/<div[^>]*data-hero-beats\b.*?<\/div>/su', $html, $match);

    $block = $match[0];

    expect($block)->toContain('grid');

    // Five lines: four cycling and the static one, all in row 1 column 1.
    expect(substr_count($block, 'col-start-1 row-start-1'))->toBe(5);
});

it('has a line for every beat in the scene map, in both locales', function () {
    /*
     * The copy and the timings are joined by the beat key. A beat with no line
     * is a silent gap in the sequence; a line with no beat never appears. Both
     * are invisible from the page and obvious here.
     */
    foreach (Locales::all() as $locale) {
        foreach (config('hero.beats') as $beat) {
            $line = __('home.hero.beats.'.$beat['key'], [], $locale);

            expect($line)->not->toBe(
                'home.hero.beats.'.$beat['key'],
                "The «{$beat['key']}» beat has no {$locale} line."
            );

            expect(trim($line))->not->toBeEmpty();
        }

        $lines = __('home.hero.beats', [], $locale);

        expect(array_keys($lines))->toBe(
            array_column(config('hero.beats'), 'key'),
            "The {$locale} lines and the scene map are not the same beats, in the same order."
        );
    }
});

it('keeps a number out of the hero copy', function (string $locale) {
    /*
     * The same rule as the plate builder and the hero case card, and for the
     * same reason — see PlateFeedbackHasNoNumbersTest. This copy sits three
     * inches from a case card that had to have a percentage taken out of it.
     *
     * Latin and Arabic-Indic digits alike.
     */
    foreach (__('home.hero.beats', [], $locale) as $key => $line) {
        expect(preg_match('/[0-9\x{0660}-\x{0669}\x{06F0}-\x{06F9}]/u', $line))->toBe(
            0,
            "home.hero.beats.{$key} in {$locale} contains a digit:\n\n  {$line}"
        );
    }
})->with(['ar', 'en']);

it('drives the copy from the video rather than from a timer of its own', function () {
    /*
     * WHY THIS IS ASSERTED IN THE SHIPPED SCRIPT AND NOT JUST REVIEWED.
     *
     * The five fallbacks — reduced motion, Save-Data, 2g/3g, a decode failure
     * and a refused autoplay — are all handled by the copy having no clock of
     * its own: hero-video.js never starts the clip, so nothing starts the
     * copy. The moment somebody adds a setInterval "so it works without the
     * video", every one of those five groups gets animation they opted out of,
     * and nothing else in the codebase objects.
     */
    $script = collect(glob(public_path('build/assets/app-*.js')))
        ->map(fn (string $path): string => (string) file_get_contents($path))
        ->implode('');

    expect($script)->not->toBeEmpty('No built bundle — run npm run build.');

    expect($script)->toContain('currentTime');
    expect($script)->toContain('data-hero-beats');

    // The bundle has no timer driving the hero copy. setTimeout survives
    // elsewhere (hero-video.js uses one as a Safari fallback), setInterval
    // does not appear at all.
    expect($script)->not->toContain('setInterval');
});

it('keeps the whole bundle under its budget', function () {
    /*
     * 7 KB gzipped for every script on the site. The number is small because
     * the alternative was Alpine at roughly fifteen, and the argument for
     * writing the DOM work by hand only holds while this stays true.
     */
    $bundle = collect(glob(public_path('build/assets/app-*.js')))->first();

    expect($bundle)->not->toBeNull('No built bundle — run npm run build.');

    $gzipped = strlen((string) gzencode((string) file_get_contents($bundle), 9));

    expect($gzipped)->toBeLessThan(
        7168,
        'The bundle is '.$gzipped.' bytes gzipped, over the 7 KB cap.'
    );
});
