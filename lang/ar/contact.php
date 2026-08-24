<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The contact page
|------------------------------------------------------------------------------
|
| NO CONTACT FORM, DELIBERATELY, and the page says so rather than leaving a
| visitor hunting for one. A patient who fills in a "get in touch" box has done
| something that feels like progress and is not — she then waits, for a reply
| that competes with the booking she actually wanted. Every route offered here
| is one she controls and gets an answer from: booking, WhatsApp, the phone.
|
| NO EMBEDDED MAP EITHER. An embed is a third-party request on a site built not
| to track its visitors, made to render an address we can render as text. The
| address block below is real text: selectable, translatable, readable by a
| screen reader, and free.
|
| Every detail comes from config/clinic.php. Dropping in the real address is a
| one-line change there, and nothing on this page needs touching.
|
*/

return [
    'meta_title' => 'تواصلي معانا — رحلة صحة',
    'meta_description' => 'عنوان العيادة، مواعيد العمل، والواتساب والتليفون. الحجز أونلاين متاح على مدار اليوم من غير مكالمة.',

    'eyebrow' => 'تواصل',
    'title' => 'إزاي توصلي لنا',
    'lead' => 'أسرع طريقة تبدأي بيها هي الحجز أونلاين — بيتم في نفس اللحظة من غير ما تستني رد. ولو عندك سؤال قبل ما تحجزي، الواتساب أسرع حاجة.',

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

    'address_heading' => 'العنوان',
    'address_note' => 'العيادة في :address. لو محتاجة تفاصيل الوصول بالظبط، ابعتي على الواتساب وهنبعتلك اللوكيشن.',
    'address_pending' => 'TODO_COPY — العنوان الكامل للعيادة: الشارع، رقم العمارة، الدور، وأقرب علامة مميزة.',

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
