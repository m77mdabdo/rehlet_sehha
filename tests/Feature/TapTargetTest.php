<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/**
 * The exemption list, and the rule that keeps it honest.
 *
 * The SIZES are measured in a real browser — a Blade file cannot tell you that
 * a link renders 32x36 in Arabic and 41x36 in English, and this project has no
 * headless-browser test runner. The sweep and its numbers are in the Task 13
 * report; TapTargetSweepTest walks the routes and asserts the markup carries the
 * utility that produces the size.
 *
 * What THIS file pins is the thing a browser cannot check: that every exemption
 * is still needed, and still explained.
 */
it('gives every tap-target exemption a written justification', function () {
    $exempt = config('tap-targets.exempt');

    expect($exempt)->not->toBeEmpty();

    foreach ($exempt as $entry) {
        expect(trim((string) $entry['selector']))->not->toBeEmpty();

        /*
         * Long enough to be an argument rather than a label. "inline link" is
         * not a justification; the reason the alternative is worse is.
         */
        expect(strlen((string) $entry['why']))->toBeGreaterThan(
            220,
            "The exemption for «{$entry['selector']}» needs a real justification, not a note. "
            .'44x44 is the default; say why this one cannot be.'
        );
    }
});

it('keeps the minimum at 44 and does not quietly lower it', function () {
    /*
     * The cheapest way to make a failing tap-target test pass is to change this
     * number. It is 44 because WCAG 2.5.8 says 44, not because 44 suited us.
     */
    expect(config('tap-targets.minimum'))->toBe(44);
});

it('names only exemptions that still exist in the markup', function () {
    /*
     * An exemption for something that is gone is stale bookkeeping and worse
     * than useless: it pre-authorises the next person to reintroduce the same
     * shape for a completely different reason. Same argument as the
     * directional-utility exemptions.
     */
    $views = collect(File::allFiles(resource_path('views')))
        ->filter(fn ($f) => str_ends_with($f->getFilename(), '.blade.php'))
        ->map(fn ($f) => File::get($f->getPathname()))
        ->implode("\n");

    // A marker from each selector that must still appear somewhere in the views.
    $markers = [
        'article a.font-medium.text-accent-dark' => 'text-accent-dark',
        'label input[type="radio"], label input[type="checkbox"]' => 'type="checkbox"',
    ];

    foreach (config('tap-targets.exempt') as $entry) {
        $marker = $markers[$entry['selector']] ?? null;

        expect($marker)->not->toBeNull(
            "The exemption «{$entry['selector']}» has no marker here. Add one so this test can tell whether it is still real."
        );

        expect(str_contains($views, $marker))->toBeTrue(
            "The exemption «{$entry['selector']}» no longer matches anything. Delete it rather than leaving it to cover something else."
        );
    }
});
