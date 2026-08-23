<x-filament-panels::page>
    {{--
        A plain form page rather than a resource. The settings table is a
        key/value store, and a generic editor over it would let anybody type
        any configuration key with any value — see App\Support\ClinicSettings
        for the allow-list that prevents exactly that.
    --}}
    <form wire:submit="save" class="space-y-6">
        {{ $this->form }}

        <div class="flex justify-start">
            <x-filament::button type="submit">
                حفظ
            </x-filament::button>
        </div>
    </form>
</x-filament-panels::page>
