<div class="mx-auto w-full max-w-3xl">
    {{-- Stepper. aria-current marks where we are for assistive technology;
         the visual state alone would say nothing. --}}
    <ol class="flex items-center gap-2" aria-label="{{ __('booking.steps.of', ['current' => $this->step, 'total' => 4]) }}">
        @foreach ([1 => 'service', 2 => 'time', 3 => 'details', 4 => 'done'] as $number => $key)
            <li class="flex flex-1 items-center gap-2" @if ($number === $this->step) aria-current="step" @endif>
                <span
                    @class([
                        'inline-flex size-8 shrink-0 items-center justify-center rounded-pill text-sm font-semibold',
                        'bg-accent text-white' => $number <= $this->step,
                        'bg-sage text-muted' => $number > $this->step,
                    ])
                    aria-hidden="true"
                >{{ $number }}</span>

                <span @class([
                    'hidden text-sm sm:inline',
                    'font-medium text-ink' => $number <= $this->step,
                    'text-muted' => $number > $this->step,
                ])>{{ __('booking.steps.'.$key) }}</span>

                @unless ($loop->last)
                    <span @class([
                        'h-px flex-1',
                        'bg-accent' => $number < $this->step,
                        'bg-line' => $number >= $this->step,
                    ]) aria-hidden="true"></span>
                @endunless
            </li>
        @endforeach
    </ol>

    {{-- The collision message. Announced, not just shown: a patient who has
         just pressed submit is looking at the button, not the top of the page. --}}
    @if ($slotWasTaken)
        <div
            class="mt-6 rounded-lg bg-gold/15 p-4 ring-1 ring-gold"
            role="alert"
            aria-live="assertive"
            wire:key="slot-taken"
        >
            <p class="text-sm font-medium text-ink">{{ __('booking.errors.slot_taken') }}</p>
        </div>
    @endif

    <div class="mt-8">
        @if ($this->step === 1)
            @include('livewire.booking.step-service')
        @elseif ($this->step === 2)
            @include('livewire.booking.step-time')
        @elseif ($this->step === 3)
            @include('livewire.booking.step-details')
        @else
            @include('livewire.booking.step-confirmation')
        @endif
    </div>
</div>
