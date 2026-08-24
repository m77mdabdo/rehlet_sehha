/**
 * Build your plate.
 *
 * THIS FILE MUST NEVER PRODUCE A NUMBER THE PATIENT CAN SEE.
 *
 * No calories, no grams, no portions, no score, no percentage. It counts
 * internally — it has to, to know which group takes the most room — but a count
 * is only ever turned into a WIDTH or into the choice of a sentence written by
 * the clinic. Nothing here formats a figure into the page.
 *
 * The reason is clinical rather than stylistic. Numeric feedback teaches people
 * to measure food, and measuring food is the habit this clinic exists to undo.
 * For anyone with a disordered relationship to eating, a number attached to a
 * food is not neutral information — it is the mechanism of the disorder. A tool
 * on a nutrition clinic's homepage must not be a calorie counter in disguise.
 *
 * If you are here to add "just a small calorie estimate", the answer is no, and
 * PlateFeedbackHasNoNumbersTest will say so too.
 *
 * Every sentence comes from the plate translation files, handed over as a JSON
 * payload on the element. No copy lives in this file.
 *
 * (Written without a glob: a star followed by a slash ends a block comment, and
 * the rest of the paragraph becomes a syntax error on the line below.)
 */

/**
 * What counts as "mostly" one group.
 *
 * A ratio, not a threshold anybody sees. At half the plate or more, one group
 * is dominant enough that saying so is useful rather than pedantic.
 */
const DOMINANT_SHARE = 0.5;

/**
 * The three groups a main meal is expected to have.
 *
 * Fruit, fat and dairy are not required — a plate without cheese is not
 * missing anything, and telling someone it is would be inventing a rule.
 */
const EXPECTED = ['vegetable', 'protein', 'starch'];

function initPlate() {
    const root = document.querySelector('[data-plate]');

    if (!root) {
        return;
    }

    let feedback;
    let colours;
    let labels;

    try {
        feedback = JSON.parse(root.dataset.plateFeedback);
        colours = JSON.parse(root.dataset.plateColours);
        labels = JSON.parse(root.dataset.plateLabels);
    } catch {
        // Leave the server-rendered empty state alone rather than replacing a
        // readable page with a broken widget.
        return;
    }

    const bar = root.querySelector('[data-plate-bar]');
    const chosenList = root.querySelector('[data-plate-chosen]');
    const message = root.querySelector('[data-plate-message]');

    const emptyLabel = bar?.dataset.emptyLabel ?? '';

    /*
     * Arabic uses ٬ ... an Arabic comma, U+060C, between list items; English
     * uses a plain one. Read off the document's own direction rather than
     * hard-coding either, so the spoken list sounds right in both locales.
     */
    const listSeparator = document.documentElement.dir === 'rtl' ? '، ' : ', ';

    /**
     * The plate: food id → { group, name }.
     *
     * A Map rather than an array so tapping the same food twice removes it
     * instead of stacking it. Quantity is not a concept this feature has.
     */
    const plate = new Map();

    function counts() {
        const totals = {};

        for (const { group } of plate.values()) {
            totals[group] = (totals[group] ?? 0) + 1;
        }

        return totals;
    }

    /**
     * Pick the sentence. FIRST match wins, so the order here is the priority:
     * an empty plate, then domination by one group, then something missing,
     * then balance.
     */
    function chooseMessage(totals) {
        const size = plate.size;

        if (size === 0) {
            return feedback.empty;
        }

        for (const [group, count] of Object.entries(totals)) {
            if (count / size >= DOMINANT_SHARE) {
                const key = `mostly_${group}`;

                if (feedback[key]) {
                    return feedback[key];
                }
            }
        }

        for (const group of EXPECTED) {
            if (!totals[group]) {
                const key = `no_${group}`;

                if (feedback[key]) {
                    return feedback[key];
                }
            }
        }

        return feedback.balanced;
    }

    function render() {
        const totals = counts();
        const size = plate.size;

        /*
         * The bar. Each segment's flex-grow is its share of the plate — the
         * ratio becomes a width and is never written down. There is no
         * percentage label here and there must not be one.
         */
        bar.replaceChildren(
            ...Object.entries(totals).map(([group, count]) => {
                const segment = document.createElement('span');

                segment.style.flexGrow = String(count);
                segment.style.backgroundColor = colours[group] ?? 'currentColor';
                segment.className = 'block h-full motion-safe:transition-all';
                // Named for a screen reader that reaches inside the bar; the
                // bar's own aria-label carries the summary.
                segment.title = labels[group] ?? group;

                return segment;
            }),
        );

        /*
         * The bar's accessible name lists the groups present, in the order
         * they take up the plate — the same information the widths carry, in
         * words, with no figures.
         */
        bar.setAttribute(
            'aria-label',
            size === 0
                ? emptyLabel
                : Object.entries(totals)
                      .sort((a, b) => b[1] - a[1])
                      .map(([group]) => labels[group] ?? group)
                      .join(listSeparator),
        );

        // What is on the plate, as names.
        chosenList.replaceChildren(
            ...[...plate.values()].map(({ name }) => {
                const item = document.createElement('li');

                item.textContent = name;

                return item;
            }),
        );

        message.textContent = chooseMessage(totals);
    }

    root.addEventListener('click', (event) => {
        const food = event.target.closest('[data-plate-food]');

        if (food) {
            const id = food.dataset.plateFood;

            if (plate.has(id)) {
                plate.delete(id);
                food.setAttribute('aria-pressed', 'false');
            } else {
                plate.set(id, {
                    group: food.dataset.plateGroup,
                    name: food.dataset.plateName,
                });
                food.setAttribute('aria-pressed', 'true');
            }

            render();

            return;
        }

        if (event.target.closest('[data-plate-reset]')) {
            plate.clear();

            root.querySelectorAll('[data-plate-food]').forEach((button) => {
                button.setAttribute('aria-pressed', 'false');
            });

            render();
        }
    });
}

initPlate();
