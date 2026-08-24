<?php

declare(strict_types=1);

/*
| Build your plate.
|
| NOT A SINGLE NUMBER IN THIS FILE, AND THERE MUST NEVER BE ONE.
|
| No calories, no grams, no portions, no scores, no targets, no percentages.
| Every string here talks about PROPORTION — what the plate is mostly made of,
| and what it is missing.
|
| The reason is clinical, not stylistic. Numeric feedback teaches people to
| measure food, and measuring food is the habit this clinic exists to undo. It
| is also actively harmful to anyone with a disordered relationship to eating:
| a number attached to a food is the mechanism of the disorder, not neutral
| information. A tool on a nutrition clinic's homepage must not be a calorie
| counter in disguise.
|
| PlateFeedbackHasNoNumbersTest fails the build if a digit appears in any
| feedback string. That test is not to be relaxed.
*/

return [
    /*
    | The lead deliberately does not use the word for calories, even to
    | disavow it. HomePageTest forbids the term anywhere in the rendered page,
    | and that guard is right: naming it — however negatively — still puts it
    | in front of somebody who came here trying not to think about it.
    */
    'eyebrow' => 'جربي بنفسك',
    'title' => 'ابني طبقك',
    'lead' => 'دوسي على الأكل اللي بتاكليه عادةً، وشوفي شكل الطبق. مفيش أرقام ولا موازين — الفكرة كلها في النسب.',

    'groups' => [
        'vegetable' => 'خضار',
        'protein' => 'بروتين',
        'starch' => 'نشويات',
        'fat' => 'دهون',
        'fruit' => 'فاكهة',
        'dairy' => 'ألبان',
    ],

    'plate_label' => 'الطبق',
    'plate_empty' => 'الطبق فاضي',
    'reset' => 'ابدئي من جديد',
    'add' => 'ضيفي :food',
    'remove' => 'شيلي :food',
    'chosen' => 'اللي على الطبق',

    /*
    | One string per state. The tool picks the FIRST that applies, so the order
    | in the JavaScript is the priority order: an empty plate, then a plate
    | dominated by one group, then a plate missing something, then balance.
    */
    'feedback' => [
        'empty' => 'ابدئي بإضافة أي أكل بتاكليه في وجبة عادية.',
        'mostly_starch' => 'الطبق ده معظمه نشويات. محتاج خضار وبروتين معاه.',
        'mostly_protein' => 'الطبق ده معظمه بروتين. الخضار هيوازنه.',
        'mostly_fat' => 'الدهون واخدة الجزء الأكبر من الطبق. جربي تضيفي خضار وبروتين.',
        'mostly_fruit' => 'الطبق كله فاكهة. حلو كوجبة خفيفة، لكن كوجبة أساسية محتاج بروتين.',
        'mostly_dairy' => 'الطبق معظمه ألبان. ضيفي خضار وحاجة فيها نشويات.',
        'mostly_vegetable' => 'خضار كتير، وده كويس. ضيفي بروتين عشان الوجبة تشبع.',
        'no_vegetable' => 'مفيش خضار على الطبق. الخضار هو اللي بيدي الوجبة حجم وإحساس بالشبع.',
        'no_protein' => 'مفيش بروتين. البروتين هو اللي بيخلي الشبع يستمر لوقت أطول.',
        'no_starch' => 'مفيش نشويات. النشويات مش عدو — هي مصدر الطاقة الأساسي.',
        'balanced' => 'الطبق ده متوازن: فيه خضار وبروتين ونشويات. كده تمام.',
    ],

    'disclaimer' => 'الأداة دي للتوضيح بس، ومش بديل عن خطة غذائية من دكتورة.',
];
