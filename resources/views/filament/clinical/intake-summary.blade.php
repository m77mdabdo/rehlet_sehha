{{--
    The clinical block, rendered only inside an authorised action.

    This view is never reachable on its own: the action that renders it is
    gated by IntakeFormPolicy, so a receptionist's request never constructs it
    and none of these values are read from the database on her behalf.
--}}
@php
    $fields = [
        'goal' => $intake?->goal ? __('booking.goals.'.$intake->goal) : null,
        'medications' => $intake?->medications,
        'conditions' => $intake?->conditions,
        'avoid_foods' => $intake?->avoid_foods,
        'note' => $intake?->note,
    ];
@endphp

<div class="space-y-4 text-sm">
    @if ($intake?->isErased())
        {{-- Erased at the patient's request. Said plainly rather than shown as
             five empty rows, which would read like data loss. --}}
        <p class="rounded-lg bg-amber-50 p-3 text-amber-900">
            {{ __('export.erased', [
                'date' => $intake->erased_at->clone()->setTimezone(config('clinic.timezone'))->translatedFormat('j F Y'),
            ]) }}
        </p>
    @else
        <dl class="divide-y divide-gray-200">
            @foreach ($fields as $key => $value)
                <div class="grid grid-cols-3 gap-3 py-2">
                    <dt class="text-gray-500">{{ __('booking.fields.'.$key) }}</dt>
                    <dd class="col-span-2 whitespace-pre-wrap">{{ $value ?: __('booking.rights.blank') }}</dd>
                </div>
            @endforeach
        </dl>

        @if ($intake?->consent_at)
            <p class="text-xs text-gray-500">
                {{ __('export.consent_given_on') }}:
                <bdi dir="auto">{{ $intake->consent_at->clone()->setTimezone(config('clinic.timezone'))->translatedFormat('j F Y — H:i') }}</bdi>
            </p>
        @endif
    @endif
</div>
