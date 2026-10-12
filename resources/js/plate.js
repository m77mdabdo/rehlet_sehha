/**
 * Build your plate.
 *
 * THIS FILE PRODUCES NUMBERS IN EXACTLY TWO PLACES. Both are marked in the
 * markup and nowhere else is permitted:
 *
 *   data-plate-quantity   how many portions of a food are on the plate
 *   data-plate-estimate   the energy span
 *
 * Everything else it computes — which group dominates, what is missing, how
 * wide a bar segment is — is turned into a WIDTH or into the choice of a
 * sentence written by the clinic. Nothing else formats a figure into the page.
 *
 * WHY THE RULE USED TO BE "NO NUMBERS AT ALL", AND WHY IT CHANGED.
 *
 * Numeric feedback teaches people to measure food, and measuring food is the
 * habit this clinic exists to undo. For anyone with a disordered relationship
 * to eating, a number attached to a food is not neutral information — it is
 * the mechanism of the disorder. That reasoning has not been retracted. On
 * 2026-10-12 the practitioner, having been shown it, decided an honest span
 * kept secondary to the proportion feedback was more useful to her patients
 * than silence. The conditions she attached are enforced here:
 *
 *   - A SPAN, never a single figure. Including for a food the patient adds
 *     herself: her own estimate is widened before it is shown, because it is
 *     at least as uncertain as ours.
 *   - Nothing unverified. A food the practitioner has not signed off arrives
 *     without energy attributes at all, counts towards the proportion
 *     feedback, and sits out of the total. The partial note says so.
 *   - No target, no goal, no daily total, no remaining count, no percentage,
 *     no progress bar, no streak, no history, no pass/fail colour.
 *   - NOTHING IS STORED. No fetch, no beacon, no localStorage, no
 *     sessionStorage, no cookie, no IndexedDB — asserted by
 *     PlateFeedbackHasNoNumbersTest, which reads this file. A food she adds
 *     lives in the Map below until she closes the tab.
 *
 * Every sentence comes from the plate translation files, handed over as JSON
 * payloads on the element. No copy lives in this file.
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

/**
 * Energy is rounded outwards to this, so the span never implies more
 * precision than a household spoon can carry. 455–518 becomes 450–520.
 */
const ROUND_TO = 10;

/**
 * How far either side of her own figure a custom food is shown.
 *
 * A patient's guess at the energy in a dish is at least as uncertain as our
 * sourced figures, and those are all spans. Showing her number back to her as
 * a single integer would give it a precision she never claimed.
 */
const OWN_ESTIMATE_BAND = 0.15;

/**
 * A food whose own span is at least this wide is a dish that varies by
 * household rather than one we are being vague about — koshari, mesaqaa.
 * Crossing it is what triggers the "varies between households" note.
 */
const WIDE_SPAN_RATIO = 1.8;

