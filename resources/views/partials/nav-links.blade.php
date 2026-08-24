@props([
    'mobile' => false,
])

@php
    /*
     * Every entry points at a section that exists on the homepage. Written as
     * full URLs rather than bare fragments so they still resolve from the
     * booking page or an article, where "#faq" alone would go nowhere.
     *
     * #about is back: the practitioner section now exists. Its copy is still
     * placeholder (see lang/about.php), but the section is real and the link
     * resolves — and PlaceholderCopyTest stops the placeholder reaching
     * production, so the risk here is a visible TODO in staging rather than a
     * link into nothing.
     */
    $home = route('home');

    $links = [
        ['label' => __('nav.services'), 'href' => $home.'#specialties'],
        /*
         * Real routes replace anchors as each page is built. The homepage
         * sections keep their ids, so an in-page anchor still resolves for
         * anything that already points at one — but the nav sends a visitor to
         * the full page, which is where the answers are.
         */
        ['label' => __('nav.packages'), 'href' => route('packages')],
        ['label' => __('nav.how_it_works'), 'href' => $home.'#how-it-works'],
        ['label' => __('nav.about'), 'href' => $home.'#about'],
        ['label' => __('nav.articles'), 'href' => $home.'#articles'],
        ['label' => __('nav.faq'), 'href' => $home.'#faq'],
        ['label' => __('nav.contact'), 'href' => $home.'#contact'],
    ];
@endphp

@foreach ($links as $link)
    <a
        href="{{ $link['href'] }}"
        @class([
            // No colour of its own: it inherits from the header, which is ink
            // when solid and white when transparent over the hero. One source
            // of truth beats two class lists kept in step by hand.
            'font-medium transition-colors hover:text-accent',
            'group-data-transparent:hover:text-white/70' => ! $mobile,
            'block border-b border-line py-3 text-lg' => $mobile,
            'text-sm' => ! $mobile,
        ])
    >
        {{ $link['label'] }}
    </a>
@endforeach
