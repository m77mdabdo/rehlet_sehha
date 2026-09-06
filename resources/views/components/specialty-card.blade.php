@props([
    'specialty',
    'promoted' => false,
    /** Where the heading links. An anchor on the services page, the specialty page elsewhere. */
    'href' => null,
    'showDescription' => true,
])

@php
    $href ??= route('specialties.show', ['slug' => $specialty->slug]);
@endphp

{{--
    One clinical area, as a card.

    A thin wrapper over x-feature-card that knows how a Specialty maps onto it.
    Shared by the homepage grid and the services page index so the promoted
    treatment is defined once. Two copies of this would drift the first time
    one of them was touched, and the whole point of the filled card is that it
    is the SAME device the packages comparison uses for its recommended column
    — the site reading as one site depends on it not being reimplemented.

    THE LIFT IS transform ONLY. No width, no margin, no padding, nothing in the
    box model, so hovering can never reflow the grid around it. Behind
    motion-safe, so a visitor who asked for less movement does not get a card
    that jumps under the pointer.

    IT GUIDES, IT DOES NOT SELL. Nothing on this grid has a price, links to
    checkout, or carries a "most popular" badge. The filled card is typographic
    hierarchy on a list of eight things that would otherwise read as eight
    identical tiles — see App\Support\PromotedSpecialty for why promotion here
    is explicitly not a recommendation.
--}}
<x-feature-card
    :icon="$specialty->icon"
    :title="$specialty->name"
    :body="$showDescription ? $specialty->description : null"
    :href="$href"
    :link-label="$slot->isNotEmpty() ? trim($slot->toHtml()) : __('specialties.see_packages')"
    :promoted="$promoted"
/>
