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

    /*
    | Household measures. See App\Enums\PortionUnit for why there are no grams
    | in this list and never will be.
    |
    | Singular and plural are separate keys rather than a trans_choice string,
    | because a trans_choice selector like [2,10] would put literal digits in
    | this file — and the rule that no digit appears in any plate string is
    | still absolute here. The count is interpolated by the component, inside
    | the scoped quantity element.
    */
    'units' => [
        'spoon' => 'ملعقة',
        'cup' => 'كوب',
        'loaf' => 'رغيف',
        'piece' => 'قطعة',
        'whole' => 'حبة',
        'small_plate' => 'طبق صغير',
    ],

    'units_plural' => [
        'spoon' => 'ملاعق',
        'cup' => 'أكواب',
        'loaf' => 'أرغفة',
        'piece' => 'قطع',
        'whole' => 'حبات',
        'small_plate' => 'أطباق صغيرة',
    ],

    'portions' => [
        'more' => 'زودي :food',
        'less' => 'قللي :food',
        'remove' => 'شيلي :food من الطبق',
        'amount' => 'الكمية',
    ],

    /*
    | THE ESTIMATE.
    |
    | A SPAN, never one figure, and never a target. Read the header of
    | PlateFeedbackHasNoNumbersTest before touching any of these: no goal, no
    | remaining count, no daily total, no percentage, no progress bar, no
    | streak, no history, and no wording a patient can fall short of.
    |
    | «حوالي» — about — is load-bearing. It is the difference between an
    | estimate and a measurement, and it stays on the front of the number.
    */
    'estimate' => [
        'label' => 'تقدير تقريبي للطاقة',
        'value' => 'حوالي :min–:max سعر حراري',
        'pending' => 'التقدير لسه مش جاهز للأكل اللي على الطبق.',
        'partial' => 'التقدير ده للأكل اللي عندنا أرقام مراجَعة له بس.',
        'varies' => 'الأكلات المركبة زي الكشري والمسقعة بتختلف كتير من بيت لبيت، فالرقم مدى واسع عن قصد.',

        /*
        | Permanent, not dismissible. This line is the condition the
        | practitioner attached to showing a number at all.
        */
        'caveat' => 'ده تقدير تقريبي، والكميات بتختلف من بيت لبيت. مش هدف ولا رقم المفروض توصليه — الخطة بتتحدد في الجلسة على حسب حالتك وتحاليلك.',
    ],

    /*
    | A food the patient adds herself. Marked as HER estimate wherever it
    | appears, so it is never mistaken for a sourced figure.
    */
    'custom' => [
        'add' => 'ضيفي أكل مش موجود',
        'name' => 'اسم الأكل',
        'group' => 'نوعه',
        'portion' => 'الكمية',
        'energy_mode' => 'الطاقة',
        'like' => 'زي أكل موجود',
        'own' => 'هكتبها بنفسي',
        'own_value' => 'تقديرك للطاقة',
        'own_hint' => 'لو مش متأكدة، اختاري أكل شبهه من القائمة.',
        'save' => 'ضيفيه للطبق',
        'cancel' => 'إلغاء',
        'badge' => 'تقديرك',
        'badge_title' => 'الأكل ده إنتي ضيفتيه، والرقم تقديرك إنتي مش رقم مراجَع من مصدر.',
        'not_saved' => 'الأكل اللي بتضيفيه مش بيتسجل عندنا، وبيختفي لما تقفلي الصفحة.',
    ],

    'disclaimer' => 'الأداة دي للتوضيح بس، ومش بديل عن خطة غذائية من دكتورة.',
];
