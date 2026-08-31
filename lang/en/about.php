<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The practitioner
|------------------------------------------------------------------------------
|
| THE FACTS ARE NOT HERE. Her name, title, degree, licensing body and
| membership number live in config/clinic.php as the single source of truth,
| and this file holds only the labels around them. CredentialsTest fails if any
| page states a qualification or a body that config does not.
|
| That matters because these are claims about a real person's professional
| standing, published under her name. The previous version of this file
| invented a university, a master's degree and the wrong syndicate — the sort
| of thing that is only ever discovered by the person it misrepresents, or by a
| patient checking.
|
| THE PHILOSOPHY PARAGRAPH IS HERS, and is now written. It was the last
| TODO_COPY on the site, and it blocked production until she answered — which
| is the correct order. A paragraph in a practitioner's voice, on the page
| about her, is not something anybody else may draft. See the note beside it.
|
*/

return [
    'eyebrow' => 'About the practitioner',

    'meta_title' => 'About Rana Salem — Rehlet Sehha',
    'meta_description' => 'Clinical nutrition specialist, BSc Agricultural Sciences from Mansoura University, registered with the Agricultural Professions Syndicate. Clinical training and qualifications in full.',

    'page_title' => 'Who will be treating you',
    'page_lead' => 'This page is about the person you will sit with. The qualifications and the registration number are written out so you can check them yourself.',

    'philosophy_heading' => 'How she works',
    /*
     * HER OWN WORDS, in English. DO NOT EDIT THIS.
     *
     * See the note beside the Arabic in lang/ar/about.php. Approved as it
     * stands.
     */
    'philosophy' => <<<'COPY'
        The first thing most patients tell me is that they want to lose weight and feel better. The second thing — a few weeks into whatever plan they tried before — is that they got tired of it.

        That exhaustion isn't weak willpower. It means the plan was built for someone else: not your kitchen, not your working hours, not your budget. A plan you have to fight is a plan you will eventually put down.

        So I don't start with a sheet of paper. I start with you — your health, your day, the food you actually like. The plan is built from your life, not from a textbook, and it adjusts with you every week until it stops being effort and starts being habit.

        My goal isn't a number on a scale. It's that you feel better — your energy, your sleep, and how you feel in your own skin.
        COPY,

    'credentials_heading' => 'Qualifications and registration',
    'degree_label' => 'Qualification',
    'licence_label' => 'Professional registration',
    'licence_value' => ':body, membership number :number, since :year',
    'licence_note' => 'The registration number is published so you can verify it against the syndicate register. A clinic that does not publish one is worth asking about.',

    'training_heading' => 'Clinical training',
    'training_lead' => 'The training that took place, in order. Hours and institutions exactly as they appear on the certificates.',
    'training_hours' => ':hours hours',

    'certificates_heading' => 'Certificates',
    'certificates_pending' => 'Images of the certificates will be added once the national ID number has been redacted.',
    'certificates_note' => 'Certificates are published with the national ID number removed. That is personal data you do not need in order to verify a qualification.',

    'portrait_alt' => 'Photograph of :name',
    'portrait_pending' => 'A photograph of the practitioner will be added shortly.',
    'portrait_pending_title' => 'Photograph to follow',

    'treats_heading' => 'Conditions she treats',
    'treats_lead' => 'These areas come from the services page, so a change there changes this.',

    'cta' => [
        'title' => 'Want to book with her?',
        'lead' => 'The first session is questions and answers, online.',
    ],
];
