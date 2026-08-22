<div>
    @if ($appointment)
        @php
            $cairo = $appointment->starts_at->clone()->setTimezone(config('clinic.timezone'));
            $cutoffHours = (int) config('clinic.booking.reschedule_min_hours', 1);
        @endphp

        <x-section-heading level="h1" :title="__('booking.manage.title')" :lead="__('booking.manage.lead')" />

        @if ($flash)
            <div class="mt-6 rounded-lg bg-sage p-4 ring-1 ring-line" role="status" aria-live="polite">
                <p class="text-sm font-medium text-ink">{{ __('booking.'.$flash.'_flash') }}</p>
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
                        <bdi dir="auto">{{ $cairo->translatedFormat('l j F Y — H:i') }}</bdi>
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

                {{--
                    A wa.me link, not a message we send. It opens the patient's
                    own WhatsApp with the reference already typed; she sends it
                    or she does not. Nothing on this site can originate a
                    WhatsApp message and no copy claims it can.

                    The prefilled text names the reference and nothing about
                    her health — it becomes a URL, and URLs end up in history
                    and in screenshots.
                --}}
                @php
                    $whatsapp = \App\Support\Contact::whatsappMessageHref(
                        __('booking.whatsapp.prefill_manage', ['reference' => $appointment->reference])
                    );
                @endphp

                @if ($whatsapp)
                    <x-button variant="ghost" :href="$whatsapp" target="_blank" rel="noopener noreferrer">
                        {{ __('booking.whatsapp.message_clinic') }}
                    </x-button>
                @endif
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

        {{--
            Data subject rights: access, correction, erasure.

            On the page the patient already has a link to, because a right that
            requires telephoning during working hours is a right most people
            never exercise.
        --}}
        @if ($intake)
            <section class="mt-12 border-t border-line pt-8" aria-labelledby="rights-heading">
                <h2 id="rights-heading" class="font-display text-lg font-semibold text-ink">
                    {{ __('booking.rights.heading') }}
                </h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('booking.rights.lead') }}</p>

                <div class="mt-5 flex flex-wrap gap-3">
                    <x-button variant="ghost" wire:click="toggleIntake" aria-expanded="{{ $showIntake ? 'true' : 'false' }}">
                        {{ $showIntake ? __('booking.rights.hide') : __('booking.rights.view') }}
                    </x-button>

                    {{-- Access without a copy is not access. A plain link, not
                         a wire:click: the file must arrive as a download, and
                         routing it through Livewire would only get in the
                         way. --}}
                    <x-button
                        variant="ghost"
                        :href="route('appointment.export', ['token' => $token])"
                        download
                    >
                        {{ __('export.download') }}
                    </x-button>
                </div>

                <p class="mt-2 text-xs text-muted">{{ __('export.download_hint') }}</p>

                @if ($showIntake)
                    <x-card class="mt-5">
                        @if ($intake->isErased())
                            {{-- Erased. Say so plainly rather than showing an
                                 empty form, which reads like data loss. --}}
                            <p class="text-sm font-medium text-ink">{{ __('booking.rights.erased_title') }}</p>
                            <p class="mt-2 text-sm leading-relaxed text-muted">
                                {{ __('booking.rights.erased_on', [
                                    'date' => $intake->erased_at->clone()->setTimezone(config('clinic.timezone'))->translatedFormat('j F Y'),
                                ]) }}
                            </p>
                        @elseif ($editingIntake)
                            <form wire:submit="saveIntake" class="space-y-4">
                                <div>
                                    <label for="goal" class="block text-sm font-medium text-ink">{{ __('booking.fields.goal') }}</label>
                                    <select id="goal" wire:model.blur="goal"
                                            class="mt-2 w-full rounded-md border-0 bg-white px-4 py-3 text-ink ring-1 ring-line">
                                        @foreach ($goals as $value => $label)
                                            <option value="{{ $value }}" @selected($goal === $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    @error('goal') <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
                                </div>

                                @foreach (['medications' => 'medications', 'conditions' => 'conditions', 'avoidFoods' => 'avoid_foods', 'note' => 'note'] as $property => $key)
                                    <div>
                                        <label for="{{ $property }}" class="block text-sm font-medium text-ink">
                                            {{ __('booking.fields.'.$key) }}
                                        </label>
                                        <textarea id="{{ $property }}" wire:model.blur="{{ $property }}" rows="2"
                                                  class="mt-2 w-full rounded-md border-0 bg-white px-4 py-3 text-ink ring-1 ring-line">{{ $$property }}</textarea>
                                        @error($property) <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p> @enderror
                                    </div>
                                @endforeach

                                <div class="flex flex-wrap gap-3 pt-2">
                                    <x-button type="submit" wire:loading.attr="disabled">
                                        <span wire:loading.remove wire:target="saveIntake">{{ __('booking.rights.save') }}</span>
                                        <span wire:loading wire:target="saveIntake">{{ __('common.loading') }}</span>
                                    </x-button>
                                    <x-button type="button" variant="ghost" wire:click="cancelEditingIntake">
                                        {{ __('booking.rights.cancel_edit') }}
                                    </x-button>
                                </div>
                            </form>
                        @else
                            <dl class="space-y-4">
                                @foreach ($intake->clinicalContent() as $key => $value)
                                    <div>
                                        <dt class="text-xs text-muted">{{ __('booking.fields.'.$key) }}</dt>
                                        <dd class="mt-1 text-sm leading-relaxed text-ink">
                                            @if ($key === 'goal' && $value)
                                                {{ __('booking.goals.'.$value) }}
                                            @else
                                                {{ $value ?: __('booking.rights.blank') }}
                                            @endif
                                        </dd>
                                    </div>
                                @endforeach
                            </dl>

                            <div class="mt-6 flex flex-wrap gap-3 border-t border-line pt-5">
                                @if ($intake->isCorrectable())
                                    <x-button variant="ghost" wire:click="startEditingIntake">
                                        {{ __('booking.rights.correct') }}
                                    </x-button>
                                @else
                                    {{-- Correction closes once the consultation
                                         has happened: a record read during a
                                         session must not change afterwards. --}}
                                    <p class="text-sm leading-relaxed text-muted">
                                        {{ __('booking.rights.correction_closed') }}
                                    </p>
                                @endif

                                <x-button variant="ghost" wire:click="startErasure">
                                    {{ __('booking.rights.erase') }}
                                </x-button>
                            </div>
                        @endif
                    </x-card>
                @endif

                @if ($confirmingErasure && ! $intake->isErased())
                    {{-- The confirmation states what goes and what stays. An
                         "are you sure?" that does not say what it deletes is
                         not informed consent to deletion. --}}
                    <x-card class="mt-5 ring-2 ring-gold">
                        <h3 class="font-display text-base font-semibold text-ink">{{ __('booking.rights.erase_confirm_title') }}</h3>

                        <p class="mt-3 text-sm font-medium text-ink">{{ __('booking.rights.erase_removes_heading') }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-muted">
                            @foreach (__('booking.rights.erase_removes') as $item)
                                <li class="flex items-start gap-2"><span aria-hidden="true">—</span><span>{{ $item }}</span></li>
                            @endforeach
                        </ul>

                        <p class="mt-4 text-sm font-medium text-ink">{{ __('booking.rights.erase_keeps_heading') }}</p>
                        <ul class="mt-2 space-y-1 text-sm text-muted">
                            @foreach (__('booking.rights.erase_keeps') as $item)
                                <li class="flex items-start gap-2"><span aria-hidden="true">—</span><span>{{ $item }}</span></li>
                            @endforeach
                        </ul>

                        @if ($appointment->starts_at->isFuture())
                            {{-- The consequence that actually matters to them. --}}
                            <p class="mt-4 rounded-md bg-gold/15 p-3 text-sm leading-relaxed text-ink">
                                {{ __('booking.rights.erase_upcoming_warning') }}
                            </p>
                        @endif

                        {{--
                            Typed confirmation, not a second button.

                            Erasure is permanent and immediate — no grace
                            period, because "deleted" that is not deleted for a
                            week destroys trust when somebody finds out. That
                            makes a mis-tap unrecoverable, so confirming has to
                            cost more than a tap. Typing is a deliberate act
                            that cannot happen in a pocket, and unlike a delay
                            it does not pretend the deletion can be undone.
                        --}}
                        <div class="mt-6 border-t border-line pt-5">
                            <label for="erasureConfirmation" class="block text-sm font-medium text-ink">
                                {{ __('booking.rights.erase_keyword_label', ['word' => $this->erasureKeyword()]) }}
                            </label>
                            <p class="mt-1 text-xs leading-relaxed text-muted">
                                {{ __('booking.rights.erase_keyword_hint') }}
                            </p>
                            <input
                                id="erasureConfirmation"
                                type="text"
                                wire:model.live="erasureConfirmation"
                                value="{{ $erasureConfirmation }}"
                                autocomplete="off"
                                autocorrect="off"
                                spellcheck="false"
                                class="mt-3 w-full max-w-xs rounded-md border-0 bg-white px-4 py-3 text-ink ring-1 ring-line"
                            >
                            @error('erasureConfirmation')
                                <p class="mt-2 text-sm text-gold" role="alert">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mt-6 flex flex-wrap gap-3">
                            <x-button
                                wire:click="eraseIntake"
                                wire:loading.attr="disabled"
                                :disabled="! $this->erasureConfirmed()"
                            >
                                <span wire:loading.remove wire:target="eraseIntake">{{ __('booking.rights.erase_confirm') }}</span>
                                <span wire:loading wire:target="eraseIntake">{{ __('common.loading') }}</span>
                            </x-button>
                            <x-button variant="ghost" wire:click="cancelErasure">
                                {{ __('booking.rights.erase_cancel') }}
                            </x-button>
                        </div>
                    </x-card>
                @endif
            </section>
        @endif
    @endif
</div>
