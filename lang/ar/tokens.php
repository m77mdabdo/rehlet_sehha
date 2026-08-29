<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| Expired links
|------------------------------------------------------------------------------
|
| Shown when a token we RECOGNISE has aged out. An unrecognised token still
| 404s, so this copy never has to serve somebody probing for a valid one — it
| only ever speaks to a patient holding a real email that has gone stale.
|
| It says what happened, why, and how to reach a person. It does not apologise
| for the expiry: a link that stops working is the correct behaviour for a
| credential anybody who has the URL can use.
*/

return [
    'expired' => [
        'eyebrow' => 'اللينك ده انتهى',
        'title' => 'اللينك ده مابقاش شغال',
        'lead' => 'اللينك اللي بعتناه ليكي بينتهي بعد مدة، عشان أي حد معاه يقدر يستخدمه — فمش صح إنه يفضل شغال للأبد.',
        'appointment' => 'لينك إدارة الميعاد بيفضل شغال لحد أسبوعين بعد الميعاد نفسه.',
        'review' => 'دعوة كتابة الرأي بتفضل مفتوحة ٣٠ يوم من وقت ما بعتناها.',
        'book' => 'احجزي موعد جديد',
        'whatsapp' => 'كلمينا على واتساب',
        'note' => 'لو محتاجة توصلي لحاجة في ميعاد قديم، كلمينا وهنساعدك.',
    ],
];
