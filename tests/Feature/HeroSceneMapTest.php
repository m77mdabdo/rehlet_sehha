<?php

declare(strict_types=1);

/**
 * THE SCENE MAP HAS TO KEEP DESCRIBING THE FILE IT DESCRIBES.
 *
 * config/hero.php says where the cuts are, and the hero swaps a line of copy on
 * each one. Nothing in the browser can tell that the timings are wrong — the
 * copy will change confidently at 5.5 seconds whether or not the picture cuts
 * there — so a re-encode that trims half a second, or a beat reordered by hand,
 * would desynchronise the whole thing silently and look like a design choice.
 *
 * These assertions are what makes that loud instead.
 */
beforeEach(function () {
    $this->hero = config('hero');
});

it('gives every beat long enough to be read', function () {
    /*
     * The floor exists because a line that appears and leaves inside five
     * seconds cannot be read by somebody who is also looking at the picture.
     * A hero that flashes sentences is worse than a hero with one sentence.
     */
    $floor = (float) $this->hero['minimum_beat'];

    expect($this->hero['beats'])->toHaveCount(4);

    foreach ($this->hero['beats'] as $beat) {
        $seconds = $beat['end'] - $beat['start'];

        expect($seconds)->toBeGreaterThanOrEqual(
            $floor,
            "The «{$beat['key']}» beat is {$seconds}s. Nothing under {$floor}s can carry a line of copy."
        );
    }
});

it('covers the clip with no gap and no overlap', function () {
    /*
     * A gap is a moment with no line on screen; an overlap is two lines
     * fighting. Both are invisible in a single viewing and obvious over a loop.
     */
    $cursor = 0.0;

    foreach ($this->hero['beats'] as $beat) {
        expect($beat['start'])->toBe($cursor, "The «{$beat['key']}» beat does not start where the last one ended.");
        $cursor = $beat['end'];
    }

    expect($cursor)->toBe($this->hero['duration'], 'The beats do not reach the end of the clip.');
});

it('points at files that exist and at the size it claims', function () {
    foreach (['video', 'poster', 'poster_webp'] as $key) {
        expect(file_exists(public_path($this->hero[$key])))
            ->toBeTrue("config/hero.php references {$this->hero[$key]}, which is not there.");
    }

    /*
     * The byte count is recorded so that a re-encode which quietly doubles the
     * file is caught here rather than in somebody's data allowance. If this
     * fails after a deliberate re-encode, update the number and say why.
     */
    expect(filesize(public_path($this->hero['video'])))->toBe($this->hero['bytes']);
});

it('still matches the actual duration of the file', function () {
    /*
     * The assertion that actually protects the sync. Everything else checks
     * that the map is internally consistent; this checks it against the clip.
     */
    if (trim((string) shell_exec('command -v ffprobe')) === '') {
        $this->markTestSkipped('ffprobe is not installed here.');
    }

    $seconds = (float) shell_exec(sprintf(
        'ffprobe -v error -show_entries format=duration -of csv=p=0 %s',
        escapeshellarg(public_path($this->hero['video'])),
    ));

    expect(abs($seconds - $this->hero['duration']))->toBeLessThan(
        0.15,
        "config/hero.php says {$this->hero['duration']}s; the file is {$seconds}s."
    );
});

it('keeps the clip it is not serving, and keeps its credits', function () {
    /*
     * The kitchen cut lost the hero on SUBJECT — a wall of chopping and frying
     * reads as a cooking site — not on craft. It is staged for a content page,
     * and its four photographers are credited nowhere else in this repository.
     *
     * This exists because an unreferenced file is the easiest thing in the
     * world to delete during a tidy-up, and deleting it would silently drop a
     * licence obligation along with it.
     */
    $alternate = config('hero.alternate');

    foreach (['video', 'poster', 'poster_webp'] as $key) {
        expect(file_exists(public_path($alternate[$key])))
            ->toBeTrue("The alternate clip references {$alternate[$key]}, which is not there.");
    }

    expect(filesize(public_path($alternate['video'])))->toBe($alternate['bytes']);

    expect(array_keys($alternate['beats']))->toBe(array_column($alternate['attribution'], 'beat'));

    foreach ($alternate['attribution'] as $credit) {
        expect(trim((string) $credit['photographer']))->not->toBeEmpty();
        expect(trim((string) $credit['source']))->not->toBeEmpty();
    }
});

it('credits every beat', function () {
    /*
     * A licence obligation attached to a file we serve. Four beats, four
     * photographers, and none of them can be dropped when a beat is swapped.
     */
    $credited = array_column($this->hero['attribution'], 'beat');

    foreach ($this->hero['beats'] as $beat) {
        expect($beat['key'])->toBeIn($credited, "The «{$beat['key']}» beat has no attribution.");
    }

    foreach ($this->hero['attribution'] as $credit) {
        foreach (['photographer', 'source', 'downloaded_at'] as $field) {
            expect(trim((string) $credit[$field]))->not->toBeEmpty(
                "Attribution for «{$credit['beat']}» has no {$field}."
            );
        }
    }
});
