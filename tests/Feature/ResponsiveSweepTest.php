<?php

declare(strict_types=1);

use App\Models\Post;
use App\Models\Specialty;
use App\Models\User;
use Database\Seeders\CategorySeeder;
use Database\Seeders\FaqSeeder;
use Database\Seeders\PostSeeder;
use Database\Seeders\ServiceSeeder;
use Database\Seeders\SpecialtySeeder;
use Database\Seeders\TagSeeder;
use Database\Seeders\WorkingHoursSeeder;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\File;

/**
 * The responsive regression net.
 *
 * WHAT THIS CAN AND CANNOT DO, stated plainly because the difference matters.
 *
 * It cannot measure. Layout happens in a browser, this project has no
 * headless-browser test runner, and adding one means a toolchain that is not
 * part of this codebase. The real sweep — 60 pages x 14 widths x 2 locales,
 * plus DPR 2 and 3 — was driven over CDP and its numbers are in the Task 13
 * report.
 *
 * What it CAN do is hold the causes in place, and every one of them is a string
 * in the markup:
 *
 *   1. A horizontal scroller must be a CONTAINING BLOCK. The booking day strip
 *      put 806px of horizontal scroll on the whole page at 320 because it was
 *      not: Tailwind's .sr-only is position:absolute, the "closed" span inside
 *      an unavailable day had no positioned ancestor, so its containing block
 *      was the initial containing block and its static position inside 1246px
 *      of scroll content stretched the document. An ancestor's overflow cannot
 *      clip a box whose containing block is outside it, which is why
 *      overflow-x:hidden did nothing and only `relative` fixed it.
 *
 *   2. Interactive elements must carry the tap-target utility, which is what
 *      produces 44x44. Checked against the routes that actually render them.
 *
 * A test that walked routes and asserted `strlen($html) > 0` would pass forever
 * and catch neither.
 */
beforeEach(function () {
    Cache::flush();

    $this->seed(WorkingHoursSeeder::class);
    $this->seed(ServiceSeeder::class);
    $this->seed(SpecialtySeeder::class);
    $this->seed(FaqSeeder::class);
    $this->seed(CategorySeeder::class);
    $this->seed(TagSeeder::class);
    $this->seed(PostSeeder::class);

    /*
     * PostSeeder seeds drafts. One is promoted here so the article route is
     * exercised, satisfying the same gates the site does rather than writing
     * a date and hoping.
     */
    $reviewer = User::factory()->create();

    if ($post = Post::query()->first()) {
        $post->citations()->update(['verified_by' => $reviewer->id, 'verified_at' => now()]);
        $post->forceFill([
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'published_at' => now()->subDay(),
        ])->saveQuietly();
    }
});

/**
 * Every public route, both locales.
 *
 * @return list<string>
 */
function sweepPaths(): array
{
    $paths = ['', '/services', '/packages', '/how-it-works', '/about', '/articles',
        '/faq', '/contact', '/privacy', '/booking'];

    foreach (Specialty::query()->where('is_active', true)->pluck('slug') as $slug) {
        $paths[] = '/specialties/'.$slug;
    }

    /*
     * published() is more than a date — it wants a named reviewer, a review
     * date and no unverified citation. Asking the scope rather than the column
     * is what keeps this list to routes that actually resolve.
     */
    if ($post = Post::query()->published()->first()) {
        $paths[] = '/articles/'.$post->slug;
    }

    return $paths;
}

it('makes every horizontal scroller a containing block', function () {
    /*
     * The 806px bug, pinned. `relative` on a scroller costs nothing and stops
     * any absolutely positioned descendant — an sr-only label today, a tooltip
     * tomorrow — from escaping to the initial containing block and dragging the
     * document width with it.
     */
    $offenders = [];

    foreach (File::allFiles(resource_path('views')) as $file) {
        if (! str_ends_with($file->getFilename(), '.blade.php')) {
            continue;
        }

        foreach (explode("\n", File::get($file->getPathname())) as $number => $line) {
            if (! str_contains($line, 'overflow-x-auto') && ! str_contains($line, 'overflow-x-scroll')) {
                continue;
            }

            /*
             * Only where the class is actually APPLIED. This file's own comment
             * explaining the bug names the utility, and the first version of
             * this test failed on it — which was a good sign that it reads the
             * markup rather than a list of files, and a bad test.
             */
            if (! str_contains($line, 'class=')) {
                continue;
            }

            // The class attribute the scroller is declared in must also position it.
            if (! preg_match('/\brelative\b/', $line)) {
                $offenders[] = $file->getFilename().':'.($number + 1);
            }
        }
    }

    expect($offenders)->toBeEmpty(
        "A horizontal scroller is not a containing block:\n  ".implode("\n  ", $offenders)
        ."\n\nAdd `relative`. Without it an absolutely positioned descendant — Tailwind's "
        .'.sr-only is one — resolves against the initial containing block and puts horizontal scroll on the page.'
    );
});

it('serves every public route in both locales', function (string $locale) {
    foreach (sweepPaths() as $path) {
        $this->get("/{$locale}{$path}")->assertOk();
    }
})->with(['ar', 'en']);

it('keeps the tap-target utility on every control that was measured under 44px', function () {
    /*
     * NAMED CONTROLS, NOT A COUNT. The first version of this test counted
     * occurrences of `tap-target` per page and required at least two — which
     * passed happily when the locale switcher was shrunk back to 32x36, because
     * a dozen other controls on the page still had the utility. A regression
     * test that cannot fail on the exact regression it was written for is
     * decoration.
     *
     * Each entry is a file that renders a control the Task 13 sweep measured
     * under 44x44 at 320, with the size it was. Removing the utility from any
     * one of them now fails here and names it.
     */
    $controls = [
        'partials/language-switcher.blade.php' => 'the locale switcher, 32x36 on 46 pages',
        'components/page-header.blade.php' => 'breadcrumb links, 35x20 on 36 pages',
        'components/layouts/app.blade.php' => 'footer service links, 64x14 on 16 pages',
        'components/article-grid.blade.php' => 'article card category link, 238x16',
        'components/feature-card.blade.php' => 'specialty card heading link, 84x23',
        'components/contact-details.blade.php' => 'phone, WhatsApp and email, 99x22',
        'pages/articles.blade.php' => 'the filter pills, 112x36',
        'pages/post.blade.php' => 'tag chips and the share row, 68x32',
        'components/sections/plate.blade.php' => 'plate food buttons and reset, 89x36',
        'vendor/pagination/tailwind.blade.php' => "Laravel's pagination links, 65x38 on 9 pages",
    ];

    foreach ($controls as $file => $what) {
        $path = resource_path('views/'.$file);

        expect(File::exists($path))->toBeTrue("{$file} is gone; this list names it for {$what}.");

        expect(str_contains(File::get($path), 'tap-target'))->toBeTrue(
            "{$file} lost the tap-target utility — {$what}. "
            .'It was measured under 44x44 at 320px in the Task 13 sweep. '
            .'If it genuinely no longer needs it, add it to config/tap-targets.php with a reason.'
        );
    }
});

it('renders those controls with the utility actually in the html', function (string $locale) {
    /*
     * The file check above proves the source says it. This proves the browser
     * receives it — a control moved behind a condition that never fires would
     * pass the first and fail this.
     */
    foreach (['', '/articles', '/contact'] as $path) {
        $html = $this->get("/{$locale}{$path}")->assertOk()->getContent();

        expect(str_contains($html, 'tap-target'))->toBeTrue(
            "/{$locale}{$path} renders no tap-target control at all."
        );
    }
})->with(['ar', 'en']);
