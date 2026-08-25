<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The review form
|------------------------------------------------------------------------------
|
| CONSENT IS A SEPARATE, UNTICKED DECISION. A patient may want to tell the
| clinic something without wanting it on the internet, and the copy says that
| plainly rather than burying it under a pre-ticked box.
|
| NO CLINICAL CONTENT. The note asks her to describe the experience rather than
| her diagnosis — a public review naming a condition is her medical
| information, published by us, in a place she cannot easily take it back from.
| The moderation queue carries the same warning for whoever approves it.
|
*/

return [
    'meta_title' => 'رأيك في الجلسة — رحلة صحة',

    'eyebrow' => 'رأيك',
    'title' => 'إزاي كانت التجربة؟',
    'lead' => 'دقيقة واحدة بالكتير. رأيك بيساعدنا نحسّن، وبيساعد حد تاني بيفكر يحجز.',

    'rating_label' => 'تقييمك',
    'rating_hint' => 'من واحد لخمسة.',
    'rating_required' => 'اختاري تقييم من فضلك.',

    'comment_label' => 'حابة تقولي إيه؟',
    'comment_hint' => 'اكتبي عن التجربة نفسها — التعامل، الخطة، المتابعة.',

    'clinical_warning_title' => 'من فضلك ما تكتبيش تفاصيل حالتك',
    'clinical_warning' => 'الرأي ده ممكن يتنشر على الموقع. اكتبي عن التجربة مش عن التشخيص أو التحاليل أو الأدوية — دي معلومات صحية خاصة بيكِ، وصعب تسحبيها بعد ما تتنشر.',

    'name_label' => 'الاسم اللي هيظهر',
    'name_hint' => 'حطينا الاسم الأول وحرف من التاني. غيّريه زي ما تحبي، أو سيبيه.',

    'consent_label' => 'موافقة إن رأيي يتنشر على الموقع',
    'consent_hint' => 'لو سبتيها فاضية، رأيك هيوصلنا وبس ومش هيتنشر في أي مكان. ومفيش رأي بيتنشر قبل ما نقراه.',

    'submit' => 'ابعتي رأيك',

    'thanks_title' => 'وصلنا، شكراً',
    'thanks_body' => 'رأيك اتسجّل. لو وافقتي على النشر، هنقراه الأول وبعدين يظهر على الموقع.',
    'thanks_private' => 'رأيك وصلنا وهيفضل بينا وبينك — مش هيتنشر.',

    'already_title' => 'إنتي كتبتي رأيك قبل كده',
    'already_body' => 'ده اللي وصلنا منك. لو عايزة تغيّري حاجة، كلّمينا على الواتساب.',

    'expired' => 'اللينك ده مش شغّال. لو محتاجة تكتبي رأيك، كلّمينا على الواتساب.',
];
