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

/**
 * Physical utilities that are physical ON PURPOSE, each with the reason.
 *
 * An exemption list rather than a quiet carve-out in the matcher: leaving a
 * usage out of the scan would look identical to never having thought about it.
 * Every entry has to name a file, the exact class token, and a written
 * justification, and the test below fails if the usage has since disappeared —
 * so a stale exemption cannot sit here pre-authorising a future mistake.
 *
 * The bar for adding one is high. "It looked wrong mirrored" is not a reason;
 * the whole point of logical properties is that mirrored is correct. A real
 * reason is a case where the thing being positioned does not belong to the
 * text at all.
 *
 * @return list<array{0: string, 1: string, 2: string}>
 */
function exemptDirectionalUsages(): array
{
    return [
        [
            'components/sections/hero.blade.php', 'lg:ml-auto',
            'The hero copy panel is pinned to the physical RIGHT in both '
            .'locales, because it is positioned against the FOOTAGE rather than '
            .'against the text. The strongest frame in the clip is a top-down '
            .'plate sitting left of centre, and the panel has to be opposite it. '
            .'Mirroring the panel with the reading direction would drop it '
            .'straight on top of the plate in English, and both ways out are '
            .'worse: mirroring the footage is forbidden, and shifting the frame '
            .'far enough to clear the panel needs roughly a 40% upscale of a '
            .'1280-wide source, which softens the sharpest image on the page. '
            .'The text alignment inside the panel is still fully logical, so '
            .'Arabic reads right and English reads left within the same shape.',
        ],
        [
            'components/sections/hero.blade.php', 'lg:left-[24%]',
            'The hero case card straddles the panel, so it has to use the same '
            .'coordinate system the panel does — see the exemption above. If '
            .'this were logical while the panel stayed physical, the two would '
            .'mirror independently and the overlap that makes them read as one '
            .'object would land on opposite corners in the two languages. It is '
            .'physical for consistency with what it overlaps, not for taste.',
        ],
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
        $relative = str_replace(resource_path('views').'/', '', $file->getRealPath());

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
                if (isExemptDirectionalUsage($relative, trim($match[0]))) {
                    continue;
                }

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

function isExemptDirectionalUsage(string $relativePath, string $token): bool
{
    foreach (exemptDirectionalUsages() as [$file, $class, $reason]) {
        if ($file === $relativePath && $class === $token) {
            return true;
        }
    }

    return false;
}

it('gives a written reason for every physical utility it allows', function () {
    foreach (exemptDirectionalUsages() as [$file, $class, $reason]) {
        $path = resource_path('views/'.$file);

        expect(file_exists($path))->toBeTrue("The exemption names {$file}, which no longer exists.");

        /*
         * An exemption for a usage that is gone is stale bookkeeping, and worse
         * than useless: it silently pre-authorises the next person to
         * reintroduce the same class for a completely different reason.
         */
        expect(str_contains(file_get_contents($path), $class))->toBeTrue(
            "{$file} no longer uses «{$class}». Delete the exemption rather than leaving it to cover something else."
        );

        expect(strlen($reason))->toBeGreaterThan(
            160,
            "The exemption for «{$class}» in {$file} needs a real justification, not a note. "
            .'Mirrored is the correct default; say why this one is not.'
        );
    }
});