function initPlate() {
    const root = document.querySelector('[data-plate]');

    if (!root) {
        return;
    }

    let feedback;
    let colours;
    let labels;
    let units;
    let portionLabels;
    let estimateStrings;
    let customStrings;

    try {
        feedback = JSON.parse(root.dataset.plateFeedback);
        colours = JSON.parse(root.dataset.plateColours);
        labels = JSON.parse(root.dataset.plateLabels);
        units = JSON.parse(root.dataset.plateUnits);
        portionLabels = JSON.parse(root.dataset.platePortionLabels);
        estimateStrings = JSON.parse(root.dataset.plateEstimateStrings);
        customStrings = JSON.parse(root.dataset.plateCustomStrings);
    } catch {
        // Leave the server-rendered empty state alone rather than replacing a
        // readable page with a broken widget.
        return;
    }

    const bar = root.querySelector('[data-plate-bar]');
    const chosenList = root.querySelector('[data-plate-chosen]');
    const message = root.querySelector('[data-plate-message]');
    const estimate = root.querySelector('[data-plate-estimate]');
    const estimateValue = root.querySelector('[data-plate-estimate-value]');
    const estimateNote = root.querySelector('[data-plate-estimate-note]');

    const emptyLabel = bar?.dataset.emptyLabel ?? '';

    /*
     * Arabic uses ٬ ... an Arabic comma, U+060C, between list items; English
     * uses a plain one. Read off the document's own direction rather than
     * hard-coding either, so the spoken list sounds right in both locales.
     */
    const listSeparator = document.documentElement.dir === 'rtl' ? '، ' : ', ';

    // Arabic-Indic digits on the Arabic page, Latin on the English one. The
    // figure is the same information either way; it should look native.
    const numberFormat = new Intl.NumberFormat(document.documentElement.lang || 'en', {
        maximumFractionDigits: 1,
    });

    const fmt = (value) => numberFormat.format(value);

    /**
     * The plate: key → food.
     *
     * A Map so the order a patient added things in is the order she reads
     * back. `portions` replaced the old boolean membership: tapping a food
     * again increases the amount rather than removing it, and removal is now
     * its own control.
     *
     * THIS IS THE ONLY PLACE ANY OF THIS LIVES. It is not persisted, not
     * mirrored anywhere, and not sent to the server — see the file header.
     */
    const plate = new Map();

    function totalsByGroup() {
        const totals = {};

        for (const { group, portions } of plate.values()) {
            totals[group] = (totals[group] ?? 0) + portions;
        }

        return totals;
    }

    const totalPortions = () => [...plate.values()].reduce((sum, item) => sum + item.portions, 0);

    /**
     * Pick the sentence. FIRST match wins, so the order here is the priority:
     * an empty plate, then domination by one group, then something missing,
     * then balance.
     *
     * Shares are computed from PORTIONS now rather than from a count of
     * distinct foods, which is the honest reading: three spoons of oil and one
     * salad is a plate that is mostly fat, and the old version called it
     * balanced because it saw one food from each of two groups.
     */
    function chooseMessage(totals) {
        const total = totalPortions();

        if (total === 0) {
            return feedback.empty;
        }

        for (const [group, portions] of Object.entries(totals)) {
            if (portions / total >= DOMINANT_SHARE) {
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

    /**
     * "ملعقة" for one, "٣ ملاعق" for more.
     *
     * The digit goes inside a data-plate-quantity element at the call site,
     * never loose in the markup.
     */
    function portionLabel({ unit, portions }) {
        if (portions === 1) {
            return units.one[unit] ?? unit;
        }

        return `${fmt(portions)} ${units.many[unit] ?? unit}`;
    }

    /**
     * The energy span for the whole plate, or null when nothing on it has a
     * reviewed figure.
     *
     * Rounded OUTWARDS — floor the bottom, ceil the top — so the span only
     * ever gets wider from rounding, never narrower. And widened by one step
     * if the two ends land on the same number, because a single figure is the
     * one output this feature must not produce.
     */
    function energySpan() {
        let min = 0;
        let max = 0;
        let covered = 0;
        let wide = false;

        for (const item of plate.values()) {
            if (item.energyMin === null || item.energyMax === null) {
                continue;
            }

            min += item.energyMin * item.portions;
            max += item.energyMax * item.portions;
            covered += item.portions;

            if (item.energyMax / Math.max(item.energyMin, 1) >= WIDE_SPAN_RATIO) {
                wide = true;
            }
        }

        if (covered === 0) {
            return null;
        }

        let low = Math.floor(min / ROUND_TO) * ROUND_TO;
        let high = Math.ceil(max / ROUND_TO) * ROUND_TO;

        if (high <= low) {
            high = low + ROUND_TO;
        }

        return { low, high, covered, wide, partial: covered < totalPortions() };
    }

    function renderEstimate() {
        const span = energySpan();

        if (!span) {
            // Nothing reviewed on the plate. Hidden entirely rather than
            // showing an empty frame: on a fresh install every food is
            // awaiting sign-off and this block never appears at all.
            estimate.hidden = true;
            estimateValue.textContent = '';
            estimateNote.hidden = true;

            return;
        }

        estimate.hidden = false;

        estimateValue.textContent = (estimateStrings.value ?? '')
            .replace(':min', fmt(span.low))
            .replace(':max', fmt(span.high));

        // Two reasons the span may need explaining, and they can both apply.
        const notes = [];

        if (span.partial) {
            notes.push(estimateStrings.partial);
        }

        if (span.wide) {
            notes.push(estimateStrings.varies);
        }

        estimateNote.textContent = notes.join(' ');
        estimateNote.hidden = notes.length === 0;
    }

    function render() {
        const totals = totalsByGroup();
        const total = totalPortions();

        /*
         * The bar. Each segment's flex-grow is its share of the plate — the
         * ratio becomes a width and is never written down. There is no
         * percentage label here and there must not be one. Weighted by
         * portions, so two loaves of bread take twice the room one does.
         */
        bar.replaceChildren(
            ...Object.entries(totals).map(([group, portions]) => {
                const segment = document.createElement('span');

                segment.style.flexGrow = String(portions);
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
            total === 0
                ? emptyLabel
                : Object.entries(totals)
                      .sort((a, b) => b[1] - a[1])
                      .map(([group]) => labels[group] ?? group)
                      .join(listSeparator),
        );

        // What is on the plate: name, amount, and the controls to change it.
        chosenList.replaceChildren(
            ...[...plate.entries()].map(([key, item]) => {
                /*
                 * TWO LINES, not one.
                 *
                 * A single row put the name, the badge, the amount and three
                 * 44px controls side by side, and at 390 the controls
                 * overlapped each other while the badge was truncated away
                 * with the end of a long name. The badge is the only thing
                 * distinguishing her own estimate from a sourced one, so it
                 * must never be the part that gets clipped.
                 */
                const row = document.createElement('li');
                row.className = 'border-b border-line/60 pb-2 last:border-0 last:pb-0';

                const top = document.createElement('div');
                top.className = 'flex items-center gap-2';

                const name = document.createElement('span');
                name.className = 'min-w-0 flex-1 truncate';
                name.textContent = item.name;
                top.append(name);

                // Her own food, marked as hers wherever it appears, so it is
                // never read as a sourced value sitting beside ones that are.
                // shrink-0, so a long name cannot push it off the row.
                if (item.isCustom) {
                    const badge = document.createElement('span');
                    badge.className =
                        'shrink-0 whitespace-nowrap rounded-pill bg-sage px-2 py-0.5 text-[0.625rem] font-medium text-ink/70';
                    badge.textContent = customStrings.badge ?? '';
                    badge.title = customStrings.badge_title ?? '';
                    top.append(badge);
                }

                const bottom = document.createElement('div');
                bottom.className = 'mt-1 flex items-center justify-between gap-2';

                // THE AMOUNT. One of the two places a digit is permitted.
                const amount = document.createElement('span');
                amount.setAttribute('data-plate-quantity', '');
                amount.className = 'whitespace-nowrap text-xs text-muted';
                amount.textContent = portionLabel(item);

                const controls = document.createElement('span');
                controls.className = 'flex shrink-0 items-center gap-1';

                for (const [action, glyph, labelKey] of [
                    ['less', '−', 'less'],
                    ['more', '+', 'more'],
                    ['remove', '×', 'remove'],
                ]) {
                    const button = document.createElement('button');
                    button.type = 'button';
                    button.dataset.plateAdjust = action;
                    button.dataset.plateKey = key;
                    // No tap-target-row here: its negative block margin plus a
                    // 2.75rem minimum is what made three of these collide.
                    // Sized explicitly instead, still comfortably tappable.
                    button.className =
                        'inline-flex size-9 shrink-0 items-center justify-center rounded-pill text-base leading-none text-ink ring-1 ring-line transition hover:bg-sage focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark';
                    button.textContent = glyph;
                    button.setAttribute(
                        'aria-label',
                        (portionLabels[labelKey] ?? '').replace(':food', item.name),
                    );

                    controls.append(button);
                }

                bottom.append(amount, controls);
                row.append(top, bottom);

                return row;
            }),
        );

        message.textContent = chooseMessage(totals);

        renderEstimate();
    }

    /** Reflect which listed foods are on the plate, for the chips. */
    function syncChips() {
        root.querySelectorAll('[data-plate-food]').forEach((button) => {
            button.setAttribute('aria-pressed', plate.has(button.dataset.plateFood) ? 'true' : 'false');
        });
    }

    function adjust(key, delta) {
        const item = plate.get(key);

        if (!item) {
            return;
        }

        const next = Number((item.portions + delta * item.step).toFixed(2));

        if (next <= 0) {
            plate.delete(key);
        } else {
            item.portions = next;
        }

        syncChips();
        render();
    }

    root.addEventListener('click', (event) => {
        const adjuster = event.target.closest('[data-plate-adjust]');

        if (adjuster) {
            const { plateAdjust, plateKey } = adjuster.dataset;

            if (plateAdjust === 'remove') {
                plate.delete(plateKey);
                syncChips();
                render();
            } else {
                adjust(plateKey, plateAdjust === 'more' ? 1 : -1);
            }

            return;
        }

        const food = event.target.closest('[data-plate-food]');

        if (food) {
            const key = food.dataset.plateFood;
            const existing = plate.get(key);

            if (existing) {
                // Tapping again adds another portion rather than removing the
                // food. Removal is the × on its row, which is explicit.
                adjust(key, 1);

                return;
            }

            const step = Number(food.dataset.platePortion) || 1;

            plate.set(key, {
                group: food.dataset.plateGroup,
                name: food.dataset.plateName,
                unit: food.dataset.plateUnit,
                step,
                portions: step,
                // Absent for a food awaiting the practitioner's sign-off.
                energyMin: food.dataset.plateEnergyMin ? Number(food.dataset.plateEnergyMin) : null,
                energyMax: food.dataset.plateEnergyMax ? Number(food.dataset.plateEnergyMax) : null,
                isCustom: false,
            });

            syncChips();
            render();

            return;
        }

        if (event.target.closest('[data-plate-reset]')) {
            plate.clear();
            syncChips();
            render();
        }
    });

    initCustomFood(root, plate, { syncChips, render });

    render();
}

/**
 * A food the patient adds herself.
 *
 * Session only, in the sense that there is no session: it goes into the same
 * Map as everything else and dies with the tab. No request is made.
 */
function initCustomFood(root, plate, { syncChips, render }) {
    const panel = root.querySelector('[data-plate-custom]');

    if (!panel) {
        return;
    }

    const nameInput = panel.querySelector('[data-plate-custom-name]');
    const groupSelect = panel.querySelector('[data-plate-custom-group]');
    const likeSelect = panel.querySelector('[data-plate-custom-like]');
    const energyInput = panel.querySelector('[data-plate-custom-energy]');
    const saveButton = panel.querySelector('[data-plate-custom-save]');

    /** Show the control for whichever way she has chosen to give the energy. */
    function syncMode() {
        const mode = panel.querySelector('[data-plate-custom-mode]:checked')?.value ?? 'like';

        likeSelect.hidden = mode !== 'like';
        energyInput.hidden = mode !== 'own';
    }

    panel.querySelectorAll('[data-plate-custom-mode]').forEach((radio) => {
        radio.addEventListener('change', syncMode);
    });

    syncMode();

    saveButton.addEventListener('click', () => {
        const name = nameInput.value.trim();

        if (name === '') {
            nameInput.focus();

            return;
        }

        const mode = panel.querySelector('[data-plate-custom-mode]:checked')?.value ?? 'like';

        let energyMin = null;
        let energyMax = null;

        if (mode === 'like') {
            // Borrow the span of a food we already have a reviewed figure for.
            const option = likeSelect.selectedOptions[0];

            if (option) {
                energyMin = Number(option.dataset.energyMin);
                energyMax = Number(option.dataset.energyMax);
            }
        } else {
            const own = Number(energyInput.value);

            if (Number.isFinite(own) && own > 0) {
                /*
                 * HER FIGURE IS WIDENED BEFORE IT IS SHOWN.
                 *
                 * She gave one number; showing it back as one number would
                 * lend her guess a precision she never claimed, and would be
                 * the only single figure anywhere in the feature. A band
                 * either side keeps it a span and keeps it honest about being
                 * an estimate — hers, which is what the badge says.
                 */
                energyMin = Math.max(0, Math.round(own * (1 - OWN_ESTIMATE_BAND)));
                energyMax = Math.round(own * (1 + OWN_ESTIMATE_BAND));
            }
        }

        const key = `custom:${Date.now()}`;

        plate.set(key, {
            group: groupSelect.value,
            name,
            unit: 'piece',
            step: 1,
            portions: 1,
            energyMin,
            energyMax,
            isCustom: true,
        });

        nameInput.value = '';
        energyInput.value = '';

        syncChips();
        render();
    });
}

initPlate();
