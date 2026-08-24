<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| Practitioner — PLACEHOLDER COPY
|------------------------------------------------------------------------------
|
| Every value marked TODO_COPY needs a real answer from the clinic before this
| goes live. PlaceholderCopyTest fails if any survives into production.
|
| Do NOT fill these in by guessing. Credentials, a university, a membership and
| a registration number are claims about a real person's qualifications; the
| structure is ours to design, the facts are not ours to invent.
|
*/

return [
    'eyebrow' => 'عن الدكتورة',
    'name' => 'TODO_COPY — اسم الدكتورة بالكامل',
    'title' => 'TODO_COPY — المسمى المهني، مثال: استشاري التغذية العلاجية',

    'philosophy' => 'TODO_COPY — فقرة قصيرة بصوت الدكتورة عن طريقتها في الشغل: '
        .'ليه بتبني الخطة من أكل البيت، وإيه اللي بتقيس عليه التقدم، وإيه اللي '
        .'مش بتعمله. من ٤٠ لـ ٦٠ كلمة، بنفس نبرة باقي الموقع.',

    'credentials_heading' => 'المؤهلات',

    'credentials' => [
        'degree' => 'TODO_COPY — الدرجة العلمية والجامعة وسنة التخرج',
        'specialisation' => 'TODO_COPY — التخصص الدقيق أو الزمالة',
        'membership' => 'TODO_COPY — العضويات المهنية',
        'experience' => 'TODO_COPY — سنين الخبرة ومجالها',
    ],

    'registration' => 'TODO_COPY — رقم القيد بنقابة الأطباء / ترخيص العيادة',

    /*
    |--------------------------------------------------------------------------
    | The standalone page
    |--------------------------------------------------------------------------
    |
    | Structure only. Everything factual about the practitioner stays TODO_COPY
    | — a page that invents a qualification is worse than a page that admits it
    | is waiting for one, and clinic:verify-copy blocks production until the
    | clinic answers.
    |
    | The photograph frames are RESERVED, not filled. The empty state is the
    | brand mark on sage, the same treatment as the homepage — never a stock
    | stand-in of somebody else's clinician, which on a page about who will be
    | treating you is a lie, and never a broken frame.
    */
    'meta_title' => 'عن الدكتورة — رحلة صحة',
    'meta_description' => 'مين اللي هيتابع حالتك: المؤهلات، التخصص، القيد المهني، وطريقة الشغل في العيادة.',

    'page_title' => 'مين اللي هيتابع حالتك',
    'page_lead' => 'الصفحة دي عن الشخص اللي هتقعدي معاها، مش عن العيادة. المؤهلات والقيد المهني مكتوبين عشان تقدري تتأكدي منهم بنفسك.',

    'philosophy_heading' => 'طريقة الشغل',
    'registration_heading' => 'القيد المهني',
    'registration_note' => 'رقم القيد مكتوب عشان تقدري تراجعيه في سجل النقابة. عيادة مش بتكتبه سؤال يستاهل تسأليه.',

    'portrait_pending_title' => 'الصورة في الطريق',
    'clinic_photo_pending' => 'صور العيادة هتتضاف قريب.',
    'clinic_photo_heading' => 'العيادة',

    'treats_heading' => 'الحالات اللي بتتابعها',
    'treats_lead' => 'المجالات دي مأخوذة من صفحة المجالات، فلو اتغيرت هناك بتتغير هنا.',

    'cta' => [
        'title' => 'عايزة تحجزي معاها؟',
        'lead' => 'الجلسة الأولى كلها أسئلة وإجابات، في العيادة أو أونلاين بنفس السعر.',
    ],

    'portrait_alt' => 'صورة :name',
    'portrait_pending' => 'صورة الدكتورة هتتضاف قريب.',
];
