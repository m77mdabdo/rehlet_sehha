@props([
    'compact' => false,
])

@php
    use App\Support\Locales;

    $current = Locales::current();
    // Two locales, so "the other one" is unambiguous. If a third is ever added
    // this becomes a dropdown — but building the dropdown now would be
    // speculative complexity for a menu with exactly one item in it.
    $other = collect(Locales::all())->first(fn (string $locale): bool => $locale !== $current) ?? $current;
@endphp

<a
    href="{{ Locales::alternateUrl($other) }}"
    hreflang="{{ $other }}"
    lang="{{ $other }}"
    dir="{{ Locales::direction($other) }}"
    rel="alternate"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center rounded-pill px-3 py-2 text-sm font-medium text-ink ring-1 ring-line transition-colors hover:bg-sage',
    ]) }}
>
    <span class="sr-only">{{ __('nav.switch_language') }}</span>
    <span aria-hidden="true">
        {{ $compact ? Locales::shortLabel($other) : Locales::nativeName($other) }}
    </span>
</a>
