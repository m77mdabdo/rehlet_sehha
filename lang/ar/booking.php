<?php

declare(strict_types=1);

return [
    'title' => 'احجزي موعدك',
    'lead' => 'اختاري الخدمة والميعاد اللي يناسبك، وهنأكدلك الحجز على طول.',
    'coming_soon' => 'لو واجهتك مشكلة في الحجز، كلمينا أو ابعتيلنا على واتساب ونحجزلك.',

    'mode' => [
        'online' => 'استشارة عن بُعد',
        'clinic' => 'في العيادة',
    ],

    'fields' => [
        'name' => 'الاسم',
        'phone' => 'رقم الموبايل',
        'email' => 'البريد الإلكتروني',
        'service' => 'الخدمة',
        'mode' => 'نوع الاستشارة',
        'date' => 'التاريخ',
        'time' => 'الميعاد',
        'birth_date' => 'تاريخ الميلاد',
        'goal' => 'إيه اللي محتاجاه؟',
        'medications' => 'أدوية بتاخديها',
        'conditions' => 'أمراض أو حالات مزمنة',
        'avoid_foods' => 'أكل بتتجنبيه',
        'note' => 'حابة تضيفي حاجة؟',
    ],

    'placeholders' => [
        'name' => 'الاسم بالكامل',
        'phone' => '01xxxxxxxxx',
        'email' => 'اختياري — عشان نبعتلك الخطة',
        'medications' => 'اسم الدوا والجرعة لو تعرفيها. لو مفيش، سيبيها فاضية.',
        'conditions' => 'زي السكري أو الضغط أو الغدة. لو مفيش، سيبيها فاضية.',
        'avoid_foods' => 'حساسية، أو أكل مش بتحبيه، أو صيام.',
        'note' => 'أي حاجة تحبي الدكتورة تعرفها قبل الجلسة.',
    ],

    'submit' => 'أكّدي الحجز',
    'optional' => 'اختياري',

    'steps' => [
        'service' => 'الخدمة',
        'time' => 'الميعاد',
        'details' => 'بياناتك',
        'done' => 'التأكيد',
        'of' => 'خطوة :current من :total',
    ],

    'actions' => [
        'next' => 'التالي',
        'back' => 'رجوع',
        'change' => 'غيّري',
        'choose_service' => 'اختاري الباقة',
        'choose_time' => 'اختاري الميعاد',
    ],

    'goals' => [
        'weight_management' => 'إدارة الوزن',
        'medical_condition' => 'حالة مرضية',
        'sports_performance' => 'تغذية رياضية',
        'pregnancy' => 'حمل أو رضاعة',
        'child_nutrition' => 'تغذية طفل',
        'lab_review' => 'قراءة تحاليل',
        'general_health' => 'صحة عامة',
        'other' => 'حاجة تانية',
    ],

    'time' => [
        'title' => 'اختاري اليوم والميعاد',
        'lead' => 'كل المواعيد بتوقيت القاهرة.',
        'no_slots' => 'مفيش مواعيد متاحة في اليوم ده. جربي يوم تاني.',
        'no_days' => 'مفيش مواعيد متاحة دلوقتي. كلمينا ونشوفلك ميعاد.',
        'timezone_note' => 'المواعيد كلها بتوقيت القاهرة.',
        'closed' => 'مغلق',
    ],

    'details' => [
        'title' => 'بياناتك',
        'lead' => 'الكلام ده بيوصل للدكتورة قبل الجلسة عشان تكون محضّرة لحالتك.',
        'patient_heading' => 'بياناتك الأساسية',
        'intake_heading' => 'معلومات طبية',
        'intake_note' => 'كل حاجة هنا اختيارية غير الهدف. اكتبي اللي تعرفيه بس.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Consent
    |--------------------------------------------------------------------------
    |
    | Deliberately plain. This is the sentence that has to be true, and that a
    | patient has to be able to understand without a lawyer: WHAT is stored,
    | that it is ENCRYPTED, and that it is used ONLY for the consultation.
    |
    | No pre-ticked box, no "by continuing you agree", no consent buried in a
    | terms-of-service link. The patient ticks it or the form does not submit.
    |
    */
    'consent' => [
        'label' => 'موافقة على حفظ بياناتي الطبية',
        'text' => 'أنا موافقة إن المعلومات الطبية اللي كتبتها هنا تتحفظ مشفّرة، وتستخدم فقط في الاستشارة ومتابعة حالتي مع الدكتورة، ومتتشاركش مع أي حد تاني.',
        'link' => 'اقرئي سياسة الخصوصية',
        'required_note' => 'لازم توافقي عشان نقدر نكمل الحجز.',
    ],

    'summary' => [
        'title' => 'ملخّص الحجز',
        'service' => 'الباقة',
        'mode' => 'نوع الاستشارة',
        'when' => 'الميعاد',
        'price' => 'السعر',
        'duration' => 'المدة',
    ],

    'confirmation' => [
        'title' => 'تم الحجز',
        'lead' => 'حجزك اتسجّل. احفظي رقم الحجز واللينك اللي تحت.',
        'reference' => 'رقم الحجز',
        'when' => 'ميعادك',
        'timezone' => 'بتوقيت القاهرة (:zone)',
        'status_note' => 'الحجز دلوقتي بانتظار التأكيد من العيادة.',
        'next_title' => 'اللي جاي',
        'next' => [
            'confirm' => 'العيادة هتأكد الميعاد خلال ساعات العمل.',
            'prepare' => 'لو معاكِ تحاليل حديثة، جهزيها قبل الجلسة.',
            'manage' => 'تقدري تلغي أو تغيري الميعاد من لينك «إدارة الحجز» اللي تحت.',
        ],
        'manage_link' => 'إدارة الحجز',
        'manage_note' => 'احفظي اللينك ده. هو الطريقة الوحيدة لإدارة الحجز من غير ما تكلمينا.',
    ],

    'manage' => [
        'title' => 'حجزك',
        'lead' => 'تقدري تلغي أو تغيري ميعادك من هنا.',
        'cancelled_by_patient' => 'ألغاه المريض من صفحة إدارة الحجز',
        'status' => 'حالة الحجز',
        'cancel' => 'إلغاء الحجز',
        'cancel_confirm' => 'متأكدة إنك عايزة تلغي الحجز؟',
        'reschedule' => 'تغيير الميعاد',
        'reschedule_title' => 'اختاري ميعاد جديد',
        'confirm_reschedule' => 'أكّدي الميعاد الجديد',
        'keep' => 'سيبيه زي ما هو',
        'cancelled_flash' => 'تم إلغاء الحجز. الميعاد رجع متاح لحد تاني.',
        'rescheduled_flash' => 'تم تغيير الميعاد.',
        'too_late_title' => 'الميعاد قرّب',
        'too_late' => 'مقدرش تلغي أو تغيري الميعاد أونلاين قبله بأقل من :hours ساعة. كلمينا وهنساعدك.',
        'already_cancelled' => 'الحجز ده اتلغى.',
        'past' => 'الميعاد ده عدّى.',
    ],

    'rights' => [
        'heading' => 'بياناتك الطبية',
        'lead' => 'دي المعلومات اللي كتبتيها وقت الحجز. من حقك تشوفيها، وتصححيها، وتمسحيها.',
        'view' => 'اعرضي بياناتي',
        'hide' => 'إخفاء',
        'blank' => 'مكتبتيش حاجة',
        'correct' => 'تصحيح البيانات',
        'save' => 'احفظي التعديل',
        'cancel_edit' => 'إلغاء التعديل',
        'correction_closed' => 'مقدرش تعدّلي البيانات بعد ما الجلسة تعدّي، عشان الملف اللي الدكتورة قرته وقت الجلسة يفضل زي ما هو. لو في غلط محتاج تصحيح، كلمينا.',
        'updated_flash' => 'اتحفظ التعديل.',

        'erase' => 'امسحي بياناتي الطبية',
        'erase_confirm_title' => 'متأكدة إنك عايزة تمسحي بياناتك الطبية؟',
        'erase_removes_heading' => 'اللي هيتمسح نهائيًا:',
        'erase_removes' => [
            'goal' => 'الهدف من الاستشارة',
            'medications' => 'الأدوية اللي كتبتيها',
            'conditions' => 'الأمراض والحالات المزمنة',
            'avoid' => 'الأكل اللي بتتجنبيه',
            'note' => 'ملاحظاتك',
        ],
        'erase_keeps_heading' => 'اللي هيفضل:',
        'erase_keeps' => [
            'appointment' => 'الحجز نفسه وميعاده — العيادة محتاجاه في سجلاتها والحسابات',
            'identity' => 'اسمك ورقم موبايلك — عشان نقدر نوصلك ونعرف الحجز ده بتاع مين',
            'consent' => 'تاريخ موافقتك — التاريخ بس، من غير عنوان الـ IP. ده دليل إن الموافقة اتاخدت صح، والعيادة محتاجاه لو حد سأل.',
        ],
        'erase_upcoming_warning' => 'الميعاد ده لسه جاي. لو مسحتي البيانات دلوقتي، الدكتورة هتيجي الجلسة من غير ما تعرف تاريخك الطبي، وهتحتاجي تقوليلها من الأول.',
        'erase_keyword' => 'مسح',
        'erase_keyword_label' => 'اكتبي كلمة «:word» عشان تأكدي',
        'erase_keyword_hint' => 'المسح نهائي وفوري، ومفيش رجوع فيه. الخطوة دي عشان ماتحصلش بالغلط.',
        'erase_keyword_mismatch' => 'اكتبي «:word» بالظبط عشان تقدري تكملي.',
        'erase_confirm' => 'أيوة، امسحي بياناتي الطبية',
        'erase_cancel' => 'لأ، سيبيها',
        'erased_title' => 'بياناتك الطبية اتمسحت.',
        'erased_on' => 'اتمسحت بتاريخ :date. الحجز نفسه لسه موجود.',
        'erased_flash' => 'اتمسحت بياناتك الطبية. الحجز نفسه لسه زي ما هو.',
    ],

    'errors' => [
        'service_unavailable' => 'الباقة دي مش متاحة دلوقتي. اختاري باقة تانية.',
        'mode_unavailable' => 'نوع الاستشارة ده مش متاح دلوقتي.',
        'slot_required' => 'اختاري ميعاد الأول.',
        'slot_expired' => 'الميعاد ده مابقاش متاح. اختاري ميعاد تاني.',
        'slot_taken' => 'للأسف حد حجز الميعاد ده قبلك بثواني. اختاري ميعاد تاني — بياناتك كلها لسه محفوظة وموصلتش لحاجة.',
        'phone_invalid' => 'اكتبي رقم موبايل مصري صحيح، زي 01012345678.',
        'consent_required' => 'لازم توافقي على حفظ بياناتك الطبية عشان نكمل.',
        'too_many_attempts' => 'حاولتي كتير في وقت قصير. جربي تاني بعد :minutes دقيقة.',
        'too_many_for_phone' => 'الرقم ده وصل للحد الأقصى من الحجوزات. جربي بعد :minutes دقيقة أو كلمينا.',
        'too_fast' => 'الفورم اتبعت بسرعة شوية. راجعي بياناتك وابعتي تاني.',
        'rejected' => 'مقدرناش نكمل الطلب ده. لو ده غلط، كلمينا.',
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
        'send_details' => 'ابعتي تفاصيل حجزك للعيادة',
        'send_details_hint' => 'هيفتح واتساب برسالة جاهزة فيها رقم الحجز. إنتِ اللي تبعتيها.',
        'message_clinic' => 'كلمي العيادة على واتساب',
        'prefill_booking' => 'أهلًا، حجزت من الموقع ورقم الحجز :reference.',
        'prefill_manage' => 'أهلًا، عندي حجز رقم :reference وحابة أسأل عنه.',
        // Reference, date, time and mode — the whole appointment and nothing
        // about her health. This text becomes a URL.
        'prefill_record' => 'تفاصيل حجزي في رحلة صحة:\nرقم الحجز: :reference\nالميعاد: :when (:zone)\nنوع الاستشارة: :mode',
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
        'title' => 'من غير إيميل مش هنقدر نبعتلك حاجة',
        'lead' => 'سيبتي خانة الإيميل فاضية. يعني مش هيوصلك:',
        'losses' => [
            'confirmation' => 'رسالة تأكيد الحجز',
            'reminders' => 'تنبيه قبل الميعاد بيوم، وتنبيه تاني قبله بساعة',
            'manage' => 'لينك إلغاء الميعاد أو تغييره من غير ما تكلمينا',
        ],
        'fallback' => 'العيادة هتكلمك على رقم موبايلك بدل كده.',
        'add' => 'أضيفي إيميلك',
        'continue' => 'كمّلي من غير إيميل',
    ],

    'contact_preference' => [
        'email' => 'إيميل',
        'phone_only' => 'موبايل بس',
    ],

    'keepsake' => [
        'title' => 'ده كل اللي هيفضل معاكِ من الحجز ده',
        'lead' => 'ماديتيناش إيميل، فمش هيوصلك تأكيد ولا تنبيه ولا لينك. احفظي المعلومات دي دلوقتي.',
        'reference_label' => 'رقم الحجز',
        'link_label' => 'لينك إدارة الحجز',
        'link_note' => 'احفظي اللينك ده. مش هيتبعت لحد ولا لأي مكان تاني، ومن غيره مش هتقدري تلغي أو تغيري الميعاد إلا بمكالمة.',
        'copy' => 'انسخي',
        'copied' => 'اتنسخ',
        'copy_manual' => 'انسخيه بنفسك',
        'copy_manual_hint' => 'المتصفح مانع النسخ التلقائي. النص محدد دلوقتي — اضغطي عليه مطولاً واختاري نسخ، أو Ctrl+C.',
        'copy_reference' => 'انسخي رقم الحجز',
        'copy_link' => 'انسخي اللينك',
        'whatsapp' => 'ابعتي التفاصيل لنفسك على واتساب',
        'whatsapp_hint' => 'هيفتح واتساب برسالة فيها تفاصيل حجزك. ابعتيها للعيادة أو لنفسك عشان تفضل معاكِ.',
        'add_email_title' => 'حابة تستلمي التأكيد والتنبيهات؟',
        'add_email_hint' => 'اكتبي إيميلك دلوقتي وهنبعتلك التأكيد فورًا، والتنبيهات هتوصلك قبل الميعاد.',
        'add_email_action' => 'ابعتوا لي التأكيد',
        'add_email_saved' => 'تمام. بعتنا التأكيد على الإيميل ده، والتنبيهات هتوصلك قبل الميعاد.',
    ],
];
