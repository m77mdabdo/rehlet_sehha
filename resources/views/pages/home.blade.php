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

    <x-sections.stories :testimonials="$testimonials" />

    <x-sections.videos :videos="$videos" />

    <x-sections.articles :posts="$posts" />

    <x-sections.faq :faqs="$faqs" />

    <x-sections.booking-cta />

    <x-sections.contact />
</x-layouts.app>
