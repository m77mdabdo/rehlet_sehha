<?php

declare(strict_types=1);

use Symfony\Component\Finder\Finder;

/**
 * The layout must flip from Arabic to English on the `dir` attribute alone —
 * no rtl.css, no per-language overrides, no `[dir="rtl"] .thing { … }` patches.
 *
 * That only holds if every directional utility is a LOGICAL one: ms-/me-,
 * ps-/pe-, start-/end-, text-start/text-end, border-s/border-e. A single ml-4
 * looks harmless in English and quietly puts the gap on the wrong side of every
 * Arabic page.
 *
 * Nothing else catches this. Pint formats, PHPStan types, neither reads Blade
 * classes — and a human reviewer sees `ml-4` and thinks nothing of it. So it is
 * a test, and it names the replacement for each offender rather than just
 * refusing.
 */

/**
 * @return array<string, string> forbidden utility => logical replacement
 */
function directionalReplacements(): array
{
    return [
        'ml' => 'ms-* (margin-inline-start)',
        'mr' => 'me-* (margin-inline-end)',
        'pl' => 'ps-* (padding-inline-start)',
        'pr' => 'pe-* (padding-inline-end)',
        'left' => 'start-* (inset-inline-start)',
        'right' => 'end-* (inset-inline-end)',
        'text-left' => 'text-start',
        'text-right' => 'text-end',
        'border-l' => 'border-s',
        'border-r' => 'border-e',
        'rounded-l' => 'rounded-s',
        'rounded-r' => 'rounded-e',
        'float-left' => 'float-start',
        'float-right' => 'float-end',
    ];
}

it('uses only logical direction utilities in blade templates', function () {
    $forbidden = directionalReplacements();

    // Longest first so `text-left` is matched before the bare `left`.
    $alternatives = collect(array_keys($forbidden))
        ->sortByDesc(fn (string $class): int => strlen($class))
        ->map(fn (string $class): string => preg_quote($class, '/'))
        ->implode('|');

    /*
     * Matches a whole class token only:
     *   - may carry variant prefixes (sm:, hover:, group-hover:, dark:)
     *   - may be negative (-ml-8)
     *   - may take a value (ml-4, ml-[3px], left-1/2) or stand alone (text-left)
     *
     * The trailing (?![\w-]) is what keeps `rounded-lg` from matching
     * `rounded-l` and `border-line` from matching `border-l`.
     */
    $pattern = '/(?<![\w-])-?(?:[a-z][\w.-]*:)*('.$alternatives.')(?:-\[[^\]]+\]|-[\w.\/%-]+)?(?![\w-])/';

    $violations = [];

    foreach (Finder::create()->files()->in(resource_path('views'))->name('*.blade.php') as $file) {
        foreach (preg_split('/\R/', $file->getContents()) ?: [] as $number => $line) {
            // Only look inside class attributes and @class([...]) arrays;
            // prose in a comment may legitimately say "left".
            if (! preg_match('/class[=\s(\[]|@class/', $line)) {
                continue;
            }

            if (preg_match_all($pattern, $line, $matches, PREG_SET_ORDER) === 0) {
                continue;
            }

            foreach ($matches as $match) {
                $violations[] = sprintf(
                    '%s:%d  %s  → use %s',
                    str_replace(base_path().'/', '', $file->getRealPath()),
                    $number + 1,
                    trim($match[0]),
                    $forbidden[$match[1]],
                );
            }
        }
    }

    expect($violations)->toBeEmpty(
        "Physical direction utilities found in Blade templates.\n\n"
        ."These do not flip with dir=\"rtl\", so the Arabic layout breaks while the\n"
        ."English one looks fine — the worst kind of bug to catch by eye.\n\n"
        .implode("\n", $violations)."\n"
    );
});

it('detects a physical utility when one is introduced', function () {
    // Guards the guard: a regex that silently stops matching would leave the
    // real test passing forever while catching nothing.
    $alternatives = collect(array_keys(directionalReplacements()))
        ->sortByDesc(fn (string $class): int => strlen($class))
        ->map(fn (string $class): string => preg_quote($class, '/'))
        ->implode('|');

    $pattern = '/(?<![\w-])-?(?:[a-z][\w.-]*:)*('.$alternatives.')(?:-\[[^\]]+\]|-[\w.\/%-]+)?(?![\w-])/';

    $shouldMatch = [
        'class="ml-4"',
        'class="-ml-8"',
        'class="md:mr-2"',
        'class="text-left"',
        'class="left-0"',
        'class="hover:pl-2"',
        'class="border-l-2"',
        'class="rounded-l-md"',
        'class="ml-[3px]"',
    ];

    $shouldNotMatch = [
        'class="ms-4"',
        'class="me-2"',
        'class="text-start"',
        'class="start-0"',
        'class="rounded-lg"',      // not rounded-l
        'class="border-line"',     // not border-l
        'class="grid-cols-4"',
        'class="prose"',
        'class="copyright"',
        'class="normal-case"',
    ];

    foreach ($shouldMatch as $sample) {
        expect(preg_match($pattern, $sample))->toBe(1, "expected to flag: {$sample}");
    }

    foreach ($shouldNotMatch as $sample) {
        expect(preg_match($pattern, $sample))->toBe(0, "false positive on: {$sample}");
    }
});
