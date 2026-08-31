<?php

declare(strict_types=1);

/**
 * A FAILURE MESSAGE THAT NEVER REACHES ANYBODY IS NOT A FAILURE MESSAGE.
 *
 * Pest's containment expectations take NEEDLES, not messages:
 *
 *   toContain('a', 'b')      asserts BOTH 'a' and 'b' are present
 *   toStartWith('a', 'b')    silently discards 'b'
 *   toHaveKey('k', 'v')      asserts the key holds the value 'v'
 *
 * So the natural spelling — `expect($x)->toContain($needle, 'why this matters')`
 * — does one of two things, both bad. In a positive assertion it quietly makes
 * the assertion STRICTER and then fails complaining that the haystack does not
 * contain the explanation. In a negated one it makes it WEAKER, and passes.
 * Either way the sentence somebody wrote for the person debugging at midnight
 * is not printed.
 *
 * This cost real time three separate times on this project before anybody
 * noticed the pattern, which is what a scan is for. The alternative spelling is
 * always available and always works:
 *
 *   expect(str_contains($haystack, $needle))->toBeTrue('why this matters');
 *
 * MULTIPLE NEEDLES ARE STILL LEGITIMATE — `toContain('a', 'b', 'c')` is a
 * perfectly good assertion. What this test forbids is a STRING LITERAL
 * containing spaces in the second position, which is what a message looks like
 * and what a needle almost never does.
 */
it('never passes a failure message where Pest expects a needle', function () {
    $offences = [];

    /** @var iterable<SplFileInfo> $files */
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(base_path('tests')));

    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }

        $tokens = token_get_all((string) file_get_contents($file->getPathname()));
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            $token = $tokens[$i];

            if (! is_array($token) || $token[0] !== T_STRING) {
                continue;
            }

            if (! in_array($token[1], ['toContain', 'toStartWith', 'toEndWith', 'toHaveKey'], true)) {
                continue;
            }

            $open = $i + 1;

            while ($open < $count && $tokens[$open] === ' ') {
                $open++;
            }

            if (($tokens[$open] ?? null) !== '(') {
                continue;
            }

            // Walk the argument list at depth 1, collecting the token that
            // begins each argument after the first.
            $depth = 0;
            $argument = 0;
            $starts = [];

            for ($k = $open; $k < $count; $k++) {
                $text = is_array($tokens[$k]) ? $tokens[$k][1] : $tokens[$k];

                if ($text === '(' || $text === '[') {
                    $depth++;

                    continue;
                }

                if ($text === ')' || $text === ']') {
                    $depth--;

                    if ($depth === 0) {
                        break;
                    }

                    continue;
                }

                if ($depth === 1 && $text === ',') {
                    $argument++;
                    $starts[$argument] = null;

                    continue;
                }

                if ($depth === 1 && $argument > 0 && ($starts[$argument] ?? null) === null) {
                    if (is_array($tokens[$k]) && $tokens[$k][0] === T_WHITESPACE) {
                        continue;
                    }

                    $starts[$argument] = $tokens[$k];
                }
            }

            foreach ($starts as $position => $start) {
                if (! is_array($start)) {
                    continue;
                }

                $isString = in_array($start[0], [T_CONSTANT_ENCAPSED_STRING, T_ENCAPSED_AND_WHITESPACE], true)
                    || $start[0] === T_START_HEREDOC;

                if (! $isString) {
                    continue;
                }

                /*
                 * A sentence, not a needle. Needles here are class names,
                 * fragments of markup, times, slugs — none of which read as
                 * prose. Four or more words with a space between them is a
                 * message somebody wrote for a human.
                 */
                $literal = trim($start[1], "'\"");

                if (str_word_count($literal) >= 4) {
                    $offences[] = sprintf(
                        '%s:%d — %s() argument %d is a sentence, not a needle: «%s»',
                        str_replace(base_path().'/', '', $file->getPathname()),
                        $start[2],
                        $token[1],
                        $position + 1,
                        mb_substr($literal, 0, 60),
                    );
                }
            }
        }
    }

    expect($offences)->toBeEmpty(
        "A failure message is being passed where Pest expects a needle:\n  "
        .implode("\n  ", $offences)
        ."\n\nUse expect(str_contains(\$haystack, \$needle))->toBeTrue(\$message) instead."
    );
});
