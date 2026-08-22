{{ __('mail.daily_schedule.heading') }}

{{ __('mail.daily_schedule.lead', ['date' => $date->translatedFormat('l j F Y')]) }}

@if ($appointments->isEmpty())
{{ __('mail.daily_schedule.empty') }}
@else
{{ __('mail.daily_schedule.count', ['count' => $appointments->count()]) }}

@foreach ($appointments as $item)
{{ $item->startsAtClinic()->format('H:i') }} — {{ $item->patient->name }} ({{ App\Support\PhoneNumber::forDisplay($item->patient->phone) }})
    {{ $item->service->name }} · {{ $item->reference }} · {{ $item->status->label() }}
@endforeach
@endif
