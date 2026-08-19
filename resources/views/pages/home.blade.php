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

    <x-sections.how-it-works />

    <x-sections.stories :testimonials="$testimonials" />

    <x-sections.articles :posts="$posts" />

    <x-sections.faq :faqs="$faqs" />

    <x-sections.booking-cta />

    <x-sections.contact />
</x-layouts.app>
