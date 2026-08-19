{{--
    The closing call to action. Task 5 replaces this band with the real booking
    form; until then it carries the same message and links to the same route,
    so nothing about the page's shape changes when the form lands.

    id="book" because the header CTA and the mobile menu both point here.
--}}

<section id="book" class="bg-ink py-20 text-white sm:py-24" aria-labelledby="book-heading">
    <x-container size="narrow" class="text-center">
        <h2 id="book-heading" class="font-display text-3xl font-semibold text-balance sm:text-4xl">
            {{ __('home.booking_cta.title') }}
        </h2>

        <p class="mt-4 text-base leading-relaxed text-pretty text-white/75 sm:text-lg">
            {{ __('home.booking_cta.lead') }}
        </p>

        <div class="mt-8 flex justify-center">
            <x-button :href="route('booking')" variant="light" size="lg">
                {{ __('home.booking_cta.cta') }}
            </x-button>
        </div>

        <p class="mt-5 text-sm text-white/55">{{ __('home.booking_cta.note') }}</p>
    </x-container>
</section>
