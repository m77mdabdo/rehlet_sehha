{{-- Stub. The consent notice links here, so the route must resolve; the copy
     is the clinic's to write and is not invented here. --}}
<x-layouts.app :title="__('privacy.title').' — '.__('common.brand')" :description="__('privacy.lead')">
    <section class="py-16 sm:py-20">
        <x-container size="narrow">
            <x-section-heading level="h1" :title="__('privacy.title')" :lead="__('privacy.lead')" />

            <div class="mt-8 space-y-4 text-sm leading-relaxed text-muted">
                @foreach (__('privacy.points') as $point)
                    <p>{{ $point }}</p>
                @endforeach
            </div>

            {{--
                The rights section describes buttons that exist. It replaced a
                promise to telephone the clinic, which is not a mechanism.
            --}}
            <section class="mt-12 border-t border-line pt-8" aria-labelledby="rights-heading">
                <h2 id="rights-heading" class="font-display text-xl font-semibold text-ink">
                    {{ __('privacy.rights.title') }}
                </h2>

                <p class="mt-3 text-sm leading-relaxed text-muted">{{ __('privacy.rights.lead') }}</p>
                <p class="mt-4 text-sm leading-relaxed text-muted">{{ __('privacy.rights.how') }}</p>

                <ul class="mt-4 space-y-3">
                    @foreach (__('privacy.rights.items') as $item)
                        <li class="flex items-start gap-3 text-sm leading-relaxed text-muted">
                            <svg class="mt-1 size-4 shrink-0 text-accent-dark" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-5 rounded-md bg-sage/60 p-4 text-sm leading-relaxed text-ink">
                    {{ __('privacy.rights.keeps') }}
                </p>

                <p class="mt-4 text-sm leading-relaxed text-muted">{{ __('privacy.rights.lost_link') }}</p>
            </section>

            {{-- The phone number stays, as an ADDITIONAL route rather than the
                 only one. --}}
            <p class="mt-12 border-t border-line pt-6 text-sm text-muted">{{ __('privacy.contact') }}</p>

            <div class="mt-4">
                <x-contact-details :show-address="false" class="text-ink" />
            </div>
        </x-container>
    </section>
</x-layouts.app>
