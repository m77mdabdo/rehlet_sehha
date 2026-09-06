@props(['specialties'])

@php
    use App\Support\PromotedSpecialty;

    /*
     * The promoted card, from the one place that decides it. Computed, never
     * hardcoded — see the class for what promotion does and does not mean on
     * a grid where nothing has a price.
     */
    $promotedIndex = PromotedSpecialty::indexIn($specialties);
@endphp

{{--
    Clinical areas — NOT the bookable packages below.

    A visitor reads this to answer "do they handle my situation?". Nothing here
    has a price and nothing links to checkout; the call to action for all of it
    is the packages section. See the specialties migration for why these are a
    separate table.
--}}

<section id="specialties" class="py-20 sm:py-24" aria-labelledby="specialties-heading">
    <x-container>
        <x-section-heading
            id="specialties-heading"
            :eyebrow="__('home.specialties.eyebrow')"
            :title="__('home.specialties.title')"
            :lead="__('home.specialties.lead')"
        />

        @if ($specialties->isEmpty())
            <p class="mt-10 text-muted">{{ __('home.specialties.empty') }}</p>
        @else
            <ul class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($specialties as $index => $specialty)
                    @php($isPromoted = $index === $promotedIndex)

                    <li class="reveal">
                        <x-specialty-card :specialty="$specialty" :promoted="$isPromoted" />
                    </li>
                @endforeach
            </ul>
        @endif
    </x-container>
</section>
