<?php

declare(strict_types=1);

return [
    // No premises — see the note on the Arabic string. Cairo remains elsewhere
    // in the site as a time zone, which is a different claim and a true one.
    'about' => 'An online clinical nutrition practice. We help you reach your goal with a realistic plan built around your case and your lab work, not a template.',
    'services_heading' => 'Services',
    'links_heading' => 'Links',
    'contact_heading' => 'Get in touch',
    'whatsapp' => 'WhatsApp',
    'phone' => 'Phone',
    'email' => 'Email',
    'address' => 'Address',
    'disclaimer_heading' => 'Medical disclaimer',
    'disclaimer' => 'The content here is for general awareness and does not replace consulting your treating physician',

    // See the Arabic file. Assembled by App\Support\OpeningHours from the
    // working_hours rows rather than typed.
    'days' => [
        6 => 'Saturday',
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
    ],
    // English needs no contraction; the key exists so both locales resolve
    // through the same code path.
    'days_to' => [
        6 => 'Saturday',
        0 => 'Sunday',
        1 => 'Monday',
        2 => 'Tuesday',
        3 => 'Wednesday',
        4 => 'Thursday',
        5 => 'Friday',
    ],
    'day_separator' => ' and ',
    'day_range' => ':from to :to',
    'hours_range' => ':days, :open – :close',
    'hours_days' => ':days, :open – :close',
    'closed_on' => 'Closed on :days',
    'am' => 'am',
    'pm' => 'pm',

];
