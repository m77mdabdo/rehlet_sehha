@props(['services'])

@php
    /*
     * The middle package is featured.
     *
     * Computed from the collection rather than stored as a column: with four
     * packages ordered by sort_order, "the middle one" is a presentation
     * decision about this section, not a fact about the service. A
     * services.is_featured column would be a second thing to keep in step with
     * sort_order, and the two would disagree the first time someone reorders.
     *
     * intdiv on count - 1 picks the lower middle for an even count, which puts
     * the highlight on the cheaper of the two central packages — the honest
     * side to err on.
     */
    $featuredIndex = $services->isEmpty() ? null : intdiv($services->count() - 1, 2);
@endphp

<section id="packages" class="bg-sage/50 py-20 sm:py-24" aria-labelledby="packages-heading">
    <x-container>
        <x-section-heading
            id="packages-heading"
            :eyebrow="__('home.packages.eyebrow')"
            :title="__('home.packages.title')"
            :lead="__('home.packages.lead')"
        >
            {{-- The section summarises; the page decides. Anyone still
                 comparing after four cards wants the comparison table, the
                 terms and the buying questions, and all three are there. --}}
            <a
                href="{{ route('packages') }}"
                class="mt-1 inline-flex items-center gap-1.5 text-sm font-medium text-accent-dark underline-offset-4 hover:underline"
            >
                {{ __('home.packages.see_all') }}
                <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M7 4l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </a>
        </x-section-heading>

        @if ($services->isEmpty())
            <p class="mt-10 text-muted">{{ __('home.packages.empty') }}</p>
        @else
            <ul class="mt-12 grid items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($services as $index => $service)
                    @php $isFeatured = $index === $featuredIndex; @endphp

                    <li class="reveal flex">
                        <x-card
                            @class([
                                'flex w-full flex-col',
                                'ring-2 ring-accent shadow-md' => $isFeatured,
                            ])
                        >
                            @if ($isFeatured)
                                <p class="mb-4 inline-flex self-start rounded-pill bg-accent px-3 py-1 text-xs font-medium text-white">
                                    {{ __('home.packages.featured') }}
                                </p>
                            @endif

                            <h3 class="font-display text-lg font-semibold text-ink">
                                {{ $service->name }}
                            </h3>

                            <p class="mt-2 text-sm leading-relaxed text-muted">
                                {{ $service->subtitle }}
                            </p>

                            <p class="mt-5 font-display text-3xl font-semibold text-accent">
                                <bdi dir="ltr">{{ number_format((float) $service->price) }}</bdi>
                                <span class="text-sm font-normal text-muted">{{ __('common.currency') }}</span>
                            </p>

                            <dl class="mt-5 space-y-2 border-t border-line pt-5 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-muted">{{ __('home.packages.duration') }}</dt>
                                    <dd class="font-medium">{{ $service->duration_minutes }} {{ __('common.minutes') }}</dd>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-muted">{{ __('home.packages.sessions') }}</dt>
                                    <dd class="font-medium">{{ $service->sessions_count }}</dd>
                                </div>
                            </dl>

                            @if (! empty($service->features))
                                <ul class="mt-5 space-y-2 text-sm text-muted">
                                    @foreach ($service->features as $feature)
                                        <li class="flex items-start gap-2">
                                            <svg class="mt-1 size-4 shrink-0 text-accent" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                                            </svg>
                                            <span>{{ $feature }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif

                            {{-- mt-auto pins every CTA to the bottom of its card
                                 regardless of how much copy sits above it. --}}
                            <div class="mt-auto pt-6">
                                <x-button
                                    :href="route('booking', ['service' => $service->slug])"
                                    :variant="$isFeatured ? 'primary' : 'ghost'"
                                    class="w-full"
                                >
                                    {{ __('home.packages.cta') }}
                                    <span class="sr-only">— {{ $service->name }}</span>
                                </x-button>
                            </div>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-container>
</section>
