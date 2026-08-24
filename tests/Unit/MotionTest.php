<?php

declare(strict_types=1);

/**
 * Motion must never be load-bearing.
 *
 * Three effects ship: the hero sliding in, sections rising as they come into
 * view, and the stats counting up. All three are decoration, and the point of
 * this file is that the page is complete without any of them.
 *
 * The failure this guards against is specific and common: a stylesheet sets
 * opacity: 0 and waits for script to clear it, and then one blocked bundle, one
 * syntax error or one unsupported API turns the whole page blank. On a clinic
 * site that is not a cosmetic bug — it is a patient who cannot find the booking
 * button.
 *
 * So the rule is: NOTHING IS HIDDEN EXCEPT UNDER A CLASS A SCRIPT HAS TO ADD,
 * or for the duration of an animation that finishes by itself. The test below
 * reads the stylesheet and enforces exactly that, rather than trusting the
 * comments in it.
 *
 * The behavioural half — reduced motion producing no movement at all, no-JS
 * leaving everything visible, the counter landing on the right value, CLS
 * staying at zero while digits change — was verified by driving headless Chrome
 * over CDP, because this project has no JavaScript test runner and adding one
 * would mean installing a toolchain that is not part of it. Those results are
 * in the task report.
 */
function stylesheet(): string
{
    return file_get_contents(resource_path('css/app.css'));
}

/**
 * Walk the stylesheet, recording where things are NESTED.
 *
 * A brace walk rather than a regex, because every question this file asks is
 * about enclosure — is this hidden only under .js-motion, is that movement
 * inside the reduced-motion guard — and enclosure is not something a regex can
 * see. Comments are skipped, since they legitimately contain both braces and
 * the selectors being looked for.
 *
 * @return array{hidden: list<array{line: int, context: string}>, blocks: list<array{selector: string, stack: list<string>}>}
 */
function walkStylesheet(): array
{
    $css = stylesheet();
    $stack = [];
    $found = [];
    $blocks = [];
    $buffer = '';
    $line = 1;
    $inComment = false;

    for ($i = 0, $length = strlen($css); $i < $length; $i++) {
        $character = $css[$i];

        if ($character === "\n") {
            $line++;
        }

        // Comments legitimately contain braces and the words we look for.
        if (! $inComment && $character === '/' && ($css[$i + 1] ?? '') === '*') {
            $inComment = true;
            $i++;

            continue;
        }

        if ($inComment) {
            if ($character === '*' && ($css[$i + 1] ?? '') === '/') {
                $inComment = false;
                $i++;
            }

            continue;
        }

        if ($character === '{') {
            $selector = trim(preg_replace('/\s+/', ' ', $buffer));
            $blocks[] = ['selector' => $selector, 'stack' => $stack];
            $stack[] = $selector;
            $buffer = '';

            continue;
        }

        if ($character === '}') {
            array_pop($stack);
            $buffer = '';

            continue;
        }

        if ($character === ';') {
            if (preg_match('/opacity\s*:\s*0(\s|$|;)/', $buffer.';') === 1) {
                $found[] = ['line' => $line, 'context' => implode(' >> ', $stack)];
            }

            $buffer = '';

            continue;
        }

        $buffer .= $character;
    }

    return ['hidden' => $found, 'blocks' => $blocks];
}

/**
 * @return list<array{line: int, context: string}>
 */
function hidingRules(): array
{
    return walkStylesheet()['hidden'];
}

/**
 * The chain of blocks a given selector sits inside, or null if it is absent.
 *
 * @return list<string>|null
 */
function enclosing(string $selector): ?array
{
    foreach (walkStylesheet()['blocks'] as $block) {
        if ($block['selector'] === $selector) {
            return $block['stack'];
        }
    }

    return null;
}

it('hides nothing that a script is not required to un-hide', function () {
    $offenders = [];

    foreach (hidingRules() as $rule) {
        /*
         * Two things may set opacity: 0.
         *
         *   - Anything under .js-motion, which only a script adds, and which a
         *     failsafe timer removes again if the bundle never checks in. No
         *     script, no hiding.
         *   - A @keyframes `from` step. An animation with fill-mode both holds
         *     that state only while it runs and then ends on its own, so
         *     nothing is waiting for anything.
         */
        $context = $rule['context'];

        if (str_contains($context, '.js-motion')) {
            continue;
        }

        if (str_contains($context, '@keyframes')) {
            continue;
        }

        $offenders[] = sprintf('app.css:%d  inside  %s', $rule['line'], $context ?: '(top level)');
    }

    expect($offenders)->toBeEmpty(
        "Something is hidden without a script being required to bring it back.\n\n"
        ."If the bundle fails to load, this content is invisible and the page is\n"
        ."broken rather than merely unanimated. Nest it under .js-motion, or use\n"
        ."a keyframe animation that finishes by itself.\n\n"
        .implode("\n", $offenders)."\n"
    );
});

