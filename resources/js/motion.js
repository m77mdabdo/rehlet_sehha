/**
 * Motion: sections rising into view, and the stats counting up.
 *
 * The hero entrance is NOT here. It is a load-triggered CSS animation with no
 * script behind it, because on a throttled slow 4G connection this bundle does
 * not initialise until about 2.1s and the first screen cannot wait that long to
 * appear. See the Motion block in app.css.
 *
 * NOTHING HERE IS LOAD-BEARING. Every effect is additive on top of a page that
 * is already complete — see the Motion block in app.css. If this file never
 * runs, the head script's failsafe removes the class that hides anything and
 * the visitor gets the finished page with no animation. That is the correct
 * outcome, not a degraded one.
 *
 * Reduced motion is handled once, in the head script, by simply never adding
 * the class. Nothing below needs to check it again, and nothing below runs a
 * shortened animation instead — the preference means none, not less.
 */

/** Rise: how far apart to start cards in the same grid, and the ceiling. */
const REVEAL_STAGGER = 60;

/**
 * The stagger cap. Eight cards at 60ms each would take almost half a second to
 * finish, by which time the last one is animating in front of somebody who has
 * already read it. Past this point they arrive together.
 */
const REVEAL_STAGGER_MAX = 240;

/**
 * Fire at 15% rather than on first pixel, so the movement finishes as the
 * element settles into frame instead of being watched from the edge.
 */
const REVEAL_RATIO = 0.15;

/** The count. Long enough to register, short enough not to be waited on. */
const COUNT_DURATION = 1200;

/** Decelerating, so it settles onto the final value rather than stopping dead. */
const easeOut = (t) => 1 - (1 - t) ** 3;

/*
|------------------------------------------------------------------------------
| Numerals
|------------------------------------------------------------------------------
*/

const DIGIT_SETS = [
    '0123456789',
    '٠١٢٣٤٥٦٧٨٩', // Arabic-Indic
    '۰۱۲۳۴۵۶۷۸۹', // Persian
];

/**
 * Which digits the page is actually using, read off the rendered string rather
 * than assumed from the locale.
 *
 * The strip renders Latin numerals in Arabic as well as English today — its
 * figures come from PHP number_format, which is not locale-aware, and the
 * markup wraps them in <bdi dir="ltr"> precisely so they sit correctly inside
 * Arabic text. Counting in a different set from the one the server rendered
 * would make the number change script halfway through the animation.
 *
 * So this detects instead of deciding, and keeps working if the formatting
 * changes later.
 */
function digitsUsedIn(text) {
    for (const set of DIGIT_SETS) {
        for (const digit of set) {
            if (text.includes(digit)) return set;
        }
    }

    return DIGIT_SETS[0];
}

function toLatinDigits(text) {
    let out = '';

    for (const character of text) {
        const found = DIGIT_SETS.findIndex((set) => set.includes(character));
        out += found > 0 ? String(DIGIT_SETS[found].indexOf(character)) : character;
    }

    return out;
}

function fromLatinDigits(text, set) {
    return set === DIGIT_SETS[0] ? text : text.replace(/[0-9]/g, (d) => set[+d]);
}

/*
|------------------------------------------------------------------------------
| The counting figures
|------------------------------------------------------------------------------
*/

/**
 * Pull a figure apart into the pieces needed to rebuild it every frame.
 *
 * Everything comes from the string the SERVER rendered — the target value, how
 * many decimals, whether thousands are grouped, which numeral set, and any
 * prefix or suffix like "+". Nothing is configured in markup and nothing is
 * hardcoded here, so the animation cannot disagree with the figure it is
 * animating towards. That matters: these are claims about a clinic, and a
 * counter that overshoots or lands on its own idea of the number would be
 * publishing a different fact.
 */
function parseFigure(element) {
    const rendered = element.textContent.trim();
    const digits = digitsUsedIn(rendered);
    const latin = toLatinDigits(rendered);

    // The numeric run, with its grouping and decimal separators.
    const match = latin.match(/[0-9][0-9,.٫٬]*/);

    if (!match) return null;

    const numeric = match[0];
    const normalised = numeric.replace(/[,٬]/g, '').replace('٫', '.');
    const target = Number.parseFloat(normalised);

    if (!Number.isFinite(target)) return null;

    const decimalPart = normalised.split('.')[1];

    return {
        element,
        target,
        digits,
        // 4.9 has to count through 4.1, 4.2 … not jump from 4 to 5.
        decimals: decimalPart ? decimalPart.length : 0,
        grouped: /[,٬]/.test(numeric),
        prefix: rendered.slice(0, match.index),
        suffix: rendered.slice(match.index + numeric.length),
        rendered,
    };
}

