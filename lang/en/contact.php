<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The contact page
|------------------------------------------------------------------------------
|
| THE PRACTICE IS ONLINE AND HAS NO PREMISES. There is no address block on
| this page, no map, and no address in config — because there is nowhere to
| go. A published address for a practice with no premises is worse than none:
| it looks authoritative and sends a patient to a door that is not there.
|
| NO CONTACT FORM, DELIBERATELY, and the page says so rather than leaving a
| visitor hunting for one. A patient who fills in a "get in touch" box has done
| something that feels like progress and is not — she then waits, for a reply
| that competes with the booking she actually wanted. Every route offered here
| is one she controls and gets an answer from: booking, WhatsApp, the phone.
|
| The platform list comes from config/clinic.php so this page and the schema
| cannot disagree about what a session runs on.
|
|
*/

return [
    'meta_title' => 'Contact — Rehlet Sehha',
    'meta_description' => 'Opening hours, WhatsApp and phone, and the platforms sessions run on. The practice is online and booking is available around the clock without a phone call.',

    'eyebrow' => 'Contact',
    'title' => 'How to reach us',
    'lead' => 'The practice is online. There are no premises to visit — sessions happen by video, and booking is confirmed immediately without a phone call.',

    'book_first' => [
        'title' => 'Booking is faster than asking',
        'body' => 'The slot is held immediately, at any hour, with a link that lets you move it yourself. No confirmation call and no waiting.',
        'cta' => 'Book an appointment',
    ],

    'no_form' => [
        'title' => 'There is no contact form here, and that is deliberate',
        'body' => 'A form lets you do something that feels like progress and is not, and then leaves you waiting. Every route below gets you an answer — or an actual appointment.',
    ],

    'channels_heading' => 'Ways to reach us',
    'whatsapp_note' => 'WhatsApp is quickest for short questions. Answered during opening hours.',
    'phone_note' => 'The phone is during opening hours only.',
    'email_note' => 'Email is for administrative matters and invoices.',

    'online_title' => 'The practice is online, and there are no premises',
    'online_body' => 'Every session happens by video. This is not a temporary arrangement, it is what the practice is. It means no travelling, no transport to arrange and no waiting room — and it also means there is no address to come to, so do not go looking for one.',

    'platforms_heading' => 'What the session runs on',
    'platforms_note' => 'You choose the platform that suits you when you book, and the link arrives with your confirmation. No account to register and nothing to install if you use WhatsApp.',
    'platforms' => [
        'zoom' => 'Zoom',
        'google_meet' => 'Google Meet',
        'whatsapp_video' => 'WhatsApp video call',
    ],

    'hours_heading' => 'Opening hours',
    'hours_note' => 'These are the clinic\'s hours. Online sessions can be arranged outside them by agreement.',
    'hours_closed' => 'Closed',
    'hours_empty' => 'Hours will be published shortly.',

    'expect_heading' => 'What happens when you get in touch',
    'expect' => [
        'If you book: the confirmation arrives immediately, and a reminder the day before.',
        'If you message on WhatsApp: answered during opening hours, usually the same day.',
        'If your question is clinical: we will say plainly if it needs a session, rather than giving half an answer in a message.',
        'If your situation is outside what we do: we will tell you, and say who to see instead.',
    ],

    'clinic_photo_heading' => 'The clinic',
    'clinic_photo_pending' => 'Photographs of the clinic will be added shortly.',

    'cta' => [
        'title' => 'The fastest way to start',
        'lead' => 'Booking online is confirmed immediately, and the first session is questions and answers.',
    ],
];
