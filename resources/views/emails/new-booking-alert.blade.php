@php
    use App\Support\Locales;
    use App\Support\PhoneNumber;

    $dir = Locales::direction(app()->getLocale());
    $cell = 'padding: 10px 0; font-size: 14px; line-height: 1.6; vertical-align: top; border-bottom: 1px solid #EEF3F8;';
    $key = 'color: #4A6684; font-size: 13px; width: 34%;';
    $gap = $dir === 'rtl' ? 'padding-left: 14px;' : 'padding-right: 14px;';
@endphp
<x-mail::message>
# {{ __('mail.new_booking.heading') }}

{{ __('mail.new_booking.lead') }}

@include('emails.partials.facts')

## {{ __('mail.new_booking.patient') }}

<table dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 16px 0; border-collapse: collapse;">
<tr>
<td dir="{{ $dir }}" style="{{ $cell }} {{ $key }} {{ $gap }}">{{ __('mail.new_booking.patient_name') }}</td>
<td dir="{{ $dir }}" style="{{ $cell }} color: #0E2E4D; font-weight: 600;">{{ $patient->name }}</td>
</tr>
<tr>
<td dir="{{ $dir }}" style="{{ $cell }} {{ $key }} {{ $gap }}">{{ __('mail.new_booking.patient_phone') }}</td>
<td dir="{{ $dir }}" style="{{ $cell }} color: #0E2E4D; font-weight: 600;"><bdi dir="ltr">{{ PhoneNumber::forDisplay($patient->phone) }}</bdi></td>
</tr>
<tr>
<td dir="{{ $dir }}" style="{{ $cell }} {{ $key }} {{ $gap }}">{{ __('mail.new_booking.patient_email') }}</td>
@if ($patient->email)
<td dir="{{ $dir }}" style="{{ $cell }} color: #0E2E4D; font-weight: 600;"><bdi dir="ltr">{{ $patient->email }}</bdi></td>
@else
{{-- Said in words, not left blank. A blank cell reads like a rendering
     fault; this row is the clinic's only warning that the patient never
     received a confirmation and has no manage link. --}}
<td dir="{{ $dir }}" style="{{ $cell }} color: #8A5A00; font-weight: 700;">{{ __('mail.new_booking.no_email') }}</td>
@endif
</tr>
<tr>
<td dir="{{ $dir }}" style="{{ $cell }} {{ $key }} {{ $gap }}">{{ __('mail.new_booking.booked_at') }}</td>
<td dir="{{ $dir }}" style="{{ $cell }} color: #0E2E4D;"><bdi dir="auto">{{ $bookedAt?->translatedFormat('j F Y — H:i') }}</bdi></td>
</tr>
<tr>
<td dir="{{ $dir }}" style="{{ $cell }} {{ $key }} {{ $gap }} border-bottom: 0;">{{ __('mail.new_booking.locale') }}</td>
<td dir="{{ $dir }}" style="{{ $cell }} color: #0E2E4D; border-bottom: 0;">{{ Locales::nativeName($appointment->locale) }}</td>
</tr>
</table>

## {{ __('mail.new_booking.intake') }}

@if ($intake === null)
{{ __('mail.new_booking.no_intake') }}
@else
<table dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 16px 0; border-collapse: collapse;">
@foreach ([
    'goal' => $goalLabel,
    'medications' => $intake->medications,
    'conditions' => $intake->conditions,
    'avoid_foods' => $intake->avoid_foods,
    'note' => $intake->note,
] as $field => $answer)
<tr>
<td dir="{{ $dir }}" style="{{ $cell }} {{ $key }} {{ $gap }}">{{ __('booking.fields.'.$field) }}</td>
<td dir="{{ $dir }}" style="{{ $cell }} color: #0E2E4D;">{{ $answer ?: __('booking.rights.blank') }}</td>
</tr>
@endforeach
</table>
@endif
</x-mail::message>
