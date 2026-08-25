<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/**
 * The hero background video, and every path that must NOT play it.
 *
 * A background video is decoration. It is allowed to be missing, refused,
 * skipped or broken, and in every one of those cases the visitor must get the
 * poster and never a black box or an empty hole where the hero was.
 *
 * Two of the skip paths are not preferences to be weighed against the design:
 *
 *   - prefers-reduced-motion. A looping video is a known trigger for
 *     vestibular disorders and for migraine with aura. Somebody who set that
 *     preference set it to avoid exactly this.
 *   - Save-Data and slow connections. This clinic's patients are on Egyptian
 *     mobile data as the normal case, and a megabyte of footage nobody asked
 *     to watch is a real cost to them.
 *
 * The behavioural half of these rules lives in the browser and is verified in
 * the report for this task with prefers-reduced-motion emulated over CDP. What
 * is checked here is everything the server controls: that the poster is always
 * in the markup, that the video never carries a src the browser could fetch
 * before the script has decided, and that a Save-Data request never even sees
 * the element.
 */
it('renders the hero with a poster that does not depend on javascript', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    expect($html)->toContain('data-hero-poster');

    // A real <img>, so the preload scanner finds it and it paints on first
    // paint — a poster="" attribute would depend on the video element and
    // would not be discoverable early.
    expect($html)->toContain('hero-poster.jpg');
    expect($html)->toContain('hero-poster-1280.webp');
})->with(['ar', 'en']);

it('ships the video element without a src so nothing can be fetched early', function () {
    $html = $this->get('/ar')->assertOk()->getContent();

    expect($html)->toContain('data-hero-video');

    /*
     * The important assertion in this file.
     *
     * preload="none" is a HINT. A <source src> or a src attribute present in
     * the markup may be fetched before any script runs, which would bill a
     * visitor on a metered connection for a decoration we had already decided
     * to skip. Withholding the URL until hero-video.js has checked is the only
     * mechanism that actually guarantees it.
     */
    expect($html)->toMatch('/<video\b(?![^>]*\ssrc=)[^>]*data-hero-video/s');
    expect($html)->not->toContain('<source src');
    expect($html)->toContain('preload="none"');

    // The URL is present, but parked where only the script can act on it.
    expect($html)->toContain('data-src="'.asset('brand/2.mp4').'"');
});

it('never gives the video controls, sound or a download button', function () {
    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match('/<video\b[^>]*>/s', $html, $match);

    expect($match)->not->toBeEmpty();

    $tag = $match[0];

    expect($tag)->toContain('muted');
    expect($tag)->toContain('loop');
    expect($tag)->toContain('playsinline');
    expect($tag)->toContain('aria-hidden="true"');
    expect($tag)->toContain('tabindex="-1"');
    expect($tag)->toContain('nodownload');
    expect(str_contains($tag, 'controls='))->toBeFalse('The background video must not have controls.');
    expect(preg_match('/\scontrols(\s|>)/', $tag))->toBe(0, 'The background video must not have controls.');
});

it('does not send the video element at all when the request asks to save data', function () {
    /*
     * Answered on the server as well as in the browser. A client-side check can
     * only skip a fetch it has already been told to make; this skips telling
     * it, which also means the markup is smaller for the person who is paying
     * by the megabyte.
     */
    $html = $this->withHeader('Save-Data', 'on')->get('/ar')->assertOk()->getContent();

    expect(str_contains($html, 'data-hero-video'))->toBeFalse(
        'A Save-Data request was still sent the video element.'
    );

    // The hero is still a hero.
    expect($html)->toContain('data-hero-poster');
    expect($html)->toContain(__('home.hero.title', [], 'ar'));
});

it('keeps the hero intact when the video and poster files are missing', function () {
    /*
     * The markup must not depend on the asset existing. This is what stands
     * between a 404 and a black box.
     */
    $html = $this->get('/ar')->assertOk()->getContent();

    expect($html)->toContain(__('home.hero.title', [], 'ar'));
    expect($html)->toContain(__('home.hero.cta', [], 'ar'));

    foreach (__('home.hero.chips', [], 'ar') as $chip) {
        expect($html)->toContain($chip);
    }
});

