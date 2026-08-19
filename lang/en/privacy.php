<?php

declare(strict_types=1);

/*
| Deliberately factual and short. These are statements about what the system
| ACTUALLY does — each one is true of the code as written, and if any stops
| being true the sentence has to change with it. Nothing here is a template.
|
| The rights section in particular describes BUTTONS THAT EXIST. It used to
| say "call us", which is a written promise without a mechanism.
*/

return [
    'title' => 'Privacy policy',
    'lead' => 'What we store, why, who can see it, and how you control it.',

    'points' => [
        'collect' => 'When you book, we store your name and mobile number, your email and date of birth if you provide them, and the medical information you enter on the booking form.',
        'encrypt' => 'The medical information — medication, conditions, foods you avoid, and your notes — is stored encrypted. Someone who obtained a copy of the database could not read it.',
        'purpose' => 'It is used for one thing only: so the doctor knows your case before the session and can follow up afterwards.',
        'sharing' => 'We do not sell your data and we do not share it with anyone else for marketing.',
        'retention' => 'Message delivery logs are deleted after 90 days and the change log after a year. Your clinical file is kept so your care can continue.',
        'audit' => 'We record that a clinical record was written or changed, never what it said. The log answers "this was edited, and when" — it does not answer "with what".',
        'cookies' => 'There is no advertising tracking and no third-party analytics on this site. Fonts are served from our own server, not from Google.',
    ],

    'rights' => [
        'title' => 'Your rights over your data',
        'lead' => 'Egypt\'s Personal Data Protection Law 151/2020 gives you the right to access, correct and erase your data. You do not need to telephone us to use any of them — they are buttons on your own booking page.',
        'how' => 'Your confirmation message contains a link called "Manage this booking". That link is yours alone, and from it you can:',
        'items' => [
            'access' => 'Read everything you entered on the booking form — your goal, medication, conditions, foods you avoid and your notes — decrypted and in full.',
            'correct' => 'Correct any of it while the appointment is still ahead. Editing closes once the session has taken place, so the record the doctor read during your consultation stays as it was; that protects you, not us. If something is wrong after that, call us.',
            'erase' => 'Delete all of your medical information at any time, including after the session. The appointment itself remains — the clinic needs it for its records and its accounts — but everything you wrote about your health is removed.',
        ],
        'keeps' => 'After erasure we keep: the appointment and its time, your name and mobile number, and the date you gave consent. Those are what the clinic needs to operate and to show that consent was properly taken.',
        'lost_link' => 'Lost the link? Call us and we will resend it to the number you booked with.',
    ],

    'contact' => 'For any question about your data, or anything these buttons do not cover:',
];
