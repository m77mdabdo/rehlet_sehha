@php
    use App\Support\PhoneNumber;
@endphp
<x-mail::message>
# {{ __('mail.delivery_failed.heading') }}

{{ __('mail.delivery_failed.lead') }}

<x-mail::panel>
**{{ __('mail.delivery_failed.action') }}**
</x-mail::panel>

**{{ __('mail.facts.reference') }}:** <bdi dir="ltr">{{ $reference }}</bdi>
**{{ __('mail.facts.when') }}:** <bdi dir="auto">{{ $startsAt->translatedFormat('l j F Y — H:i') }}</bdi> — {{ __('mail.facts.timezone', ['zone' => $timezone]) }}
**{{ __('mail.new_booking.patient_name') }}:** {{ $patient->name }}
**{{ __('mail.new_booking.patient_phone') }}:** <bdi dir="ltr">{{ PhoneNumber::forDisplay($patient->phone) }}</bdi>
**{{ __('mail.delivery_failed.template') }}:** <bdi dir="ltr">{{ $failedTemplate }}</bdi>
**{{ __('mail.delivery_failed.error') }}:** {{ $reason }}
</x-mail::message>
