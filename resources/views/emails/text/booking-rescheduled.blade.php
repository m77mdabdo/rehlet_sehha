{{ __('mail.rescheduled.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.rescheduled.lead') }}

{{ __('mail.rescheduled.old_time') }}: {{ $previousStartsAt->translatedFormat('l j F Y — H:i') }}
{{ __('mail.rescheduled.new_time') }}: {{ $startsAt->translatedFormat('l j F Y — H:i') }}
{{ __('mail.facts.timezone', ['zone' => $timezone]) }}

{{ __('mail.rescheduled.note') }}

@include('emails.text.partials.facts')

--
{{ __('mail.manage.label') }}
{{ $manageUrl }}
@if ($clinicPhone)

{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif
