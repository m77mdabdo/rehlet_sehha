<?php

declare(strict_types=1);

use Illuminate\Support\Facades\File;

/*
|------------------------------------------------------------------------------
| The compiled CSS and JS must be in the repository
|------------------------------------------------------------------------------
|
| There is no Node on the Hostinger plan — the runbook says not to run
| `composer setup` for exactly that reason — so the Vite build cannot run
| during deployment. The committed build is the only copy that will ever exist
| on the server.
|
| Laravel's stock .gitignore ignores /public/build, and this project un-ignored
| it on 2026-10-12 after the following happened:
|
|   1. The build was force-added once, in 68fb233. That worked.
|   2. A later build produced new hashed filenames.
|   3. `git add -A` skipped them silently, because they matched the ignore rule.
|   4. The same commit DELETED the previously tracked files, which were no
|      longer on disk under those names.
|   5. manifest.json — which is tracked — was updated to point at the new names.
|   6. Production reset to that commit and served a homepage that referenced a
|      stylesheet and a script which were not there. No CSS, no JavaScript, and
|      a 200 on every page, so nothing alerted.
|
| This test is the tripwire for step 3. It reads the manifest the way the
| framework does and checks the files it names are actually present AND
| tracked by git — because present-on-disk is what fooled everybody the first
| time: the developer machine always has them.
*/

it('has every asset the manifest names present on disk', function () {
    $manifestPath = public_path('build/manifest.json');

    expect(File::exists($manifestPath))->toBeTrue('There is no build manifest. Run `npm run build`.');

    $manifest = json_decode(File::get($manifestPath), true);

    expect($manifest)->toBeArray()->not->toBeEmpty();

    $missing = [];

    foreach ($manifest as $entry) {
        foreach (array_merge([$entry['file'] ?? null], $entry['css'] ?? []) as $file) {
            if ($file === null) {
                continue;
            }

            if (! File::exists(public_path('build/'.$file))) {
                $missing[] = $file;
            }
        }
    }

    expect($missing)->toBe([], "The manifest names files that do not exist:\n  ".implode("\n  ", $missing));
});

it('has every asset the manifest names tracked by git', function () {
    /*
     * The assertion that would have caught it. The files were on disk here and
     * absent from the repository, so every local check passed and the server —
     * which only ever sees the repository — got nothing.
     */
    $manifest = json_decode(File::get(public_path('build/manifest.json')), true);

    $tracked = collect(explode("\n", (string) shell_exec('cd '.escapeshellarg(base_path()).' && git ls-files public/build 2>/dev/null')))
        ->filter()
        ->map(fn (string $path): string => str_replace('public/build/', '', $path))
        ->all();

    expect($tracked)->not->toBeEmpty('Nothing under public/build is tracked. Is it gitignored again?');

    $untracked = [];

    foreach ($manifest as $entry) {
        foreach (array_merge([$entry['file'] ?? null], $entry['css'] ?? []) as $file) {
            if ($file !== null && ! in_array($file, $tracked, true)) {
                $untracked[] = $file;
            }
        }
    }

    expect($untracked)->toBe([], implode("\n", [
        'The manifest names files that git does not track, so they will not reach the server:',
        '  '.implode("\n  ", $untracked),
        '',
        'This is the failure that served a homepage with no CSS and no JavaScript.',
        'Check .gitignore has not re-added /public/build.',
    ]));
});

it('does not ignore a NEW file in the build directory', function () {
    /*
     * The root cause, asserted directly — and asserted against a path that
     * does NOT exist and is NOT tracked, which is the whole point.
     *
     * The obvious version of this test checked an existing file like
     * manifest.json and passed even with /public/build ignored again, because
     * git stops consulting .gitignore once a path is tracked. That is exactly
     * the blind spot that caused the outage: the previously committed assets
     * stayed visible while every NEW build output was silently excluded.
     *
     * A build produces new hashed filenames every time, so the path that
     * matters is always an untracked one.
     */
    $probe = 'public/build/assets/app-ProbeHash123.js';

    $ignored = trim((string) shell_exec(
        'cd '.escapeshellarg(base_path()).' && git check-ignore '.escapeshellarg($probe).' 2>/dev/null'
    ));

    expect($ignored)->toBe('', implode("\n", [
        'A new file under public/build would be gitignored.',
        'Every build produces new hashed filenames, so this means the next build',
        'will not reach the server — which served a homepage with no CSS and no',
        'JavaScript the last time it happened. See the note in .gitignore.',
    ]));
});
