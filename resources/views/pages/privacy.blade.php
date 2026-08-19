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

            <p class="mt-10 border-t border-line pt-6 text-sm text-muted">{{ __('privacy.contact') }}</p>

            <div class="mt-4">
                <x-contact-details :show-address="false" class="text-ink" />
            </div>
        </x-container>
    </section>
</x-layouts.app>
