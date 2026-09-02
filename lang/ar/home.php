<?php

declare(strict_types=1);

return [
    'meta_title' => 'رحلة صحة — عيادة تغذية علاجية',
    'meta_description' => 'خطة غذائية مبنية على حالتك أنت، من أكل بيتك وبميزانيتك. احجزي استشارتك أونلاين أو في العيادة.',
    'og_alt' => 'رحلة صحة — عيادة تغذية علاجية',

    'hero' => [
        'eyebrow' => 'تغذية علاجية',
        'title' => 'خطة أكل تقدري تكملي عليها',
        'lead' => 'مش رجيم قاسي ولا أكل غالي. نبني مع بعض خطة من أكل بيتك، تناسب مواعيد يومك وميزانيتك، ونفضل نظبطها معاكِ لحد ما تبقى عادة.',
        'cta' => 'احجزي موعدك',
        'secondary_cta' => 'شوفي الباقات',

        // Credential chips. Short, factual, and none of them a promise about
        // an outcome — "خطة مكتوبة" is something we do, "تخسي ١٠ كيلو" is not.
        /*
         | THE SCENE-SYNCED HERO COPY.
         |
         | Four lines, one per shot of the hero clip, changing ON the cut and
         | never on a timer of their own — the timings live in config/hero.php
         | and the beat keys below are what tie the two together.
         |
         | THEY ARE ONE SENTENCE BROKEN ACROSS FOUR PICTURES, not four slogans.
         | `chop` says the plan is CUT to your day while the picture is a knife
         | on a board; `plate` refuses to moralise the finished meal at exactly
         | the moment an advert would promise something. Read in order they are
         | continuous, and they loop cleanly from `plate` back to `wash`.
         |
         | `cook` IS ALSO THE STATIC LINE — the one sentence a reduced-motion,
         | Save-Data, slow-connection, no-JS or failed-video visitor gets, and
         | the only one in the accessibility tree. It is the only one of the
         | four that is a complete thought on its own: the others open with
         | connectives because they are links in a chain. It is also the
         | longest, which is why it sits on the darkest beat.
         |
         | NO NUMBERS, and nothing anybody can fall short of. Same rule as the
         | plate builder and the hero case card — see
         | PlateFeedbackHasNoNumbersTest for the argument.
         */
        'beats' => [
            'wash' => 'بنبدأ من اللي في مطبخك.',
            'chop' => 'والخطة بتتفصّل على يومك إنتِ.',
            'cook' => 'مش لازم تغيّري كل حاجة مرة واحدة. حاجة تثبت، وبعدين اللي بعدها.',
            'plate' => 'وفي الآخر، وجبة عادية — مش مكافأة ولا عقاب.',
        ],

        'chips' => [
            'licensed' => 'عيادة مرخّصة',
            'online' => 'أونلاين أو في العيادة',
            'plan' => 'خطة مكتوبة بعد كل جلسة',
        ],

        'case_card' => [
            'label' => 'نموذج توضيحي',
            'title' => 'متابعة الأسبوع 8',
            'subtitle' => 'برنامج متابعة ثلاثة أشهر',
            'metrics' => [
                'energy' => ['label' => 'مستوى الطاقة', 'value' => 'أحسن من البداية'],
                'sleep' => ['label' => 'انتظام النوم', 'value' => '6 ليالي من 7'],
                'labs' => ['label' => 'التحاليل', 'value' => 'في المعدل الطبيعي'],
                'adherence' => ['label' => 'الالتزام بالخطة', 'value' => 'ماشية بانتظام'],
            ],
            'note' => 'بيانات توضيحية مش حالة حقيقية. المتابعة عندنا بتتقاس بالطاقة والنوم والتحاليل والالتزام.',
        ],
    ],

    'stats' => [
        'title' => 'أرقام العيادة',
        'cases' => 'حالة تمت متابعتها',
        'years' => 'سنين خبرة',
        'training_hours' => 'ساعة تدريب إكلينيكي في مستشفيات جامعية',
        'support_days' => 'أيام متابعة أسبوعياً',
    ],

    'specialties' => [
        'eyebrow' => 'مجالات العمل',
        'title' => 'بنشتغل في إيه بالظبط',
        'lead' => 'دي المجالات اللي العيادة متخصصة فيها. لو حالتك من ضمنها، ابدئي باستشارة ونشوف الخطة المناسبة.',
        'empty' => 'المجالات هتكون متاحة قريب.',
    ],

    'packages' => [
        'see_all' => 'شوفي كل التفاصيل والأسعار',
        'eyebrow' => 'الباقات',
        'title' => 'ابدئي من اللي يناسبك',
        'lead' => 'استشارة واحدة لو عايزة تعرفي إنتِ فين، أو متابعة كاملة لو عايزة نتيجة تفضل معاكِ.',
        'featured' => 'الأكثر طلبًا',
        'duration' => 'مدة الجلسة',
        'sessions' => 'عدد الجلسات',
        'cta' => 'احجزي دي',
        'empty' => 'الباقات هتكون متاحة قريب.',
    ],

    'how_it_works' => [
        'eyebrow' => 'طريقة العمل',
        'title' => 'إزاي بنشتغل مع بعض',
        'lead' => 'أربع خطوات واضحة من أول ما تحجزي لحد ما الخطة تبقى جزء من يومك.',
        'steps' => [
            'one' => [
                'title' => 'تحجزي موعدك',
                'body' => 'تختاري الباقة والميعاد اللي يناسبك، أونلاين أو في العيادة، والتأكيد بييجي على طول.',
            ],
            'two' => [
                'title' => 'نفهم حالتك',
                'body' => 'نراجع تاريخك الصحي وعاداتك وتحاليلك لو معاكِ. الجلسة دي كلها أسئلة وإجابات، مش قياسات.',
            ],
            'three' => [
                'title' => 'تستلمي خطتك',
                'body' => 'خطة مكتوبة من أكل بيتك، فيها بدائل لكل صنف عشان متقفيش لو حاجة مش متوفرة.',
            ],
            'four' => [
                'title' => 'نظبطها مع بعض',
                'body' => 'في المتابعة بنعدّل حسب استجابة جسمك وحسب اللي عملي عليه فعلًا، لحد ما تبقى عادة.',
            ],
        ],
    ],

    'stories' => [
        'aggregate' => 'من :count تقييم',
        'eyebrow' => 'تجارب',
        'title' => 'ناس مشيت الرحلة دي',
        'lead' => 'كلام بلسانهم عن اللي اتغير معاهم.',
        'empty' => 'التجارب هتتنشر قريب.',
        'rating_label' => 'تقييم :count من 5',
    ],

    'articles' => [
        'eyebrow' => 'مقالات',
        'title' => 'اقرئي وإنتِ مستنية',
        'lead' => 'كلام عملي عن الأكل والتحاليل والعادات، من غير وعود ولا تخويف.',
        'read_more' => 'اقرئي المقال',
        'reading_time' => ':count دقيقة قراءة',
        'empty' => 'المقالات هتتنشر قريب.',
    ],

    'faq' => [
        'eyebrow' => 'أسئلة شائعة',
        'title' => 'أسئلة بتتسأل كتير',
        'lead' => 'لو سؤالك مش هنا، ابعتيلنا على واتساب ونرد عليكِ.',
        'empty' => 'الأسئلة هتتضاف قريب.',
    ],

    'booking_cta' => [
        'title' => 'جاهزة تبدئي؟',
        'lead' => 'احجزي استشارتك دلوقتي، واختاري تيجي العيادة ولا نتقابل أونلاين.',
        'cta' => 'احجزي موعدك',
        'note' => 'العيادة بتأكد الميعاد خلال ساعات المتابعة.',
    ],

    'contact' => [
        'eyebrow' => 'تواصل',
        'title' => 'تحبي تسألي الأول؟',
        'lead' => 'كلمينا أو ابعتيلنا رسالة، ونجاوبك من غير ما تحجزي.',
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
        'eyebrow' => 'اتفرجي',
        'title' => 'فيديوهات من العيادة',
        'lead' => 'شرح بسيط لأسئلة بتتكرر كتير، من الدكتورة نفسها.',
        'empty' => 'مفيش فيديوهات متاحة دلوقتي.',
        'play' => 'شغّلي: :title',
        'close' => 'إغلاق',
        'privacy' => 'الفيديو مش بيتحمّل من يوتيوب غير لما تدوسي شغّل.',
        'duration' => 'المدة',
        'featured' => 'الأحدث',
    ],
];
