@php
    use App\Support\PhoneNumber;
@endphp
<x-mail::message>
# {{ __('mail.cancelled_alert.heading') }}

{{ __('mail.cancelled_alert.lead') }}

@include('emails.partials.facts')

**{{ __('mail.new_booking.patient_name') }}:** {{ $patient->name }}
**{{ __('mail.new_booking.patient_phone') }}:** <bdi dir="ltr">{{ PhoneNumber::forDisplay($patient->phone) }}</bdi>
@if ($reason)
**{{ __('mail.cancelled_alert.reason') }}:** {{ $reason }}
@endif
</x-mail::message>