it('keeps every movement behind a reduced-motion guard', function () {
    /*
     * Not shortened — absent. Scroll-linked or looping movement is a known
     * trigger for vestibular disorders and for migraine with aura, and somebody
     * who set that preference set it to avoid exactly this.
     *
     * This checks the ACTUAL enclosing blocks, not merely that some block is
     * open. An earlier version of this test only counted braces, and passed
     * when the entrance rule was moved into an unrelated @media — which is the
     * precise mistake it exists to catch.
     */
    $guard = '@media (prefers-reduced-motion: no-preference)';

    foreach (['.js-motion .reveal', '.js-motion .reveal.is-revealed', '[data-enter]'] as $selector) {
        $stack = enclosing($selector);

        expect($stack)->not->toBeNull("The {$selector} rule is gone from app.css.");

        expect(in_array($guard, $stack, true))->toBeTrue(
            "{$selector} is not inside {$guard}.\n"
            .'It is inside: '.(implode(' >> ', $stack) ?: '(top level)')
        );
    }
});

it('reserves the width of the counting figures whether or not they count', function () {
    /*
     * The single most likely way this task could have regressed CLS. "500+" is
     * wider than "0+", so a strip that counts without reserving space reflows
     * on nearly every frame and drags its neighbours with it.
     *
     * These two properties must apply unconditionally. If they only applied
     * while animating, then turning the animation on would itself change the
     * layout — which is the regression they exist to prevent.
     */
    foreach (hidingRules() as $rule) {
        expect($rule['context'])->not->toContain('.stat-figure');
    }

    $css = stylesheet();
    $position = strpos($css, '.stat-figure {');

    expect($position)->not->toBeFalse('The .stat-figure rule is gone.');

    $block = substr($css, $position, strpos($css, '}', $position) - $position);

    expect($block)->toContain('tabular-nums');
    expect($block)->toContain('inline-block');

    // Unconditional: nothing enclosing it at all.
    expect(enclosing('.stat-figure'))->toBe(
        [],
        '.stat-figure is nested inside something. It must apply in every state, '
        .'because turning the animation on must not change the layout.'
    );
})->skip(fn (): bool => ! str_contains(stylesheet(), '.stat-figure'), 'No counting figures.');

it('renders the stats strip visible, with no inline opacity and the real figures', function (string $locale) {
    $html = $this->get("/{$locale}")->assertOk()->getContent();

    // Nothing hidden inline either — a style attribute would bypass the rule above.
    expect(preg_match('/style="[^"]*opacity:\s*0[^.\d]/', $html))->toBe(0, 'Something is inline-hidden.');

    foreach ([
        number_format((int) config('clinic.stats.cases')).'+',
        config('clinic.stats.years').'+',
        number_format((float) config('clinic.stats.rating'), 1),
    ] as $value) {
        // str_contains rather than toContain: Pest reads extra arguments to
        // toContain as further needles, not as a failure message.
        expect(str_contains($html, '>'.$value.'</bdi>'))->toBeTrue(
            "The stats strip does not render «{$value}» as server-side text. "
            .'The figures must be in the markup, not produced by the counter.'
        );
    }
})->with(['ar', 'en']);

it('arms a failsafe that un-hides the page if the bundle never arrives', function () {
    $layout = file_get_contents(resource_path('views/components/layouts/app.blade.php'));

    expect($layout)->toContain("classList.add('js-motion')");
    expect($layout)->toContain("classList.remove('js-motion')");
    expect($layout)->toContain('data-motion-ready');

    // Reduced motion and missing IntersectionObserver both decline before the
    // class is ever added, so neither can leave anything hidden.
    expect($layout)->toContain("'IntersectionObserver' in window");
    expect($layout)->toContain('prefers-reduced-motion: reduce');

    $script = file_get_contents(resource_path('js/motion.js'));

    // And the bundle stands the failsafe down as its first act, before any
    // early return, so declining to animate still counts as having arrived.
    expect($script)->toContain("setAttribute('data-motion-ready'");
});

it('does not animate the booking wizard, the admin panel or the tap targets', function () {
    /*
     * A patient mid-form does not want movement, staff use the admin all day,
     * and a control that responds to a tap has to feel instant rather than
     * animated.
     */
    $wizard = $this->get('/ar/booking')->assertOk()->getContent();

    expect(str_contains($wizard, 'class="reveal'))->toBeFalse('The booking wizard has a scroll reveal in it.');
    expect(str_contains($wizard, 'data-enter'))->toBeFalse('The booking wizard has an entrance animation in it.');

    foreach (['plate', 'matcher'] as $section) {
        $view = file_get_contents(resource_path("views/components/sections/{$section}.blade.php"));

        expect(str_contains($view, 'reveal'))->toBeFalse("The {$section} has a scroll reveal on a tap target.");
    }
});
