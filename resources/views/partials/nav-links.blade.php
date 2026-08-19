@props([
    'mobile' => false,
])

@php
    /*
     * Only `home` is a real route so far. The rest are in-page anchors that
     * become their own routes in Task 3 — written as full URLs rather than bare
     * fragments so they still resolve from any page once those pages exist.
     */
    $links = [
        ['label' => __('nav.services'), 'href' => route('home').'#services'],
        ['label' => __('nav.about'), 'href' => route('home').'#about'],
        ['label' => __('nav.articles'), 'href' => route('home').'#articles'],
        ['label' => __('nav.faq'), 'href' => route('home').'#faq'],
        ['label' => __('nav.contact'), 'href' => route('home').'#contact'],
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
