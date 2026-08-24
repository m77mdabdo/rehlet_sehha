<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The contact page
|------------------------------------------------------------------------------
|
| NO CONTACT FORM, DELIBERATELY, and the page says so rather than leaving a
| visitor hunting for one. A patient who fills in a "get in touch" box has done
| something that feels like progress and is not — she then waits, for a reply
| that competes with the booking she actually wanted. Every route offered here
| is one she controls and gets an answer from: booking, WhatsApp, the phone.
|
| NO EMBEDDED MAP EITHER. An embed is a third-party request on a site built not
| to track its visitors, made to render an address we can render as text. The
| address block below is real text: selectable, translatable, readable by a
| screen reader, and free.
|
| Every detail comes from config/clinic.php. Dropping in the real address is a
| one-line change there, and nothing on this page needs touching.
|
*/

return [
    'meta_title' => 'Contact — Rehlet Sehha',
    'meta_description' => 'The clinic address, opening hours, WhatsApp and phone. Online booking is available around the clock without a phone call.',

    'eyebrow' => 'Contact',
    'title' => 'How to reach us',
    'lead' => 'The fastest way to start is to book online — it is confirmed immediately, with no waiting for a reply. If you have a question before booking, WhatsApp is quickest.',

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

    'address_heading' => 'Address',
    'address_note' => 'The clinic is in :address. If you need exact directions, message us on WhatsApp and we will send the location.',
    'address_pending' => 'TODO_COPY — the clinic\'s full address: street, building number, floor, and the nearest landmark.',

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
