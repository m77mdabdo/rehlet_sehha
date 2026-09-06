@props([
    'as' => 'div',
    'padding' => true,
    /**
     * 'paper' is the ordinary card. 'ink' is the promoted one. 'warning' is
     * the one that needs a ring rather than a fill.
     *
     * A PROP RATHER THAN A CLASS PASSED IN FROM OUTSIDE, and that is not
     * fussiness — it is a bug that shipped. Passing `bg-ink` alongside this
     * component's own `bg-white` puts two utilities of the same property in
     * one class attribute, and the winner is decided by their order in the
     * generated stylesheet, not by the order they were written. Tailwind emits
     * bg-white after bg-ink, so the promoted card rendered white and the
     * promotion silently did nothing: correct markup, correct data, no visible
     * effect.
     *
     * IT RECURRED, WHICH IS WHY THE LIST ABOVE EXISTS RATHER THAN A DOCUMENT
     * SAYING "DO NOT DO THIS". The contact page's primary booking card passed
     * `bg-ink text-white ring-0` at the call site and shipped as WHITE TEXT ON
     * WHITE — the whole call to action invisible, on the page whose job is
     * getting somebody to book. Nothing errored, the markup was correct, and
     * both classes were present in the DOM; `.bg-white` is simply emitted
     * later in the stylesheet than `.bg-ink`.
     *
     * A tone that does not exist yet is a tone to add here. It is never a
     * class to pass in.
     */
    'tone' => 'paper',
])

@php
    $tones = [
        'paper' => 'bg-white ring-line',
        'ink' => 'bg-ink text-white ring-ink',
        // A card that needs to be noticed without being alarming: the
        // erase-confirmation, where the ring is the whole signal.
        'warning' => 'bg-white ring-2 ring-gold',
    ];
@endphp

<{{ $as }}
    {{ $attributes->merge([
        'class' => 'rounded-lg ring-1 shadow-sm '
            .($tones[$tone] ?? $tones['paper']).' '
            .($padding ? 'p-6 sm:p-8' : ''),
    ]) }}
>
    {{ $slot }}
</{{ $as }}>
