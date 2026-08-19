{{--
    Placeholder. Task 5 replaces the body of this page with the real booking
    form; the route, the title and the ?service= handling already work so the
    packages section can deep-link today.
--}}

<x-layouts.app :title="__('booking.title').' — '.__('common.brand')" :description="__('booking.lead')">
    <section class="py-20 sm:py-28">
        <x-container size="narrow">
            <x-section-heading
                level="h1"
                :eyebrow="__('nav.book')"
                :title="__('booking.title')"
                :lead="__('booking.lead')"
            />

            <x-card class="mt-10">
                @if ($selected)
                    {{-- Proof the deep link arrived intact. Task 5 turns this
                         into the form's preselected service. --}}
                    <p class="text-sm text-muted">{{ __('booking.fields.service') }}</p>
                    <p class="mt-1 font-display text-xl font-semibold text-ink">{{ $selected->name }}</p>

                    <dl class="mt-5 space-y-2 border-t border-line pt-5 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted">{{ __('home.packages.duration') }}</dt>
                            <dd class="font-medium">{{ $selected->duration_minutes }} {{ __('common.minutes') }}</dd>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <dt class="text-muted">{{ __('home.packages.sessions') }}</dt>
                            <dd class="font-medium">{{ $selected->sessions_count }}</dd>
                        </div>
                    </dl>
                @else
                    <p class="text-sm text-muted">{{ __('booking.lead') }}</p>
                @endif

                <p class="mt-6 border-t border-line pt-6 text-sm text-muted">
                    {{ __('booking.coming_soon') }}
                </p>

                <div class="mt-6">
                    <x-contact-details :show-address="false" class="text-ink" />
                </div>
            </x-card>
        </x-container>
    </section>
</x-layouts.app>
