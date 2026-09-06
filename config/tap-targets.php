<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| Tap targets: the minimum, and the only things allowed under it
|------------------------------------------------------------------------------
|
| WCAG 2.5.8 asks for 44x44 CSS pixels on anything you are expected to hit with
| a finger. The Task 13 sweep found sixteen element classes under it at 320px —
| the worst being the locale switcher at 32x36 on 46 pages, and the footer
| service links at 64x14. They are fixed by the `tap-target` utility, which adds
| padding and never touches font size, so the type looks identical and the box
| is larger.
|
| THIS FILE IS THE EXEMPTION LIST, and it is deliberately hard to add to.
|
| Same shape as the directional-utility exemptions in DirectionalClassesTest: an
| entry must carry a WRITTEN JUSTIFICATION, and TapTargetTest fails both when an
| unexempted element is too small AND when an exempted one has quietly become
| large enough — because an exemption for a case that no longer exists silently
| pre-authorises the next person to reintroduce it for a different reason.
*/

return [

    'minimum' => 44,

    /*
     * Selectors measured at 320. `why` is not documentation, it is the entry's
     * reason to exist, and the test requires it to be substantial.
     */
    'exempt' => [
        [
            'selector' => 'article a.font-medium.text-accent-dark',
            'why' => 'An internal link INSIDE A SENTENCE of article prose, e.g. the '
                .'link to a specialty page in the middle of a paragraph. WCAG 2.5.8 '
                .'exempts a target "in a sentence or block of text" by name, and the '
                .'exemption exists because the alternative is worse: giving an inline '
                .'anchor a 44px box breaks the line box it sits in, pushing the lines '
                .'around it apart and leaving a visible gap mid-paragraph. The reader '
                .'reaches these with a tap on a word she is already reading, not by '
                .'hunting for a control.',
        ],
        [
            'selector' => 'label input[type="radio"], label input[type="checkbox"]',
            'why' => 'A radio or checkbox WRAPPED IN ITS OWN LABEL. The measured box '
                .'is the 16-20px input, but the target a finger actually hits is the '
                .'label, which here is the whole service card or the whole consent '
                .'block — hundreds of pixels in both directions. WCAG measures the '
                .'target, not the input element, so enlarging the box would be '
                .'answering a question nobody asked and would put a 44px control in '
                .'the middle of a sentence of consent copy. Verified by measurement: '
                .'every one of these has a label ancestor.',
        ],
    ],
];
