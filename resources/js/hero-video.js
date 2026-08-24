/**
 * The hero background video.
 *
 * The element ships with NO src. This file decides whether the visitor gets a
 * video at all, and only then hands one over.
 *
 * That order matters. preload="none" is a hint the browser is allowed to
 * ignore, and a <source> present in the markup can be fetched before any of
 * these checks run. Withholding the URL is the only way to promise that a
 * visitor on a metered connection is not billed for a decoration.
 *
 * Three groups never get it:
 *
 *   - prefers-reduced-motion: reduce. Not a nicety. A looping video is a known
 *     trigger for vestibular disorders and for migraine with aura, and the
 *     person who set that preference did so to avoid exactly this.
 *   - Save-Data, or an effective connection of 2g/3g. Egyptian mobile data is
 *     the normal case for this clinic's patients, not an edge case, and a
 *     megabyte of background footage is a real cost to them.
 *   - Anyone whose browser cannot play it. The poster underneath is the
 *     design, not a fallback for it, so failure is silent and looks intended.
 *
 * The poster is a sibling <img> that is never removed. The video fades in over
 * it and is transparent until it can actually play, so a 404, a decode error
 * or a refused autoplay all end with the poster still on screen. There is no
 * path here that produces a black box.
 *
 * Nothing this file does can move the layout: both elements are absolutely
 * positioned, so the section is sized by its content whatever happens.
 */

/**
 * Connection types that do not get a background video.
 *
 * 3g is included deliberately. It is the common case on Egyptian mobile, and a
 * megabyte at 3g speeds is several seconds of somebody's data allowance spent
 * on something they did not ask to watch.
 */
const SLOW_CONNECTIONS = ['slow-2g', '2g', '3g'];

/**
 * How long to wait before even thinking about the video.
 *
 * Idle time, not a fixed delay, so the fetch queues behind first paint and the
 * hero's own images rather than competing with them. The timeout is the
 * backstop for a page that never goes idle.
 */
const IDLE_TIMEOUT = 2500;

function initHeroVideo() {
    const video = document.querySelector('[data-hero-video]');

    if (!video) {
        // Save-Data was answered on the server, or the section has no media.
        return;
    }

    const source = video.dataset.src;

    if (!source) {
        return;
    }

    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

    /**
     * Give up for good and leave the poster showing.
     *
     * Removing the element rather than hiding it releases the decoder and
     * stops a partially-buffered file from continuing to download.
     */
    const abandon = () => {
        video.removeAttribute('src');
        video.load();
        video.remove();
    };

    const connection = navigator.connection;

    if (connection?.saveData || SLOW_CONNECTIONS.includes(connection?.effectiveType)) {
        abandon();

        return;
    }

    if (reducedMotion.matches) {
        abandon();

        return;
    }

    /*
     * The preference can be turned on while the page is open — someone reaches
     * for it *because* something started moving. Honour it immediately rather
     * than only at load.
     */
    reducedMotion.addEventListener('change', (event) => {
        if (event.matches) {
            video.pause();
            video.classList.add('opacity-0');
            abandon();
        }
    });

    const start = () => {
        video.addEventListener(
            'canplay',
            () => {
                video.classList.remove('opacity-0');
            },
            { once: true },
        );

        // A 404, a codec the browser will not decode, a truncated file.
        video.addEventListener('error', abandon, { once: true });

        video.src = source;

        // Autoplay can still be refused by policy or by a battery-saver mode.
        // That is a legitimate answer, not an error: keep the poster.
        video.play()?.catch(abandon);
    };

    /*
     * Load, THEN idle. Both, in that order, and the order is the whole point.
     *
     * requestIdleCallback on its own fires in any gap the main thread happens
     * to leave — including one before the page has finished loading — so the
     * video ends up competing for bandwidth with the poster and the fonts that
     * decide when the hero actually paints. Measured on this page it cost
     * 0.28s of both FCP and LCP, which is a decoration slowing down the
     * headline it sits behind.
     *
     * Waiting for `load` first means every byte that affects what the visitor
     * reads has already been fetched before the video asks for anything.
     */
    const afterLoad = (fn) => {
        if (document.readyState === 'complete') {
            fn();
        } else {
            window.addEventListener('load', fn, { once: true });
        }
    };

    afterLoad(() => {
        if ('requestIdleCallback' in window) {
            requestIdleCallback(start, { timeout: IDLE_TIMEOUT });
        } else {
            // Safari has no requestIdleCallback.
            window.setTimeout(start, 600);
        }
    });
}

initHeroVideo();
