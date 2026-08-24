<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The services page
|------------------------------------------------------------------------------
|
| NOT the homepage section rewritten. There, eight cards carry a line each;
| here each area gets what the card leaves out — what the work actually covers,
| what the first session looks like, and who it suits.
|
| KEYED BY SPECIALTY SLUG. The name and the one-line description come from the
| specialties table, so this page and the homepage can never describe the same
| area differently. What lives here is only what the schema does not model.
| ServicesPageTest fails if an active specialty has no entry.
|
| NOTHING HERE IS CLINICAL GUIDANCE. These describe a service, not a treatment:
| what happens in a room, and who tends to book it. No targets, no numbers, no
| "eat X for Y". That belongs to the consultation and to a licensed
| practitioner, not to a marketing page.
|
*/

return [
    'meta_title' => 'What we treat — Rehlet Sehha',
    'meta_description' => 'The clinical areas this practice works in, in full: medical nutrition, weight management, pregnancy and breastfeeding, child nutrition, PCOS and hormones, lab review, sports nutrition and corporate programmes.',

    'eyebrow' => 'What we treat',
    'title' => 'The areas this clinic works in',
    'lead' => 'Eight areas, each worked differently. If yours is among them, this page shows what a first session looks like and what we need from you.',

    'covers_heading' => 'What it covers',
    'suits_heading' => 'Who it suits',
    'more' => 'More about :name',

    'areas' => [
        'medical-nutrition' => [
            'body' => 'This is the largest part of the practice. Where there is a long-running condition — diabetes, blood pressure, cholesterol, fatty liver — food is part of the treatment plan rather than a substitute for it. We work alongside the medication your doctor has prescribed, review the results you already have, and build a plan that runs in your own kitchen without special foods or expensive supplements.',
            'covers' => [
                'Reviewing the results and reports you already have, before any talk of a plan',
                'Coordinating with your treating physician where the condition needs it',
                'A written plan that fits around your medication times and your day',
                'Follow-up that adjusts as your results or your life change',
            ],
            'suits' => 'You have a clear diagnosis from a doctor and want food working with the treatment — or your results have started moving and nobody has explained what that means.',
        ],
        'weight-management' => [
            'body' => 'Long, slow work, not a diet. The plan is built from the food already in your kitchen and the hours you actually keep, and it is adjusted every week around what really happened rather than what was supposed to. The goal is not a number; it is a way of eating you can keep going when the week is bad.',
            'covers' => [
                'Going through what you have tried before and where it stopped',
                'A plan built on your own food and budget, not on somebody else\'s shopping list',
                'Work on the hard moments — family lunches, travel, night shifts',
                'Weekly adjustment rather than one plan handed over and finished',
            ],
            'suits' => 'You have tried before and come back, or eating goes well for a while and then falls over, or you want someone alongside you rather than another sheet of paper.',
        ],
        'pregnancy-nutrition' => [
            'body' => 'Pregnancy and breastfeeding are two periods where needs change quickly and the advice arriving from every direction contradicts itself. We work in coordination with your obstetrician, and around what is actually available in Egyptian shops and in your budget.',
            'covers' => [
                'A plan that changes with each trimester and again for feeding',
                'Working around nausea, reflux and the symptoms that get in the way of eating',
                'Following the tests your doctor orders and explaining what they say',
                'Coordinating with your obstetrician where the situation needs it',
            ],
            'suits' => 'You are pregnant or breastfeeding and want one clear answer instead of contradictory advice — or you have something like gestational diabetes and need a plan that works with it.',
        ],
        'child-nutrition' => [
            'body' => 'More work with the parents than with the child. Most of what reaches us is not a deficiency; it is a daily battle around the table. We work on the shape of the meal and how it is offered long before anyone mentions a supplement.',
            'covers' => [
                'Assessing growth with your paediatrician, not instead of them',
                'Handling a child who refuses whole categories of food',
                'Introducing solids, and ordering meals by age',
                'A school-day plan that survives contact with a working household',
            ],
            'suits' => 'Your child refuses food, or the paediatrician has flagged growth, or you are starting solids and do not know where to begin.',
        ],
        'pcos-hormonal' => [
            'body' => 'PCOS and hormonal conditions affect eating in ways that are not always obvious, and a plan that works in one case may not in the next. We work with your gynaecologist or endocrinologist and build on the results you have rather than on general advice from the internet.',
            'covers' => [
                'Reviewing the hormone and glucose results your doctor ordered',
                'A plan that runs alongside the treatment you are already on',
                'Work on the symptoms that affect eating itself',
                'Longer follow-up, because change in these conditions takes time',
            ],
            'suits' => 'You have a PCOS or thyroid diagnosis and want food to be part of the plan rather than a scatter of tips.',
        ],
        'lab-review' => [
            'body' => 'A short session with one purpose: understanding what the results you have mean for how you eat, and what the next step is. Not a diagnosis — that belongs to the doctor who ordered the test. What we do is translate the result into decisions about food, and say plainly when it needs a different specialist.',
            'covers' => [
                'Reading the results you already have, without ordering new ones',
                'A written explanation of the result and of what follows it',
                'Saying clearly when something needs to go back to a specialist',
                'No obligation to a package afterwards',
            ],
            'suits' => 'You have results and do not know what to do with them, or you are not ready for a programme and want a clear opinion first.',
        ],
        'sports-nutrition' => [
            'body' => 'Work with people who train seriously and want food working with the training rather than against it. Built around your actual training schedule and hours, and away from expensive supplements that earn nothing.',
            'covers' => [
                'Arranging food around when you actually train',
                'A plan that matches your goals and your real schedule',
                'An honest review of any supplements you are taking',
                'Handling competition periods, and breaks from training',
            ],
            'suits' => 'You train regularly and feel eating is not keeping up with the effort, or you take supplements and are not sure about them.',
        ],
        'corporate-wellness' => [
            'body' => 'Programmes for companies: group or individual sessions for staff, and written material to circulate internally. The shape is agreed around the size of the team and the nature of the work.',
            'covers' => [
                'An introductory group session, online or at your offices',
                'Individual consultations for the staff who want them',
                'Written material in the clinic\'s voice for internal circulation',
                'An aggregate report carrying no individual data about any employee',
            ],
            'suits' => 'You run a team or a workplace health programme and want something practical rather than a lecture.',
        ],
    ],

    /*
     * ALT TEXT, not the section title.
     *
     * A blind patient is entitled to what a sighted one gets from looking, so
     * each of these says what is actually in the frame. config/photos.php
     * carries a factual `describes` note per image; these are the reader-facing
     * version of it, in her own language.
     */
    'photo_alt' => [
        'medical-nutrition' => 'A blood pressure reading being taken: a cuff on a patient\'s upper arm and the gauge resting on the table.',
        'weight-management' => 'A kitchen counter with leafy greens, peppers, tomatoes, garlic, a plate of meat and a bowl of chickpeas.',
        'pregnancy-nutrition' => 'A pregnant woman in a hijab holding a pair of small baby shoes, her other hand resting on her bump.',
        'child-nutrition' => 'A jar of infant formula, a feeding bottle and teat, and a measuring scoop of powder on a pale blue surface.',
    ],

    'statement' => 'If your situation is not one of these, tell us. Better than saying yes to something outside what we do.',

    'cta' => [
        'title' => 'Not sure which area fits?',
        'lead' => 'Start with a single consultation. If it turns out you need a different specialist, we will say so in the first session.',
    ],
];
