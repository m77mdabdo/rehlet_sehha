@props([
    'name',
    'size' => 24,
])

@php
    /*
     * The specialty icon set.
     *
     * Inline paths rather than an icon package: nothing new may be installed,
     * and eight glyphs do not justify a dependency that ships nine hundred.
     * Each is a 24×24 stroke drawing in currentColor, so it inherits the
     * surrounding text colour and works on paper and on navy without a variant.
     *
     * Keys are the `icon` column on specialties. An unknown key renders the
     * fallback dot rather than an empty box, so a typo in a seeder degrades to
     * something plain instead of collapsing the card layout.
     */
    $paths = [
        // Medical nutrition — a stethoscope.
        'stethoscope' => '<path d="M6 3v6a5 5 0 0 0 10 0V3"/><path d="M4 3h4M14 3h4"/><path d="M11 14v2a5 5 0 0 0 10 0v-1"/><circle cx="21" cy="12" r="2"/>',
        // Weight management — a target, not a scale. No number is ever shown.
        'target' => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="5"/><circle cx="12" cy="12" r="1.5"/>',
        // Pregnancy and breastfeeding — a heart held in a curve.
        'heart' => '<path d="M12 20s-7-4.6-7-9.4A3.9 3.9 0 0 1 12 8a3.9 3.9 0 0 1 7 2.6C19 15.4 12 20 12 20z"/>',
        // Sports — a bolt.
        'bolt' => '<path d="M13 2 4 14h7l-1 8 9-12h-7l1-8z"/>',
        // Children — a face.
        'smile' => '<circle cx="12" cy="12" r="9"/><path d="M8.5 14.5a4.5 4.5 0 0 0 7 0"/><path d="M9 9.5h.01M15 9.5h.01"/>',
        // PCOS and hormonal health — a cycle.
        'cycle' => '<path d="M20 12a8 8 0 1 1-2.3-5.6"/><path d="M20 4v4h-4"/><circle cx="12" cy="12" r="2.5"/>',
        // Lab review — a flask.
        'flask' => '<path d="M9 3h6"/><path d="M10 3v6.2L4.8 18A2 2 0 0 0 6.5 21h11a2 2 0 0 0 1.7-3L14 9.2V3"/><path d="M7.5 15h9"/>',
        // Corporate — a briefcase.
        'briefcase' => '<rect x="3" y="7" width="18" height="13" rx="2"/><path d="M9 7V5a2 2 0 0 1 2-2h2a2 2 0 0 1 2 2v2"/><path d="M3 12h18"/>',
    ];

    $path = $paths[$name] ?? '<circle cx="12" cy="12" r="4"/>';
@endphp

<svg
    {{ $attributes->merge(['class' => 'shrink-0']) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.6"
    stroke-linecap="round"
    stroke-linejoin="round"
    xmlns="http://www.w3.org/2000/svg"
    aria-hidden="true"
    focusable="false"
>
    {!! $path !!}
</svg>
