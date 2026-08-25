<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The practitioner
|------------------------------------------------------------------------------
|
| THE FACTS ARE NOT HERE. Her name, title, degree, licensing body and
| membership number live in config/clinic.php as the single source of truth,
| and this file holds only the labels around them. CredentialsTest fails if any
| page states a qualification or a body that config does not.
|
| That matters because these are claims about a real person's professional
| standing, published under her name. The previous version of this file
| invented a university, a master's degree and the wrong syndicate — the sort
| of thing that is only ever discovered by the person it misrepresents, or by a
| patient checking.
|
| ONE THING IS STILL TODO_COPY: the philosophy paragraph. It has to be in her
| voice, and nobody else can write it. clinic:verify-copy blocks production
| until she does.
|
*/

return [
    'eyebrow' => 'عن الدكتورة',

    'meta_title' => 'عن الدكتورة رنا سالم — رحلة صحة',
    'meta_description' => 'أخصائية تغذية إكلينيكية، بكالوريوس العلوم الزراعية من جامعة المنصورة، مقيّدة بنقابة المهن الزراعية. التدريب الإكلينيكي والمؤهلات بالتفصيل.',

    'page_title' => 'مين اللي هيتابع حالتك',
    'page_lead' => 'الصفحة دي عن الشخص اللي هتقعدي معاها. المؤهلات ورقم القيد مكتوبين عشان تقدري تتأكدي منهم بنفسك.',

    'philosophy_heading' => 'طريقة الشغل',
    'philosophy' => 'TODO_COPY — فقرة قصيرة بصوت الدكتورة عن طريقتها في الشغل: '
        .'ليه بتبني الخطة من أكل البيت، وإيه اللي بتقيس عليه التقدم، وإيه اللي '
        .'مش بتعمله. من ٤٠ لـ ٦٠ كلمة، بنفس نبرة باقي الموقع.',

    'credentials_heading' => 'المؤهلات والقيد',
    'degree_label' => 'المؤهل',
    'licence_label' => 'القيد المهني',
    'licence_value' => 'عضوية :body رقم :number منذ :year',
    'licence_note' => 'رقم القيد مكتوب عشان تقدري تراجعيه في سجل النقابة. عيادة مش بتكتبه سؤال يستاهل تسأليه.',

    'training_heading' => 'التدريب الإكلينيكي',
    'training_lead' => 'التدريب اللي حصل، بالترتيب. الساعات والجهات مكتوبة زي ما هي في الشهادات.',
    'training_hours' => ':hours ساعة',

    'certificates_heading' => 'الشهادات',
    'certificates_pending' => 'صور الشهادات هتتضاف قريب، بعد إخفاء الرقم القومي منها.',
    'certificates_note' => 'الشهادات بتتنشر بعد ما نخفي منها الرقم القومي. ده بيانات شخصية مش لازمة عشان تتأكدي من المؤهل.',

    'portrait_alt' => 'صورة :name',
    'portrait_pending' => 'صورة الدكتورة هتتضاف قريب.',
    'portrait_pending_title' => 'الصورة في الطريق',

    'treats_heading' => 'الحالات اللي بتتابعها',
    'treats_lead' => 'المجالات دي مأخوذة من صفحة المجالات، فلو اتغيرت هناك بتتغير هنا.',

    'cta' => [
        'title' => 'عايزة تحجزي معاها؟',
        'lead' => 'الجلسة الأولى كلها أسئلة وإجابات، أونلاين.',
    ],
];
