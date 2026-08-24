/**
 * The package matcher.
 *
 * Three questions, scored in the browser, ending in a recommendation and a
 * deep link into the booking wizard.
 *
 * NOTHING LEAVES THIS FUNCTION. No fetch, no beacon, no localStorage, no
 * analytics event. The answers exist in one array in memory and are gone when
 * the tab closes. That is a promise made on screen in the section copy, and
 * this file is where it is either kept or broken — so nothing may be added here
 * that reports what somebody answered.
 *
 * Not a single sentence lives in this file either: every question, option and
 * result comes from the matcher translation files, handed over as a JSON payload
 * on the element, so the clinic can reword the quiz without touching JavaScript.
 */

/**
 * Which package each answer points at.
 *
 * Kept as weights rather than a decision tree, because a tree of three
 * questions is eight branches to maintain and this is four lines. The heaviest
 * total wins; ties fall to the earlier package, which is the cheaper one — if
 * the quiz cannot tell, it should not upsell.
 */
const WEIGHTS = {
    // Question 1 — what brought you here.
    understand: { 'lab-review': 3 },
    start: { 'single-consultation': 2, 'one-month-programme': 1 },
    condition: { 'three-months-programme': 2, 'one-month-programme': 1 },

    // Question 2 — have you done this before.
    never: { 'single-consultation': 2 },
    tried: { 'one-month-programme': 2 },
    many: { 'three-months-programme': 3 },

    // Question 3 — how much follow-up.
    once: { 'single-consultation': 2, 'lab-review': 1 },
    month: { 'one-month-programme': 3 },
    longer: { 'three-months-programme': 3 },
};

function initMatcher() {
    const root = document.querySelector('[data-matcher]');

    if (!root) {
        return;
    }

    let payload;

    try {
        payload = JSON.parse(root.dataset.matcherPayload);
    } catch {
        // Malformed payload: leave the server-rendered first question in place
        // rather than replacing a readable page with a broken widget.
        return;
    }

    const { questions, results } = payload;

    if (!questions?.length || !results?.length) {
        return;
    }

    const quiz = root.querySelector('[data-matcher-quiz]');
    const resultPane = root.querySelector('[data-matcher-result]');
    const progress = root.querySelector('[data-matcher-progress]');
    const questionEl = root.querySelector('[data-matcher-question]');
    const optionsEl = root.querySelector('[data-matcher-options]');
    const backButton = root.querySelector('[data-matcher-back]');
    const announce = root.querySelector('[data-matcher-announce]');

    const resultName = root.querySelector('[data-matcher-result-name]');
    const resultWhy = root.querySelector('[data-matcher-result-why]');
    const cta = root.querySelector('[data-matcher-cta]');

    // The whole of the state. Retaking the quiz is emptying this array.
    const answers = [];

    const progressTemplate = progress?.dataset.template ?? '';
    const ctaTemplate = cta?.dataset.template ?? '';

    function renderQuestion() {
        const index = answers.length;
        const question = questions[index];

        quiz.hidden = false;
        resultPane.hidden = true;

        // Rebuilt from the translation payload, so the words are always the
        // server's rather than anything this file invented.
        if (progress) {
            progress.textContent = formatProgress(index + 1, questions.length);
        }

        questionEl.textContent = question.text;

        optionsEl.replaceChildren(
            ...question.options.map((option) => {
                const button = document.createElement('button');

                button.type = 'button';
                button.dataset.matcherOption = option.id;
                button.textContent = option.text;
                button.className =
                    'block w-full rounded-lg bg-white p-4 text-start text-sm leading-relaxed text-ink ring-1 ring-line transition hover:ring-accent focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark';

                return button;
            }),
        );

        backButton.disabled = index === 0;

        if (announce) {
            announce.textContent = question.text;
        }
    }

    /**
     * The progress line, with the numbers put into the server's sentence.
     *
     * The template still carries :current and :total, so the substitution is a
     * plain string replace. Matching digits out of a rendered string would
     * break the moment the copy used Arabic-Indic numerals or put the total
     * first — neither of which is this file's business to know about.
     */
    function formatProgress(current, total) {
        return progressTemplate
            .replace(':current', String(current))
            .replace(':total', String(total));
    }

    function score() {
        const totals = {};

        for (const answer of answers) {
            const weights = WEIGHTS[answer] ?? {};

            for (const [slug, weight] of Object.entries(weights)) {
                totals[slug] = (totals[slug] ?? 0) + weight;
            }
        }

        let best = null;

        // results is in the order the translation file declares, which is
        // cheapest-first — so a tie resolves to the less expensive package.
        for (const result of results) {
            const total = totals[result.slug] ?? 0;

            if (best === null || total > best.total) {
                best = { result, total };
            }
        }

        return best?.result ?? results[0];
    }

    function renderResult() {
        const result = score();

        quiz.hidden = true;
        resultPane.hidden = false;

        resultName.textContent = result.name;
        resultWhy.textContent = result.why;

        cta.href = result.url;
        cta.textContent = ctaTemplate.replace(':package', result.name);

        if (announce) {
            announce.textContent = `${result.name}. ${result.why}`;
        }
    }

    root.addEventListener('click', (event) => {
        const option = event.target.closest('[data-matcher-option]');

        if (option) {
            answers.push(option.dataset.matcherOption);

            if (answers.length >= questions.length) {
                renderResult();
            } else {
                renderQuestion();
            }

            return;
        }

        if (event.target.closest('[data-matcher-back]')) {
            answers.pop();
            renderQuestion();

            return;
        }

        if (event.target.closest('[data-matcher-restart]')) {
            answers.length = 0;
            renderQuestion();
        }
    });
}

initMatcher();
