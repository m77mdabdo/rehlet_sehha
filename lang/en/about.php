<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| Practitioner — PLACEHOLDER COPY
|------------------------------------------------------------------------------
|
| Every value marked TODO_COPY needs a real answer from the clinic before this
| goes live. PlaceholderCopyTest fails if any survives into production.
|
| Do NOT fill these in by guessing. Credentials, a university, a membership and
| a registration number are claims about a real person's qualifications; the
| structure is ours to design, the facts are not ours to invent.
|
*/

return [
    'eyebrow' => 'About the practitioner',
    'name' => 'TODO_COPY — the doctor’s full name',
    'title' => 'TODO_COPY — professional title, e.g. Consultant in Clinical Nutrition',

    'philosophy' => 'TODO_COPY — a short paragraph in the doctor’s own voice on how she works: '
        .'why plans are built from home cooking, what progress is measured on, and what she '
        .'does not do. Forty to sixty words, in the same tone as the rest of the site.',

    'credentials_heading' => 'Qualifications',

    'credentials' => [
        'degree' => 'TODO_COPY — degree, university and year',
        'specialisation' => 'TODO_COPY — sub-specialty or fellowship',
        'membership' => 'TODO_COPY — professional memberships',
        'experience' => 'TODO_COPY — years of practice and in what',
    ],

    'registration' => 'TODO_COPY — medical syndicate registration number / clinic licence',

    /*
    |--------------------------------------------------------------------------
    | The standalone page
    |--------------------------------------------------------------------------
    |
    | Structure only. Everything factual about the practitioner stays TODO_COPY
    | — a page that invents a qualification is worse than a page that admits it
    | is waiting for one, and clinic:verify-copy blocks production until the
    | clinic answers.
    |
    | The photograph frames are RESERVED, not filled. The empty state is the
    | brand mark on sage, the same treatment as the homepage — never a stock
    | stand-in of somebody else's clinician, which on a page about who will be
    | treating you is a lie, and never a broken frame.
    */
    'meta_title' => 'About the practitioner — Rehlet Sehha',
    'meta_description' => 'Who will be treating you: qualifications, specialisation, professional registration, and how the clinic works.',

    'page_title' => 'Who will be treating you',
    'page_lead' => 'This page is about the person you will sit with, not about the clinic. The qualifications and the registration number are written out so you can check them yourself.',

    'philosophy_heading' => 'How she works',
    'registration_heading' => 'Professional registration',
    'registration_note' => 'The registration number is published so you can verify it against the syndicate register. A clinic that does not publish one is worth asking about.',

    'portrait_pending_title' => 'Photograph to follow',
    'clinic_photo_pending' => 'Photographs of the clinic will be added shortly.',
    'clinic_photo_heading' => 'The clinic',

    'treats_heading' => 'Conditions she treats',
    'treats_lead' => 'These areas come from the services page, so a change there changes this.',

    'cta' => [
        'title' => 'Want to book with her?',
        'lead' => 'The first session is questions and answers, in clinic or online at the same price.',
    ],

    'portrait_alt' => 'Portrait of :name',
    'portrait_pending' => 'A photograph of the doctor will be added shortly.',
];
