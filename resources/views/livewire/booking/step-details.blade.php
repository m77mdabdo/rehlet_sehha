@php
    $service = $this->service();
    $slot = $this->selectedSlot();
@endphp

<div>
    <x-section-heading
        level="h2"
        :title="__('booking.details.title')"
        :lead="__('booking.details.lead')"
    />

    {{-- Summary bar, so the patient can see what they are agreeing to without
         losing what they have typed by going back to check. --}}
    @if ($service && $slot)
        <x-card class="mt-6">
            <h3 class="sr-only">{{ __('booking.summary.title') }}</h3>

            <dl class="grid gap-4 sm:grid-cols-3">
                <div>
                    <dt class="text-xs text-muted">{{ __('booking.summary.service') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">{{ $service->name }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">{{ __('booking.summary.when') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">
                        <bdi dir="auto">{{ $slot->startsAtCairo->translatedFormat('D j M — H:i') }}</bdi>
                    </dd>
                </div>
                <div>
                    <dt class="text-xs text-muted">{{ __('booking.summary.price') }}</dt>
                    <dd class="mt-1 text-sm font-medium text-ink">
                        <bdi dir="ltr">{{ number_format((float) $service->price) }}</bdi> {{ __('common.currency') }}
                    </dd>
                </div>
            </dl>
        </x-card>
    @endif

    {{--
        EVERY FIELD RENDERS ITS CURRENT VALUE.

        Livewire does not put values into wire:model inputs server-side — it
        hydrates them in the browser. That is fine while the form stays on
        screen, but this form does not: after a collision the patient is sent
        back to step 2, and step 3's inputs are destroyed and rebuilt from the
        server's HTML when they return. Without an explicit value they come
        back EMPTY, and a patient who has just typed out her medication list
        watches it disappear — the precise outcome the collision handling
        exists to prevent. The component state was never lost; only the markup
        failed to show it.
    --}}
    <form wire:submit="submit" class="mt-8 space-y-8">
        {{-- Honeypot. Positioned off-screen rather than type="hidden": a bot
             filling every input it can read will fill this one, a human never
             sees it, and a screen reader is told to ignore it. --}}
        <div class="sr-only-focusable absolute -start-[9999px] top-0" aria-hidden="true">
            <label for="website">Website</label>
            <input id="website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
        </div>

        <fieldset class="space-y-4">
            <legend class="font-display text-base font-semibold text-ink">
                {{ __('booking.details.patient_heading') }}
            </legend>

            <div>
                <label for="name" class="block text-sm font-medium text-ink">{{ __('booking.fields.name') }}</label>
                <input
                    id="name" type="text" wire:model.blur="name" value="{{ $name }}"
                    placeholder="{{ __('booking.placeholders.name') }}"
                    autocomplete="name" required
                    class="mt-2 w-full rounded-md border-0 bg-white px-4 py-3 text-ink ring-1 ring-line placeholder:text-muted/70"
                >
                @error('name') <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="phone" class="block text-sm font-medium text-ink">{{ __('booking.fields.phone') }}</label>
                {{-- inputmode/dir so an Arabic-reading patient still gets a
                     numeric keypad and left-to-right digits. --}}
                <input
                    id="phone" type="tel" wire:model.blur="phone" value="{{ $phone }}" dir="ltr" inputmode="tel"
                    placeholder="{{ __('booking.placeholders.phone') }}"
                    autocomplete="tel" required
                    class="mt-2 w-full rounded-md border-0 bg-white px-4 py-3 text-start text-ink ring-1 ring-line placeholder:text-muted/70"
                >
                @error('phone') <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="email" class="block text-sm font-medium text-ink">
                        {{ __('booking.fields.email') }}
                        <span class="text-muted">({{ __('booking.optional') }})</span>
                    </label>
                    <input
                        id="email" type="email" wire:model.blur="email" value="{{ $email }}" dir="ltr"
                        placeholder="{{ __('booking.placeholders.email') }}" autocomplete="email"
                        class="mt-2 w-full rounded-md border-0 bg-white px-4 py-3 text-start text-ink ring-1 ring-line placeholder:text-muted/70"
                    >
                    @error('email') <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="birthDate" class="block text-sm font-medium text-ink">
                        {{ __('booking.fields.birth_date') }}
                        <span class="text-muted">({{ __('booking.optional') }})</span>
                    </label>
                    <input
                        id="birthDate" type="date" wire:model.blur="birthDate" value="{{ $birthDate }}" dir="ltr"
                        class="mt-2 w-full rounded-md border-0 bg-white px-4 py-3 text-start text-ink ring-1 ring-line"
                    >
                    @error('birthDate') <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
                </div>
            </div>
        </fieldset>

        <fieldset class="space-y-4">
            <legend class="font-display text-base font-semibold text-ink">
                {{ __('booking.details.intake_heading') }}
            </legend>
            <p class="text-sm text-muted">{{ __('booking.details.intake_note') }}</p>

            <div>
                <label for="goal" class="block text-sm font-medium text-ink">{{ __('booking.fields.goal') }}</label>
                <select
                    id="goal" wire:model.blur="goal" required
                    class="mt-2 w-full rounded-md border-0 bg-white px-4 py-3 text-ink ring-1 ring-line"
                >
                    <option value="">{{ __('booking.fields.goal') }}</option>
                    @foreach ($goals as $value => $label)
                        <option value="{{ $value }}" @selected($goal === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('goal') <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
            </div>

            @foreach ([
                'medications' => 'medications',
                'conditions' => 'conditions',
                'avoidFoods' => 'avoid_foods',
                'note' => 'note',
            ] as $property => $key)
                <div>
                    <label for="{{ $property }}" class="block text-sm font-medium text-ink">
                        {{ __('booking.fields.'.$key) }}
                        <span class="text-muted">({{ __('booking.optional') }})</span>
                    </label>
                    <textarea
                        id="{{ $property }}" wire:model.blur="{{ $property }}" rows="2"
                        placeholder="{{ __('booking.placeholders.'.$key) }}"
                        class="mt-2 w-full rounded-md border-0 bg-white px-4 py-3 text-ink ring-1 ring-line placeholder:text-muted/70"
                    >{{ $$property }}</textarea>
                    @error($property) <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
                </div>
            @endforeach
        </fieldset>

        {{-- Consent. Never pre-checked, never implied by pressing submit, and
             the wording says what is stored and what it is used for in one
             sentence a patient can actually read. --}}
        <fieldset class="rounded-lg bg-sage/60 p-5 ring-1 ring-line">
            <legend class="sr-only">{{ __('booking.consent.label') }}</legend>

            <label class="flex cursor-pointer items-start gap-3">
                <input
                    type="checkbox" wire:model.live="consent" id="consent" @checked($consent)
                    class="mt-1 size-5 shrink-0 rounded-sm accent-[color:var(--color-accent)]"
                >
                <span>
                    <span class="block text-sm font-medium text-ink">{{ __('booking.consent.label') }}</span>
                    <span class="mt-1 block text-sm leading-relaxed text-muted">{{ __('booking.consent.text') }}</span>
                </span>
            </label>

            <p class="mt-3 ms-8">
                <a href="{{ route('privacy') }}" target="_blank" rel="noopener"
                   class="text-sm font-medium text-accent-dark underline">
                    {{ __('booking.consent.link') }}
                </a>
            </p>

            @error('consent') <p class="mt-3 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
        </fieldset>

        {{--
            The no-email notice.

            A NOTICE, not a validation error, and the distinction is the whole
            point of this block. Booking without an email is allowed — a real
            share of patients here do not use email, and requiring one would
            cost the clinic those bookings outright.

            What is not allowed is her discovering afterwards that nothing was
            ever going to arrive. The field is labelled "optional", which reads
            as "we do not need it" rather than "we cannot reach you", and this
            is the last moment the difference can be explained.

            So it states exactly what does not arrive, says what happens
            instead, and offers both doors. Neither is the quiet default:
            continuing is a button she presses, not a timeout she waits out.
        --}}
        @if ($showNoEmailNotice)
            <div
                class="rounded-lg bg-gold/15 p-5 ring-1 ring-gold"
                role="alert"
                aria-live="assertive"
                tabindex="-1"
                data-no-email-notice
            >
                <h3 class="font-display text-base font-semibold text-ink">
                    {{ __('booking.no_email.title') }}
                </h3>

                <p class="mt-2 text-sm leading-relaxed text-ink">{{ __('booking.no_email.lead') }}</p>

                <ul class="mt-3 space-y-1.5">
                    @foreach (__('booking.no_email.losses') as $loss)
                        <li class="flex items-start gap-2 text-sm leading-relaxed text-ink">
                            <span aria-hidden="true">—</span><span>{{ $loss }}</span>
                        </li>
                    @endforeach
                </ul>

                <p class="mt-3 text-sm leading-relaxed text-ink">{{ __('booking.no_email.fallback') }}</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    {{-- Adding the address is listed first and styled as the
                         primary action, because it is the outcome that leaves
                         the patient better off. It is not the only one. --}}
                    <x-button type="button" wire:click="addEmailInstead" wire:loading.attr="disabled">
                        {{ __('booking.no_email.add') }}
                    </x-button>

                    <x-button
                        type="button"
                        variant="ghost"
                        wire:click="continueWithoutEmail"
                        wire:loading.attr="disabled"
                        wire:target="continueWithoutEmail"
                    >
                        <span wire:loading.remove wire:target="continueWithoutEmail">
                            {{ __('booking.no_email.continue') }}
                        </span>
                        <span wire:loading wire:target="continueWithoutEmail">{{ __('common.loading') }}</span>
                    </x-button>
                </div>
            </div>
        @endif

        <div class="flex items-center justify-between gap-3">
            <x-button type="button" variant="ghost" wire:click="back" wire:loading.attr="disabled">
                {{ __('booking.actions.back') }}
            </x-button>

            {{-- Disabled during flight, so a double-click cannot submit twice.
                 The transaction and the unique index would both survive it,
                 but the patient should never see two references. --}}
            <x-button type="submit" size="lg" wire:loading.attr="disabled" wire:target="submit">
                <span wire:loading.remove wire:target="submit">{{ __('booking.submit') }}</span>
                <span wire:loading wire:target="submit">{{ __('common.loading') }}</span>
            </x-button>
        </div>
    </form>
</div>
