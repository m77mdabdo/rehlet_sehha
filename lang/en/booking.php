<?php

declare(strict_types=1);

return [
    'title' => 'Book an appointment',
    'lead' => 'Pick the service and a time that suits you, and we will confirm your booking straight away.',
    'coming_soon' => 'If you have trouble booking, call or message us on WhatsApp and we will book it for you.',

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
        'lead' => 'Your booking is recorded. Keep your reference and the link below.',
        'reference' => 'Booking reference',
        'when' => 'Your appointment',
        'timezone' => 'Cairo time (:zone)',
        'status_note' => 'The booking is awaiting confirmation from the clinic.',
        'next_title' => 'What happens next',
        'next' => [
            'confirm' => 'The clinic will confirm the time during working hours.',
            'prepare' => 'If you have recent lab results, have them ready for the session.',
            'manage' => 'You can cancel or move the appointment from the “Manage this booking” link below.',
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

    'rights' => [
        'heading' => 'Your medical information',
        'lead' => 'This is what you wrote when you booked. You may read it, correct it, and delete it.',
        'view' => 'Show my information',
        'hide' => 'Hide',
        'blank' => 'You left this blank',
        'correct' => 'Correct this',
        'save' => 'Save changes',
        'cancel_edit' => 'Cancel',
        'correction_closed' => 'This cannot be edited after the session has taken place, so the record the doctor read during your consultation stays as it was. If something is wrong and needs correcting, please call us.',
        'updated_flash' => 'Your changes have been saved.',

        'erase' => 'Delete my medical information',
        'erase_confirm_title' => 'Delete your medical information?',
        'erase_removes_heading' => 'This will be permanently removed:',
        'erase_removes' => [
            'goal' => 'What you came for',
            'medications' => 'The medication you listed',
            'conditions' => 'Your conditions and chronic illness',
            'avoid' => 'The food you avoid',
            'note' => 'Your notes',
        ],
        'erase_keeps_heading' => 'This will be kept:',
        'erase_keeps' => [
            'appointment' => 'The appointment and its time — the clinic needs it for its records and its accounts',
            'identity' => 'Your name and mobile number — so we can reach you and know whose booking this is',
            'consent' => 'The date you gave consent — the date only, not the IP address it came from. It is the evidence consent was properly taken, which the clinic needs if it is ever asked.',
        ],
        'erase_upcoming_warning' => 'This appointment has not happened yet. If you delete this now, the doctor will arrive without your history and you will need to go through it again from the start.',
        'erase_keyword' => 'DELETE',
        'erase_keyword_label' => 'Type ":word" to confirm',
        'erase_keyword_hint' => 'This is permanent and immediate — there is no undo. This step exists so it cannot happen by accident.',
        'erase_keyword_mismatch' => 'Type ":word" exactly to continue.',
        'erase_confirm' => 'Yes, delete my medical information',
        'erase_cancel' => 'No, keep it',
        'erased_title' => 'Your medical information has been deleted.',
        'erased_on' => 'Deleted on :date. The appointment itself remains.',
        'erased_flash' => 'Your medical information has been deleted. The appointment itself is unchanged.',
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

    /*
    |--------------------------------------------------------------------------
    | WhatsApp
    |--------------------------------------------------------------------------
    |
    | wa.me links only. The site cannot send a WhatsApp message and never
    | claims it will — these open the patient's own WhatsApp with text already
    | typed, and she decides whether to send it.
    |
    | THE PREFILLED TEXT CARRIES THE REFERENCE AND NOTHING CLINICAL. It becomes
    | part of a URL, and a URL survives in browser history, in a screenshot,
    | and in the address bar during a screen share.
    |
    */
    'whatsapp' => [
        'send_details' => 'Send your booking details to the clinic',
        'send_details_hint' => 'Opens WhatsApp with a message ready, carrying your reference. You choose whether to send it.',
        'message_clinic' => 'Message the clinic on WhatsApp',
        'prefill_booking' => 'Hello, I booked through the website. My reference is :reference.',
        'prefill_manage' => 'Hello, I have a booking with reference :reference and would like to ask about it.',
        // Reference, date, time and mode — the whole appointment and nothing
        // about her health. This text becomes a URL.
        'prefill_record' => 'My booking at Rehlet Sehha:\nReference: :reference\nAppointment: :when (:zone)\nConsultation type: :mode',
    ],

    /*
    |--------------------------------------------------------------------------
    | Booking without an email address
    |--------------------------------------------------------------------------
    |
    | Email is optional and stays optional: a real share of patients here do
    | not use email, and requiring one costs the clinic those bookings.
    |
    | What is NOT optional is telling her what she gives up. Everything the
    | site sends — the confirmation, both reminders, and the only link that
    | lets her cancel without telephoning — travels by email and nothing else.
    | "Optional" reads as "we do not need it", not as "we cannot reach you".
    |
    */
    'no_email' => [
        'title' => 'Without an email address we cannot send you anything',
        'lead' => 'You have left the email field empty. That means you will not receive:',
        'losses' => [
            'confirmation' => 'Your booking confirmation',
            'reminders' => 'A reminder the day before, and another an hour before',
            'manage' => 'The link that lets you cancel or change the time without calling us',
        ],
        'fallback' => 'The clinic will telephone you on your mobile number instead.',
        'add' => 'Add my email',
        'continue' => 'Continue without email',
    ],

    'contact_preference' => [
        'email' => 'Email',
        'phone_only' => 'Phone only',
    ],

    'keepsake' => [
        'title' => 'This is the only record of this booking you will have',
        'lead' => 'You did not give us an email address, so no confirmation, reminder or link will be sent. Save these details now.',
        'reference_label' => 'Booking reference',
        'link_label' => 'Booking management link',
        'link_note' => 'Save this link. It will not be sent to you or anywhere else, and without it you cannot cancel or change the time except by calling.',
        'copy' => 'Copy',
        'copied' => 'Copied',
        'copy_reference' => 'Copy the reference',
        'copy_link' => 'Copy the link',
        'whatsapp' => 'Send the details to yourself on WhatsApp',
        'whatsapp_hint' => 'Opens WhatsApp with your booking details ready. Send it to the clinic or to yourself so you keep a copy.',
        'add_email_title' => 'Would you like the confirmation and reminders?',
        'add_email_hint' => 'Enter your email now and we will send the confirmation straight away, with reminders before the appointment.',
        'add_email_action' => 'Send me the confirmation',
        'add_email_saved' => 'Done. We have sent the confirmation to that address, and reminders will arrive before your appointment.',
    ],
];
