@props([
    'size' => 40,
])

{{--
    THE ICON MARK — use this UNDER 48px only.

    ┌──────────────────────────────────────────────────────────────────────┐
    │  <x-logo.mark>        under 48px only: favicon, inline nav, dense    │
    │                       UI. The ECG pulse is dropped.                  │
    │  <x-logo.mark-full>   48px and up: hero, footer, anywhere it reads   │
    │                       as the logo rather than as an icon.            │
    └──────────────────────────────────────────────────────────────────────┘

    The pulse is dropped here because a 10-unit stroke zig-zagging through a
    24px box turns to mush — NOT because it is optional. Above 48px a mark
    without the pulse is a different logo, so this component is the wrong one
    to reach for at hero size. x-logo.lockup picks the tier from its size prop;
    prefer that over choosing by hand.

    Geometry matches public/brand/mark-icon-navy.svg and is held to it by
    LogoGeometryTest. Only the framing differs: that file is cropped tight
    because an exported asset should carry no padding, while this shares the
    full mark's square 400 frame so the two render at the same optical size and
    the lockup can swap tiers at 48px without the logo visibly jumping.

    The +17 x offset centres it. Content spans x 72–362, whose midpoint is 217,
    so a 400-wide frame starts at 17. This read -17 until it was measured with
    getBBox: offsetting the FRAME is the opposite sign to offsetting the
    content, and the mark sat 34 units right of centre instead of 0.

    The ring and capsule are currentColor so the mark inverts by itself. Only
    the gold dot is fixed — it is the one element that carries the brand
    regardless of the surface behind it.

    Decorative by default: the lockup beside it already carries the name in
    real text, so announcing it again would make a screen reader say the
    clinic's name twice.
--}}

<svg
    {{ $attributes->merge(['class' => 'shrink-0']) }}
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="17 0 400 400"
    fill="none"
    xmlns="http://www.w3.org/2000/svg"
    role="presentation"
    aria-hidden="true"
    focusable="false"
>
    <circle cx="200" cy="200" r="116" fill="none" stroke="currentColor" stroke-width="24" />
    <rect x="170" y="140" width="60" height="124" rx="30" fill="currentColor" />
    <circle cx="336" cy="202" r="26" fill="var(--color-gold)" />
</svg>
