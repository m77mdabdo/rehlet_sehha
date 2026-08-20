<?php

declare(strict_types=1);

/*
| Copy for the downloadable record.
|
| This document is the ACCESS RIGHT under Egyptian law 151/2020 in substance,
| not a convenience. It has to be readable on its own, years from now, by
| someone who no longer remembers this clinic's website.
*/

return [
    'title' => 'نسخة من ملفك',
    'lead' => 'دي نسخة كاملة من البيانات المسجّلة عندنا عن الحجز ده. تقدري تحفظيها أو تطبعيها أو توديها لدكتور تاني.',
    'generated_on' => 'اتصدرت في',

    'sections' => [
        'appointment' => 'تفاصيل الحجز',
        'patient' => 'بياناتك',
        'intake' => 'المعلومات الطبية اللي كتبتيها',
        'consent' => 'الموافقة',
    ],

    'no_intake' => 'مفيش معلومات طبية مسجّلة مع الحجز ده.',
    'erased' => 'المعلومات الطبية بتاعة الحجز ده اتمسحت بناءً على طلبك بتاريخ :date. الحجز نفسه لسه في سجلات العيادة، لكن اللي كتبتيه عن صحتك اتشال نهائيًا.',
    'erased_on' => 'اتمسحت في',
    'consent_given_on' => 'الموافقة اتسجلت في',

    'rights_note' => 'من حقك في أي وقت تشوفي بياناتك، تصححيها، أو تمسحيها — من لينك «إدارة الحجز» اللي في رسالة التأكيد. لو ضاع منك اللينك، كلمينا.',

    'download' => 'حمّلي نسخة من ملفك',
    'download_hint' => 'ملف تقدري تحفظيه أو تطبعيه أو تبعتيه لدكتور تاني.',
];
