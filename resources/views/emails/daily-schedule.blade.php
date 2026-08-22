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
<td dir="{{ $dir }}" style="{{ $td }} {{ $gap }}">{{ $item->patient->name }}<br><span style="color: #4A6684; font-size: 12px;"><bdi dir="ltr">{{ PhoneNumber::forDisplay($item->patient->phone) }}</bdi></span>
@unless ($item->isReachableByEmail())
{{-- Marked in today's list as well as tomorrow's call list. The
     practitioner reading this at 07:00 should be able to see, without
     cross-referencing anything, which of the people arriving today were
     never sent a reminder and may simply not turn up. --}}
<br><span style="color: #8A5A00; font-size: 11px; font-weight: 700;">{{ __('booking.contact_preference.phone_only') }}</span>
@endunless
</td>
<td dir="{{ $dir }}" style="{{ $td }} {{ $gap }}">{{ $item->service->name }}<br><span style="color: #4A6684; font-size: 12px;"><bdi dir="ltr">{{ $item->reference }}</bdi></span></td>
<td dir="{{ $dir }}" style="{{ $td }} color: #4A6684; font-size: 13px;">{{ $item->status->label() }}</td>
</tr>
@endforeach
</table>
@endif

{{--
    The call list — TOMORROW's patients with no email address.

    Kept as its own block, below the schedule, because it is a different job
    for a different person: the practitioner reads the schedule, whoever is on
    reception works this list. Merging them would bury a task that has a
    deadline inside a list that does not.

    These patients received no confirmation and will receive no reminder. A
    telephone call is the only thing standing between them and a missed
    appointment. Name, phone, time and service — and nothing clinical, because
    this is a list to dial from, not a file to read.
--}}
<h2>{{ __('mail.daily_schedule.call_heading') }}</h2>

@if ($callList->isEmpty())
<x-mail::panel>
{{ __('mail.daily_schedule.call_empty') }}
</x-mail::panel>
@else
{{ __('mail.daily_schedule.call_lead', ['date' => $callDate->translatedFormat('l j F Y')]) }}

**{{ __('mail.daily_schedule.call_count', ['count' => $callList->count()]) }}**

<table dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 18px 0; border-collapse: collapse;">
<tr>
<th dir="{{ $dir }}" style="{{ $th }} {{ $gap }} width: 18%;">{{ __('mail.daily_schedule.time') }}</th>
<th dir="{{ $dir }}" style="{{ $th }} {{ $gap }}">{{ __('mail.daily_schedule.patient') }}</th>
<th dir="{{ $dir }}" style="{{ $th }} {{ $gap }}">{{ __('mail.daily_schedule.phone') }}</th>
<th dir="{{ $dir }}" style="{{ $th }}">{{ __('mail.daily_schedule.service') }}</th>
</tr>
@foreach ($callList as $item)
<tr>
<td dir="{{ $dir }}" style="{{ $td }} {{ $gap }} font-weight: 700;"><bdi dir="ltr">{{ $item->startsAtClinic()->format('H:i') }}</bdi></td>
<td dir="{{ $dir }}" style="{{ $td }} {{ $gap }}">{{ $item->patient->name }}</td>
{{-- The number is the point of this table, so it is the emphasis. --}}
<td dir="{{ $dir }}" style="{{ $td }} {{ $gap }} font-weight: 700;"><bdi dir="ltr">{{ PhoneNumber::forDisplay($item->patient->phone) }}</bdi></td>
<td dir="{{ $dir }}" style="{{ $td }}">{{ $item->service->name }}<br><span style="color: #4A6684; font-size: 12px;"><bdi dir="ltr">{{ $item->reference }}</bdi></span></td>
</tr>
@endforeach
</table>
@endif
</x-mail::message>
