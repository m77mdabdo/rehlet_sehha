{{ __('mail.reminder_1h.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.reminder_1h.lead') }}

@include('emails.text.partials.facts')
@if ($isOnline)

{{ __('mail.reminder_1h.online_note') }}
@endif
@if ($clinicPhone)

{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif

--
{{ __('mail.manage.label') }}
{{ $manageUrl }}
