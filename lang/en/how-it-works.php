<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The how-it-works page
|------------------------------------------------------------------------------
|
| NOT the homepage section rewritten. There, four steps get a sentence each.
| Here each one gets what the sentence leaves out: what actually happens in the
| room, how long it takes, what to bring, and what you leave with.
|
| And the part the homepage omits ENTIRELY — the weeks between sessions, which
| is where a plan either survives or does not. That section is the reason this
| page exists rather than being a longer version of a summary.
|
| NOTHING HERE IS CLINICAL GUIDANCE. It describes a process, not a treatment.
|
*/

return [
    'meta_title' => 'How it works — Rehlet Sehha',
    'meta_description' => 'Exactly what happens: from booking, to the first session, to the written plan, to follow-up. How long each takes, what to bring, and what happens in the weeks between.',

    'eyebrow' => 'How it works',
    'title' => 'Exactly what happens',
    'lead' => 'From booking to the point where the plan is simply part of your day. Written out in full so you know what a first session is before you walk into one.',

    'duration_label' => 'How long',
    'bring_label' => 'Bring',
    'leave_label' => 'You leave with',

    'steps' => [
        [
            'number' => 'Step one',
            'title' => 'Booking',
            'body' => 'You pick the package and the time on the site. There is no confirmation call and no waiting for a reply — the slot is held immediately, and the confirmation carries a link that lets you move it yourself without speaking to anybody.',
            'duration' => 'Two minutes',
            'bring' => 'A working mobile number. Email is optional — a real share of our patients do not use one, and messages arrive on WhatsApp.',
            'leave' => 'A confirmation, a rescheduling link, and the session link if you chose online.',
        ],
        [
            'number' => 'Step two',
            'title' => 'The first session',
            'body' => 'This session is questions. Your medical history, the medication you take, the shape of your day, who cooks at home, what you have tried before and where it stopped. There are no measurements and there is no scale — those are not what builds the plan.',
            'duration' => 'Forty-five to sixty minutes, in clinic or online at the same price',
            'bring' => 'Any test results or reports you have, and the names of any medication or supplements you take. If you cannot remember the names, photograph the boxes.',
            'leave' => 'A clear understanding of what we are working on, and when the plan arrives.',
        ],
        [
            'number' => 'Step three',
            'title' => 'The written plan',
            'body' => 'You do not leave a session trying to remember a conversation. The plan arrives in writing within a day or two: food from your own kitchen, a substitute for every item so nothing stops if something is unavailable or expensive, and timings that fit the hours you work rather than an ideal schedule.',
            'duration' => 'Arrives within twenty-four to forty-eight hours of the session',
            'bring' => 'Nothing. This part is ours.',
            'leave' => 'A document you can open whenever you need it, and forward to whoever cooks.',
        ],
        [
            'number' => 'Step four',
            'title' => 'Follow-up',
            'body' => 'Every follow-up starts from what actually happened rather than from what was supposed to. If a meal never gets made, it does not reappear. If a week went badly, we look at what made it bad and adjust the plan so it can absorb that week when it comes round again.',
            'duration' => 'Twenty to thirty minutes, weekly or fortnightly depending on the package',
            'bring' => 'An honest account of the week. This is not an exam.',
            'leave' => 'An updated plan after every session.',
        ],
    ],

    /*
     * ALT TEXT, not the section title — a blind patient gets what a sighted
     * one gets from looking. A CAPTION is separate and visible, and only where
     * the image carries something the body text does not already say.
     */
    'photo_alt' => [
        'plan' => 'A clinician in a white coat writing a weekly meal plan on a clipboard, the patient\'s hands folded on the desk opposite.',
        'kitchen' => 'A kitchen table with olive oil, a tomato, corn, blueberries, pears, courgette and fresh basil.',
    ],

    'photo_caption' => [
        'plan' => 'The plan is written during the session and reaches you in full a day or two later.',
    ],

    'statement' => 'The session is an hour a week. The rest of the time is what decides the outcome.',

    'between' => [
        'eyebrow' => 'The part the homepage does not cover',
        'title' => 'What happens between sessions',
        'lead' => 'The part that makes the most difference, and the part people ask about least before booking.',
        'items' => [
            [
                'title' => 'Short questions have somewhere to go',
                'body' => '“Can I have this instead of that?” or “there is a family lunch today, what do I do?” do not need an appointment. They go to WhatsApp and are usually answered the same day, on the follow-up packages.',
            ],
            [
                'title' => 'The plan changes without waiting',
                'body' => 'You do not have to hold something that is not working until the next session. If an item is unavailable or you dislike it, the substitute reaches you without an appointment.',
            ],
            [
                'title' => 'A gap is not the end',
                'body' => 'A bad week is not a reason to stop. A package extends to absorb a delayed session as long as you come back within a month. What derails a plan is a long silence, not a bad week.',
            ],
            [
                'title' => 'We do not chase you beyond that',
                'body' => 'No motivational messages, no daily reminders, nothing that makes your phone buzz. One appointment reminder before the session, and that is all.',
            ],
        ],
    ],

    'privacy' => [
        'title' => 'Your information',
        'body' => 'What you say in a session stays in your file. We do not share it with anyone other than your treating physician, and only if you ask us to. You can request a copy of your file or ask for it to be erased at any time — both rights are written into the privacy policy.',
        'link' => 'Read the privacy policy',
    ],

    'cta' => [
        'title' => 'Ready to start?',
        'lead' => 'The first session is questions and answers. Nothing to prepare beyond any results you already have.',
    ],
];
