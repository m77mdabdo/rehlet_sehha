@props([
    'as' => 'div',
    'padding' => true,
    /**
     * 'paper' is the ordinary card. 'ink' is the promoted one.
     *
     * A PROP RATHER THAN A CLASS PASSED IN FROM OUTSIDE, and that is not
     * fussiness — it is a bug that shipped. Passing `bg-ink` alongside this
     * component's own `bg-white` puts two utilities of the same property in
     * one class attribute, and the winner is decided by their order in the
     * generated stylesheet, not by the order they were written. Tailwind emits
     * bg-white after bg-ink, so the promoted card rendered white and the
     * promotion silently did nothing: correct markup, correct data, no visible
     * effect.
     */
    'tone' => 'paper',
])

@php
    $tones = [
        'paper' => 'bg-white ring-line',
        'ink' => 'bg-ink text-white ring-ink',
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
