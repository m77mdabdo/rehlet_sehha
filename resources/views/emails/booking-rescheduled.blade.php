@php
    use App\Support\Locales;

    $dir = Locales::direction(app()->getLocale());

    /*
     * Both times are stated, old first. A message naming only the new time is
     * indistinguishable from the original confirmation to anyone with three
     * of these in her inbox — see the notification class.
     *
     * The old time is struck through and grey; the new one is the emphasis.
     * Neither is forced to a Latin direction: an Arabic date under dir="ltr"
     * loses its day number to the far end of the string.
     */
@endphp
<x-mail::message>
# {{ __('mail.rescheduled.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.rescheduled.lead') }}

<table dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 22px 0; border-collapse: collapse;">
<tr>
<td dir="{{ $dir }}" style="padding: 11px 0; color: #4A6684; font-size: 13px; vertical-align: top; width: 38%; border-bottom: 1px solid #EEF3F8;">{{ __('mail.rescheduled.old_time') }}</td>
<td dir="{{ $dir }}" style="padding: 11px 0; color: #4A6684; font-size: 15px; text-decoration: line-through; vertical-align: top; border-bottom: 1px solid #EEF3F8;"><bdi dir="auto">{{ $previousStartsAt->translatedFormat('l j F Y — H:i') }}</bdi></td>
</tr>
<tr>
<td dir="{{ $dir }}" style="padding: 11px 0; color: #4A6684; font-size: 13px; vertical-align: top; border-bottom: 0;">{{ __('mail.rescheduled.new_time') }}</td>
<td dir="{{ $dir }}" style="padding: 11px 0; color: #0E2E4D; font-size: 16px; font-weight: 700; vertical-align: top; border-bottom: 0;"><bdi dir="auto">{{ $startsAt->translatedFormat('l j F Y — H:i') }}</bdi><br><span style="font-weight: 400; color: #4A6684; font-size: 12px;">{{ __('mail.facts.timezone', ['zone' => $timezone]) }}</span></td>
</tr>
</table>

{{ __('mail.rescheduled.note') }}

@include('emails.partials.facts')

<x-mail::button :url="$manageUrl">
{{ __('mail.manage.button') }}
</x-mail::button>

@if ($clinicPhone)
{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif
</x-mail::message>
