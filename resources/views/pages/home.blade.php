<x-layouts.app :footer-services="$services">
    {{-- Hero --}}
    <section class="relative overflow-hidden bg-linear-to-b from-sage to-paper py-20 sm:py-28">
        <x-container>
            <div class="grid items-center gap-12 lg:grid-cols-12">
                <div class="lg:col-span-7">
                    <x-section-heading
                        level="h1"
                        :eyebrow="__('home.hero.eyebrow')"
                        :title="__('home.hero.title')"
                        :lead="__('home.hero.lead')"
                    >
                        <div class="mt-6 flex flex-wrap items-center gap-3">
                            <x-button :href="route('home').'#book'" size="lg">
                                {{ __('home.hero.cta') }}
                            </x-button>

                            <x-button variant="ghost" size="lg" :href="route('home').'#services'">
                                {{ __('home.hero.secondary_cta') }}
                            </x-button>
                        </div>
                    </x-section-heading>
                </div>

                {{--
                    The mark at display size, doubling as proof that the SVG
                    inherits currentColor and stays optically centred.

                    mark-full, not mark: at 200px this reads as the logo, and
                    the icon tier's simplification would be the wrong drawing —
                    it is also what the home-screen icons show.
                --}}
                <div class="hidden justify-center lg:col-span-5 lg:flex">
                    <div class="rounded-lg bg-white/70 p-14 shadow-md ring-1 ring-line">
                        <x-logo.mark-full :size="200" class="text-ink" />
                    </div>
                </div>
            </div>
        </x-container>
    </section>

    {{-- Services: real rows from the database, proving translated JSON columns render. --}}
    <section id="services" class="py-20 sm:py-24" aria-labelledby="services-heading">
        <x-container>
            <x-section-heading
                :eyebrow="__('home.services.eyebrow')"
                :title="__('home.services.title')"
                :lead="__('home.services.lead')"
                id="services-heading"
            />

            @if ($services->isEmpty())
                <p class="mt-10 text-muted">{{ __('home.services.empty') }}</p>
            @else
                <div class="mt-12 grid gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($services as $service)
                        <x-card class="flex flex-col">
                            <h3 class="font-display text-lg font-semibold text-ink">
                                {{ $service->name }}
                            </h3>

                            <p class="mt-2 flex-1 text-sm leading-relaxed text-muted">
                                {{ $service->subtitle }}
                            </p>

                            <dl class="mt-5 space-y-2 border-t border-line pt-5 text-sm">
                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-muted">{{ __('home.services.duration') }}</dt>
                                    <dd class="font-medium">
                                        {{ $service->duration_minutes }} {{ __('common.minutes') }}
                                    </dd>
                                </div>

                                <div class="flex items-center justify-between gap-3">
                                    <dt class="text-muted">{{ __('home.services.sessions') }}</dt>
                                    <dd class="font-medium">{{ $service->sessions_count }}</dd>
                                </div>
                            </dl>

                            <p class="mt-5 font-display text-2xl font-semibold text-accent">
                                {{ (int) $service->price }}
                                <span class="text-sm font-normal text-muted">{{ __('common.currency') }}</span>
                            </p>
                        </x-card>
                    @endforeach
                </div>
            @endif
        </x-container>
    </section>
</x-layouts.app>
