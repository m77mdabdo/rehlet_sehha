<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The hero video, and the beats the copy is synced to
|------------------------------------------------------------------------------
|
| THIS FILE IS THE CONTRACT BETWEEN THE FOOTAGE AND THE WORDS. The clip is four
| separate shots, hard-cut, and the copy changes ON the cut rather than on a
| timer that happens to be running. That only works while these timings are the
| real timings, so they are recorded here rather than duplicated into a script.
|
| WHY THE CLIP IS ASSEMBLED AND NOT FOUND. Forty-six candidates were pulled from
| Pexels across ten searches and measured with clinic:fetch-pexels-videos. Every
| single one was a single continuous shot; not one had a second scene. That is
| not a search-term problem, it is what a stock library sells — single takes,
| with multi-scene sequences packaged as separate clips. So the four beats below
| are four separately vetted clips, cut together at lengths we chose.
|
| THE FIVE-SECOND FLOOR IS NOT DECORATION. A line of copy that appears and
| leaves inside five seconds cannot be read by somebody who is also looking at
| the picture, and a hero that flashes sentences at a reader is worse than a
| hero with one sentence. HeroSceneMapTest asserts every beat clears it; if a
| future edit shortens a beat, that test is the thing that says no.
|
| LUMINANCE IS RECORDED BECAUSE TYPE SITS ON THIS. Measured on the delivered
| encode, 0..255, BEFORE the section's own bg-ink/[0.38] overlay. Beat 3 is the
| darkest by a distance, which is why it carries the longest line.
*/

return [

    /*
     * The clip, its poster, and the poster's WebP twin.
     *
     * The poster is frame 0 — which is the first frame of beat 1, not a still
     * chosen separately — because it is what a reduced-motion, Save-Data or
     * slow-connection visitor sees INSTEAD of the video, and a poster that
     * shows a moment the clip never opens on is a small lie.
     */
    'video' => 'brand/hero-prep.mp4',
    'poster' => 'brand/hero-prep-poster.jpg',
    'poster_webp' => 'brand/hero-prep-poster-1280.webp',

    'duration' => 22.0,

    /*
     * The shortest a beat may be, in seconds. Read by the test, not by taste.
     */
    'minimum_beat' => 5.0,

    /*
     * The beats, in order. `key` is the translation key under home.hero.beats,
     * so the copy and the timings cannot drift apart without one of them
     * failing to resolve.
     */
    'beats' => [
        [
            'key' => 'wash',
            'start' => 0.0,
            'end' => 5.5,
            'shows' => 'Two hands turning a whole lettuce under a running tap.',
            'luma' => [103, 111],
        ],
        [
            'key' => 'chop',
            'start' => 5.5,
            'end' => 11.0,
            'shows' => 'Top-down on a dark board: diced pepper, onion, garlic under a knife.',
            'luma' => [99, 100],
        ],
        [
            'key' => 'cook',
            'start' => 11.0,
            'end' => 16.5,
            'shows' => 'Oil poured into a pan on a gas hob, swirling.',
            'luma' => [39, 65],
        ],
        [
            'key' => 'plate',
            'start' => 16.5,
            'end' => 22.0,
            'shows' => 'Avocado on seeded toast, a tomato sliced beside it.',
            'luma' => [73, 76],
        ],
    ],

    /*
     * Pexels attribution, per beat.
     *
     * Kept in the application rather than in a notes file because it is a
     * licence obligation attached to a file we serve, and a notes file is the
     * thing that gets lost in a repository move.
     */
    'attribution' => [
        [
            'beat' => 'wash',
            'pexels_id' => 7204589,
            'photographer' => 'ArtHouse Studio',
            'photographer_url' => 'https://www.pexels.com/@arthousestudio',
            'source' => 'https://www.pexels.com/video/washing-lettuces-with-tap-water-7204589/',
            'downloaded_at' => '2026-09-02',
        ],
        [
            'beat' => 'chop',
            'pexels_id' => 34799727,
            'photographer' => 'Florian Delée',
            'photographer_url' => 'https://www.pexels.com/@florian-delee-209542985',
            'source' => 'https://www.pexels.com/video/enhancing-knife-skills-in-home-cooking-video-34799727/',
            'downloaded_at' => '2026-09-02',
        ],
        [
            'beat' => 'cook',
            'pexels_id' => 4912636,
            'photographer' => 'RDNE Stock project',
            'photographer_url' => 'https://www.pexels.com/@rdne',
            'source' => 'https://www.pexels.com/video/person-pouring-an-olive-oil-in-the-frying-pan-4912636/',
            'downloaded_at' => '2026-09-02',
        ],
        [
            'beat' => 'plate',
            'pexels_id' => 9020879,
            'photographer' => 'T Leish',
            'photographer_url' => 'https://www.pexels.com/@leish',
            'source' => 'https://www.pexels.com/video/person-slicing-tomatoes-9020879/',
            'downloaded_at' => '2026-09-02',
        ],
    ],

    /*
     * The encode, recorded so a re-encode reproduces it rather than guessing.
     *
     * CRF 31 with a light denoise rather than CRF 30 clean: the denoise is
     * taking stock sensor grain out, not detail, and it is what brings 22
     * seconds under the budget honestly instead of by moving the budget.
     *
     *   ffmpeg -i master.mp4 -an -vf hqdn3d=1.5:1.5:6:6 \
     *          -c:v libx264 -profile:v high -crf 31 -preset slow \
     *          -pix_fmt yuv420p -movflags +faststart -r 24 hero-prep.mp4
     */
    'encode' => 'h264 High · CRF 31 · hqdn3d=1.5:1.5:6:6 · 1280x720 · 24fps · no audio · faststart',
    'bytes' => 1558502,
];
