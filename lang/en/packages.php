<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The packages page
|------------------------------------------------------------------------------
|
| NOT the homepage section rewritten. The homepage shows four cards and a
| price; this page is where somebody decides. Everything here answers a
| question the cards leave open — what is actually included, what happens in
| the weeks between sessions, how payment works, and what happens when life
| gets in the way.
|
| A page that repeated the section verbatim would rank below it and deserve to.
|
| THE COMPARISON MATRIX IS KEYED BY SERVICE SLUG. Prices, session counts and
| durations are NOT here — those are read from the services table so the table
| and the cards can never disagree about a number. What lives here is the
| wording for things the schema does not model. PackagesPageTest fails if an
| active package has no entry, so a new package cannot render a row of blanks.
|
*/

return [
    'meta_title' => 'Packages and pricing — Rehlet Sehha',
    'meta_description' => 'What each nutrition package actually includes: session counts, what happens between sessions, payment methods, and the rescheduling and cancellation policy in full.',

    'eyebrow' => 'Packages and pricing',
    'title' => 'Choose the package that fits your situation',
    'lead' => 'Four ways to start, from a single consultation to three months of follow-up. The difference between them is not how much service you get — it is how long we have to keep adjusting the plan with you.',

    'comparison' => [
        'title' => 'Full comparison',
        'lead' => 'The same prices as the homepage, with the detail that actually matters when you are deciding.',
        'scroll_hint' => 'The table scrolls sideways on small screens.',
        'aria' => 'Comparison of follow-up packages',
        'feature_column' => 'Compare',
        'rows' => [
            'price' => 'Price',
            'sessions' => 'Sessions',
            'duration' => 'Session length',
            'format' => 'Where it happens',
            'plan' => 'Written plan',
            'between' => 'Between sessions',
            'labs' => 'Lab review',
            'adjust' => 'Plan adjustments',
            'suits' => 'Who it suits',
        ],
        'cta' => 'Book this one',
    ],

    'matrix' => [
        'single-consultation' => [
            'format' => 'In clinic or online',
            'plan' => 'A written plan after the session',
            'between' => 'One follow-up message a week later',
            'labs' => 'We review labs you already have',
            'adjust' => 'None — the plan is handed over once',
            'suits' => 'Starting properly without a long commitment, or a second opinion on a plan that is working',
        ],
        'one-month-programme' => [
            'format' => 'In clinic or online',
            'plan' => 'A written plan after every session',
            'between' => 'WhatsApp open for short questions all month',
            'labs' => 'We review labs you already have',
            'adjust' => 'Weekly, based on what actually happened',
            'suits' => 'Eating that is changing but needs tuning, or an event or trip coming up',
        ],
        'three-months-programme' => [
            'format' => 'In clinic or online',
            'plan' => 'A written plan after every session',
            'between' => 'WhatsApp open, plus a short check-in every fortnight',
            'labs' => 'Two lab reviews across the programme',
            'adjust' => 'Weekly, with a fuller review each month',
            'suits' => 'A long-running condition such as diabetes or PCOS, or having tried before and come back',
        ],
        'lab-review' => [
            'format' => 'Usually online',
            'plan' => 'A written report with the result and the next step',
            'between' => 'No follow-up afterwards',
            'labs' => 'This is the service',
            'adjust' => 'None',
            'suits' => 'You have results and do not know what to do with them, and are not ready for a programme',
        ],
    ],

    'statement' => 'The expensive package is not the best package. The best one is the one you will finish.',

    'between' => [
        'eyebrow' => 'The part nobody talks about',
        'title' => 'What happens between sessions',
        'lead' => 'The session itself is an hour every week or two. The rest of the time is what decides the outcome, and it is what we work on.',
        'steps' => [
            [
                'title' => 'The plan arrives in writing',
                'body' => 'You do not leave a session trying to remember a conversation. You get a document with what was agreed — quantities, substitutions, and meal timings that fit your day — and you can open it whenever you need it.',
            ],
            [
                'title' => 'Short questions have somewhere to go',
                'body' => 'Not every question needs an appointment. “Can I have this instead of that?” or “there is a family lunch today, what do I do?” go to WhatsApp and are usually answered the same day.',
            ],
            [
                'title' => 'What did not work gets changed',
                'body' => 'If a meal never actually happens, it does not reappear in the next plan. The following session starts from what really happened rather than from what was supposed to.',
            ],
            [
                'title' => 'A bad week is not the end',
                'body' => 'If a week goes badly, that is not a reason to stop. The next session treats it as information about your life rather than as a failure, and the plan is adjusted so it can absorb that week when it comes round again.',
            ],
        ],
    ],

    'terms' => [
        'eyebrow' => 'The terms, plainly',
        'title' => 'Payment and rescheduling',
        'lead' => 'Written here so they are known before you book rather than after.',
        'payment' => [
            'title' => 'Payment',
            'items' => [
                'You pay for the first session only to try it; the rest of a package is split across two payments.',
                'Cash at the clinic, or InstaPay and mobile wallet before an online session.',
                'No payment method carries an extra fee, and there is no file-opening or booking fee.',
                'A receipt reaches you on WhatsApp either way.',
            ],
        ],
        'cancellation' => [
            'title' => 'Rescheduling and cancellation',
            'items' => [
                'Rescheduling is free up to twenty-four hours before, from a link in your confirmation message.',
                'Later than that the session counts against the package, except where something medical came up.',
                'Cancelling before a package starts is refunded in full.',
                'A package extends to absorb a delayed session, as long as you return within a month.',
            ],
        ],
        'note' => 'If something happens that is not written here, tell us. These terms exist to protect both our time, not to be held against you.',
    ],

    'faq' => [
        'eyebrow' => 'Before you book',
        'title' => 'Questions about booking and paying',
        'lead' => 'The questions people ask at the point of deciding. If yours is not here, send it and we will answer.',
        'empty' => 'These questions will be available shortly.',
    ],

    'cta' => [
        'title' => 'Not sure which one?',
        'lead' => 'Start with a single consultation. If you decide to continue, its fee comes off whichever package you choose.',
    ],
];