function formatFigure(figure, value) {
    let text = value.toFixed(figure.decimals);

    if (figure.grouped) {
        const [whole, fraction] = text.split('.');
        text = whole.replace(/\B(?=(\d{3})+(?!\d))/g, ',') + (fraction ? '.' + fraction : '');
    }

    return figure.prefix + fromLatinDigits(text, figure.digits) + figure.suffix;
}

/**
 * Pin the width before the first frame.
 *
 * THE SINGLE MOST LIKELY WAY THIS TASK COULD REGRESS CLS. "500+" is wider than
 * "0+", so a strip that counts up without reserving space reflows on almost
 * every frame, and drags its neighbours with it.
 *
 * Measured from the final string, which is what is on screen right now —
 * the server rendered the finished value and nothing has touched it yet. Taken
 * after fonts have settled, because measuring mid-swap pins a width the final
 * face will not fit into.
 */
function reserveWidth(figure) {
    const width = figure.element.getBoundingClientRect().width;

    if (width > 0) figure.element.style.minWidth = `${Math.ceil(width)}px`;
}

function runCount(figure) {
    reserveWidth(figure);

    const start = performance.now();

    const step = (now) => {
        const progress = Math.min((now - start) / COUNT_DURATION, 1);

        figure.element.textContent = formatFigure(figure, easeOut(progress) * figure.target);

        if (progress < 1) {
            requestAnimationFrame(step);
        } else {
            // Land on the server's own string, not on our reconstruction of it.
            figure.element.textContent = figure.rendered;
        }
    };

    requestAnimationFrame(step);
}

/*
|------------------------------------------------------------------------------
| Wiring
|------------------------------------------------------------------------------
*/

function initReveals(fontsReady) {
    const targets = document.querySelectorAll('.reveal');
    const figures = document.querySelectorAll('.stat-figure');

    if (!targets.length && !figures.length) return;

    const counted = new WeakSet();

    const observer = new IntersectionObserver(
        (entries) => {
            for (const entry of entries) {
                if (!entry.isIntersecting) continue;

                const element = entry.target;

                if (element.classList.contains('reveal')) {
                    element.classList.add('is-revealed');
                }

                /*
                 * Figures are observed in their own right rather than found
                 * inside a revealed ancestor. They happen to sit inside one
                 * today, but tying the count to that would mean deleting a
                 * wrapper class silently stops the numbers working.
                 */
                if (element.classList.contains('stat-figure') && !counted.has(element)) {
                    counted.add(element);

                    const figure = parseFigure(element);

                    // Wait for the real face before measuring: a width pinned
                    // mid font-swap is a width the final face may not fit.
                    if (figure) fontsReady.then(() => runCount(figure));
                }

                // Once. Scrolling back up does not replay it — a page that
                // re-animates every time you look at it is exhausting.
                observer.unobserve(element);
            }
        },
        { threshold: REVEAL_RATIO },
    );

    for (const figure of figures) observer.observe(figure);

    /*
     * Stagger by position within the grid, worked out once at startup rather
     * than from the order things happen to intersect — which would depend on
     * scroll speed and give the same grid a different rhythm every time.
     */
    const counts = new Map();

    for (const element of targets) {
        const parent = element.parentElement;
        const index = counts.get(parent) ?? 0;

        counts.set(parent, index + 1);

        if (index > 0) {
            element.style.setProperty(
                '--reveal-delay',
                `${Math.min(index * REVEAL_STAGGER, REVEAL_STAGGER_MAX)}ms`,
            );
        }

        observer.observe(element);
    }
}

function initMotion() {
    const root = document.documentElement;

    // Stand the head script's failsafe down: we are here, nothing will be left
    // hidden. Done before any early return, so declining to animate still
    // counts as arriving.
    root.setAttribute('data-motion-ready', '');

    if (!root.classList.contains('js-motion')) {
        // Reduced motion, no IntersectionObserver, or the failsafe already
        // fired. Nothing is hidden, so there is nothing to do.
        return;
    }

    const fontsReady = document.fonts ? document.fonts.ready : Promise.resolve();

    initReveals(fontsReady);
}

initMotion();
