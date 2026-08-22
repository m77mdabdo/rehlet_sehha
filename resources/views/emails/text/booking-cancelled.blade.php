{{ __('mail.cancelled.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.cancelled.lead') }}

@include('emails.text.partials.facts')

{{ __('mail.cancelled.rebook') }}
{{ $bookingUrl }}
@if ($clinicPhone)

{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif
