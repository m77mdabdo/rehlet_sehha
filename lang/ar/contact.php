<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The contact page
|------------------------------------------------------------------------------
|
| THE PRACTICE IS ONLINE AND HAS NO PREMISES. There is no address block on
| this page, no map, and no address in config — because there is nowhere to
| go. A published address for a practice with no premises is worse than none:
| it looks authoritative and sends a patient to a door that is not there.
|
| NO CONTACT FORM, DELIBERATELY, and the page says so rather than leaving a
| visitor hunting for one. A patient who fills in a "get in touch" box has done
| something that feels like progress and is not — she then waits, for a reply
| that competes with the booking she actually wanted. Every route offered here
| is one she controls and gets an answer from: booking, WhatsApp, the phone.
|
| The platform list comes from config/clinic.php so this page and the schema
| cannot disagree about what a session runs on.
|
|
*/

return [
    'meta_title' => 'تواصلي معانا — رحلة صحة',
    'meta_description' => 'مواعيد العمل، الواتساب والتليفون، والمنصات اللي الجلسة بتتم عليها. العيادة أونلاين والحجز متاح على مدار اليوم من غير مكالمة.',

    'eyebrow' => 'تواصل',
    'title' => 'إزاي توصلي لنا',
    'lead' => 'العيادة أونلاين. مفيش مقر تيجي له — الجلسات بتتم بالفيديو، والحجز بيتم في نفس اللحظة من غير مكالمة.',

    'book_first' => [
        'title' => 'الحجز أسرع من السؤال',
        'body' => 'الميعاد بيتحجز في نفس اللحظة، ٢٤ ساعة في اليوم، ومعاه لينك تقدري تأجّلي بيه بنفسك. مفيش مكالمة تأكيد ومفيش انتظار.',
        'cta' => 'احجزي موعدك',
    ],

    'no_form' => [
        'title' => 'مفيش فورم «تواصل معنا» هنا، وده مقصود',
        'body' => 'الفورم بيخلّيكي تعملي حاجة شكلها تقدّم وهي مش كده، وبعدين تستني. الطرق اللي تحت كلها بتديكي رد — أو ميعاد فعلي.',
    ],

    'channels_heading' => 'طرق التواصل',
    'whatsapp_note' => 'الواتساب أسرع طريقة للأسئلة القصيرة. بيترد عليه في ساعات العمل.',
    'phone_note' => 'التليفون في ساعات العمل بس.',
    'email_note' => 'الإيميل للأمور الإدارية والفواتير.',

    'online_title' => 'العيادة أونلاين، ومفيش مقر',
    'online_body' => 'كل الجلسات بتتم بالفيديو. ده مش ترتيب مؤقت، ده شكل العيادة. معناه إنك مش محتاجة تتحركي ولا تحجزي مواصلات ولا تستني في انتظار — ومعناه كمان إن مفيش عنوان تيجي له، فمتدوريش على واحد.',

    'platforms_heading' => 'الجلسة بتتم على إيه',
    'platforms_note' => 'بتختاري المنصة اللي تريحك وقت الحجز، واللينك بيوصلك مع التأكيد. مش محتاجة تسجّلي حساب ولا تنزّلي حاجة لو هتستخدمي الواتساب.',
    'platforms' => [
        'zoom' => 'زووم',
        'google_meet' => 'جوجل ميت',
        'whatsapp_video' => 'مكالمة فيديو واتساب',
    ],

    'hours_heading' => 'مواعيد العمل',
    'hours_note' => 'المواعيد دي بتاعة العيادة. الجلسات الأونلاين ممكن تتحدد بره المواعيد دي حسب الاتفاق.',
    'hours_closed' => 'مقفول',
    'hours_empty' => 'المواعيد هتتحدث قريب.',

    'expect_heading' => 'هيحصل إيه لما تتواصلي',
    'expect' => [
        'لو حجزتي: التأكيد بيوصل في نفس اللحظة، والتذكير بيوصل قبل الميعاد بيوم.',
        'لو بعتّي واتساب: بيترد عليه في ساعات العمل، عادةً في نفس اليوم.',
        'لو سؤالك طبي: هنقولك بصراحة لو محتاج جلسة بدل ما نجاوب جواب ناقص في رسالة.',
        'لو حالتك مش تخصصنا: هنقولك، وهنرشّحلك تروحي لمين.',
    ],

    'clinic_photo_heading' => 'العيادة',
    'clinic_photo_pending' => 'صور العيادة هتتضاف قريب.',

    'cta' => [
        'title' => 'أسرع طريقة تبدأي',
        'lead' => 'الحجز أونلاين بيتم في نفس اللحظة، والجلسة الأولى كلها أسئلة وإجابات.',
    ],
];
