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

    ONE MARK, AT EVERY SIZE. This used to pick between two marks from the size
    prop — a full mark at 48px and up, a pulse-less one below. There is now a
    single mark, so there is nothing to choose and the selection is gone rather
    than left switching between two identical things.
--}}

{{--
    NO COLOUR OF ITS OWN — it inherits.

    It used to hardcode text-ink, which meant the footer had to pass text-white
    and win a specificity race to invert it, and the header could not invert it
    at all: over the hero video the wordmark stayed navy on navy and measured
    1.20:1. Inheriting means the surface decides, once, and the mark and the
    wordmark can never disagree with the text around them.
--}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3']) }}>
    <x-logo.mark-full :size="$size" />

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
