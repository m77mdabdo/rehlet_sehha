@props([
    'size' => 64,
])

{{--
    THE MARK. There is only one, and it is used at every size.

    Plate ring, capsule, and the gold pulse crossing through it and out to the
    endpoint dot on the right. It matches public/brand/mark-navy.svg, which is
    what a printer, a designer or a social profile gets, and LogoGeometryTest
    holds the two together so they cannot drift.

    ────────────────────────────────────────────────────────────────────────
    WHY THERE IS NO REDUCED VARIANT ANY MORE — READ THIS BEFORE "FIXING" IT.

    There used to be a second mark for use below 48px, with the pulse removed,
    and x-logo.lockup switched to it automatically. The reason was measured
    rather than invented: a 10-unit stroke zig-zagging through a 24px box has
    nowhere near the pixels it needs, so at 16–32px the pulse turns to mush and
    the favicon reads as a dark ring with a gold smudge across it.

    That is still true. It was overridden deliberately, by the client, with
    the trade-off stated and accepted: one mark everywhere is worth more than
    a legible favicon. This is a DECISION, not an oversight.

    So if you are here because the favicon looks bad at 16px — it does, that is
    known, and reintroducing a pulse-less variant is not the fix. Take it up as
    a brand question rather than a code one.
    ────────────────────────────────────────────────────────────────────────

    Only the framing differs from mark-navy.svg, deliberately. That file is
    cropped tight (viewBox "70 70 286 262") because an exported asset should
    carry no padding. Here the mark sits in a square 400 frame so it drops into
    any square slot without the caller doing arithmetic. 12.75 is the offset
    that centres the content: it spans x 76.5–349, whose midpoint is 212.75, so
    a 400-wide frame starts at 12.75. (The x offset centres the FRAME on the
    content, which is the opposite sign to offsetting the content itself — a
    sign error that once pushed the retired icon mark 34 units off centre.)

    Navy parts are currentColor so the mark inverts on the navy footer with no
    second asset. The gold is a token and stays gold on every surface: it is
    the one element that carries the brand regardless of what is behind it.

    Decorative by default: the lockup beside it already carries the name in
    real text, so announcing it again would make a screen reader say the
    clinic's name twice.
--}}

<svg
    {{ $attributes->merge(['class' => 'shrink-0']) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="12.75 0 400 400"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    role="presentation"
    aria-hidden="true"
    focusable="false"
>
    {{-- Plate ring --}}
    <circle cx="200" cy="200" r="118" fill="none" stroke="currentColor" stroke-width="11" />

    {{-- Inner dashed ring: the plate's rim, held back at 30% so it reads as
         texture rather than as a second ring competing with the first. --}}
    <circle
        cx="200"
        cy="200"
        r="98"
        fill="none"
        stroke="currentColor"
        stroke-width="2.5"
        stroke-opacity=".3"
        stroke-dasharray="5 10"
        stroke-linecap="round"
    />

    {{-- Capsule: open stroke on top, solid below. --}}
    <path d="M176 202 V174 a24 24 0 0 1 48 0 V202" fill="none" stroke="currentColor" stroke-width="8" />
    <path d="M176 202 V230 a24 24 0 0 0 48 0 V202 Z" fill="currentColor" />

    {{-- The pulse, and the endpoint it travels to. --}}
    <path
        d="M124 202 H162 L176 176 L194 230 L206 202 H320"
        fill="none"
        stroke="var(--color-gold)"
        stroke-width="10"
        stroke-linecap="round"
        stroke-linejoin="round"
    />
    <circle cx="336" cy="202" r="13" fill="var(--color-gold)" />
</svg>
