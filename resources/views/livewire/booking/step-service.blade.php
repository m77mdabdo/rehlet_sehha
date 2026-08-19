<div>
    <x-section-heading
        level="h2"
        :title="__('booking.actions.choose_service')"
        :lead="__('booking.lead')"
    />

    <ul class="mt-8 grid gap-4 sm:grid-cols-2">
        @foreach ($services as $service)
            <li wire:key="service-{{ $service->id }}">
                {{-- A real radio, visually replaced by the card. Keyboard
                     users get arrow-key group navigation for free, which a
                     div-with-click-handler would have to reimplement badly. --}}
                <label
                    @class([
                        'flex h-full cursor-pointer flex-col rounded-lg bg-white p-5 ring-1 transition-shadow',
                        'ring-2 ring-accent shadow-md' => $serviceId === $service->id,
                        'ring-line hover:shadow-sm' => $serviceId !== $service->id,
                    ])
                >
                    <span class="flex items-start justify-between gap-3">
                        <span class="font-display text-base font-semibold text-ink">{{ $service->name }}</span>

                        <input
                            type="radio"
                            name="service"
                            value="{{ $service->id }}"
                            wire:click="selectService({{ $service->id }})"
                            @checked($serviceId === $service->id)
                            class="mt-1 size-4 shrink-0 accent-[color:var(--color-accent)]"
                        >
                    </span>

                    <span class="mt-2 flex-1 text-sm leading-relaxed text-muted">{{ $service->subtitle }}</span>

                    <span class="mt-4 flex items-baseline gap-2 border-t border-line pt-4">
                        <bdi dir="ltr" class="font-display text-2xl font-semibold text-accent">
                            {{ number_format((float) $service->price) }}
                        </bdi>
                        <span class="text-sm text-muted">{{ __('common.currency') }}</span>
                        <span class="ms-auto text-sm text-muted">
                            {{ $service->duration_minutes }} {{ __('common.minutes') }}
                        </span>
                    </span>
                </label>
            </li>
        @endforeach
    </ul>

    @error('serviceId')
        <p class="mt-3 text-sm text-gold" role="alert">{{ $message }}</p>
    @enderror

    {{-- Mode. Rendered from config, so a disabled mode is not merely hidden —
         it is not in the list at all. With one bookable mode this is a stated
         fact rather than a choice, which is more honest than a radio group
         with a single option. --}}
    <div class="mt-8">
        <h3 class="font-display text-base font-semibold text-ink">{{ __('booking.fields.mode') }}</h3>

        @if (count($modes) === 1)
            <p class="mt-2 inline-flex items-center gap-2 rounded-pill bg-sage px-4 py-2 text-sm text-ink">
                {{ reset($modes) }}
            </p>
        @else
            <div class="mt-3 flex flex-wrap gap-3">
                @foreach ($modes as $value => $label)
                    <label class="cursor-pointer">
                        <input type="radio" wire:model="mode" value="{{ $value }}" class="peer sr-only">
                        <span class="inline-flex items-center rounded-pill px-4 py-2 text-sm ring-1 ring-line peer-checked:bg-accent peer-checked:text-white">
                            {{ $label }}
                        </span>
                    </label>
                @endforeach
            </div>
        @endif

        @error('mode')
            <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p>
        @enderror
    </div>

    <div class="mt-10 flex justify-end">
        <x-button wire:click="next" wire:loading.attr="disabled" size="lg">
            <span wire:loading.remove wire:target="next">{{ __('booking.actions.next') }}</span>
            <span wire:loading wire:target="next">{{ __('common.loading') }}</span>
        </x-button>
    </div>
</div>
