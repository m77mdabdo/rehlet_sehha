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
    'meta_title' => 'Articles — Rehlet Sehha',
    'meta_description' => 'Writing from the clinic on therapeutic nutrition, habits and lab results — in plain language, without promises.',

    'eyebrow' => 'Articles',
    'title' => 'Writing from the clinic',
    'lead' => 'Short pieces on the questions that come up again and again in sessions. Not personal advice — that belongs in a session, not on a page.',

    /*
     * ALT TEXT for the article covers, keyed by post slug. Describes the
     * frame, never the headline — a blind reader already has the headline.
     */
    'cover_alt' => [
        'building-a-habit-that-lasts' => 'An open cookbook on a table surrounded by radishes, tomatoes, avocado, cucumber and oat crackers.',
        'protein-on-an-egyptian-budget' => 'A kitchen counter with leafy greens, peppers, tomatoes, garlic, a plate of meat and a bowl of chickpeas.',
        'reading-your-lab-results' => 'Beetroot, kiwi and a halved pomegranate laid beside a laptop and a stethoscope on a dark surface.',
    ],

    'empty' => 'Articles will be available shortly.',
    'reading_time' => ':minutes min read',
    'published_on' => 'Published :date',

    'author_line' => 'Written by :name',
    'back_to_index' => 'All articles',

    'related_heading' => 'More on this topic',
    'related_empty' => 'No other articles on this topic yet.',

    'share_heading' => 'Share this',
    'share_whatsapp' => 'WhatsApp',
    'share_copy' => 'Copy link',
    'share_copied' => 'Copied',
    'share_note' => 'These buttons carry no tracking. The link opens on your device, and we do not learn that you shared it.',

    'cta' => [
        'title' => 'A question about your own situation?',
        'lead' => 'Articles speak generally. Your situation needs a session.',
    ],
];
