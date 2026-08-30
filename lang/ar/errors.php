<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| Error pages
|------------------------------------------------------------------------------
|
| WRITTEN FOR A PATIENT, NOT FOR A DEVELOPER. Every one answers "what do I do
| now" rather than "what went wrong": a woman who meets a 500 on the way to
| cancelling an appointment does not care what a 500 is, she cares whether the
| appointment is still there and how to reach a person.
|
| Each carries a reassurance line, because the fear an error produces on a
| medical site is specific — that something has been lost, or booked twice, or
| charged. Saying plainly that nothing has changed is the most useful sentence
| on the page.
*/

return [
    'code' => 'خطأ :code',
    'home' => 'الصفحة الرئيسية',
    'book' => 'احجزي موعد',
    'call_us' => 'محتاجة حد يساعدك؟ كلمينا:',

    'not_found' => [
        'title' => 'الصفحة دي مش موجودة',
        'body' => 'يمكن اللينك قديم، أو فيه حرف ناقص. لو كنتي بتدوري على ميعادك، اللينك بتاعه بيتبعت في الإيميل — دوري عليه هناك.',
        'reassure' => 'ميعادك زي ما هو. الصفحة بس هي اللي مش موجودة.',
    ],

    'forbidden' => [
        'title' => 'الصفحة دي مش ليكي',
        'body' => 'اللينك ده بيفتح لحد معين بس. لو إنتِ شايفة إن المفروض تشوفيها، كلمينا ونشوف إيه اللي حصل.',
        'reassure' => 'مفيش حاجة اتغيرت في حجزك.',
    ],

    'expired' => [
        'title' => 'الصفحة قعدت مفتوحة كتير',
        'body' => 'عشان أمانك، الصفحات اللي بتتساب مفتوحة فترة طويلة بتتقفل لوحدها. افتحي الصفحة من الأول وكمّلي عادي.',
        'reassure' => 'اللي كتبتيه مااتبعتش، وممكن تعمليه تاني دلوقتي.',
    ],

    'too_many' => [
        'title' => 'محاولات كتير في وقت قصير',
        'body' => 'استني شوية وجربي تاني. لو ده حصل وإنتِ بتحاولي تحجزي، كلمينا وهنحجزلك بنفسنا.',
        'reassure' => 'ده إجراء أمان، مش مشكلة في حسابك.',
    ],

    'server' => [
        'title' => 'في حاجة عندنا مش شغالة',
        'body' => 'المشكلة من ناحيتنا مش من ناحيتك. جربي تاني بعد شوية، ولو محتاجة حاجة مستعجلة كلمينا على طول.',
        'reassure' => 'لو كنتي بتحجزي، الحجز يا اتم يا مااتمش — مفيش حاجة اتحجزت مرتين.',
    ],

    'maintenance' => [
        'title' => 'بنعمل تحديث بسيط',
        'body' => 'الموقع هيرجع خلال دقايق. لو محتاجة تحجزي أو تغيري ميعاد دلوقتي، كلمينا وهنعملها ليكي.',
        'reassure' => 'كل المواعيد المحجوزة زي ما هي.',
    ],
];
