<?php

declare(strict_types=1);

/*
| Copy for every notification the clinic sends.
|
| SUBJECT LINES CARRY NO CLINICAL CONTENT. Not a goal, not a condition, not a
| medication, not the name of a service that implies any of them. A subject
| line is rendered on a locked phone screen, on a smartwatch, and in a preview
| pane on a shared desk — none of which the patient chose. The reference number
| and the date are enough for someone to recognise their own appointment, and
| mean nothing to anyone reading over their shoulder.
|
| The body may name the service, because opening the mail is an act the patient
| controls.
*/

return [

    /*
    |--------------------------------------------------------------------------
    | Shared furniture
    |--------------------------------------------------------------------------
    */

    'greeting' => 'Hello :name,',
    'greeting_generic' => 'Hello,',

    'facts' => [
        'reference' => 'Reference',
        'service' => 'Service',
        'when' => 'Appointment',
        'mode' => 'Consultation type',
        'price' => 'Price',
        'timezone' => 'Cairo time (:zone)',
    ],

    'manage' => [
        'label' => 'Manage this booking',
        'button' => 'Cancel or change this appointment',
        'hint' => 'This link is yours alone — please do not forward it. Anyone who has it can view and cancel your booking.',
    ],

    'call_us' => 'Need to speak to us? :phone',

    'footer' => [
        'automated' => 'This message was sent automatically from :address. If you reply, your message reaches the clinic team at :reply.',
        'rights' => 'You may view, correct or delete your data at any time from the booking management page.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient — booking confirmed
    |--------------------------------------------------------------------------
    */

    'confirmed' => [
        'subject' => 'Your booking is recorded — :reference',
        'heading' => 'Your booking is recorded',
        'lead' => 'We have your booking. Here are the details in full — please keep this message.',
        'pending_note' => 'The booking is awaiting confirmation from the clinic. We will be in touch if anything changes.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient — reminders
    |--------------------------------------------------------------------------
    */

    'reminder_24h' => [
        'subject' => 'Your appointment is tomorrow — :reference',
        'heading' => 'Your appointment is tomorrow',
        'lead' => 'A reminder that your appointment is less than 24 hours away.',
        'note' => 'If the time no longer suits you, change or cancel it from the link below — that gives the slot to someone else in good time.',
    ],

    'reminder_1h' => [
        'subject' => 'Your appointment is in an hour — :reference',
        'heading' => 'Your appointment is in an hour',
        'lead' => 'Your appointment starts in about an hour.',
        'online_note' => 'This is an online consultation. Find somewhere quiet with a good connection; we will send the joining link before the appointment.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient — cancellation and reschedule
    |--------------------------------------------------------------------------
    */

    'cancelled' => [
        'subject' => 'Booking cancelled — :reference',
        'heading' => 'Booking cancelled',
        'lead' => 'This booking has been cancelled. These were its details.',
        'rebook' => 'If you would like to book again, we are here.',
        'rebook_button' => 'Book a new appointment',
    ],

    'rescheduled' => [
        'subject' => 'Your appointment has moved — :reference',
        'heading' => 'Your appointment has moved',
        'lead' => 'Your appointment has been moved. Here are both the old and the new times.',
        'old_time' => 'Previous time',
        'new_time' => 'New time',
        'note' => 'The old time has gone back into the calendar, and the new one is held in your name.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Clinic — internal alerts
    |--------------------------------------------------------------------------
    |
    | Sent to the clinic, so written for the clinic. These DO carry the intake
    | summary: the practitioner needs it to prepare, and it is going to the
    | address that already holds the patient's file.
    |
    */

    'new_booking' => [
        'subject' => 'New booking — :reference',
        'heading' => 'New booking',
        'lead' => 'A new booking came in from the website.',
        'patient' => 'Patient details',
        'patient_name' => 'Name',
        'patient_phone' => 'Mobile',
        'patient_email' => 'Email',
        'no_email' => 'No email address — no confirmation was sent. Call her.',
        'intake' => 'Medical information',
        'no_intake' => 'No medical information recorded.',
        'booked_at' => 'Booked at',
        'locale' => 'Booking language',
    ],

    'cancelled_alert' => [
        'subject' => 'Booking cancelled — :reference',
        'heading' => 'A patient cancelled',
        'lead' => 'This booking was cancelled from the management page, and the slot has gone back into the calendar.',
        'reason' => 'Reason',
    ],

    'daily_schedule' => [
        'subject' => "Today's appointments — :date",
        'heading' => "Today's appointments",
        'lead' => 'Appointments for :date, Cairo time.',
        'empty' => 'No appointments today.',
        'count' => 'Appointments: :count',
        'time' => 'Time',
        'patient' => 'Patient',
        'service' => 'Service',
        'status' => 'Status',

        /*
         * The call list. Patients booked for TOMORROW who gave no email
         * address, so nothing has reached them and nothing will: no
         * confirmation, no reminder the day before, none an hour before.
         * Someone has to ring them.
         */
        'call_heading' => 'Calls to make',
        'call_lead' => 'These are tomorrow\'s appointments (:date) for patients who gave no email address. They received no confirmation and will get no reminder — someone needs to call them.',
        'call_empty' => 'Every appointment tomorrow has an email address. No calls needed.',
        'call_count' => 'Calls: :count',
        'phone' => 'Mobile',
    ],

    /*
    |--------------------------------------------------------------------------
    | Clinic — delivery failure
    |--------------------------------------------------------------------------
    */

    'delivery_failed' => [
        'subject' => 'Alert: a confirmation did not arrive — :reference',
        'heading' => 'A message to a patient failed',
        'lead' => 'We tried to send a message to a patient and every attempt failed. Someone has booked and does not know it worked.',
        'action' => 'Call the patient and confirm the booking yourself.',
        'template' => 'Message',
        'recipient' => 'Recipient',
        'error' => 'Error',
    ],
    'review_requested' => [
        'subject' => 'Would you tell us how it went?',
        'heading' => 'Your view matters',
        'lead' => 'It has been a few days since your session. If you would like to say how it went, this link opens a short page — a rating and a couple of lines, a minute at most.',
        'button' => 'Write your review',
        'consent_note' => 'Nothing is published automatically. There is a box on the page where you choose whether it may appear on the site, and it is not ticked. Leave it unticked and your review reaches us only.',
        'no_obligation' => 'And if you would rather not write anything, that is completely fine — we will not ask again about this visit.',
    ],
];
