<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| Photography
|------------------------------------------------------------------------------
|
| THE RULE THIS FILE EXISTS TO ENFORCE:
|
|   NO IDENTIFIABLE FACE APPEARS BESIDE CONDITION-SPECIFIC CONTENT.
|
| Not a style preference. Every photograph on this site is licensed stock, and
| a stock licence permits commercial use without guaranteeing a model release.
| Placing a recognisable person next to diabetes or hypertension content states
| that this person has that condition — which is exactly the "sensitive use"
| a release covers, and we can evidence none for any of these images.
|
| The cost of the rule is close to zero: hands, devices, cropped torsos and
| food carry the clinical meaning perfectly well, and the strongest images in
| the library never showed a face to begin with. The cost of breaking it is a
| real person, findable by reverse image search, implied to have a disease.
|
| So `faces` below is false for every entry, and PhotoLibraryTest fails if one
| is ever set true. If a photograph of a real, consenting patient or of the
| practitioner herself is ever added, it is not stock and does not belong in
| this file — it belongs somewhere with its release stored alongside it.
|
| ---------------------------------------------------------------------------
|
| ORIGINALS ARE NOT IN GIT AND NOT SERVED. public/photos is gitignored: 48 MB
| of stock we are not the only holder of does not belong in this repository's
| history. `php artisan clinic:process-photos` turns them into the small WebP
| set under public/media, and that processed set IS committed — it is what the
| site actually serves, it is two orders of magnitude smaller, and a deploy
| must not depend on a directory nobody has.
|
| See docs/media/photography.md for where the originals live.
|
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Where things are
    |--------------------------------------------------------------------------
    */

    // Not served, not committed. The command reads from here.
    'source_directory' => 'photos',

    // Served and committed. The command writes here.
    'output_directory' => 'media',

    /*
    |--------------------------------------------------------------------------
    | Variants, budgeted by PIXELS rather than by width
    |--------------------------------------------------------------------------
    |
    | The first version of this budgeted by width and it was wrong, in a way
    | worth writing down because it is the obvious way to do it.
    |
    | "1400 wide, at most 120 KB" charges a 1400x933 landscape (1.3 megapixels)
    | and a 1400x2489 portrait (3.5 megapixels) the same. Two thirds of this
    | library is portrait, so four food images could not fit their budget at
    | any quality — not because they were badly compressed but because they
    | were being asked to hold three times as many pixels for the same bytes.
    |
    | Budgeting by megapixel fixes both halves of that. The cap says how much
    | IMAGE each variant holds regardless of shape, which is what a browser
    | actually needs for a component that is around 700 CSS pixels on its long
    | edge at 2x; the byte ceiling then scales with it. A tall portrait comes
    | out narrower than a landscape at the same variant, which is correct — it
    | is the same amount of picture.
    |
    | Widths for the srcset are derived per image from its own aspect ratio.
    */
    'variants' => [
        'sm' => ['max_pixels' => 350_000, 'max_bytes' => 45 * 1024],
        'md' => ['max_pixels' => 800_000, 'max_bytes' => 85 * 1024],
        'lg' => ['max_pixels' => 1_600_000, 'max_bytes' => 150 * 1024],
    ],

    'quality' => [
        'start' => 80,
        // Below this the artefacts are visible on skin and on food, which is
        // most of this library. An image that cannot fit its budget at 50 is
        // reported rather than silently shipped ugly.
        'floor' => 50,
        'step' => 4,
    ],

    /*
    |--------------------------------------------------------------------------
    | The library
    |--------------------------------------------------------------------------
    |
    | `crop` is [x, y, width, height] in SOURCE pixels, applied before resizing.
    | It is here rather than done by hand in an image editor so that the reason
    | for every crop is written down and re-running the command reproduces it
    | exactly. Three of the four crops exist to enforce a rule, not to improve
    | a composition.
    |
    | `describes` is a factual note of what is in the frame, for whoever writes
    | the alt text. It is NOT the alt text: that is bilingual copy and lives in
    | the translation files with the section it appears in.
    |
    | `attribution` records where the image came from: photographer, the Pexels
    | page, and the date it was fetched. Pexels asks for no credit; we keep it
    | because a licence you cannot evidence is a licence you do not have, and
    | because "where did this photograph on a medical site come from" should be
    | answerable from a file rather than from memory. Entries added by hand
    | before clinic:fetch-pexels existed have none, and that is recorded by its
    | absence rather than by a guess.
    */
    'library' => [

        // ---- Diabetes ------------------------------------------------------
        'diabetes-lancet-mono' => [
            'source' => 'pexels-artempodrez-6823396.jpg',
            'topic' => 'diabetes',
            'faces' => false,
            'crop' => null,
            'describes' => 'Black and white. Two hands using a lancet device at a table, a glucose meter and test strips beside them.',
        ],
        'diabetes-glucose-meter' => [
            'source' => 'pexels-artempodrez-6823407.jpg',
            'topic' => 'diabetes',
            'faces' => false,
            'crop' => null,
            'describes' => 'Two hands holding a blood glucose meter and inserting a test strip.',
        ],
        'diabetes-finger-prick' => [
            'source' => 'pexels-artempodrez-6823416.jpg',
            'topic' => 'diabetes',
            'faces' => false,
            'crop' => null,
            'describes' => 'A fingertip with a small drop of blood after a lancet prick, a glucose meter out of focus behind.',
        ],
        'diabetes-insulin-pen' => [
            'source' => 'pexels-mikhail-nilov-8669941.jpg',
            'topic' => 'diabetes',
            'faces' => false,
            'crop' => null,
            'describes' => 'A seated woman in jeans holding an injector pen against her thigh. Framed below the shoulders.',
        ],

        // ---- Hypertension --------------------------------------------------
        'blood-pressure-cuff-mono' => [
            'source' => 'pexels-anntarazevich-7904472.jpg',
            'topic' => 'hypertension',
            'faces' => false,
            'crop' => null,
            'describes' => 'Black and white. A clinician in a white coat holding an aneroid blood pressure cuff and its bulb.',
        ],
        'blood-pressure-reading' => [
            'source' => 'pexels-pavel-danilyuk-7108338.jpg',
            'topic' => 'hypertension',
            'faces' => false,
            'crop' => null,
            'describes' => 'A blood pressure reading being taken: a cuff on a patient\'s upper arm, a clinician\'s hands, the gauge resting on the table.',
        ],

        // ---- Pregnancy -----------------------------------------------------
        'pregnancy-baby-shoes' => [
            'source' => 'pexels-radhwan-taha-613797792-17314487.jpg',
            'topic' => 'pregnancy',
            'faces' => false,
            'crop' => null,
            'describes' => 'A pregnant woman in a hijab holding a pair of small blue baby shoes, her other hand resting on her bump. Framed below the face.',
        ],
        'pregnancy-bump' => [
            'source' => 'pexels-tima-miroshnichenko-6463621.jpg',
            'topic' => 'pregnancy',
            'faces' => false,
            // THE RULE. The chin and mouth are in the top of the source frame.
            // Cropped away, not framed around, so re-running cannot restore it.
            'crop' => [0, 830, 3952, 5098],
            'describes' => 'A pregnant woman in a grey linen shirt with both hands under her bump.',
        ],

        // ---- Infant and child nutrition ------------------------------------
        'infant-formula' => [
            'source' => 'pexels-towfiqu-barbhuiya-3440682-11501481.jpg',
            'topic' => 'child-nutrition',
            'faces' => false,
            'crop' => null,
            'describes' => 'A jar of infant formula, a feeding bottle and teat, and a measuring scoop of powder on a pale blue surface.',
        ],
        'infant-feeding-hands' => [
            'source' => 'pexels-sarah-chai-7282318.jpg',
            'topic' => 'child-nutrition',
            'faces' => false,
            // THE RULE. The infant's head is in the upper right of the source.
            'crop' => [600, 1500, 2400, 2200],
            'describes' => 'A baby\'s hand resting on a feeding bottle held by an adult. No faces in frame.',
        ],

        // ---- Food and meal planning ----------------------------------------
        'food-kitchen-still-life' => [
            'source' => 'pexels-arthousestudio-4589141.jpg',
            'topic' => 'food',
            'faces' => false,
            'crop' => null,
            'describes' => 'A kitchen table with olive oil, a tomato, corn, blueberries, pears, courgette and fresh basil.',
        ],
        'food-market-counter' => [
            'source' => 'pexels-collab-media-173741945-15209802.jpg',
            'topic' => 'food',
            'faces' => false,
            'crop' => null,
            'describes' => 'A kitchen counter with leafy greens, peppers, tomatoes, garlic, a plate of raw meat and a bowl of chickpeas.',
        ],
        'food-fruit-bowl' => [
            'source' => 'pexels-dalia-al-refai-235376732-17131199.jpg',
            'topic' => 'food',
            'faces' => false,
            'crop' => null,
            'describes' => 'Two hands holding a bowl of cut strawberries, kiwi, grapes, pineapple and mango.',
        ],
        'food-clinical-flatlay' => [
            'source' => 'pexels-rarnie-mccudden-802464.jpg',
            'topic' => 'food',
            'faces' => false,
            'crop' => null,
            'describes' => 'Beetroot, kiwi and a halved pomegranate laid beside a laptop and a stethoscope on dark slate.',
        ],
        'food-cookbook-overhead' => [
            'source' => 'pexels-yaroslav-shuraev-8845419.jpg',
            'topic' => 'food',
            'faces' => false,
            'crop' => null,
            'describes' => 'Overhead view of an open cookbook on a table surrounded by radishes, tomatoes, avocado, cucumber and oat crackers.',
        ],
        'food-vegetables-overhead' => [
            'source' => 'pexels-yaroslav-shuraev-8845420.jpg',
            'topic' => 'food',
            'faces' => false,
            'crop' => null,
            'describes' => 'Overhead view of lettuce, carrots, radishes, tomatoes, avocado, cucumber and grapes on a pale surface.',
        ],

        // ---- Consultation --------------------------------------------------
        'consultation-meal-plan' => [
            'source' => 'pexels-beyzahzah-89810429-15319019.jpg',
            'topic' => 'clinic',
            'faces' => false,
            // Composition only: the lower third is empty desk. No books in this frame.
            'crop' => [0, 430, 3864, 4100],
            'describes' => 'A clinician in a white coat writing on a weekly meal plan on a clipboard, the patient\'s hands folded opposite, a bowl of fruit on the desk.',
        ],
        'consultation-desk-wide' => [
            'source' => 'pexels-beyzahzah-89810429-15319040.jpg',
            'topic' => 'clinic',
            'faces' => false,
            /*
             * Not composition. The top of this frame has two legible Turkish
             * textbooks on the desk. A visitor who looks closely and reads
             * another country's clinic off our page has learned that the
             * photograph is not us, which is a trust problem on a page whose
             * entire job is trust.
             */
            'crop' => [0, 490, 4729, 3490],
            'describes' => 'A consultation across a white desk: the patient\'s hands folded, a clinician writing a weekly meal plan, a bowl of bananas, apples and oranges between them.',
        ],

        // ---- Added via clinic:fetch-pexels, each inspected at full size ----
        'kitchen-hands-herbs' => [
            'source' => 'pexels-losmuertoscrew-7601397.jpg',
            'topic' => 'food',
            'faces' => false,
            'crop' => null,
            'describes' => 'Hands chopping coriander on a wooden board in warm light, a halved red onion and a bowl of rice beside them. Framed below the shoulders.',
            'attribution' => [
                'photographer' => 'Los Muertos Crew',
                'source' => 'https://www.pexels.com/photo/close-up-photo-of-a-person-slicing-cilantro-7601397/',
                'downloaded_at' => '2026-08-30',
            ],
        ],
        'pantry-jars-legumes' => [
            'source' => 'pexels-ronlach-8287244.jpg',
            'topic' => 'food',
            'faces' => false,
            'crop' => null,
            'describes' => 'Three clip-top glass jars of red beans, lentils and rice on a wooden table. No labels and no text anywhere in frame.',
            'attribution' => [
                'photographer' => 'Ron Lach',
                'source' => 'https://www.pexels.com/photo/clear-glass-jars-with-raw-beans-seeds-and-rice-on-brown-wooden-table-8287244/',
                'downloaded_at' => '2026-08-30',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Rejected, and why
    |--------------------------------------------------------------------------
    |
    | Written down rather than deleted, so nobody re-adds one in six months
    | having no idea it was already considered. The command refuses to process
    | anything named here even if someone adds it to the library above.
    |
    | Seven of these ten are the same photograph taken ten different ways: a
    | tape measure round a waist, a scale with a number on it, a body being
    | appraised. That is what stock libraries return for "nutrition" and
    | "obesity", and it is precisely the thing this clinic positions against —
    | the same rule that trimmed the scale and the measuring tape out of the
    | hero video.
    */
    'rejected' => [
        // ---- From clinic:fetch-pexels on 2026-08-30. Opened, then refused --
        'pexels-airam-dato-on-33554298.jpg' => 'A market stall of vegetable crates, and legible English price signage: "CARROTS 90", plus handwritten label cards on several crates. Legible text on a photograph is what made the 2.mp4 footage unusable.',
        'pexels-anntarazevich-7771969.jpg' => 'Bulk-food dispensers with a hand at the lever. The printed product labels along the row are legible.',
        'pexels-helenajankovicovakovacova-10697692.jpg' => 'A wide market aisle with a dozen shoppers, several faces identifiable at full size. No evidenced release.',
        'pexels-pramodtiwari-17161099.jpg' => 'Market stall with a vendor facing the camera, face fully identifiable.',
        'pexels-pramodtiwari-17160606.jpg' => 'The same stall and vendor, face identifiable.',
        'pexels-pramodtiwari-17160607.jpg' => 'The same stall and vendor, face identifiable.',
        'pexels-mingchelee-30393388.jpg' => 'Market scene with identifiable faces in the middle ground.',
        'pexels-andres-ayrton-6550832.jpg' => 'A digital bathroom scale with feet on it and a weight on the display. Scale and a weight readout.',
        'pexels-andres-ayrton-6551440.jpg' => 'A tape measure being pulled around a bare waist.',
        'pexels-anntarazevich-5629206.jpg' => 'A tape measure around the waist of a woman in a bra. Measuring tape and body appraisal.',
        'pexels-cottonbro-6636375.jpg' => 'A young woman holding a tape measure and looking at it. The tape is the subject.',
        'pexels-deon-black-3867281-5750475.jpg' => 'An apple wrapped in a tape measure. The most literal diet-culture image in the set.',
        'pexels-karola-g-5714346.jpg' => 'A tape measure around a bare waist, holding a salad bowl. Tape measure plus body-as-problem.',
        'pexels-towfiqu-barbhuiya-3440682-11309666.jpg' => 'A person grasping their own abdominal fat through a shirt. The body framed as the problem — the visual equivalent of a before-and-after.',
        'pexels-jhon-macias-285181985-13357338.jpg' => 'An adult bottle-feeding an infant whose face is fully visible. Identifiable minor, no evidenced release.',
        'pexels-shvetsa-3845407.jpg' => 'A woman kissing a baby, both faces fully visible. Two identifiable people, no evidenced release.',
        'pexels-ron-lach-8487214.jpg' => 'A woman slumped at a desk with her head in her hand, face clearly identifiable. Also an ambiguous stock cliche that illustrates nothing specific.',
        'pexels-artempodrez-6823407 (1).jpg' => 'Byte-for-byte duplicate of pexels-artempodrez-6823407.jpg.',
    ],
];
