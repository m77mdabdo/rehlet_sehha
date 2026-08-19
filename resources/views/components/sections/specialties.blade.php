@props(['specialties'])

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
                @foreach ($specialties as $specialty)
                    <li class="reveal">
                        <x-card class="h-full">
                            <span class="inline-flex size-12 items-center justify-center rounded-md bg-sage text-accent">
                                <x-icon :name="$specialty->icon" :size="24" />
                            </span>

                            <h3 class="mt-4 font-display text-base font-semibold text-ink">
                                {{ $specialty->name }}
                            </h3>

                            <p class="mt-2 text-sm leading-relaxed text-muted">
                                {{ $specialty->description }}
                            </p>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-container>
</section>
