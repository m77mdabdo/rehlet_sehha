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
| THE PHILOSOPHY PARAGRAPH IS HERS, and is now written. It was the last
| TODO_COPY on the site, and it blocked production until she answered — which
| is the correct order. A paragraph in a practitioner's voice, on the page
| about her, is not something anybody else may draft. See the note beside it.
|
*/

return [
    'eyebrow' => 'عن الدكتورة',

    'meta_title' => 'عن الدكتورة رنا سالم — رحلة صحة',
    'meta_description' => 'أخصائية تغذية إكلينيكية، بكالوريوس العلوم الزراعية من جامعة المنصورة، مقيّدة بنقابة المهن الزراعية. التدريب الإكلينيكي والمؤهلات بالتفصيل.',

    'page_title' => 'مين اللي هيتابع حالتك',
    'page_lead' => 'الصفحة دي عن الشخص اللي هتقعدي معاها. المؤهلات ورقم القيد مكتوبين عشان تقدري تتأكدي منهم بنفسك.',

    'philosophy_heading' => 'طريقة الشغل',
    /*
     * HER OWN WORDS. DO NOT EDIT THIS.
     *
     * Written by Dr. Rana, from her own answers, and approved as it stands.
     * It is the one paragraph on this site that had to come from her and could
     * not be drafted by anybody else — the same rule the articles enforce with
     * PRACTITIONER_VOICE, applied to the page that is about her.
     *
     * Four paragraphs separated by a blank line, rendered as four <p> by
     * <x-prose-paragraphs>. The blank lines are structure, not formatting.
     */
    'philosophy' => <<<'COPY'
        أول جملة بسمعها من أغلب اللي بييجي لي: "محتاجة أخس وأبقى أحسن." والحاجة التانية اللي بسمعها بعد كام أسبوع من أي نظام تاني جربوه: "زهقت."

        والزهق ده مش ضعف إرادة. الزهق ده إن النظام كان مصمم لحد تاني — مش لأكل بيتك، ولا لمواعيد شغلك، ولا لميزانيتك. أي خطة بتحسّي إنك بتحاربيها هتسيبيها، ودي مسألة وقت مش أكتر.

        عشان كده أنا مبدأش بورقة. بدأ بيكِ — بصحتك، وبيومك، وباللي بتحبي تاكليه. الخطة بتتبني من حياتك مش من كتاب، وبتتعدّل معاكِ كل أسبوع لحد ما تبقى عادة مش مجهود.

        هدفي مش رقم على ميزان. هدفي إنك تحسّي إنك أحسن — طاقة، ونوم، وراحة مع نفسك.
        COPY,

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
