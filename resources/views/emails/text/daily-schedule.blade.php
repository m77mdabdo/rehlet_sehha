{{ __('mail.daily_schedule.heading') }}

{{ __('mail.daily_schedule.lead', ['date' => $date->translatedFormat('l j F Y')]) }}

@if ($appointments->isEmpty())
{{ __('mail.daily_schedule.empty') }}
@else
{{ __('mail.daily_schedule.count', ['count' => $appointments->count()]) }}

@foreach ($appointments as $item)
{{ $item->startsAtClinic()->format('H:i') }} — {{ $item->patient->name }} ({{ App\Support\PhoneNumber::forDisplay($item->patient->phone) }})
    {{ $item->service->name }} · {{ $item->reference }} · {{ $item->status->label() }}@unless ($item->isReachableByEmail()) · {{ __('booking.contact_preference.phone_only') }}@endunless

@endforeach
@endif

{{ __('mail.daily_schedule.call_heading') }}
--
@if ($callList->isEmpty())
{{ __('mail.daily_schedule.call_empty') }}
@else
{{ __('mail.daily_schedule.call_lead', ['date' => $callDate->translatedFormat('l j F Y')]) }}

{{ __('mail.daily_schedule.call_count', ['count' => $callList->count()]) }}

@foreach ($callList as $item)
{{ $item->startsAtClinic()->format('H:i') }} — {{ $item->patient->name }}
    {{ App\Support\PhoneNumber::forDisplay($item->patient->phone) }} · {{ $item->service->name }} · {{ $item->reference }}
@endforeach
@endif
