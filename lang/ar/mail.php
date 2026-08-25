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

    'greeting' => 'أهلًا :name،',
    'greeting_generic' => 'أهلًا،',

    'facts' => [
        'reference' => 'رقم الحجز',
        'service' => 'الباقة',
        'when' => 'الميعاد',
        'mode' => 'نوع الاستشارة',
        'price' => 'السعر',
        'timezone' => 'بتوقيت القاهرة (:zone)',
    ],

    'manage' => [
        'label' => 'إدارة الحجز',
        'button' => 'إلغاء الميعاد أو تغييره',
        'hint' => 'اللينك ده خاص بيكِ — متبعتيهوش لحد. أي حد معاه يقدر يشوف حجزك ويلغيه.',
    ],

    'call_us' => 'محتاجة تكلمينا؟ :phone',

    'footer' => [
        'automated' => 'الرسالة دي متبعوتة أوتوماتيك من :address. لو رديتي عليها هتوصل لفريق العيادة على :reply.',
        'rights' => 'من حقك تشوفي بياناتك أو تصححيها أو تمسحيها في أي وقت من صفحة إدارة الحجز.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient — booking confirmed
    |--------------------------------------------------------------------------
    */

    'confirmed' => [
        'subject' => 'حجزك اتسجّل — :reference',
        'heading' => 'حجزك اتسجّل',
        'lead' => 'استلمنا حجزك، ودي كل تفاصيله. احتفظي بالرسالة دي.',
        'pending_note' => 'الحجز دلوقتي بانتظار تأكيد العيادة. هنتواصل معاكِ لو فيه أي تغيير.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient — reminders
    |--------------------------------------------------------------------------
    */

    'reminder_24h' => [
        'subject' => 'فاكرة ميعادك بكرة؟ — :reference',
        'heading' => 'ميعادك بكرة',
        'lead' => 'دي فكرة بميعادك، باقي عليه أقل من ٢٤ ساعة.',
        'note' => 'لو الميعاد مش مناسب، تقدري تغيريه أو تلغيه من اللينك اللي تحت — ده بيدّي فرصة لحد تاني ياخد الميعاد.',
    ],

    'reminder_1h' => [
        'subject' => 'ميعادك بعد ساعة — :reference',
        'heading' => 'ميعادك بعد ساعة',
        'lead' => 'باقي حوالي ساعة على ميعادك.',
        'online_note' => 'الاستشارة أونلاين. جهّزي مكان هادي ونت كويس، وهنبعتلك لينك الدخول قبل الميعاد.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Patient — cancellation and reschedule
    |--------------------------------------------------------------------------
    */

    'cancelled' => [
        'subject' => 'الحجز اتلغى — :reference',
        'heading' => 'الحجز اتلغى',
        'lead' => 'الحجز ده اتلغى، ودي كانت تفاصيله.',
        'rebook' => 'لو حابة تحجزي من تاني، إحنا موجودين.',
        'rebook_button' => 'احجزي ميعاد جديد',
    ],

    'rescheduled' => [
        'subject' => 'ميعادك اتغيّر — :reference',
        'heading' => 'ميعادك اتغيّر',
        'lead' => 'ميعادك اتنقل. دي التفاصيل القديمة والجديدة.',
        'old_time' => 'الميعاد القديم',
        'new_time' => 'الميعاد الجديد',
        'note' => 'الميعاد القديم رجع للكالندر، والميعاد الجديد محجوز باسمك.',
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
        'subject' => 'حجز جديد — :reference',
        'heading' => 'حجز جديد',
        'lead' => 'اتسجّل حجز جديد من الموقع.',
        'patient' => 'بيانات المريضة',
        'patient_name' => 'الاسم',
        'patient_phone' => 'الموبايل',
        'patient_email' => 'الإيميل',
        'no_email' => 'مفيش إيميل — التأكيد ماتبعتش. كلميها.',
        'intake' => 'المعلومات الطبية',
        'no_intake' => 'مفيش معلومات طبية متسجلة.',
        'booked_at' => 'اتحجز في',
        'locale' => 'لغة الحجز',
    ],

    'cancelled_alert' => [
        'subject' => 'إلغاء حجز — :reference',
        'heading' => 'المريضة لغت الحجز',
        'lead' => 'الحجز ده اتلغى من صفحة إدارة الحجز، والميعاد رجع للكالندر.',
        'reason' => 'السبب',
    ],

    'daily_schedule' => [
        'subject' => 'مواعيد النهاردة — :date',
        'heading' => 'مواعيد النهاردة',
        'lead' => 'دي مواعيد يوم :date بتوقيت القاهرة.',
        'empty' => 'مفيش مواعيد النهاردة.',
        'count' => 'عدد المواعيد: :count',
        'time' => 'الساعة',
        'patient' => 'المريضة',
        'service' => 'الباقة',
        'status' => 'الحالة',

        /*
         * The call list. Patients booked for TOMORROW who gave no email
         * address, so nothing has reached them and nothing will: no تأكيد, no
         * تنبيه قبل يوم، ولا قبل ساعة. Someone has to ring them.
         */
        'call_heading' => 'مكالمات لازم تتعمل',
        'call_lead' => 'دول مواعيد بكرة (:date) لمرضى مادوش إيميل. مش وصلهم تأكيد ولا هيوصلهم تنبيه — لازم حد يكلمهم.',
        'call_empty' => 'كل مواعيد بكرة ليها إيميل. مفيش مكالمات مطلوبة.',
        'call_count' => 'عدد المكالمات: :count',
        'phone' => 'الموبايل',
    ],

    /*
    |--------------------------------------------------------------------------
    | Clinic — delivery failure
    |--------------------------------------------------------------------------
    */

    'delivery_failed' => [
        'subject' => 'تنبيه: رسالة تأكيد ماوصلتش — :reference',
        'heading' => 'رسالة للمريضة ماوصلتش',
        'lead' => 'حاولنا نبعت رسالة للمريضة والمحاولات كلها فشلت. يعني فيه حد حجز ومش عارف إن الحجز اتسجّل.',
        'action' => 'كلمي المريضة وأكدي الحجز بنفسك.',
        'template' => 'نوع الرسالة',
        'recipient' => 'المرسل إليه',
        'error' => 'الخطأ',
    ],
    'review_requested' => [
        'subject' => 'تحبي تقولي رأيك؟',
        'heading' => 'رأيك يفرق',
        'lead' => 'عدّى كام يوم على جلستك. لو حابة تقولي رأيك في التجربة، اللينك ده بيفتح صفحة قصيرة — تقييم وكلمتين، ودقيقة واحدة بالكتير.',
        'button' => 'اكتبي رأيك',
        'consent_note' => 'رأيك مش بيتنشر أوتوماتيك. في خانة في الصفحة بتختاري منها لو موافقة ينشر على الموقع، وهي مش متعلّمة. لو مكتبتيهاش، رأيك بيوصلنا وبس.',
        'no_obligation' => 'ولو مش حابة تكتبي حاجة، مفيش مشكلة خالص — مش هنبعتلك تاني عن الموضوع ده.',
    ],
];
