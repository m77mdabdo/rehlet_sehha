<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/**
 * The photography rules, enforced rather than remembered.
 *
 * Two rules, both of which cost something real if broken.
 *
 * NO IDENTIFIABLE FACE BESIDE CONDITION-SPECIFIC CONTENT. Every photograph on
 * this site is licensed stock, and a stock licence permits commercial use
 * without guaranteeing a model release. A recognisable person next to diabetes
 * or hypertension content is a statement that this person has that condition —
 * the exact "sensitive use" a release covers, and we can evidence none. The
 * person is findable by reverse image search; they did not agree to this.
 *
 * NO SCALES, TAPE MEASURES OR BODIES BEING APPRAISED. The same rule that
 * trimmed the weighing scale and the measuring tape out of the hero video.
 * Seven of the ten rejected images were that same photograph taken different
 * ways, because that is what stock libraries return for "nutrition" — which is
 * exactly the thing this clinic positions against.
 *
 * See docs/media/photography.md.
 */
it('marks no library image as containing an identifiable face', function () {
    $offenders = [];

    foreach (config('photos.library') as $slug => $entry) {
        if (($entry['faces'] ?? false) === true) {
            $offenders[] = $slug;
        }
    }

    expect($offenders)->toBeEmpty(
        "These images are marked as containing an identifiable face:\n  "
        .implode("\n  ", $offenders)
        ."\n\nCrop the face out or drop the image. A stock licence does not\n"
        ."evidence a release, and placing a recognisable person beside\n"
        .'condition content asserts that they have the condition.'
    );
});

it('never uses a rejected image', function () {
    $rejected = config('photos.rejected');

    expect($rejected)->not->toBeEmpty();

    foreach (config('photos.library') as $slug => $entry) {
        expect(array_key_exists($entry['source'], $rejected))->toBeFalse(
            "«{$slug}» uses {$entry['source']}, which is on the rejected list:\n  "
            .($rejected[$entry['source']] ?? '')
        );
    }
});

it('gives every rejection a written reason', function () {
    /*
     * A rejection list without reasons is a list somebody overrules. Each entry
     * has to say what is wrong with the image, so the next person deciding
     * whether to re-add it is reading an argument rather than a filename.
     */
    foreach (config('photos.rejected') as $file => $reason) {
        expect(strlen($reason))->toBeGreaterThan(
            40,
            "The rejection of {$file} needs a real reason, not a note."
        );
    }
});

it('describes every image factually, for whoever writes the alt text', function () {
    foreach (config('photos.library') as $slug => $entry) {
        expect($entry)->toHaveKey('describes');
        expect(strlen($entry['describes']))->toBeGreaterThan(
            30,
            "«{$slug}» has no usable description. A blind patient gets the same "
            .'information as a sighted one, and that starts here.'
        );

        expect($entry)->toHaveKey('topic');
    }
});

it('has processed every library image into the served set', function () {
    /*
     * public/photos is gitignored, so a checkout has the manifest but not the
     * originals. This is what proves the SERVED set is complete and committed —
     * if it is not, the site 404s images on a machine that never had the
     * originals, which is every machine except the one that processed them.
     */
    $directory = public_path((string) config('photos.output_directory'));
    $missing = [];

    foreach (config('photos.library') as $slug => $entry) {
        foreach (array_keys((array) config('photos.variants')) as $variant) {
            $path = $directory."/{$slug}-{$variant}.webp";

            if (! File::exists($path)) {
                $missing[] = "{$slug}-{$variant}.webp";
            }
        }
    }

    expect($missing)->toBeEmpty(
        "Missing from public/media:\n  ".implode("\n  ", $missing)
        ."\n\nRun `php artisan clinic:process-photos` and commit the result."
    );
});

it('keeps every processed file inside its weight budget', function () {
    /*
     * The budget is what makes the per-page weight ceiling hold by
     * construction. Checked against the files on disk rather than trusting the
     * command that wrote them, because the failure mode is somebody replacing
     * one by hand.
     */
    $directory = public_path((string) config('photos.output_directory'));
    $over = [];

    foreach (config('photos.library') as $slug => $entry) {
        foreach ((array) config('photos.variants') as $variant => $budget) {
            $path = $directory."/{$slug}-{$variant}.webp";

            if (! File::exists($path)) {
                continue;
            }

            $bytes = File::size($path);

            if ($bytes > $budget['max_bytes']) {
                $over[] = sprintf(
                    '%s-%s.webp is %.0fK, over its %.0fK budget',
                    $slug, $variant, $bytes / 1024, $budget['max_bytes'] / 1024,
                );
            }

            // And within its pixel budget, which is what the byte budget is for.
            [$width, $height] = (array) getimagesize($path);

            if ((int) $width * (int) $height > $budget['max_pixels'] * 1.02) {
                $over[] = sprintf('%s-%s.webp is %dx%d, over its pixel budget', $slug, $variant, $width, $height);
            }
        }
    }

    expect($over)->toBeEmpty("Over budget:\n  ".implode("\n  ", $over));
});

it('does not commit the originals', function () {
    /*
     * 48 MB of licensed stock we are not the only holder of. It does not belong
     * in this repository's history, and a deploy must not depend on a directory
     * nobody has.
     */
    $gitignore = File::get(base_path('.gitignore'));

    expect($gitignore)->toContain('/public/photos');

    // And the docs have to say where they actually are, or the ignore rule
    // turns into "nobody knows where the originals went".
    expect(File::exists(base_path('docs/media/photography.md')))->toBeTrue();
    expect(File::get(base_path('docs/media/photography.md')))->toContain('originals');
});