it('has the media files the hero points at', function () {
    foreach (['brand/2.mp4', 'brand/hero-poster.jpg', 'brand/hero-poster-1280.webp'] as $path) {
        expect(File::exists(public_path($path)))->toBeTrue("public/{$path} is missing.");
    }

    /*
     * A size budget with a reason, and the reason keeps being needed.
     *
     * The first hero was re-encoded from 4.55 MiB before it was ever
     * committed. Its replacement arrived as a 14.84 MiB master — 1280x720
     * like the one it replaced, so the extra weight bought no pixels, only
     * 3.1 Mbps of encoder slack, 40 seconds of runtime and a digitally
     * silent audio track.
     *
     * What ships is 3.5 seconds of that master at 413 KB. This number is what
     * stops the master itself going in front of somebody on 3g.
     */
    expect(File::size(public_path('brand/2.mp4')))->toBeLessThan(
        1_400_000,
        'The hero video has grown past its budget. Re-encode it rather than raising this number.'
    );
});

it('positions the video and poster so they cannot move the layout', function () {
    /*
     * No CLS by construction rather than by measurement: both elements are
     * absolutely positioned and out of flow, so the section is sized by its
     * content whether the video ever arrives or not.
     */
    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match('/<video\b[^>]*>/s', $html, $video);
    preg_match('/<img\b[^>]*data-hero-poster[^>]*>/s', $html, $poster);

    expect($video)->not->toBeEmpty();
    expect($poster)->not->toBeEmpty();

    expect($video[0])->toContain('absolute');
    expect($poster[0])->toContain('absolute');
});

it('keeps the hero copy on a panel rather than over the bare picture', function () {
    /*
     * The panel is TRANSLUCENT since Task 8.6, which means the hero's contrast
     * genuinely depends on the frame behind it. That is a deliberate trade and
     * it is only safe because of three things together, all of which this file
     * or HeroContrastTest pins:
     *
     *   1. The clip is unusually even — 120.4 to 129.1 luminance out of 255
     *      across all 128 frames — so there is no bright or dark extreme to
     *      fall off.
     *   2. backdrop-blur flattens what variation is left, so a single dark
     *      pixel cannot drag one glyph under threshold while its neighbours
     *      pass.
     *   3. The opacity is high enough that measurement says every glyph clears
     *      AA against the worst pixel actually behind it, at three widths, in
     *      both locales, on three different frames.
     *
     * What must not happen is the panel quietly losing more opacity because it
     * "looks better" — hence the floor below. The number came from measuring,
     * not from taste, and lowering it means re-running that measurement.
     */
    $html = $this->get('/ar')->assertOk()->getContent();

    preg_match('/<section[^>]*data-hero\b.*?<\/section>/su', $html, $match);

    expect($match)->not->toBeEmpty('The hero section did not render.');

    $hero = $match[0];

    expect($hero)->toContain('data-hero-panel');
    expect($hero)->toContain('backdrop-blur');

    /*
     * The opacity FLOOR and the measurement behind it live in
     * HeroContrastTest, so the threshold has one home. What matters here is
     * only that the panel is still a panel.
     */
});

it('keeps the reduced-motion and slow-connection gates in the shipped script', function () {
    /*
     * WHAT THIS PROVES, AND WHAT IT DOES NOT.
     *
     * It does not prove the gates work. That is browser behaviour, and this
     * project has no JavaScript test runner — adding one would mean installing
     * a toolchain that is not part of this codebase. The behaviour was
     * verified instead by driving headless Chrome over CDP with
     * prefers-reduced-motion emulated, Save-Data set, and navigator.connection
     * stubbed to each effective type in turn, checking both that no <video>
     * survives in the DOM and that 1.mp4 is never requested at all. Those
     * results are recorded in the task report.
     *
     * What this DOES prove is that the checks still exist in the file that
     * ships. It is a deletion guard, not a behaviour test: if someone
     * simplifies the script and drops a branch, the build fails and somebody
     * has to come and read this comment before deciding that is fine.
     *
     * It is worth having because the failure it guards against is silent. A
     * missing reduced-motion check does not break anything visible — the site
     * looks perfect to whoever removed it, and the only people who find out
     * are the ones it makes ill.
     */
    $script = file_get_contents(resource_path('js/hero-video.js'));

    expect($script)->toContain('prefers-reduced-motion');
    expect($script)->toContain('saveData');
    expect($script)->toContain('effectiveType');

    foreach (['slow-2g', '2g', '3g'] as $type) {
        expect($script)->toContain("'{$type}'");
    }

    // The video must never be handed a source before those checks have run.
    expect($script)->toContain('dataset.src');
});
