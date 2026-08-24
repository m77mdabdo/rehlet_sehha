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
    'eyebrow' => 'مش عارفة تبدئي منين؟',
    'title' => 'اعرفي الباقة المناسبة ليكِ',
    'lead' => 'تلات أسئلة، وهنقولك الباقة اللي غالبًا تناسب حالتك — وليه.',

    'privacy_note' => 'إجاباتك مش بتتسجّل ولا بتتبعت لحد. الحسبة كلها بتحصل على جهازك.',

    'progress' => 'سؤال :current من :total',
    'back' => 'رجوع',
    'restart' => 'ابدئي من جديد',

    'questions' => [
        [
            'id' => 'goal',
            'text' => 'إيه اللي جابك هنا؟',
            'options' => [
                ['id' => 'understand', 'text' => 'عايزة أفهم تحاليلي وحالتي الأول'],
                ['id' => 'start', 'text' => 'عايزة أبدأ خطة وأشوف نتيجة'],
                ['id' => 'condition', 'text' => 'عندي حالة معينة (تكيس، سكر، ضغط…) ومحتاجة متابعة'],
            ],
        ],
        [
            'id' => 'history',
            'text' => 'جربتي خطط غذائية قبل كده؟',
            'options' => [
                ['id' => 'never', 'text' => 'أول مرة'],
                ['id' => 'tried', 'text' => 'جربت وما كملتش'],
                ['id' => 'many', 'text' => 'جربت كذا مرة والوزن بيرجع'],
            ],
        ],
        [
            'id' => 'support',
            'text' => 'محتاجة متابعة قد إيه؟',
            'options' => [
                ['id' => 'once', 'text' => 'مرة واحدة تكفيني دلوقتي'],
                ['id' => 'month', 'text' => 'متابعة لشهر'],
                ['id' => 'longer', 'text' => 'متابعة مستمرة لفترة أطول'],
            ],
        ],
    ],

    /*
    | One entry per service slug. `why` is the part that matters: the patient
    | asked which package, and telling her only the name answers half of it.
    */
    'results' => [
        'lab-review' => [
            'name' => 'مراجعة تحاليل',
            'why' => 'إنتِ عايزة تفهمي الأول قبل ما تبدئي. الجلسة دي بنقرا فيها تحاليلك ونقولك يعني إيه، وإيه اللي محتاج يتظبط — من غير ما تلتزمي ببرنامج لسه.',
        ],
        'single-consultation' => [
            'name' => 'استشارة تغذية فردية',
            'why' => 'جلسة واحدة بنراجع فيها تاريخك وعاداتك وتخرجي بخطة تقدري تمشي عليها. مناسبة لو دي أول مرة وعايزة تبدئي صح من غير ارتباط طويل.',
        ],
        'one-month-programme' => [
            'name' => 'برنامج متابعة شهر',
            'why' => 'المشكلة مش في الخطة، المشكلة في الاستمرار عليها. شهر متابعة معناه إن الخطة بتتظبط معاكِ لما الحياة تتغير، مش إنك تمشي لوحدك بعد جلسة.',
        ],
        'three-months-programme' => [
            'name' => 'برنامج متابعة ثلاثة أشهر',
            'why' => 'لما الوزن بيرجع كل مرة، الفرق مش في خطة جديدة — الفرق في الوقت. تلات شهور بتدي فرصة إن العادات تثبت فعلًا، وإن الحالة تتابع مع الدكتورة على طول الطريق.',
        ],
    ],

    'result_heading' => 'الباقة اللي بنرشحها ليكِ',
    'result_why_heading' => 'ليه دي بالذات؟',
    'cta' => 'احجزي :package',
    'other_packages' => 'شوفي باقي الباقات',
    'not_binding' => 'الترشيح ده مجرد نقطة بداية. الدكتورة ممكن ترشح غيره بعد ما تشوف حالتك.',
];
