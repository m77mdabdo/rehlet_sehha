@props([
    'size' => 64,
])

{{--
    THE FULL MARK — use this at 48px and above.

    ┌──────────────────────────────────────────────────────────────────────┐
    │  <x-logo.mark-full>   48px and up: hero, footer, anywhere it reads   │
    │                       as the logo rather than as an icon.            │
    │  <x-logo.mark>        under 48px only: favicon, inline nav, dense    │
    │                       UI. It drops the ECG pulse.                    │
    └──────────────────────────────────────────────────────────────────────┘

    The pulse is removed at icon size because a 10-unit stroke zig-zagging
    through a 24px box turns to mush — NOT because it is optional. Above 48px,
    a mark without the pulse is a different logo, so do not reach for
    <x-logo.mark> just because it is shorter to type. x-logo.lockup picks the
    tier for you from its size prop; prefer that over choosing by hand.

    Geometry is copied verbatim from public/brand/mark-navy.svg and is held to
    it by LogoGeometryTest — the inline copy and the file cannot drift apart.

    Only the framing differs from that file, deliberately. mark-navy.svg is
    cropped tight (viewBox "70 70 286 262") because an exported asset should
    carry no padding. Here the mark shares the icon tier's square 400 frame so
    the two render at the same optical size, and the lockup can swap tiers at
    48px without the logo visibly jumping. 12.75 is the offset that centres the
    content: it spans x 76.5–349, whose midpoint is 212.75, so a 400-wide frame
    starts at 12.75. (The x offset centres the FRAME on the content, which is
    the opposite sign to offsetting the content itself — the reason the icon
    tier carried a wrong -17 until it was measured.)

    Navy parts are currentColor so the mark inverts on the navy footer with no
    second asset. The gold is a token and stays gold on every surface: it is
    the one element that carries the brand regardless of what is behind it.
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
