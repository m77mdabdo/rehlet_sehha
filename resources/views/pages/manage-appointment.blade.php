{{--
    The self-service page. noindex because the URL contains a bearer token: a
    crawler that reached it would put a working cancellation link in a search
    index.
--}}
<x-layouts.app
    :title="__('booking.manage.title').' — '.__('common.brand')"
    :description="__('booking.manage.lead')"
    :indexable="false"
>

    <section class="py-12 sm:py-16">
        <x-container size="narrow">
            <livewire:appointment-manager :token="$token" />
        </x-container>
    </section>
</x-layouts.app>
