@php
    $appointment = $this->bookedAppointment();
@endphp

<div>
    @if ($appointment)
        {{-- Receipt-style. The reference is the largest thing on the screen
             because it is the one piece a patient may need to read out. --}}
        <x-card class="text-center">
            <span class="inline-flex size-14 items-center justify-center rounded-pill bg-accent/10 text-accent-dark">
                <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>

            <h2 class="mt-4 font-display text-2xl font-semibold text-ink">{{ __('booking.confirmation.title') }}</h2>
            <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('booking.confirmation.lead') }}</p>

            <dl class="mt-8 space-y-4 border-t border-line pt-6 text-start">
                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-sm text-muted">{{ __('booking.confirmation.reference') }}</dt>
                    <dd class="font-display text-xl font-semibold text-ink">
                        <bdi dir="ltr">{{ $appointment->reference }}</bdi>
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-sm text-muted">{{ __('booking.summary.service') }}</dt>
                    <dd class="text-sm font-medium text-ink">{{ $appointment->service->name }}</dd>
                </div>

                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-sm text-muted">{{ __('booking.confirmation.when') }}</dt>
                    <dd class="text-end text-sm font-medium text-ink">
                        <bdi dir="ltr">
                            {{ $appointment->starts_at->clone()->setTimezone(config('clinic.timezone'))->translatedFormat('l j F Y — H:i') }}
                        </bdi>
                        {{-- The timezone is stated explicitly. A patient
                             consulting from Riyadh or London needs to know
                             which clock this is, and "17:00" alone does not
                             say. --}}
                        <span class="mt-1 block text-xs font-normal text-muted">
                            {{ __('booking.confirmation.timezone', ['zone' => config('clinic.timezone')]) }}
                        </span>
                    </dd>
                </div>

                <div class="flex items-baseline justify-between gap-4">
                    <dt class="text-sm text-muted">{{ __('booking.summary.mode') }}</dt>
                    <dd class="text-sm font-medium text-ink">{{ __('booking.mode.'.$appointment->mode->value) }}</dd>
                </div>
            </dl>

            <p class="mt-6 rounded-md bg-sage/60 p-3 text-sm text-ink">
                {{ __('booking.confirmation.status_note') }}
            </p>
        </x-card>

        <div class="mt-8">
            <h3 class="font-display text-base font-semibold text-ink">{{ __('booking.confirmation.next_title') }}</h3>

            <ol class="mt-4 space-y-3">
                @foreach (__('booking.confirmation.next') as $item)
                    <li class="flex items-start gap-3 text-sm leading-relaxed text-muted">
                        <span class="mt-2 size-1.5 shrink-0 rounded-pill bg-accent" aria-hidden="true"></span>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        @if ($cancelToken)
            <div class="mt-8 rounded-lg bg-white p-5 ring-1 ring-line">
                <x-button variant="ghost" :href="route('appointment.manage', ['token' => $cancelToken])" class="w-full">
                    {{ __('booking.confirmation.manage_link') }}
                </x-button>
                <p class="mt-3 text-xs leading-relaxed text-muted">{{ __('booking.confirmation.manage_note') }}</p>
            </div>
        @endif
    @endif
</div>
