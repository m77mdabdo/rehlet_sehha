<?php

declare(strict_types=1);

return [
    'title' => 'Book an appointment',
    'lead' => 'Pick the service and a time that suits you, and we will send your confirmation on WhatsApp.',
    'coming_soon' => 'The booking form is being built. Until it is ready, call or message us on WhatsApp and we will book it for you.',

    'mode' => [
        'online' => 'Remote consultation',
        'clinic' => 'At the clinic',
    ],

    'fields' => [
        'name' => 'Name',
        'phone' => 'Mobile number',
        'email' => 'Email address',
        'service' => 'Service',
        'mode' => 'Consultation type',
        'date' => 'Date',
        'time' => 'Time',
        'birth_date' => 'Date of birth',
        'goal' => 'What do you need help with?',
        'medications' => 'Medication you take',
        'conditions' => 'Conditions or chronic illness',
        'avoid_foods' => 'Food you avoid',
        'note' => 'Anything you would like to add?',
    ],

    'placeholders' => [
        'name' => 'Your full name',
        'phone' => '01xxxxxxxxx',
        'email' => 'Optional — so we can send you the plan',
        'medications' => 'Name and dose if you know it. Leave blank if none.',
        'conditions' => 'Diabetes, blood pressure, thyroid, and so on. Leave blank if none.',
        'avoid_foods' => 'Allergies, dislikes, or fasting.',
        'note' => 'Anything you would like the doctor to know beforehand.',
    ],

    'submit' => 'Confirm booking',
    'optional' => 'optional',

    'steps' => [
        'service' => 'Service',
        'time' => 'Time',
        'details' => 'Your details',
        'done' => 'Confirmation',
        'of' => 'Step :current of :total',
    ],

    'actions' => [
        'next' => 'Next',
        'back' => 'Back',
        'change' => 'Change',
        'choose_service' => 'Choose a package',
        'choose_time' => 'Choose a time',
    ],

    'goals' => [
        'weight_management' => 'Weight management',
        'medical_condition' => 'A medical condition',
        'sports_performance' => 'Sports nutrition',
        'pregnancy' => 'Pregnancy or breastfeeding',
        'child_nutrition' => 'Child nutrition',
        'lab_review' => 'Reading lab results',
        'general_health' => 'General health',
        'other' => 'Something else',
    ],

    'time' => [
        'title' => 'Pick a day and a time',
        'lead' => 'All times are Cairo time.',
        'no_slots' => 'No times available on this day. Try another.',
        'no_days' => 'No times are available right now. Get in touch and we will find you one.',
        'timezone_note' => 'All times shown are Cairo time.',
        'closed' => 'Closed',
    ],

    'details' => [
        'title' => 'Your details',
        'lead' => 'This reaches the doctor before your session so she can prepare for your case.',
        'patient_heading' => 'About you',
        'intake_heading' => 'Medical information',
        'intake_note' => 'Everything here except the goal is optional. Write only what you know.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent
    |--------------------------------------------------------------------------
    |
    | Deliberately plain. This is the sentence that has to be true, and that a
    | patient must be able to understand without a lawyer: WHAT is stored, that
    | it is ENCRYPTED, and that it is used ONLY for the consultation.
    |
    | No pre-ticked box, no "by continuing you agree", no consent buried inside
    | a terms-of-service link. The patient ticks it or the form does not submit.
    |
    */
    'consent' => [
        'label' => 'Consent to storing my medical information',
        'text' => 'I agree that the medical information I have entered here will be stored encrypted, used only for my consultation and follow-up with the doctor, and shared with no one else.',
        'link' => 'Read the privacy policy',
        'required_note' => 'We need your consent before we can complete the booking.',
    ],

    'summary' => [
        'title' => 'Booking summary',
        'service' => 'Package',
        'mode' => 'Consultation type',
        'when' => 'Time',
        'price' => 'Price',
        'duration' => 'Length',
    ],

    'confirmation' => [
        'title' => 'Booked',
        'lead' => 'Your booking is recorded, and your confirmation will arrive on WhatsApp.',
        'reference' => 'Booking reference',
        'when' => 'Your appointment',
        'timezone' => 'Cairo time (:zone)',
        'status_note' => 'The booking is awaiting confirmation from the clinic.',
        'next_title' => 'What happens next',
        'next' => [
            'confirm' => 'We will confirm the time on WhatsApp during working hours.',
            'prepare' => 'If you have recent lab results, have them ready for the session.',
            'manage' => 'You can cancel or move the appointment from the link in your confirmation message.',
        ],
        'manage_link' => 'Manage this booking',
        'manage_note' => 'Keep this link. It is the only way to manage the booking without calling us.',
    ],

    'manage' => [
        'title' => 'Your booking',
        'lead' => 'You can cancel or move your appointment here.',
        'cancelled_by_patient' => 'Cancelled by the patient from the self-service page',
        'status' => 'Status',
        'cancel' => 'Cancel booking',
        'cancel_confirm' => 'Are you sure you want to cancel?',
        'reschedule' => 'Change the time',
        'reschedule_title' => 'Pick a new time',
        'confirm_reschedule' => 'Confirm the new time',
        'keep' => 'Keep it as it is',
        'cancelled_flash' => 'Your booking is cancelled. The slot is available to someone else again.',
        'rescheduled_flash' => 'Your appointment has been moved.',
        'too_late_title' => 'It is close to the appointment',
        'too_late' => 'Appointments cannot be cancelled or moved online less than :hours hour(s) beforehand. Call us and we will help.',
        'already_cancelled' => 'This booking has been cancelled.',
        'past' => 'This appointment has passed.',
    ],

    'errors' => [
        'service_unavailable' => 'That package is not available right now. Please choose another.',
        'mode_unavailable' => 'That consultation type is not available right now.',
        'slot_required' => 'Please choose a time first.',
        'slot_expired' => 'That time is no longer available. Please choose another.',
        'slot_taken' => 'Someone booked that time seconds before you. Please pick another — everything you typed is still here and nothing has been sent anywhere.',
        'phone_invalid' => 'Please enter a valid Egyptian mobile number, like 01012345678.',
        'consent_required' => 'We need your consent to store your medical information before we can continue.',
        'too_many_attempts' => 'Too many attempts in a short time. Please try again in :minutes minute(s).',
        'too_many_for_phone' => 'This number has reached the booking limit. Try again in :minutes minute(s), or call us.',
        'too_fast' => 'That was submitted very quickly. Please check your details and send again.',
        'rejected' => 'We could not process this request. If this is a mistake, please call us.',
    ],
];
