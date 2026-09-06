{{--
    A clinical area's landing page.

    Lean on purpose. Someone arrives here from a search for their own
    condition and is deciding, in about fifteen seconds, whether this clinic
    handles it. That decision needs the name, what is actually covered, the
    packages that suit it, and a way to book — in that order. Everything else
    is a reason to scroll past the booking button.
--}}

<x-layouts.app
    :title="$specialty->name.' — '.__('common.brand')"
    :description="$specialty->description"
    :schema="$schema"
>
    {{-- The same header every standalone page gets. The icon and the two
         buttons that used to sit inside it now follow it on paper: a coloured
         icon tile and two solid pills fight a photograph, and the header's job
         is the title. --}}
    <x-page-header
        page="specialties"
        :title="$specialty->name"
        :lead="$specialty->description"
        :trail="[
            ['label' => __('nav.home'), 'url' => route('home')],
            ['label' => __('nav.services'), 'url' => route('home').'#specialties'],
            ['label' => $specialty->name],
        ]"
    />

    <section class="border-b border-line bg-paper py-10">
        <x-container>
            <div class="flex flex-wrap items-center gap-5">
                <span class="inline-flex size-14 shrink-0 items-center justify-center rounded-md bg-sage text-accent">
                    <x-icon :name="$specialty->icon" :size="28" />
                </span>

                <div class="flex flex-wrap gap-3">
                    <x-button :href="route('booking')" size="lg">{{ __('home.hero.cta') }}</x-button>
                    <x-button variant="ghost" size="lg" href="#packages">{{ __('specialties.see_packages') }}</x-button>
                </div>
            </div>
        </x-container>
    </section>

    {{-- What a first session in this area actually involves. Generic across
         areas by design: the specifics belong to the consultation, and
         inventing per-area clinical detail would be writing medical copy. --}}
    <section class="py-16 sm:py-20" aria-labelledby="covered-heading">
        <x-container>
            <x-section-heading
                id="covered-heading"
                :eyebrow="__('specialties.covered.eyebrow')"
                :title="__('specialties.covered.title')"
                :lead="__('specialties.covered.lead')"
            />

            <ul class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach (__('specialties.covered.items') as $item)
                    <li class="reveal">
                        <x-card class="h-full">
                            <h3 class="font-display text-base font-semibold text-ink">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-muted">{{ $item['body'] }}</p>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        </x-container>
    </section>

    <section id="packages" class="bg-sage/50 py-16 sm:py-20" aria-labelledby="specialty-packages-heading">
        <x-container>
            <x-section-heading
                id="specialty-packages-heading"
                :eyebrow="__('home.packages.eyebrow')"
                :title="__('specialties.packages.title', ['specialty' => $specialty->name])"
                :lead="__('specialties.packages.lead')"
            />

            @if ($services->isEmpty())
                {{-- No pairing seeded. Better to say nothing about packages than
                     to fall back to the full price list, which would undo the
                     entire point of narrowing. --}}
                <p class="mt-10 text-muted">{{ __('specialties.packages.empty') }}</p>
            @else
                <ul class="mt-10 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($services as $index => $service)
                        <li class="reveal flex">
                            <x-card @class(['flex w-full flex-col', 'ring-2 ring-accent' => $index === 0])>
                                @if ($index === 0)
                                    {{-- First by pivot sort_order — "start here
                                         for THIS area", which is not the same as
                                         the homepage's most-popular. --}}
                                    <p class="mb-4 inline-flex self-start rounded-pill bg-accent px-3 py-1 text-xs font-medium text-white">
                                        {{ __('specialties.packages.recommended') }}
                                    </p>
                                @endif

                                <h3 class="font-display text-lg font-semibold text-ink">{{ $service->name }}</h3>
                                <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">{{ $service->subtitle }}</p>

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

                                <div class="mt-auto pt-6">
                                    <x-button
                                        :href="route('booking', ['service' => $service->slug])"
                                        :variant="$index === 0 ? 'primary' : 'ghost'"
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

    @if ($others->isNotEmpty())
        <section class="py-16 sm:py-20" aria-labelledby="other-areas-heading">
            <x-container>
                <h2 id="other-areas-heading" class="font-display text-2xl font-semibold text-ink">
                    {{ __('specialties.others') }}
                </h2>

                <ul class="mt-6 flex flex-wrap gap-3">
                    @foreach ($others as $other)
                        <li>
                            <a
                                href="{{ route('specialties.show', ['slug' => $other->slug]) }}"
                                class="inline-flex items-center gap-2 rounded-pill bg-white px-4 py-2 text-sm font-medium text-ink ring-1 ring-line transition-colors hover:bg-sage"
                            >
                                <x-icon :name="$other->icon" :size="16" class="text-accent-dark" />
                                {{ $other->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </x-container>
        </section>
    @endif

    <x-sections.booking-cta />
</x-layouts.app>
