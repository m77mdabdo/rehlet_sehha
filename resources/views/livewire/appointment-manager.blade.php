<div>
    @if ($appointment)
        @php
            $cairo = $appointment->starts_at->clone()->setTimezone(config('clinic.timezone'));
            $cutoffHours = (int) config('clinic.booking.reschedule_min_hours', 1);
        @endphp

        <x-section-heading level="h1" :title="__('booking.manage.title')" :lead="__('booking.manage.lead')" />

        @if ($flash)
            <div class="mt-6 rounded-lg bg-sage p-4 ring-1 ring-line" role="status" aria-live="polite">
                <p class="text-sm font-medium text-ink">{{ __('booking.manage.'.$flash.'_flash') }}</p>
            </div>
        @endif

        <x-card class="mt-8">
            <dl class="space-y-4">
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-sm text-muted">{{ __('booking.confirmation.reference') }}</dt>
                    <dd class="font-display text-lg font-semibold text-ink"><bdi dir="ltr">{{ $appointment->reference }}</bdi></dd>
                </div>
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-sm text-muted">{{ __('booking.summary.service') }}</dt>
                    <dd class="text-sm font-medium text-ink">{{ $appointment->service->name }}</dd>
                </div>
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-sm text-muted">{{ __('booking.confirmation.when') }}</dt>
                    <dd class="text-end text-sm font-medium text-ink">
                        <bdi dir="ltr">{{ $cairo->translatedFormat('l j F Y — H:i') }}</bdi>
                        <span class="mt-1 block text-xs font-normal text-muted">
                            {{ __('booking.confirmation.timezone', ['zone' => config('clinic.timezone')]) }}
                        </span>
                    </dd>
                </div>
                <div class="flex items-baseline justify-between gap-4 border-t border-line pt-4">
                    <dt class="text-sm text-muted">{{ __('booking.manage.status') }}</dt>
                    <dd class="text-sm font-medium text-ink">{{ $appointment->status->label() }}</dd>
                </div>
            </dl>
        </x-card>

        @php $state = $this->state(); @endphp

        @if ($state === 'cancelled')
            <p class="mt-6 text-sm text-muted">{{ __('booking.manage.already_cancelled') }}</p>
        @endif

        @if ($state === 'past')
            <p class="mt-6 text-sm text-muted">{{ __('booking.manage.past') }}</p>
        @endif

        @if ($state === 'too_late')
            {{--
                Past the cutoff. The clinic's phone number, not a disabled
                button: a patient who needs to cancel ninety minutes before
                still needs to cancel, and a greyed-out control tells her
                nothing about what to do instead.
            --}}
            <x-card class="mt-6">
                <h2 class="font-display text-base font-semibold text-ink">{{ __('booking.manage.too_late_title') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">
                    {{ __('booking.manage.too_late', ['hours' => $cutoffHours]) }}
                </p>
                <div class="mt-4">
                    <x-contact-details :show-address="false" class="text-ink" />
                </div>
            </x-card>
        @endif

        @if ($state === 'open' && ! $showReschedule)
            <div class="mt-8 flex flex-wrap gap-3">
                <x-button wire:click="startReschedule" wire:loading.attr="disabled">
                    {{ __('booking.manage.reschedule') }}
                </x-button>

                <x-button
                    variant="ghost"
                    wire:click="cancel"
                    wire:confirm="{{ __('booking.manage.cancel_confirm') }}"
                    wire:loading.attr="disabled"
                >
                    <span wire:loading.remove wire:target="cancel">{{ __('booking.manage.cancel') }}</span>
                    <span wire:loading wire:target="cancel">{{ __('common.loading') }}</span>
                </x-button>
            </div>
        @endif

        @if ($state === 'open' && $showReschedule)
            @php $days = $this->days(); $slots = $this->slots(); @endphp

            <div class="mt-8">
                <h2 class="font-display text-lg font-semibold text-ink">{{ __('booking.manage.reschedule_title') }}</h2>

                @if ($slotWasTaken)
                    <div class="mt-4 rounded-lg bg-gold/15 p-4 ring-1 ring-gold" role="alert" aria-live="assertive">
                        <p class="text-sm font-medium text-ink">{{ __('booking.errors.slot_taken') }}</p>
                    </div>
                @endif

                <div class="mt-4 -mx-1 flex gap-2 overflow-x-auto px-1 pb-2" role="group" aria-label="{{ __('booking.fields.date') }}">
                    @foreach ($days as $day)
                        <button
                            type="button"
                            wire:key="rday-{{ $day['date'] }}"
                            wire:click="selectDate('{{ $day['date'] }}')"
                            @disabled(! $day['available'])
                            aria-current="{{ $day['selected'] ? 'date' : 'false' }}"
                            @class([
                                'flex min-w-18 shrink-0 flex-col items-center rounded-md px-3 py-3 text-sm',
                                'bg-accent text-white' => $day['selected'],
                                'bg-white text-ink ring-1 ring-line hover:bg-sage' => ! $day['selected'] && $day['available'],
                                'cursor-not-allowed bg-sage/50 text-muted/60' => ! $day['available'],
                            ])
                        >
                            <span class="text-xs">{{ $day['weekday'] }}</span>
                            <span class="mt-1 font-medium">{{ $day['label'] }}</span>
                        </button>
                    @endforeach
                </div>

                @if ($slots->isEmpty())
                    <p class="mt-4 text-muted">{{ __('booking.time.no_slots') }}</p>
                @else
                    <div class="mt-4 grid grid-cols-3 gap-2 sm:grid-cols-4" role="group" aria-label="{{ __('booking.fields.time') }}">
                        @foreach ($slots as $slot)
                            <button
                                type="button"
                                wire:key="rslot-{{ $slot->key() }}"
                                wire:click="selectSlot('{{ $slot->key() }}')"
                                aria-pressed="{{ $slotKey === $slot->key() ? 'true' : 'false' }}"
                                @class([
                                    'rounded-md px-3 py-3 text-sm font-medium',
                                    'bg-accent text-white' => $slotKey === $slot->key(),
                                    'bg-white text-ink ring-1 ring-line hover:bg-sage' => $slotKey !== $slot->key(),
                                ])
                            >
                                <bdi dir="ltr">{{ $slot->cairoTime() }}</bdi>
                            </button>
                        @endforeach
                    </div>
                @endif

                <p class="mt-4 text-xs text-muted">{{ __('booking.time.timezone_note') }}</p>

                <div class="mt-6 flex flex-wrap gap-3">
                    <x-button wire:click="confirmReschedule" wire:loading.attr="disabled" :disabled="$slotKey === null">
                        <span wire:loading.remove wire:target="confirmReschedule">{{ __('booking.manage.confirm_reschedule') }}</span>
                        <span wire:loading wire:target="confirmReschedule">{{ __('common.loading') }}</span>
                    </x-button>

                    <x-button variant="ghost" wire:click="$set('showReschedule', false)">
                        {{ __('booking.manage.keep') }}
                    </x-button>
                </div>
            </div>
        @endif
    @endif
</div>
