<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The FAQ page
|------------------------------------------------------------------------------
|
| Every active question, GROUPED, rather than the homepage's general six. The
| grouping comes from the faqs.category column, so a new category appears here
| the moment a row uses it — the page cannot silently drop a group by listing
| them by hand.
|
| The buying questions also appear on the packages page, which is correct: they
| are answered where the decision is made AND where somebody looking for
| answers goes. That is the same question in two places a patient might look,
| not duplicated marketing prose.
|
*/

return [
    'meta_title' => 'أسئلة شائعة — رحلة صحة',
    'meta_description' => 'الأسئلة اللي بتتسأل قبل الحجز وبعده: الجلسات، الأونلاين، التحاليل، الدفع، التأجيل والإلغاء.',

    'eyebrow' => 'أسئلة شائعة',
    'title' => 'الأسئلة اللي بتتسأل',
    'lead' => 'مقسومة على مجموعتين: أسئلة عن الشغل نفسه، وأسئلة عن الحجز والدفع. لو سؤالك مش هنا ابعتيه على الواتساب.',

    'categories' => [
        'general' => [
            'title' => 'عن الشغل والجلسات',
            'lead' => 'إيه اللي بيحصل في الجلسة، والأونلاين، والحالات اللي بنتعامل معاها.',
        ],
        'buying' => [
            'title' => 'عن الحجز والدفع',
            'lead' => 'الباقات، الدفع، التأجيل والإلغاء. نفس الإجابات الموجودة في صفحة الباقات.',
        ],
    ],

    'empty' => 'الأسئلة هتكون متاحة قريب.',

    'still_asking' => [
        'title' => 'سؤالك مش هنا؟',
        'body' => 'ابعتيه على الواتساب. لو الإجابة محتاجة جلسة هنقولك بصراحة بدل ما نجاوب جواب ناقص في رسالة.',
    ],

    'cta' => [
        'title' => 'وصلتي لإجابة؟',
        'lead' => 'لو الباقي أسئلة عن حالتك إنتي، دي بقى شغل الجلسة الأولى.',
    ],
];
