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
    'video' => 'brand/hero-consultation.mp4',
    'poster' => 'brand/hero-consultation-poster.jpg',
    'poster_webp' => 'brand/hero-consultation-poster-1280.webp',

    'duration' => 22.0,

    /*
     * The shortest a beat may be, in seconds. Read by the test, not by taste.
     */
    'minimum_beat' => 5.0,

    /*
     * The one line that carries the meaning when there is no video.
     *
     * `plan` and not `talk`, though `talk` is the beat the poster comes from.
     * The poster ALREADY SHOWS the consultation; captioning a picture of two
     * people at a desk with a sentence about the session beginning says one
     * thing twice. This line says the thing the picture cannot.
     *
     * It is also the only one of the four that is a complete thought rather
     * than a link in a chain, and it is the longest, which is why it sits on
     * the darkest beat. It survived the change of footage unaltered — it was
     * never about a kitchen, it is about the pace of change.
     */
    'static_beat' => 'plan',

    /*
     * The beats, in order. `key` is the translation key under home.hero.beats,
     * so the copy and the timings cannot drift apart without one of them
     * failing to resolve.
     */
    'beats' => [
        [
            'key' => 'talk',
            'start' => 0.0,
            'end' => 5.5,
            'shows' => 'Two people across a desk, faces out of frame. Hands, a laptop, a glass of water, a plant, a blank sheet being turned.',
            'luma' => [135, 147],
        ],
        [
            'key' => 'write',
            'start' => 5.5,
            'end' => 11.0,
            'shows' => 'Hands writing in a notebook on a warm wooden desk.',
            'luma' => [113, 121],
        ],
        [
            'key' => 'notes',
            'start' => 11.0,
            'end' => 16.5,
            'shows' => 'A hand writing on a spiral pad, checked sleeve, soft daylight.',
            'luma' => [107, 109],
        ],
        [
            'key' => 'plan',
            'start' => 16.5,
            'end' => 22.0,
            'shows' => 'A hand writing in a notebook beside a closed laptop, grey desk, low key.',
            'luma' => [78, 80],
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
            'beat' => 'talk',
            'pexels_id' => 7735502,
            'photographer' => 'Mikhail Nilov',
            'photographer_url' => 'https://www.pexels.com/@mikhail-nilov',
            'source' => 'https://www.pexels.com/video/discussing-details-of-a-contract-7735502/',
            'downloaded_at' => '2026-09-02',
        ],
        [
            'beat' => 'write',
            'pexels_id' => 6326847,
            'photographer' => 'kaboompics.com',
            'photographer_url' => 'https://kaboompics.com/',
            'source' => 'https://www.pexels.com/video/person-writing-on-a-notebook-using-a-pen-6326847/',
            'downloaded_at' => '2026-09-02',
        ],
        [
            'beat' => 'notes',
            'pexels_id' => 5330654,
            'photographer' => 'Tima Miroshnichenko',
            'photographer_url' => 'https://www.pexels.com/@tima-miroshnichenko',
            'source' => 'https://www.pexels.com/video/therapist-taking-notes-during-session-5330654/',
            'downloaded_at' => '2026-09-02',
        ],
        [
            'beat' => 'plan',
            'pexels_id' => 5212605,
            'photographer' => 'Tima Miroshnichenko',
            'photographer_url' => 'https://www.pexels.com/@tima-miroshnichenko',
            'source' => 'https://www.pexels.com/video/man-writing-on-his-notebook-5212605/',
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
    'bytes' => 1042087,

    /*
     * THE KITCHEN CLIP, KEPT AND NOT SERVED BY THE HERO.
     *
     * hero-prep.mp4 was built first and is a better SEQUENCE than the one
     * above — four unmistakably different scenes rather than one strong shot
     * and three near-identical ones. It lost the hero on subject, not on
     * craft: a wall of chopping and frying reads as a cooking site, and this
     * is a clinic. That was the more serious error of the two.
     *
     * It stays here because it is staged for a content page and because its
     * attribution is a licence obligation attached to a file in this
     * repository. An entry nobody reads is still the only place four
     * photographers are credited.
     */
    'alternate' => [
        'name' => 'kitchen',
        'video' => 'brand/hero-prep.mp4',
        'poster' => 'brand/hero-prep-poster.jpg',
        'poster_webp' => 'brand/hero-prep-poster-1280.webp',
        'duration' => 22.0,
        'bytes' => 1558502,
        'beats' => ['wash' => [0.0, 5.5], 'chop' => [5.5, 11.0], 'cook' => [11.0, 16.5], 'plate' => [16.5, 22.0]],
        'attribution' => [
            ['beat' => 'wash', 'pexels_id' => 7204589, 'photographer' => 'ArtHouse Studio',
                'source' => 'https://www.pexels.com/video/washing-lettuces-with-tap-water-7204589/'],
            ['beat' => 'chop', 'pexels_id' => 34799727, 'photographer' => 'Florian Delée',
                'source' => 'https://www.pexels.com/video/enhancing-knife-skills-in-home-cooking-video-34799727/'],
            ['beat' => 'cook', 'pexels_id' => 4912636, 'photographer' => 'RDNE Stock project',
                'source' => 'https://www.pexels.com/video/person-pouring-an-olive-oil-in-the-frying-pan-4912636/'],
            ['beat' => 'plate', 'pexels_id' => 9020879, 'photographer' => 'T Leish',
                'source' => 'https://www.pexels.com/video/person-slicing-tomatoes-9020879/'],
        ],
        'downloaded_at' => '2026-09-02',
    ],
];
