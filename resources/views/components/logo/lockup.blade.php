@props([
    'size' => 40,
    'tagline' => false,
])

{{--
    Mark plus wordmark.

    The wordmark is real, translatable HTML text — never an image. Google reads
    the clinic's name from here, it flips with the document direction for free,
    it stays sharp at any zoom, and someone using a screen reader hears the
    name rather than alt text approximating it.

    The mark tier is chosen from the size rather than passed in, so the brand
    rule holds by construction: raise this lockup past 48px and the pulse comes
    back on its own. Choosing by hand is how the hero ended up showing the
    sub-48px mark at 200px.
--}}

@php
    // 48px is the brand pack's threshold: below it the ECG pulse turns to mush,
    // above it a mark without the pulse is a different logo.
    $markTier = $size >= 48 ? 'logo.mark-full' : 'logo.mark';
@endphp

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 text-ink']) }}>
    <x-dynamic-component :component="$markTier" :size="$size" />

    <span class="flex flex-col leading-none">
        <span class="font-display text-xl font-semibold tracking-tight">
            {{ __('common.brand') }}
        </span>

        @if ($tagline)
            <span class="mt-1 text-xs font-normal text-muted">
                {{ __('common.brand_tagline') }}
            </span>
        @endif
    </span>
</span>
