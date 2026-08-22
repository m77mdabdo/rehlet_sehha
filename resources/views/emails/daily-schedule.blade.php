@php
    use App\Support\Locales;
    use App\Support\PhoneNumber;

    $dir = Locales::direction(app()->getLocale());
    $th = 'padding: 8px 0; color: #4A6684; font-size: 12px; font-weight: 600; border-bottom: 1px solid #DBE0E6; vertical-align: bottom;';
    $td = 'padding: 11px 0; color: #0E2E4D; font-size: 14px; line-height: 1.5; border-bottom: 1px solid #EEF3F8; vertical-align: top;';
    $gap = $dir === 'rtl' ? 'padding-left: 12px;' : 'padding-right: 12px;';
@endphp
<x-mail::message>
# {{ __('mail.daily_schedule.heading') }}

{{ __('mail.daily_schedule.lead', ['date' => $date->translatedFormat('l j F Y')]) }}

@if ($appointments->isEmpty())
{{-- Sent even on an empty day, on purpose: a digest that only arrives when
     there is something in it cannot tell the clinic the difference between a
     quiet Tuesday and a cron that stopped running three weeks ago. --}}
<x-mail::panel>
{{ __('mail.daily_schedule.empty') }}
</x-mail::panel>
@else
**{{ __('mail.daily_schedule.count', ['count' => $appointments->count()]) }}**

<table dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 18px 0; border-collapse: collapse;">
<tr>
<th dir="{{ $dir }}" style="{{ $th }} {{ $gap }} width: 18%;">{{ __('mail.daily_schedule.time') }}</th>
<th dir="{{ $dir }}" style="{{ $th }} {{ $gap }}">{{ __('mail.daily_schedule.patient') }}</th>
<th dir="{{ $dir }}" style="{{ $th }} {{ $gap }}">{{ __('mail.daily_schedule.service') }}</th>
<th dir="{{ $dir }}" style="{{ $th }} width: 16%;">{{ __('mail.daily_schedule.status') }}</th>
</tr>
@foreach ($appointments as $item)
<tr>
<td dir="{{ $dir }}" style="{{ $td }} {{ $gap }} font-weight: 700;"><bdi dir="ltr">{{ $item->startsAtClinic()->format('H:i') }}</bdi></td>
<td dir="{{ $dir }}" style="{{ $td }} {{ $gap }}">{{ $item->patient->name }}<br><span style="color: #4A6684; font-size: 12px;"><bdi dir="ltr">{{ PhoneNumber::forDisplay($item->patient->phone) }}</bdi></span></td>
<td dir="{{ $dir }}" style="{{ $td }} {{ $gap }}">{{ $item->service->name }}<br><span style="color: #4A6684; font-size: 12px;"><bdi dir="ltr">{{ $item->reference }}</bdi></span></td>
<td dir="{{ $dir }}" style="{{ $td }} color: #4A6684; font-size: 13px;">{{ $item->status->label() }}</td>
</tr>
@endforeach
</table>
@endif
</x-mail::message>
