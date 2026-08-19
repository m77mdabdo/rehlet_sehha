@php
    $days = $this->days();
    $slots = $this->slots();
    $chosen = $this->service();
@endphp

<div>
    <x-section-heading
        level="h2"
        :title="__('booking.time.title')"
        :lead="__('booking.time.lead')"
    />

    {{-- Which package this calendar is for.
         Deep-linked patients arrive straight here having clicked a package on
         the homepage, and a calendar with no reminder of what is being booked
         leaves them checking by going back — which loses their place. --}}
    @if ($chosen)
        <div class="mt-6 flex flex-wrap items-center justify-between gap-3 rounded-md bg-sage/60 px-4 py-3">
            <p class="text-sm">
                <span class="text-muted">{{ __('booking.summary.service') }}:</span>
                <span class="font-medium text-ink">{{ $chosen->name }}</span>
                <span class="text-muted">
                    — {{ $chosen->duration_minutes }} {{ __('common.minutes') }}
                </span>
            </p>

            <button
                type="button"
                wire:click="back"
                class="rounded-sm text-sm font-medium text-accent-dark underline"
            >
                {{ __('booking.actions.change') }}
            </button>
        </div>
    @endif

    @if ($days === [])
        <p class="mt-8 text-muted">{{ __('booking.time.no_days') }}</p>
    @else
        {{-- Day strip. overflow-x-auto with logical scroll padding so it works
             identically in both directions. --}}
        <div class="mt-8 -mx-1 flex gap-2 overflow-x-auto px-1 pb-2" role="group" aria-label="{{ __('booking.fields.date') }}">
            @foreach ($days as $day)
                <button
                    type="button"
                    wire:key="day-{{ $day['date'] }}"
                    wire:click="selectDate('{{ $day['date'] }}')"
                    wire:loading.attr="disabled"
                    @disabled(! $day['available'])
                    aria-current="{{ $day['selected'] ? 'date' : 'false' }}"
                    @class([
                        'flex min-w-18 shrink-0 flex-col items-center rounded-md px-3 py-3 text-sm transition-colors',
                        'bg-accent text-white' => $day['selected'],
                        'bg-white text-ink ring-1 ring-line hover:bg-sage' => ! $day['selected'] && $day['available'],
                        'cursor-not-allowed bg-sage/50 text-muted/60' => ! $day['available'],
                    ])
                >
                    <span class="text-xs">{{ $day['weekday'] }}</span>
                    <span class="mt-1 font-medium">{{ $day['label'] }}</span>
                    @unless ($day['available'])
                        <span class="sr-only">— {{ __('booking.time.closed') }}</span>
                    @endunless
                </button>
            @endforeach
        </div>

        <div class="mt-6" wire:loading.class="opacity-50" wire:target="selectDate,selectSlot">
            @if ($slots->isEmpty())
                <p class="text-muted">{{ __('booking.time.no_slots') }}</p>
            @else
                <div class="grid grid-cols-3 gap-2 sm:grid-cols-4" role="group" aria-label="{{ __('booking.fields.time') }}">
                    @foreach ($slots as $slot)
                        <button
                            type="button"
                            wire:key="slot-{{ $slot->key() }}"
                            wire:click="selectSlot('{{ $slot->key() }}')"
                            wire:loading.attr="disabled"
                            aria-pressed="{{ $slotKey === $slot->key() ? 'true' : 'false' }}"
                            @class([
                                'rounded-md px-3 py-3 text-sm font-medium transition-colors',
                                'bg-accent text-white' => $slotKey === $slot->key(),
                                'bg-white text-ink ring-1 ring-line hover:bg-sage' => $slotKey !== $slot->key(),
                            ])
                        >
                            <bdi dir="ltr">{{ $slot->cairoTime() }}</bdi>
                        </button>
                    @endforeach
                </div>
            @endif
        </div>

        <p class="mt-4 text-xs text-muted">{{ __('booking.time.timezone_note') }}</p>
    @endif

    @error('slotKey')
        <p class="mt-3 text-sm text-gold" role="alert">{{ $message }}</p>
    @enderror

    <div class="mt-10 flex items-center justify-between gap-3">
        <x-button variant="ghost" wire:click="back" wire:loading.attr="disabled">
            {{ __('booking.actions.back') }}
        </x-button>

        <x-button wire:click="next" wire:loading.attr="disabled" size="lg">
            <span wire:loading.remove wire:target="next">{{ __('booking.actions.next') }}</span>
            <span wire:loading wire:target="next">{{ __('common.loading') }}</span>
        </x-button>
    </div>
</div>
