{{--
    The homepage is a running order and nothing else. Every section owns its
    own markup, its own heading level and its own empty state, so changing one
    never means reading past nine others to find it.
--}}

<x-layouts.app
    :footer-services="$services"
    :schema="$schema"
>
    <x-sections.hero />

    {{-- Was inside the hero until 8.16, straddling the copy panel. See the
         section itself for why it could not stay once the copy moved onto the
         video, and why being in normal flow is what stops it overlapping the
         stats strip. --}}
    <x-sections.case-card />

    <x-sections.stats />

    <x-sections.specialties :specialties="$specialties" />

    <x-sections.packages :services="$services" />

    {{-- The matcher sits directly after the packages, because it answers the
         question the packages leave a visitor holding: which of these is
         mine. Putting it further down would mean she has already left. --}}
    <x-sections.matcher :services="$services" />

    <x-sections.how-it-works />

    <x-sections.about />

    <x-sections.plate :foods="$plateFoods" />

    {{-- Skipped entirely below three approved reviews. An empty testimonials
         block advertises that nobody has said anything. --}}
    @if ($reviews)
        <x-sections.stories :reviews="$reviews" :aggregate="$reviewAggregate" />
    @endif

    <x-sections.videos :videos="$videos" />

    <x-sections.articles :posts="$posts" />

    <x-sections.faq :faqs="$faqs" />

    <x-sections.booking-cta />

    <x-sections.contact />
</x-layouts.app>
