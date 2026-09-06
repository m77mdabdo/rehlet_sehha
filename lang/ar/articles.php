<?php

declare(strict_types=1);

/*
|------------------------------------------------------------------------------
| The articles index and the article page
|------------------------------------------------------------------------------
|
| A LIST, NOT A FILTERED PAGINATED INDEX. There are three published articles.
| Category filters and pagination over three items are scaffolding for content
| that does not exist, and they advertise the emptiness rather than covering
| it — a filter with one item behind it tells a visitor the blog is empty more
| loudly than a plain list of three ever would.
|
| When the blog is real this page grows the controls it needs. Today it does
| not need them.
|
*/

return [
    'read_more' => 'اقرأي المقال',
    'meta_title' => 'مقالات — رحلة صحة',
    'meta_description' => 'مقالات مكتوبة من العيادة عن التغذية العلاجية والعادات والتحاليل، بلغة مفهومة ومن غير وعود.',

    'eyebrow' => 'مقالات',
    'title' => 'كلام مكتوب من العيادة',
    'lead' => 'مقالات قصيرة عن الأسئلة اللي بتتكرر في الجلسات. مش نصايح شخصية — دي بتتقال في جلسة، مش على صفحة.',

    /*
     * ALT TEXT for the article covers, keyed by post slug. Describes the
     * frame, never the headline — a blind reader already has the headline.
     */
    /*
    |--------------------------------------------------------------------------
    | Cover alt text
    |--------------------------------------------------------------------------
    |
    | ONE KEY PER ARTICLE SLUG, describing what is FACTUALLY in the frame —
    | not what the article is about. A reader using a screen reader is being
    | told what the picture shows; she can read the article for the argument.
    |
    | `describes` in config/photos.php is a different thing again: it is a note
    | for whoever maintains the library. This is the copy a person hears.
    */
    'cover_alt' => [
        'why-we-quit-in-week-three' => 'سلطانيات فيها مكرونة ودقيق ودرة وعدس أحمر ورز، وجنبهم شرايح عيش، متحطين على سطح رمادي.',
        'what-the-scale-does-not-say' => 'إبريق مية من إزاز وكبايات على ترابيزة خشب جنب شباك مطبخ في ضوء دافي.',
        'what-fix-your-diet-actually-means' => 'إيدين بتقطع طماطاية على لوح خشب، وجنبها مصفاة فيها خس وخضار.',
        'dietitian-versus-downloadable-plan' => 'من فوق: إيدين ماسكة موبايل شاشته فاضية جنب سلطانية حبوب وكوباية عصير وفاكهة.',
        'normal-results-still-tired' => 'حامل فيه أنابيب تحليل دم بغطا برتقاني من غير أي بيانات مكتوبة، وجنبه جهاز طرد مركزي.',
        'what-to-bring-to-a-first-appointment' => 'إيدين بتحط ملف وأوراق في شنطة مفتوحة. مفيش وشوش في الصورة.',
        'pcos-and-food-judging-a-claim' => 'عيدان قرفة وقرفة مطحونة في سلطانية زرقا على سطح داكن، وجنبهم جوزة الطيب وحبات فلفل.',
        'questions-to-ask-about-hormones-and-food' => 'إيدين اتنين على ترابيزة بيضا، وبينهم ورقة مفتوحة فاضية ودفاتر مقفولة. مفيش وشوش.',
        'pregnancy-eating-myths' => 'طبق أبيض فيه شرايح جبنة قديمة وعيش وعين جمل وزيتون أسود على خلفية خضرا، وجنبه بيضة كاملة.',
        'feeding-and-eating-recurring-questions' => 'سلطانيتين صغيرين فيهم قرع مهروس على لوح خشب رمادي، وحواليهم قرعة ومعالق وبذور.',
        'the-child-who-will-not-eat' => 'إيدين طفلة مفرودة على مفرش أبيض جنب طبق فيه جزر وهليون وقرنبيط ما اتلمسش.',
        'eating-around-training' => 'سلطانية فيها تونة وبيض مسلوق ونص ونص وحمص وكرنب أحمر وخس ومخلل وبصل أحمر.',
        'postpartum-nutrition' => 'إيد بترفع مكرونة من حلة على البوتاجاز في مطبخ بيت، وقدامها طماطم وبيض وخضار على الرخامة.',
        'diabetes-in-women' => 'جهاز قياس سكر شاشته فاضية على ترابيزة خشب، وإيد بتحط نقطة دم على شريط التحليل، وسماعة طبيب مش واضحة وراه.',
    ],

    'empty' => 'المقالات هتكون متاحة قريب.',
    'reading_time' => ':minutes دقايق قراءة',
    'published_on' => 'اتنشر في :date',

    'author_line' => 'مكتوبة بمعرفة :name',

    /*
    |--------------------------------------------------------------------------
    | Clinical review
    |--------------------------------------------------------------------------
    |
    | Every published article names the clinician who checked it. Not a
    | decoration: an article on a clinic's site is read as advice from the
    | practitioner the reader is about to book with, and «مكتوبة بمعرفة» — the
    | old byline — said who typed it, not who is answerable for it.
    |
    | The date is separate from the publication date on purpose. An article
    | reviewed two years ago and republished last week is a different thing
    | from one reviewed last week, and the reader can only tell if both are
    | shown.
    */
    'reviewed_by' => 'روجعت إكلينيكيًا بمعرفة :name',
    'reviewed_on' => 'روجعت في :date',

    /*
    | The disclaimer, in the body rather than only in the footer.
    |
    | A footer disclaimer is read by nobody and is on the wrong screen: by the
    | time a reader reaches it she has already read the article. This one sits
    | above the first paragraph, where it is part of the thing being read.
    */
    'disclaimer_heading' => 'اقري ده الأول',
    'disclaimer_body' => 'المقال ده للتوعية العامة. مش تشخيص، ومش خطة علاجية، ومش بديل عن إنك تتكلمي مع طبيبك أو مع أخصائي تغذية شاف حالتك وتحاليلك. متغيّريش دوا ولا جرعة بناءً على أي حاجة مكتوبة هنا.',

    /*
    |--------------------------------------------------------------------------
    | References
    |--------------------------------------------------------------------------
    |
    | These articles report what named bodies recommend rather than telling the
    | reader what to do, so the list of what was reported is part of the
    | article, not an appendix to it. A claim attributed to the ADA that the
    | reader cannot trace is a claim borrowing the ADA's authority.
    */
    'references_heading' => 'المصادر',
    'references_note' => 'المقال ده بينقل توصيات جهات مسماة بالاسم. دي قايمة اللي اتنقل عنه، عشان تقدري ترجعي له بنفسك.',
    'references_open' => 'افتحي المصدر',

    'back_to_index' => 'كل المقالات',

    'related_heading' => 'مقالات في نفس الموضوع',
    'related_empty' => 'مفيش مقالات تانية في الموضوع ده لسه.',

    'share_heading' => 'شاركي المقال',
    'share_whatsapp' => 'واتساب',
    'share_copy' => 'انسخي اللينك',
    'share_copied' => 'اتنسخ',
    'share_note' => 'الأزرار دي مفيهاش أي تتبع. اللينك بيتفتح عندك، وإحنا مش بنعرف إنك شاركتي.',

    'cta' => [
        'title' => 'عندك سؤال عن حالتك إنتي؟',
        'lead' => 'المقالات بتتكلم بشكل عام. حالتك محتاجة جلسة.',
    ],

    'tag_lead' => 'كل المقالات المرتبطة بـ:tag.',
    'filter_all' => 'كل المقالات',
    'filter_heading' => 'التصنيفات',
    'in_category' => 'في :category',
    'tags_heading' => 'مواضيع المقال',
    'updated_on' => 'اتحدّث في :date',

];
