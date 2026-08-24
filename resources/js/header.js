/**
 * The header over the hero.
 *
 * Transparent while the hero is behind it, solid once you have scrolled past.
 *
 * THE DEFAULT IS SOLID, AND THAT IS THE WHOLE SAFETY ARGUMENT. The markup ships
 * the solid treatment; this file only ever ADDS the transparent state, and only
 * on a page that actually has a hero to be transparent over. So a script that
 * fails to load, a page without a hero, and every other route all get a
 * readable header — the failure mode is "not as pretty", never "white on
 * white".
 *
 * It uses IntersectionObserver rather than a scroll listener. A scroll handler
 * would run on every frame of every scroll on every page, and this needs to
 * know exactly one thing: whether the hero still reaches the header. The
 * observer answers that natively, off the main thread, and fires twice per
 * page.
 */

/**
 * The transparent state is only correct while unobstructed footage is behind
 * the header. The root margin pulls the observation line down by the header's
 * own height so the switch happens as the hero's bottom edge meets it, not
 * after it has already slid underneath.
 */
function headerHeight(header) {
    return Math.round(header.getBoundingClientRect().height) || 72;
}

function initHeader() {
    const header = document.querySelector('[data-header]');
    const hero = document.querySelector('[data-hero]');

    if (!header || !hero) {
        // No hero on this page: the solid header in the markup is already right.
        return;
    }

    /*
     * The hero has no media, so there is nothing to be transparent over — the
     * section is a flat colour and white text on it is a guess. Leave it solid.
     */
    if (!hero.querySelector('[data-hero-poster]')) {
        return;
    }

    const setTransparent = (on) => {
        if (on) {
            header.setAttribute('data-transparent', '');
        } else {
            header.removeAttribute('data-transparent');
        }
    };

    let overHero = false;
    let menuOpen = header.querySelector('[data-menu-toggle]')?.getAttribute('aria-expanded') === 'true';

    const apply = () => setTransparent(overHero && !menuOpen);

    const observer = new IntersectionObserver(
        ([entry]) => {
            overHero = entry.isIntersecting;
            apply();
        },
        { rootMargin: `-${headerHeight(header)}px 0px 0px 0px`, threshold: 0 },
    );

    observer.observe(hero);

    /*
     * An open mobile menu is an opaque panel hanging off the header. Leaving
     * the bar above it transparent would put white links on the video directly
     * above ink links on paper, in one control. Solid for as long as it is
     * open, then back.
     */
    const toggle = header.querySelector('[data-menu-toggle]');

    if (toggle) {
        new MutationObserver(() => {
            menuOpen = toggle.getAttribute('aria-expanded') === 'true';
            apply();
        }).observe(toggle, { attributes: true, attributeFilter: ['aria-expanded'] });
    }
}

initHeader();
