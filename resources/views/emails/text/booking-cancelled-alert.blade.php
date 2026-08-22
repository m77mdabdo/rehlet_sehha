{{ __('mail.cancelled_alert.heading') }}

{{ __('mail.cancelled_alert.lead') }}

@include('emails.text.partials.facts')

{{ __('mail.new_booking.patient_name') }}: {{ $patient->name }}
{{ __('mail.new_booking.patient_phone') }}: {{ App\Support\PhoneNumber::forDisplay($patient->phone) }}
@if ($reason)
{{ __('mail.cancelled_alert.reason') }}: {{ $reason }}
@endif
