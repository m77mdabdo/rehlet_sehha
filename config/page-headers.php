<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The image behind each page header
|------------------------------------------------------------------------------
|
| One place, so that "which picture does the FAQ page use" is answerable without
| opening seven Blade files, and so that a page added later has somewhere
| obvious to declare itself.
|
| A PAGE WITH NO ENTRY GETS THE NAVY GRADIENT, not a borrowed photograph. That
| is the whole reason this is a lookup rather than a default: an off-topic
| image on a page header is worse than no image, because at this size it is the
| first thing anybody sees and it makes a claim about what the page is about.
|
| ---------------------------------------------------------------------------
| FOUR LIBRARY IMAGES ARE DELIBERATELY NOT HERE, AND IT IS WORTH SAYING WHY
| ---------------------------------------------------------------------------
|
| They are fine at the sizes they are already used at and wrong at this one. A
| page header is the largest image on its page; detail that is a smudge in a
| 400px card is legible at 1600px.
|
|   consultation-desk-wide   A WHITE COAT, and a clipboard reading "WEEKLY MEAL
|   consultation-meal-plan   PLAN" with the days of the week down the side. Two
|                            separate rules: legible text, and imagery that
|                            reads as a doctor's surgery when Rana is a
|                            nutritionist. The same reasoning that keeps
|                            'stethoscope' and 'white coat' out of
|                            FetchPexelsPhotos::CLINICAL_TERMS.
|
|   food-cookbook-overhead   An open cookbook with legible printed recipe text
|                            across both pages.
|
|   food-clinical-flatlay    A stethoscope, for the same scope-of-practice
|                            reason as the two above.
|
| ---------------------------------------------------------------------------
| AND THE ABOUT PAGE HAS NO ENTRY AT ALL, WHICH IS THE POINT OF THE FALLBACK
| ---------------------------------------------------------------------------
|
| It gets the navy ground. StandalonePagesTest has refused a stock photograph
| on that page since long before this task, and the reason is worth restating:
| on the page about WHO WILL BE TREATING YOU, a stock photograph is a claim a
| patient cannot check. A warm shot of somebody's hands chopping herbs is not
| Rana's hands, and putting it at the top of her biography implies it is.
|
| It stays empty until the practice supplies a real photograph.
|
| ---------------------------------------------------------------------------
|
| `focus` is object-position, tuned per image so the subject survives the crop.
| A header is a wide, short box — as little as 5:1 at 1920 — so almost all of
| the vertical is thrown away and the number that matters is the second one.
*/

