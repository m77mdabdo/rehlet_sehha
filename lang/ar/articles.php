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
    'meta_title' => 'مقالات — رحلة صحة',
    'meta_description' => 'مقالات مكتوبة من العيادة عن التغذية العلاجية والعادات والتحاليل، بلغة مفهومة ومن غير وعود.',

    'eyebrow' => 'مقالات',
    'title' => 'كلام مكتوب من العيادة',
    'lead' => 'مقالات قصيرة عن الأسئلة اللي بتتكرر في الجلسات. مش نصايح شخصية — دي بتتقال في جلسة، مش على صفحة.',

    /*
     * ALT TEXT for the article covers, keyed by post slug. Describes the
     * frame, never the headline — a blind reader already has the headline.
     */
    'cover_alt' => [
        'building-a-habit-that-lasts' => 'كتاب طبخ مفتوح على ترابيزة وحواليه فجل وطماطم وأفوكادو وخيار وبقسماط.',
        'protein-on-an-egyptian-budget' => 'ترابيزة مطبخ عليها خضار ورق أخضر وفلفل وطماطم وتوم وطبق لحمة وسلطانية حمص.',
        'reading-your-lab-results' => 'بنجر وكيوي ورمّان مقطوع جنب لابتوب وسماعة طبيب على سطح داكن.',
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
];
