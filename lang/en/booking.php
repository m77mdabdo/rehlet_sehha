<?php

declare(strict_types=1);

return [
    'title' => 'Book an appointment',
    'lead' => 'Pick the service and a time that suits you, and we will send your confirmation on WhatsApp.',
    'mode' => [
        'online' => 'Remote consultation',
        'clinic' => 'At the clinic',
    ],
    'fields' => [
        'name' => 'Name',
        'phone' => 'Mobile number',
        'email' => 'Email address',
        'service' => 'Service',
        'date' => 'Date',
        'time' => 'Time',
        'notes' => 'Anything you would like to add?',
    ],
    'submit' => 'Confirm booking',
    'optional' => 'optional',
];
