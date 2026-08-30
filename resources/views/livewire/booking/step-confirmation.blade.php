@php
    use App\Support\Contact;

    $appointment = $this->bookedAppointment();

    /*
     * Whether anything we send will ever arrive.
     *
     * For a patient with no email address this screen is not a receipt — it is
     * the ONLY copy of the booking that will ever exist. No confirmation, no
     * reminders, and no second chance to find the manage link. The layout
     * below changes shape accordingly rather than showing her the same
     * "check your inbox" page as everybody else.
     */
    $reachable = $appointment?->isReachableByEmail() ?? true;

    $cairo = $appointment?->starts_at->clone()->setTimezone(config('clinic.timezone'));

    $manageUrl = $appointment ? route('appointment.manage', ['token' => $appointment->cancel_token]) : null;

    /*
     * The wa.me prefill for an unreachable patient carries the whole
     * appointment — reference, date, time, mode — so that sending it to
     * herself produces the record the email would have been.
     *
     * It still carries nothing clinical. This text becomes a URL, and a URL
     * survives in browser history, in a screenshot, and in the address bar
     * during a screen share. The manage token is NOT in it either: that is a
     * bearer credential, and a WhatsApp message is forwarded far more casually
     * than an email.
     */
    $whatsappRecord = $appointment ? Contact::whatsappMessageHref(__('booking.whatsapp.prefill_record', [
        'reference' => $appointment->reference,
        'when' => $cairo->translatedFormat('l j F Y — H:i'),
        'zone' => config('clinic.timezone'),
        'mode' => __('booking.mode.'.$appointment->mode->value),
    ])) : null;
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
                        <bdi dir="auto">
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

        {{-- Announces a successful copy to a screen reader; nothing moves on
             screen, and this is the one action on the page a patient with no
             other record cannot afford to be unsure about. --}}
        <div class="sr-only" role="status" aria-live="polite" data-copy-announcer></div>

        @if ($cancelToken && $reachable)
            <div class="mt-8 rounded-lg bg-white p-5 ring-1 ring-line">
                <x-button variant="ghost" :href="$manageUrl" class="w-full">
                    {{ __('booking.confirmation.manage_link') }}
                </x-button>
                <p class="mt-3 text-xs leading-relaxed text-muted">{{ __('booking.confirmation.manage_note') }}</p>
            </div>

            {{--
                WhatsApp for a patient who WILL get an email: a secondary
                convenience, carrying the reference only. She already has the
                booking in writing; this just puts it in the app she uses to
                talk to the clinic.

                A patient with no email gets a different, fuller version of
                this in the keepsake block below — for her it is the record,
                not a convenience.
            --}}
            @php
                $whatsapp = Contact::whatsappMessageHref(
                    __('booking.whatsapp.prefill_booking', ['reference' => $appointment->reference])
                );
            @endphp

            @if ($whatsapp)
                <div class="mt-4 rounded-lg bg-white p-5 ring-1 ring-line">
                    <x-button variant="ghost" :href="$whatsapp" target="_blank" rel="noopener noreferrer" class="w-full">
                        {{ __('booking.whatsapp.send_details') }}
                    </x-button>
                    <p class="mt-3 text-xs leading-relaxed text-muted">{{ __('booking.whatsapp.send_details_hint') }}</p>
                </div>
            @endif
        @endif

        {{--
            THE KEEPSAKE BLOCK.

            Only for a patient with no email address, and it is the substance
            of this whole screen for her. Nothing will be sent: no
            confirmation, no reminder the day before, no reminder an hour
            before, and no second copy of the manage link — which is a bearer
            credential generated once and never shown again.

            So the reference and the link are large, selectable and copyable
            rather than decorative, and the WhatsApp action is primary: sending
            the details to herself is the closest thing to the email she is not
            getting, and it lands in an app she already uses every day.
        --}}
        @if ($cancelToken && ! $reachable)
            <div class="mt-8 rounded-lg bg-gold/15 p-5 ring-1 ring-gold sm:p-6" data-keepsake>
                <h3 class="font-display text-base font-semibold text-ink">{{ __('booking.keepsake.title') }}</h3>
                <p class="mt-2 text-sm leading-relaxed text-ink">{{ __('booking.keepsake.lead') }}</p>

                {{-- The reference, big enough to read across a room and
                     selectable so it can be copied by hand if the button
                     fails. --}}
                <div class="mt-5 rounded-lg bg-white p-4 ring-1 ring-line">
                    <p class="text-xs text-muted">{{ __('booking.keepsake.reference_label') }}</p>
                    <div class="mt-1 flex flex-wrap items-center justify-between gap-3" data-copy-scope>
                        <bdi dir="ltr" class="select-all font-display text-2xl font-semibold tracking-wide text-ink">{{ $appointment->reference }}</bdi>

                        <button
                            type="button"
                            class="rounded-pill px-4 py-2 text-sm font-medium text-accent-dark ring-1 ring-line hover:bg-sage/60"
                            data-copy="{{ $appointment->reference }}"
                            data-idle-label="{{ __('booking.keepsake.copy') }}"
                            data-copied-label="{{ __('booking.keepsake.copied') }}"
                            {{-- Shown when BOTH clipboard paths fail. Without
                                 these the button changed nothing and said
                                 nothing, on the one screen where this is the
                                 patient's only record of her booking. --}}
                            data-manual-label="{{ __('booking.keepsake.copy_manual') }}"
                            data-manual-hint="{{ __('booking.keepsake.copy_manual_hint') }}"
                            aria-label="{{ __('booking.keepsake.copy_reference') }}"
                        >
                            <span data-copy-label>{{ __('booking.keepsake.copy') }}</span>
                        </button>
                    </div>
                </div>

                {{-- The manage link. The only route back into this booking
                     that does not involve telephoning the clinic. --}}
                <div class="mt-4 rounded-lg bg-white p-4 ring-1 ring-line" data-copy-scope>
                    <p class="text-xs text-muted">{{ __('booking.keepsake.link_label') }}</p>

                    <p class="mt-1 break-all text-xs leading-relaxed text-ink">
                        <bdi dir="ltr" class="select-all">{{ $manageUrl }}</bdi>
                    </p>

                    <div class="mt-3 flex flex-wrap gap-3">
                        <button
                            type="button"
                            class="rounded-pill px-4 py-2 text-sm font-medium text-accent-dark ring-1 ring-line hover:bg-sage/60"
                            data-copy="{{ $manageUrl }}"
                            data-idle-label="{{ __('booking.keepsake.copy') }}"
                            data-copied-label="{{ __('booking.keepsake.copied') }}"
                            {{-- Shown when BOTH clipboard paths fail. Without
                                 these the button changed nothing and said
                                 nothing, on the one screen where this is the
                                 patient's only record of her booking. --}}
                            data-manual-label="{{ __('booking.keepsake.copy_manual') }}"
                            data-manual-hint="{{ __('booking.keepsake.copy_manual_hint') }}"
                            aria-label="{{ __('booking.keepsake.copy_link') }}"
                        >
                            <span data-copy-label>{{ __('booking.keepsake.copy') }}</span>
                        </button>

                        <x-button variant="ghost" :href="$manageUrl">
                            {{ __('booking.confirmation.manage_link') }}
                        </x-button>
                    </div>

                    <p class="mt-3 text-xs leading-relaxed text-muted">{{ __('booking.keepsake.link_note') }}</p>
                </div>

                {{-- Primary action: send the record to herself. A wa.me link
                     opens her own WhatsApp with the appointment already typed;
                     nothing is sent by us. --}}
                @if ($whatsappRecord)
                    <div class="mt-5">
                        <x-button :href="$whatsappRecord" target="_blank" rel="noopener noreferrer" class="w-full">
                            {{ __('booking.keepsake.whatsapp') }}
                        </x-button>
                        <p class="mt-2 text-xs leading-relaxed text-muted">{{ __('booking.keepsake.whatsapp_hint') }}</p>
                    </div>
                @endif
            </div>

        @endif

        {{--
            The second chance, and its receipt.

            Deliberately OUTSIDE the keepsake branch above. Adding an address
            makes the patient reachable, which flips $reachable and would take
            the whole block — including the confirmation that it worked — off
            the screen in the same round trip. She would press the button and
            watch everything vanish, with no way to tell whether it saved.

            So the form shows while she is unreachable, and the acknowledgement
            shows once she is not.
        --}}
        @if ($cancelToken && (! $reachable || $lateEmailSaved))
            <div class="mt-4 rounded-lg bg-white p-5 ring-1 ring-line">
                @if ($lateEmailSaved)
                    <p class="text-sm font-medium text-ink" role="status">{{ __('booking.keepsake.add_email_saved') }}</p>
                @else
                    <h3 class="font-display text-base font-semibold text-ink">{{ __('booking.keepsake.add_email_title') }}</h3>
                    <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('booking.keepsake.add_email_hint') }}</p>

                    <div class="mt-4 flex flex-wrap items-start gap-3">
                        <div class="min-w-0 flex-1">
                            <label for="lateEmail" class="sr-only">{{ __('booking.fields.email') }}</label>
                            <input
                                id="lateEmail" type="email" wire:model.blur="lateEmail" value="{{ $lateEmail }}" dir="ltr"
                                placeholder="{{ __('booking.placeholders.email') }}" autocomplete="email"
                                class="w-full rounded-md border-0 bg-white px-4 py-3 text-start text-ink ring-1 ring-line placeholder:text-muted/70"
                            >
                        </div>

                        <x-button wire:click="saveLateEmail" wire:loading.attr="disabled" wire:target="saveLateEmail">
                            <span wire:loading.remove wire:target="saveLateEmail">{{ __('booking.keepsake.add_email_action') }}</span>
                            <span wire:loading wire:target="saveLateEmail">{{ __('common.loading') }}</span>
                        </x-button>
                    </div>

                    @error('lateEmail') <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
                @endif
            </div>

        @endif
    @endif
</div>
