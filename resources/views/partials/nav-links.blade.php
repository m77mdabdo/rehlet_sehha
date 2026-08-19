@props([
    'mobile' => false,
])

@php
    /*
     * Every entry points at a section that exists on the homepage. Written as
     * full URLs rather than bare fragments so they still resolve from the
     * booking page or an article, where "#faq" alone would go nowhere.
     *
     * There is deliberately no "عن الدكتورة / About" entry any more. It used to
     * point at #about, which was never built — the homepage has no
     * practitioner-bio section, so the link had nothing honest to resolve to.
     * #how-it-works is the section that actually answers "how does this
     * clinic work", and #packages was added because it is the page's main
     * conversion path and was reachable only by scrolling.
     */
    $home = route('home');

    $links = [
        ['label' => __('nav.services'), 'href' => $home.'#specialties'],
        ['label' => __('nav.packages'), 'href' => $home.'#packages'],
        ['label' => __('nav.how_it_works'), 'href' => $home.'#how-it-works'],
        ['label' => __('nav.articles'), 'href' => $home.'#articles'],
        ['label' => __('nav.faq'), 'href' => $home.'#faq'],
        ['label' => __('nav.contact'), 'href' => $home.'#contact'],
    ];
@endphp

@foreach ($links as $link)
    <a
        href="{{ $link['href'] }}"
        @class([
            'font-medium text-ink transition-colors hover:text-accent',
            'block border-b border-line py-3 text-lg' => $mobile,
            'text-sm' => ! $mobile,
        ])
    >
        {{ $link['label'] }}
    </a>
@endforeach