return [

    /*
     * Height. Roughly the 30vh/40vh of the reference, expressed in rem rather
     * than vh ON PURPOSE: mobile browsers resize the viewport as the URL bar
     * hides, and a vh-sized header reflows the whole page when they do. This
     * site is at CLS 0.0000 and a header that breathes with the address bar
     * would be the thing that ended it.
     */
    'height' => 'min-h-[17rem] sm:min-h-[21rem] lg:min-h-[25rem]',

    /*
     * The scrim over the photograph.
     *
     * NOT A FLAT WASH AND NOT A COLOUR FILTER. The reference site puts a green
     * filter over its header photographs and the picture underneath stops
     * existing; the point of choosing an image is lost the moment it is dyed.
     * This is the same technique as the hero: a gradient that spends its ink
     * where the words are, dense across the middle band where the centred
     * title sits, thin at the top so the photograph is still a photograph.
     *
     * THE TOP STOP WENT FROM 0.34 TO 0.60 BECAUSE THE BREADCRUMB SITS THERE.
     * The title is centred in the dense band and measured 6.94:1 on the
     * brightest header; the breadcrumb is above it, in the part deliberately
     * left thin so the photograph shows, and its links measured 3.10:1 against
     * a needed 4.5. Contrast governs, so the top of the gradient is heavier
     * than the composition would otherwise want. The picture is still visible
     * — see the screenshots — but this is the constraint that set the number,
     * not taste.
     *
     * 0.52 cleared seven headers and left the English services page at 4.40
     * against a needed 4.5, because two-hands-blank-page is a white table in
     * daylight and the English trail is longer, so its links sit further out
     * into the bright part of the frame. 0.60 is the value that clears every
     * page in both locales; the binding case is that one.
     *
     * Measured, not chosen.
     */
    /*
     * The scrim over the photograph.
     *
     * NOT A FLAT WASH AND NOT A COLOUR FILTER. The reference site puts a green
     * filter over its header photographs and the picture underneath stops
     * existing; the point of choosing an image is lost the moment it is dyed.
     *
     * A RADIAL, NOT A VERTICAL GRADIENT, and that took a wrong turn to find.
     * The first attempt ran bottom-to-top like the hero's. It could not work:
     * the hero's copy is a column down one side, so there is a whole other
     * side to leave alone, whereas this title is CENTRED and the text runs
     * from the breadcrumb near the top to the lead near the bottom. A vertical
     * gradient dense enough for the breadcrumb, with the title and lead below
     * it, ends up dense everywhere — which is a flat wash with extra steps, and
     * the photograph had gone.
     *
     * With centred text the clear space is at the SIDES, so the scrim is an
     * ellipse behind the words and the picture survives left and right of it.
     * A low flat base underneath keeps the two ends of the header from reading
     * as a bright frame around a dark blob.
     *
     * The densities are measured against the brightest region of every image
     * that uses them, in both locales — see the task report. The binding
     * element is the BREADCRUMB LINK, not the title: the title is 52px and
     * needs 3:1, the links are 14px and need 4.5:1, and they sit higher up
     * where the ellipse is already falling away.
     */
    'scrim' => 'radial-gradient(115% 82% at 50% 46%,'
        .' rgb(14 46 77 / 0.88) 0%,'
        .' rgb(14 46 77 / 0.84) 38%,'
        .' rgb(14 46 77 / 0.60) 62%,'
        .' rgb(14 46 77 / 0.26) 82%,'
        .' rgb(14 46 77 / 0.10) 100%),'
        .' linear-gradient(to top, rgb(14 46 77 / 0.30), rgb(14 46 77 / 0.22))',

    /*
     * page key => ['photo' => library slug, 'focus' => object-position]
     *
     * The key is the route name where there is one obvious route, so a page
     * looks itself up rather than being told.
     */
    'pages' => [
        'services' => [
            'photo' => 'kitchen-hands-herbs',
            'focus' => '50% 50%',
            'note' => 'Hands chopping herbs in warm light. two-hands-blank-page was the first choice and reads better as a subject — a consultation with nothing written yet — but it is a white table in flat daylight, and under a scrim dark enough for white text it goes grey. An image needs tonal range to survive being scrimmed at all; that one has almost none.',
        ],
        'packages' => [
            'photo' => 'pantry-jars-legumes',
            'focus' => '50% 55%',
            'note' => 'Three jars of staples. Chosen for what it does NOT say: no plate, no portion, no price, nothing that reads as a tier.',
        ],
        'how-it-works' => [
            'photo' => 'packing-papers-into-bag',
            'focus' => '50% 50%',
            'note' => 'Papers going into a bag — preparing for an appointment, which is literally step one of this page.',
        ],
        'articles' => [
            'photo' => 'food-vegetables-overhead',
            'focus' => '50% 50%',
            'note' => 'Ordinary vegetables, overhead. The library cookbook shot would have been the obvious pick and carries legible recipe text at this size.',
        ],
        'faq' => [
            'photo' => 'water-jug-morning-table',
            'focus' => '50% 55%',
            'note' => 'A jug of water by a window. Quiet and unloaded, for a page that answers worries.',
        ],
        'contact' => [
            'photo' => 'phone-blank-screen-breakfast',
            'focus' => '50% 50%',
            'note' => 'A hand holding a phone with a BLANK screen. The blankness is the point: a screen with an interface on it dates the page and risks legible text.',
        ],
        'specialties' => [
            'photo' => 'hands-slicing-tomato-board',
            'focus' => '50% 55%',
            'note' => 'Shared by the eight specialty pages. One honest image beats eight strained ones; a per-specialty picture is a later task with its own vetting.',
        ],
    ],
];
