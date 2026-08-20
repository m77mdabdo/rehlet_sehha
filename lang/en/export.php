<?php

declare(strict_types=1);

/*
| Copy for the downloadable record.
|
| This document is the ACCESS RIGHT under Egyptian law 151/2020 in substance,
| not a convenience. It has to be readable on its own, years from now, by
| someone who no longer remembers this clinic's website.
*/

return [
    'title' => 'A copy of your record',
    'lead' => 'This is a complete copy of what we hold about this appointment. You can keep it, print it, or take it to another doctor.',
    'generated_on' => 'Generated on',

    'sections' => [
        'appointment' => 'Appointment details',
        'patient' => 'Your details',
        'intake' => 'The medical information you provided',
        'consent' => 'Consent',
    ],

    'no_intake' => 'No medical information is recorded against this appointment.',
    'erased' => 'The medical information for this appointment was erased at your request on :date. The appointment itself remains in the clinic’s records, but everything you wrote about your health has been permanently removed.',
    'erased_on' => 'Erased on',
    'consent_given_on' => 'Consent recorded on',

    'rights_note' => 'You may view, correct or delete your data at any time from the “Manage this booking” link in your confirmation message. If you have lost the link, call us.',

    'download' => 'Download a copy of your record',
    'download_hint' => 'A file you can keep, print, or give to another doctor.',
];
