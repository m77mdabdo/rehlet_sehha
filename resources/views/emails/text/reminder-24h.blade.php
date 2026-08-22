{{ __('mail.reminder_24h.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.reminder_24h.lead') }}

@include('emails.text.partials.facts')

{{ __('mail.reminder_24h.note') }}

--
{{ __('mail.manage.label') }}
{{ $manageUrl }}

{{ __('mail.manage.hint') }}
@if ($clinicPhone)

{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif
