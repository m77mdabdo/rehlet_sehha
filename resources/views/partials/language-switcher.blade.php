@props([
    'compact' => false,
])

@php
    /*
     * On a page whose URL carries a bearer token, this link necessarily
     * contains that token — it points at the same page in the other language.
     * That is fine as navigation: it is same-origin, the patient already holds
     * the token, and Referrer-Policy: no-referrer stops it reaching anywhere
     * else.
     *
     * What is NOT fine is rel="alternate" and hreflang. Those are instructions
     * to a crawler that this URL is a translation worth indexing — the same
     * signal the <link> tags carry, which is why those are suppressed on token
     * pages too. The link stays; the invitation to index it goes.
     */
    $advertise = $indexable ?? true;
@endphp

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
    @if ($advertise) hreflang="{{ $other }}" rel="alternate" @endif
    lang="{{ $other }}"
    dir="{{ Locales::direction($other) }}"
    {{ $attributes->merge([
        'class' => 'inline-flex items-center justify-center rounded-pill px-3 py-2 text-sm font-medium ring-1 ring-line transition-colors hover:bg-sage group-data-transparent:ring-white/60 group-data-transparent:hover:bg-white/15',
    ]) }}
>
    <span class="sr-only">{{ __('nav.switch_language') }}</span>
    <span aria-hidden="true">
        {{ $compact ? Locales::shortLabel($other) : Locales::nativeName($other) }}
    </span>
</a>
