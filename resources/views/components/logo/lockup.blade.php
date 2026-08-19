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
--}}

<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 text-ink']) }}>
    <x-logo.mark :size="$size" />

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
