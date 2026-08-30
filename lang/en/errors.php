<?php

declare(strict_types=1);

// See the Arabic file: every page answers "what do I do now", and every one
// carries a reassurance line, because the fear an error causes on a medical
// site is that something has been lost or booked twice.

return [
    'code' => 'Error :code',
    'home' => 'Home',
    'book' => 'Book an appointment',
    'call_us' => 'Need somebody? Call us:',

    'not_found' => [
        'title' => 'This page does not exist',
        'body' => 'The link may be old, or a character may be missing. If you were looking for your appointment, its link is in your confirmation email.',
        'reassure' => 'Your appointment is unaffected. Only the page is missing.',
    ],

    'forbidden' => [
        'title' => 'This page is not for you',
        'body' => 'That link opens for one person only. If you believe you should be able to see it, get in touch and we will look.',
        'reassure' => 'Nothing about your booking has changed.',
    ],

    'expired' => [
        'title' => 'This page was open too long',
        'body' => 'For your security, pages left open for a long time close themselves. Open it again and carry on.',
        'reassure' => 'What you typed was not submitted, and you can do it again now.',
    ],

    'too_many' => [
        'title' => 'Too many attempts, too quickly',
        'body' => 'Wait a little and try again. If this happened while you were booking, call us and we will book it for you.',
        'reassure' => 'This is a safety measure, not a problem with your account.',
    ],

    'server' => [
        'title' => 'Something on our side is not working',
        'body' => 'This is our fault, not yours. Try again shortly, and if it is urgent please call.',
        'reassure' => 'If you were booking, it either completed or it did not — nothing was booked twice.',
    ],

    'maintenance' => [
        'title' => 'We are updating the site',
        'body' => 'It will be back within a few minutes. If you need to book or change an appointment now, call us and we will do it for you.',
        'reassure' => 'Every existing appointment is unaffected.',
    ],
];
