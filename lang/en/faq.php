<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The FAQ page
|------------------------------------------------------------------------------
|
| Every active question, GROUPED, rather than the homepage's general six. The
| grouping comes from the faqs.category column, so a new category appears here
| the moment a row uses it — the page cannot silently drop a group by listing
| them by hand.
|
| The buying questions also appear on the packages page, which is correct: they
| are answered where the decision is made AND where somebody looking for
| answers goes. That is the same question in two places a patient might look,
| not duplicated marketing prose.
|
*/

return [
    'meta_title' => 'Frequently asked questions — Rehlet Sehha',
    'meta_description' => 'The questions people ask before booking and after: sessions, online consultations, lab results, payment, rescheduling and cancellation.',

    'eyebrow' => 'Frequently asked',
    'title' => 'The questions people ask',
    'lead' => 'In two groups: questions about the work itself, and questions about booking and paying. If yours is not here, send it on WhatsApp.',

    'categories' => [
        'general' => [
            'title' => 'About the work and the sessions',
            'lead' => 'What happens in a session, online consultations, and the conditions we handle.',
        ],
        'buying' => [
            'title' => 'About booking and paying',
            'lead' => 'Packages, payment, rescheduling and cancellation. The same answers as on the packages page.',
        ],
    ],

    'empty' => 'These questions will be available shortly.',

    'still_asking' => [
        'title' => 'Not answered here?',
        'body' => 'Send it on WhatsApp. If the answer needs a session we will say so plainly, rather than giving half an answer in a message.',
    ],

    'cta' => [
        'title' => 'Got your answer?',
        'lead' => 'If what is left are questions about your own situation, those are what the first session is for.',
    ],
];
