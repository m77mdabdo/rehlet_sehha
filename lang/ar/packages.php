<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The packages page
|------------------------------------------------------------------------------
|
| NOT the homepage section rewritten. The homepage shows four cards and a
| price; this page is where somebody decides. Everything here answers a
| question the cards leave open — what is actually included, what happens in
| the weeks between sessions, how payment works, and what happens when life
| gets in the way.
|
| A page that repeated the section verbatim would rank below it and deserve to.
|
| THE COMPARISON MATRIX IS KEYED BY SERVICE SLUG. Prices, session counts and
| durations are NOT here — those are read from the services table so the table
| and the cards can never disagree about a number. What lives here is the
| wording for things the schema does not model. PackagesPageTest fails if an
| active package has no entry, so a new package cannot render a row of blanks.
|
*/

return [
    'meta_title' => 'باقات المتابعة والأسعار — رحلة صحة',
    'meta_description' => 'أسعار الاستشارة وبرامج المتابعة بالتفصيل: إيه اللي داخل في كل باقة، إيه اللي بيحصل بين الجلسات، طرق الدفع، وسياسة التأجيل والإلغاء.',

    'eyebrow' => 'الباقات والأسعار',
    'title' => 'اختاري الباقة اللي تناسب حالتك',
    'lead' => 'أربع طرق تبدأي بيها، من استشارة واحدة لحد متابعة ثلاثة شهور. الفرق بينهم مش في «كمية» الخدمة، ده في طول الفترة اللي بنعدّل فيها الخطة معاكِ.',

    'comparison' => [
        'title' => 'مقارنة كاملة بين الباقات',
        'lead' => 'نفس الأسعار اللي على الصفحة الرئيسية، بس هنا بالتفصيل اللي بيفرق وقت القرار.',
        'scroll_hint' => 'الجدول بيتحرك يمين وشمال على الشاشات الصغيرة.',
        'aria' => 'مقارنة بين باقات المتابعة',
        'feature_column' => 'المقارنة',
        'rows' => [
            'price' => 'السعر',
            'sessions' => 'عدد الجلسات',
            'duration' => 'مدة الجلسة',
            'format' => 'مكان الجلسة',
            'plan' => 'الخطة المكتوبة',
            'between' => 'بين الجلسات',
            'labs' => 'مراجعة التحاليل',
            'adjust' => 'تعديل الخطة',
            'suits' => 'مناسبة لمين',
        ],
        /*
         * PRESENTATION ONLY — none of this changes what the table says.
         *
         * absent_markers: the words a matrix value starts with when the answer
         * is "no". Two rows have a real yes/no in them — support between
         * sessions, and whether the plan gets adjusted — and in those a symbol
         * reads faster than a sentence. The sentence still renders in full; the
         * marker only decides whether it gets a tick or a dash beside it.
         *
         * Kept as a list here rather than matched against a hardcoded Arabic
         * string in a Blade file, so rewording the copy is a one-line change in
         * the same file as the copy. PackagesPageTest fails if a matrix value
         * reads as a negation but starts with something not listed, so drift is
         * loud rather than silent.
         */
        'recommended' => 'الأكثر طلباً',
        // Both spellings: the clinic writes either, and TranslationParityTest
        // requires the two locales to have the same shape here anyway.
        'absent_markers' => ['مفيش', 'مافيش'],
        'cta' => 'احجزي دي',
    ],

    'matrix' => [
        'single-consultation' => [
            'format' => 'في العيادة أو أونلاين',
            'plan' => 'خطة مكتوبة تستلميها بعد الجلسة',
            'between' => 'رسالة متابعة واحدة بعد أسبوع',
            'labs' => 'بنراجع تحاليل موجودة معاكِ',
            'adjust' => 'مفيش — الخطة بتتسلّم مرة واحدة',
            'suits' => 'لو عايزة تبدأي صح من غير التزام طويل، أو محتاجة رأي في خطة شغّالة معاكِ',
        ],
        'one-month-programme' => [
            'format' => 'في العيادة أو أونلاين',
            'plan' => 'خطة مكتوبة بعد كل جلسة',
            'between' => 'واتساب مفتوح للأسئلة القصيرة طول الشهر',
            'labs' => 'بنراجع تحاليل موجودة معاكِ',
            'adjust' => 'أسبوعياً، حسب اللي حصل فعلاً',
            'suits' => 'لو الأكل بيتغيّر لكن محتاج ضبط، أو عندك مناسبة أو سفر قريب',
        ],
        'three-months-programme' => [
            'format' => 'في العيادة أو أونلاين',
            'plan' => 'خطة مكتوبة بعد كل جلسة',
            'between' => 'واتساب مفتوح، ومراجعة سريعة كل أسبوعين',
            'labs' => 'مراجعة تحاليل مرتين خلال البرنامج',
            'adjust' => 'أسبوعياً، مع مراجعة أكبر كل شهر',
            'suits' => 'لو في حالة مزمنة زي السكري أو تكيس المبايض، أو جرّبتي قبل كده ورجعتي',
        ],
        'lab-review' => [
            'format' => 'أونلاين غالباً',
            'plan' => 'تقرير مكتوب بالنتيجة والخطوة اللي بعدها',
            'between' => 'مفيش متابعة بعدها',
            'labs' => 'دي الخدمة نفسها',
            'adjust' => 'مفيش',
            'suits' => 'لو معاكِ تحاليل ومش عارفة تعمليها إيه، ولسه مش جاهزة لبرنامج',
        ],
    ],

    'statement' => 'الباقة الغالية مش أحسن باقة. أحسن باقة هي اللي هتقدري تكمّليها.',

    'between' => [
        'eyebrow' => 'الجزء اللي محدش بيتكلم عنه',
        'title' => 'اللي بيحصل بين الجلسات',
        'lead' => 'الجلسة نفسها ساعة كل أسبوع أو كل أسبوعين. باقي الوقت هو اللي بيحدد النتيجة، وده اللي بنشتغل عليه.',
        'steps' => [
            [
                'title' => 'الخطة بتوصلك مكتوبة',
                'body' => 'مش بتخرجي من الجلسة فاكرة كلام. بتستلمي ملف فيه اللي اتفقنا عليه، بالكميات والبدائل ومواعيد الأكل اللي تناسب يومك، وتقدري تفتحيه في أي وقت.',
            ],
            [
                'title' => 'الأسئلة القصيرة ليها مكان',
                'body' => 'مش كل سؤال محتاج ميعاد. «ده ينفع بدل ده؟» أو «العزومة النهاردة أعمل فيها إيه؟» بيتبعتوا على الواتساب وبيترد عليهم في نفس اليوم عادةً.',
            ],
            [
                'title' => 'اللي مشيش بنغيّره',
                'body' => 'لو أكلة معينة مش بتتنفذ، مش بنكرّرها في الخطة الجاية. الجلسة اللي بعدها بتبدأ من اللي حصل فعلاً مش من اللي كان مفروض يحصل.',
            ],
            [
                'title' => 'الانقطاع مش نهاية',
                'body' => 'لو أسبوع عدّى وحش، ده مش سبب تسيبي. الجلسة الجاية بتتعامل معاه كمعلومة عن حياتك مش كفشل، وبنعدّل الخطة عشان تستحمل الأسبوع ده لما يرجع.',
            ],
        ],
    ],

    'terms' => [
        'eyebrow' => 'الشروط بوضوح',
        'title' => 'الدفع والتأجيل',
        'lead' => 'مكتوبة هنا عشان تكون معروفة قبل الحجز مش بعده.',
        'payment' => [
            'title' => 'الدفع',
            'items' => [
                'بتدفعي الجلسة الأولى بس عشان تجرّبي، وباقي الباقة على دفعتين.',
                'كاش في العيادة، أو إنستاباي أو محفظة موبايل قبل الجلسة الأونلاين.',
                'مفيش رسوم إضافية على أي طريقة دفع، ومفيش رسوم فتح ملف أو حجز.',
                'الإيصال بيوصلك على الواتساب في كل الحالات.',
            ],
        ],
        'cancellation' => [
            'title' => 'التأجيل والإلغاء',
            'items' => [
                'التأجيل مجاني قبل الموعد بأربعة وعشرين ساعة، من لينك في رسالة التأكيد.',
                'أقل من كده الجلسة بتتحسب من الباقة، إلا في الظروف الصحية.',
                'الإلغاء قبل ما الباقة تبدأ بيرجّع المبلغ كامل.',
                'مدة الباقة بتتمدّ لو جلسة اتأجلت، طالما رجعتي في خلال شهر.',
            ],
        ],
        'note' => 'لو حصل ظرف مش مكتوب هنا، كلّمينا. الشروط دي عشان تحمي وقت الاتنين مش عشان تتمسك عليكِ.',
    ],

    'faq' => [
        'eyebrow' => 'قبل ما تحجزي',
        'title' => 'أسئلة عن الحجز والدفع',
        'lead' => 'الأسئلة اللي بتتسأل وقت القرار. لو سؤالك مش هنا، ابعتيه وهنرد.',
        'empty' => 'الأسئلة هتكون متاحة قريب.',
    ],

    'cta' => [
        'title' => 'مش متأكدة أنهي باقة؟',
        'lead' => 'ابدأي باستشارة فردية. لو قررتي تكمّلي، سعرها بيتحسب من الباقة اللي هتختاريها.',
    ],
];
