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
                        <x-card class="flex h-full flex-col">
                            <span class="inline-flex size-12 items-center justify-center rounded-md bg-sage text-accent">
                                <x-icon :name="$specialty->icon" :size="24" />
                            </span>

                            {{-- The heading is the link, not the whole card: a
                                 block-level anchor makes a screen reader read
                                 the entire card as one enormous link name. --}}
                            <h3 class="mt-4 font-display text-base font-semibold text-ink">
                                <a
                                    href="{{ route('specialties.show', ['slug' => $specialty->slug]) }}"
                                    class="rounded-sm transition-colors hover:text-accent-dark"
                                >
                                    {{ $specialty->name }}
                                </a>
                            </h3>

                            <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">
                                {{ $specialty->description }}
                            </p>

                            <p class="mt-4 inline-flex items-center gap-2 text-sm font-medium text-accent-dark" aria-hidden="true">
                                {{ __('specialties.see_packages') }}
                                <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M4 10h11M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </p>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-container>
</section>
