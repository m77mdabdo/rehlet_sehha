@props(['absent' => false])

{{--
    Yes or no, as a symbol.

    Only used on the two comparison rows that carry a real yes/no. The sentence
    still renders in full beside it — this is a reading aid, not a replacement
    for the answer.

    aria-hidden, because the text says the same thing and a screen reader
    hearing "tick, no follow-up afterwards" is being told the opposite twice.

    NOT GOLD, and that is measured rather than preferred. Gold on white is
    2.06:1, below even the 3:1 that a meaningful graphic needs — it would be a
    decoration a low-vision patient cannot see, on the row where the answer
    matters most. Accent blue is 5.55:1 on white and 5.33:1 on the zebra
    stripe. Gold appears where it clears AA comfortably: on the navy header,
    at 6.73:1.
--}}

@if ($absent)
    {{-- A dash, muted: the absence of a thing, drawn as an absence. --}}
    <svg class="mt-1 size-4 shrink-0 text-muted" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M5 10h10" stroke-linecap="round" />
    </svg>
@else
    <svg class="mt-1 size-4 shrink-0 text-accent" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
@endif
