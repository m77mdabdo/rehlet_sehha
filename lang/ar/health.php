<?php

declare(strict_types=1);

/*
 * The page the clinic opens when it wants to know whether the site is fine.
 *
 * Written for a receptionist, not for a developer. "قاعدة البيانات" is a phrase
 * she can read out on the phone; "MySQL connection pool" is not. Every failed
 * line therefore says what stopped working for a PATIENT — no reminders going
 * out, no new bookings being saved — because that is what she needs to know in
 * order to decide whether to start phoning people.
 */

return [
    'title' => 'حالة الموقع',

    'healthy' => 'الموقع شغال تمام',
    'healthy_body' => 'كل حاجة بتشتغل زي ما المفروض. مفيش حاجة محتاجة منك أي تدخل.',

    'degraded' => 'في حاجة مش شغالة',
    'degraded_body' => 'الموقع لسه بيفتح للناس، بس في جزء واقف. ابعتي الصفحة دي للي بيمسك الموقع.',

    'ok' => 'تمام',
    'failed' => 'واقف',

    'checks' => [
        'database' => [
            'label' => 'قاعدة البيانات',
            'ok' => 'المواعيد والبيانات كلها موجودة وبتتقري.',
            'failed' => 'الحجوزات الجديدة مش هتتسجل. ده أهم حاجة تتظبط.',
        ],
        'storage' => [
            'label' => 'مساحة التخزين',
            'ok' => 'في مساحة والملفات بتتكتب.',
            'failed' => 'المساحة خلصت أو مقفولة. الاستمارات الجديدة ممكن متتحفظش.',
        ],
        'cache' => [
            'label' => 'الذاكرة المؤقتة',
            'ok' => 'شغالة.',
            'failed' => 'الموقع هيفضل شغال بس أبطأ من العادي.',
        ],
        'scheduler' => [
            'label' => 'المهام التلقائية',
            'ok' => 'اشتغلت من شوية.',
            'failed' => 'التذكيرات مش بتتبعت للمرضى، وجدول اليوم مش بيوصل الصبح.',
        ],
        'queue' => [
            'label' => 'الرسايل',
            'ok' => 'مفيش رسالة متأخرة.',
            'failed' => 'في رسايل واقفة متبعتتش. يعني في مريض متبعتلوش تأكيد أو تذكير.',
        ],
        'backup' => [
            'label' => 'النسخة الاحتياطية',
            'ok' => 'آخر نسخة اتاخدت في وقتها.',
            'failed' => 'مفيش نسخة جديدة. البيانات شغالة، بس مفيش حاجة نرجع منها لو حصل مشكلة.',
        ],
    ],
];
