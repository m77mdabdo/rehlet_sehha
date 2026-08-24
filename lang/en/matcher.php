<?php

declare(strict_types=1);

/*
| Package matcher.
|
| Three questions that end in a recommendation and a booking link.
|
| This exists because the pricing section is where most visitors stop: they
| read four packages, cannot tell which one is theirs, and leave. The matcher
| answers the question they were actually asking — "which of these is for
| someone like me" — which is not a question a price list can answer.
|
| EVERY QUESTION, OPTION AND RESULT LIVES HERE, not in the Blade file or the
| JavaScript. The clinic will want to reword these, and rewording copy must
| never mean editing a script. The `slug` on each result is the only thing that
| couples this file to the database, and it is checked by a test.
|
| NOTHING IS COLLECTED. No answers are sent anywhere, nothing is stored, and no
| analytics fire on the choices. That is stated in the UI, in one line, because
| a health quiz that quietly profiles you is exactly what patients fear — and
| saying so is the only way she can know.
*/

return [
    'eyebrow' => 'Not sure where to start?',
    'title' => 'Find the package that fits you',
    'lead' => 'Three questions, and we will tell you which package probably suits you — and why.',

    'privacy_note' => 'Your answers are not recorded and not sent anywhere. All of this happens on your own device.',

    'progress' => 'Question :current of :total',
    'back' => 'Back',
    'restart' => 'Start again',

    'questions' => [
        [
            'id' => 'goal',
            'text' => 'What brought you here?',
            'options' => [
                ['id' => 'understand', 'text' => 'I want to understand my lab results and my situation first'],
                ['id' => 'start', 'text' => 'I want to start a plan and see a result'],
                ['id' => 'condition', 'text' => 'I have a condition (PCOS, diabetes, blood pressure…) and need follow-up'],
            ],
        ],
        [
            'id' => 'history',
            'text' => 'Have you followed an eating plan before?',
            'options' => [
                ['id' => 'never', 'text' => 'This is my first time'],
                ['id' => 'tried', 'text' => 'I tried one and did not finish it'],
                ['id' => 'many', 'text' => 'Several times, and the weight comes back'],
            ],
        ],
        [
            'id' => 'support',
            'text' => 'How much follow-up do you want?',
            'options' => [
                ['id' => 'once', 'text' => 'One session is enough for now'],
                ['id' => 'month', 'text' => 'A month of follow-up'],
                ['id' => 'longer', 'text' => 'Ongoing follow-up over a longer period'],
            ],
        ],
    ],

    /*
    | One entry per service slug. `why` is the part that matters: the patient
    | asked which package, and telling her only the name answers half of it.
    */
    'results' => [
        'lab-review' => [
            'name' => 'Lab review',
            'why' => 'You want to understand things before you commit. In this session we read your results with you and explain what they mean and what needs attention — without signing you up to a programme yet.',
        ],
        'single-consultation' => [
            'name' => 'Single nutrition consultation',
            'why' => 'One session going through your history and your habits, and you leave with a plan you can actually follow. Right if this is your first time and you want to start properly without a long commitment.',
        ],
        'one-month-programme' => [
            'name' => 'One month follow-up',
            'why' => 'The problem is rarely the plan — it is keeping to it. A month of follow-up means the plan gets adjusted with you as life changes, rather than you carrying on alone after one session.',
        ],
        'three-months-programme' => [
            'name' => 'Three month follow-up',
            'why' => 'When the weight comes back each time, the missing piece is not another plan — it is time. Three months gives habits a real chance to settle, with the practitioner following your case the whole way.',
        ],
    ],

    'result_heading' => 'The package we would suggest',
    'result_why_heading' => 'Why this one?',
    'cta' => 'Book :package',
    'other_packages' => 'See the other packages',
    'not_binding' => 'This is a starting point, not a decision. The practitioner may suggest something else once she has seen your case.',
];
