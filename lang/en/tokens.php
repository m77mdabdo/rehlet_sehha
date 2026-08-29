<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| Expired links
|------------------------------------------------------------------------------
|
| Shown when a token we RECOGNISE has aged out. An unrecognised token still
| 404s, so this copy never has to serve somebody probing for a valid one — it
| only ever speaks to a patient holding a real email that has gone stale.
|
| It says what happened, why, and how to reach a person. It does not apologise
| for the expiry: a link that stops working is the correct behaviour for a
| credential anybody who has the URL can use.
*/

return [
    'expired' => [
        'eyebrow' => 'This link has expired',
        'title' => 'This link no longer works',
        'lead' => 'The links we email you expire after a while, because anybody holding the URL can use them — so they should not work forever.',
        'appointment' => 'An appointment link keeps working until two weeks after the appointment itself.',
        'review' => 'A review invitation stays open for 30 days from the day we sent it.',
        'book' => 'Book a new appointment',
        'whatsapp' => 'Message us on WhatsApp',
        'note' => 'If you need something from an older appointment, get in touch and we will help.',
    ],
];
