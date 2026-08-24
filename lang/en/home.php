<?php

declare(strict_types=1);

return [
    'meta_title' => 'Rehlet Sehha — Clinical Nutrition',
    'meta_description' => 'A nutrition plan built around your case, your kitchen and your budget. Book online or at the clinic.',
    'og_alt' => 'Rehlet Sehha — clinical nutrition practice',

    'hero' => [
        'eyebrow' => 'Clinical nutrition',
        'title' => 'An eating plan you can actually stay on',
        'lead' => 'No punishing diets, no expensive ingredients. We build a plan from the food already in your kitchen, around the hours you actually keep, and keep adjusting it until it becomes a habit.',
        'cta' => 'Book an appointment',
        'secondary_cta' => 'See the packages',

        // Factual, and none of them a promise about an outcome.
        'chips' => [
            'licensed' => 'Licensed clinic',
            'online' => 'Online or in clinic',
            'plan' => 'A written plan after every session',
        ],

        'case_card' => [
            'label' => 'Illustrative example',
            'title' => 'Week 8 follow-up',
            'subtitle' => 'Three month programme',
            'metrics' => [
                'energy' => ['label' => 'Energy levels', 'value' => 'Better than at the start'],
                'sleep' => ['label' => 'Sleep consistency', 'value' => '6 nights out of 7'],
                'labs' => ['label' => 'Lab results', 'value' => 'Within normal range'],
            ],
            'adherence' => 'Plan adherence',
            'note' => 'An illustration, not a real patient. Progress here is measured by energy, sleep, lab results and adherence.',
        ],
    ],

    'stats' => [
        'title' => 'The practice in numbers',
        'cases' => 'cases supported',
        'years' => 'years in clinical nutrition',
        'rating' => 'patient rating',
        'support_days' => 'days of follow-up a week',
        'rating_suffix' => 'out of 5',
    ],

    'specialties' => [
        'eyebrow' => 'What we cover',
        'title' => 'The areas we work in',
        'lead' => 'These are the clinical areas the practice covers. If yours is on the list, start with a consultation and we will find the right plan.',
        'empty' => 'Areas will be listed shortly.',
    ],

    'packages' => [
        'eyebrow' => 'Packages',
        'title' => 'Start wherever suits you',
        'lead' => 'A single consultation to find out where you stand, or a full programme if you want a result that lasts.',
        'featured' => 'Most chosen',
        'duration' => 'Session length',
        'sessions' => 'Sessions',
        'cta' => 'Choose this',
        'empty' => 'Packages will be available shortly.',
    ],

    'how_it_works' => [
        'eyebrow' => 'How it works',
        'title' => 'How we work together',
        'lead' => 'Four clear steps, from booking to the point where the plan is simply part of your day.',
        'steps' => [
            'one' => [
                'title' => 'Book your appointment',
                'body' => 'Pick the package and a time that suits you, online or at the clinic, and your confirmation appears straight away.',
            ],
            'two' => [
                'title' => 'We understand your case',
                'body' => 'We go through your medical history, your habits and any lab work you have. This session is questions and answers, not measurements.',
            ],
            'three' => [
                'title' => 'You get your plan',
                'body' => 'A written plan built from the food in your kitchen, with substitutes for every item so nothing stalls when something is unavailable.',
            ],
            'four' => [
                'title' => 'We adjust it together',
                'body' => 'Follow-ups adjust the plan to how your body responds and to what you actually managed, until it becomes a habit.',
            ],
        ],
    ],

    'stories' => [
        'eyebrow' => 'Stories',
        'title' => 'People who have walked this',
        'lead' => 'In their own words, about what changed.',
        'empty' => 'Stories will be published shortly.',
        'rating_label' => 'Rated :count out of 5',
    ],

    'articles' => [
        'eyebrow' => 'Articles',
        'title' => 'Something to read meanwhile',
        'lead' => 'Practical writing on food, lab work and habits — no promises, no scare stories.',
        'read_more' => 'Read the article',
        'reading_time' => ':count min read',
        'empty' => 'Articles will be published shortly.',
    ],

    'faq' => [
        'eyebrow' => 'FAQ',
        'title' => 'Questions we get a lot',
        'lead' => 'If yours is not here, send us a message on WhatsApp and we will answer it.',
        'empty' => 'Questions will be added shortly.',
    ],

    'booking_cta' => [
        'title' => 'Ready to start?',
        'lead' => 'Book your consultation now, and choose whether to come to the clinic or meet online.',
        'cta' => 'Book an appointment',
        'note' => 'The clinic confirms the time within working hours.',
    ],

    'contact' => [
        'eyebrow' => 'Contact',
        'title' => 'Want to ask first?',
        'lead' => 'Call us or send a message, and we will answer without you booking anything.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Video library
    |--------------------------------------------------------------------------
    |
    | The gallery loads thumbnails only. No YouTube iframe and no YouTube
    | script until a patient actually presses play — see the section component
    | for why that matters on a medical site.
    |
    */
    'videos' => [
        'eyebrow' => 'Watch',
        'title' => 'Videos from the clinic',
        'lead' => 'Plain answers to the questions we are asked most, from the practitioner herself.',
        'empty' => 'There are no videos available at the moment.',
        'play' => 'Play: :title',
        'close' => 'Close',
        'privacy' => 'Nothing loads from YouTube until you press play.',
        'duration' => 'Length',
        'featured' => 'Latest',
    ],
];
