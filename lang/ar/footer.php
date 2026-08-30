<?php

declare(strict_types=1);

return [
    /*
     * NO PREMISES. The practice is online, and this line sits on every page —
     * which is how it survived the contact page being rebuilt around that
     * fact. It used to say «عيادة تغذية علاجية في القاهرة», and a patient who
     * read the footer and went looking for a clinic in Cairo would have found
     * nothing there.
     *
     * Cairo still appears elsewhere in the site as a TIME ZONE, which is a
     * different claim and a true one.
     */
    'about' => 'عيادة تغذية إكلينيكية أونلاين. بنساعدك توصلي لهدفك بخطة واقعية مبنية على حالتك وتحاليلك، مش على نظام جاهز.',
    'services_heading' => 'الخدمات',
    'links_heading' => 'روابط',
    'contact_heading' => 'تواصلي معنا',
    'whatsapp' => 'واتساب',
    'phone' => 'تليفون',
    'email' => 'إيميل',
    'address' => 'العنوان',
    'disclaimer_heading' => 'تنبيه طبي',
    'disclaimer' => 'المحتوى هنا للتوعية العامة ولا يغني عن استشارة طبيبك المعالج',

    /*
    |--------------------------------------------------------------------------
    | Opening hours
    |--------------------------------------------------------------------------
    |
    | Assembled by App\Support\OpeningHours from the working_hours rows. The
    | old 'hours' key was a hand-typed sentence that went stale the first time
    | anybody edited the schedule.
    */
    'days' => [
        6 => 'السبت',
        0 => 'الأحد',
        1 => 'الاثنين',
        2 => 'الثلاثاء',
        3 => 'الأربعاء',
        4 => 'الخميس',
        5 => 'الجمعة',
    ],
    /*
     * The contracted forms. Arabic لِ + الخميس becomes للخميس, not لـالخميس —
     * so the end of a range needs its own list rather than a prefix glued to
     * the plain name.
     */
    'days_to' => [
        6 => 'للسبت',
        0 => 'للأحد',
        1 => 'للاثنين',
        2 => 'للثلاثاء',
        3 => 'للأربعاء',
        4 => 'للخميس',
        5 => 'للجمعة',
    ],
    'day_separator' => ' و',
    'day_range' => 'من :from :to',
    'hours_range' => ':days، :open – :close',
    'hours_days' => ':days، :open – :close',
    'closed_on' => ':days مغلق',
    'am' => 'ص',
    'pm' => 'م',

];
