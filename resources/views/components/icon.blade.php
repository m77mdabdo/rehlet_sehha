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
        /*
         * Medical nutrition — a bowl with a pulse over it.
         *
         * IT WAS A STETHOSCOPE AND THAT WAS WRONG, for the same reason a white
         * coat is wrong in a header photograph and 'stethoscope' is a refused
         * search term in FetchPexelsPhotos: Rana is a licensed nutritionist and
         * not a physician, and the instrument is the single most legible symbol
         * of "doctor" there is. An icon is more abstract than a photograph and
         * it makes the same claim.
         *
         * A bowl says food, the pulse line says clinical. Together they say
         * what this specialty actually is.
         */
        'nutrition-clinical' => '<path d="M3.5 11h17a8.5 8.5 0 0 1-17 0z"/><path d="M2.5 20.5h19"/><path d="M6.5 7.5h2l1.5-3 2 5 1.5-2h4"/>',
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

        /*
         * THE FOUR STEPS, drawn for this site and for nothing else.
         *
         * Same 24x24 box, same 1.6 stroke, same round caps and joins as the
         * eight above, so a step card and a specialty card sitting in the same
         * column do not look like two different sets bought from two places.
         * Each is built from the same primitives — a rounded rectangle, a
         * circle, a short run of line — which is what actually makes a set
         * read as a set.
         */

        // Step one, booking — a calendar with the day chosen.
        'calendar-check' => '<rect x="3" y="5" width="18" height="16" rx="2.5"/><path d="M3 10h18"/><path d="M8 3v4M16 3v4"/><path d="m9 15.5 2 2 4-4"/>',

        // Step two, the first session — two speech shapes, one listening.
        'listening' => '<path d="M3 7.5A2.5 2.5 0 0 1 5.5 5h7A2.5 2.5 0 0 1 15 7.5v3A2.5 2.5 0 0 1 12.5 13H8l-3.5 3v-3H5.5A2.5 2.5 0 0 1 3 10.5z"/><path d="M18 9.5h.5A2.5 2.5 0 0 1 21 12v3a2.5 2.5 0 0 1-2.5 2.5H18l-2.5 2.5v-2.5h-1"/>',

        // Step three, the written plan — a document with lines on it.
        'plan-document' => '<path d="M6 3h8l5 5v13a1.5 1.5 0 0 1-1.5 1.5h-11A1.5 1.5 0 0 1 5 21V4.5A1.5 1.5 0 0 1 6.5 3z"/><path d="M14 3v5h5"/><path d="M8.5 13h7M8.5 17h4.5"/>',

        // Step four, follow-up — a dial being adjusted, not a loop that repeats.
        'adjust' => '<path d="M4 8h9M17 8h3"/><circle cx="15" cy="8" r="2"/><path d="M4 16h3M11 16h9"/><circle cx="9" cy="16" r="2"/>',
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
