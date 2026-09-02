/**
 * The hero copy, synced to the cuts in the hero clip.
 *
 * Four lines, one per shot, changing ON the cut. The timings are not in this
 * file and must not be: they live in config/hero.php next to the footage they
 * describe, they are asserted against the real file by HeroSceneMapTest, and
 * they arrive here as JSON on the video element.
 *
 * THE VIDEO IS THE CLOCK, AND THAT IS THE WHOLE DESIGN.
 *
 * Nothing here runs on a timer of its own. The current beat is read off
 * video.currentTime, so the words cannot drift out of step with the picture
 * however long the page is left open, however the browser throttles a
 * background tab, and whatever the video does on loop.
 *
 * It also collapses five separate fallbacks into no code at all. Reduced
 * motion, Save-Data, 2g/3g, a browser that will not decode the file, and a
 * refused autoplay are all cases where hero-video.js never starts the clip —
 * so this file never starts either, and the server-rendered static line stays
 * exactly where it was. There is no "if reduced motion" branch below, because
 * there is nothing for one to do.
 *
 * WHAT IS AND IS NOT IN THE ACCESSIBILITY TREE. The static line is real markup
 * in the DOM before this file is parsed, and it is the only sentence a screen
 * reader ever reaches. The four cycling lines are aria-hidden in the Blade
 * template — announcing a new line every five and a half seconds would be an
 * interruption rather than information, and the picture they caption is itself
 * decorative. This file does not touch aria anywhere; if it fails to load, the
 * accessibility of the section is identical.
 *
 * NOTHING HERE CAN MOVE THE LAYOUT. Every line, static one included, sits in
 * the same CSS grid cell, so the box is already as tall as its tallest child
 * before any of this runs. Only opacity and transform are touched.
 */

/**
 * How far the line travels as it changes, in pixels.
 *
 * Small on purpose. This is a caption under a heading, not a slideshow, and
 * the movement is there to make the swap legible as a swap rather than to be
 * noticed on its own.
 */
const TRAVEL = 6;

function initHeroBeats() {
    const container = document.querySelector('[data-hero-beats]');
    const video = document.querySelector('[data-hero-video]');

    if (!container || !video) {
        return;
    }

    let beats;

    try {
        beats = JSON.parse(video.dataset.heroBeatsSource);
    } catch {
        // Leave the static line alone rather than replacing a readable
        // sentence with a broken widget.
        return;
    }

    if (!Array.isArray(beats) || beats.length === 0) {
        return;
    }

    const staticLine = container.querySelector('[data-hero-beat-static]');

    const lines = beats.map((beat) => container.querySelector(`[data-hero-beat="${beat.key}"]`));

    if (lines.some((line) => !line)) {
        return;
    }

    // Set once, so the first beat animates in from the same offset as every
    // other one rather than appearing without movement.
    lines.forEach((line) => {
        line.style.transform = `translateY(${TRAVEL}px)`;
    });

    let current = -1;
    let running = false;

    function show(index) {
        if (index === current) {
            return;
        }

        current = index;

        /*
         * The static line fades out on the first swap and never comes back.
         * It is not removed and not hidden from assistive technology — it is
         * still the one sentence a screen reader gets. It simply stops being
         * the one a sighted visitor is reading.
         */
        if (staticLine) {
            staticLine.style.opacity = '0';
        }

        lines.forEach((line, i) => {
            const active = i === index;

            line.style.opacity = active ? '1' : '0';
            line.style.transform = active ? 'translateY(0)' : `translateY(${TRAVEL}px)`;
        });
    }

    /** Which beat the clip is in right now. */
    function beatAt(time) {
        for (let i = beats.length - 1; i >= 0; i--) {
            if (time >= beats[i].start) {
                return i;
            }
        }

        return 0;
    }

    function frame() {
        if (!running) {
            return;
        }

        show(beatAt(video.currentTime));

        requestAnimationFrame(frame);
    }

    function stop() {
        running = false;
    }

    function go() {
        if (running) {
            return;
        }

        running = true;
        requestAnimationFrame(frame);
    }

    /*
     * Only while the clip is both playing AND on screen.
     *
     * A requestAnimationFrame loop is the only way to land the swap on the cut
     * rather than up to a quarter-second after it — `timeupdate` fires about
     * four times a second and that is visibly late. The cost of a rAF loop is
     * a callback per frame, so it is gated twice: the browser stops firing it
     * in a background tab, and the observer below stops it as soon as the hero
     * scrolls away.
     */
    video.addEventListener('playing', go);
    video.addEventListener('pause', stop);
    video.addEventListener('ended', stop);

    if ('IntersectionObserver' in window) {
        new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting && !video.paused) {
                    go();
                } else if (!entry.isIntersecting) {
                    stop();
                }
            },
            { threshold: 0 },
        ).observe(container);
    }
}

initHeroBeats();
