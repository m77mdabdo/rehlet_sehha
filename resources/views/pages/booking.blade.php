<x-layouts.app :title="__('booking.title').' — '.__('common.brand')" :description="__('booking.lead')">
    <section class="py-12 sm:py-16">
        <x-container>
            <h1 class="sr-only">{{ __('booking.title') }}</h1>

            <livewire:booking-wizard :service="$preselectedService" />
        </x-container>
    </section>
</x-layouts.app>
