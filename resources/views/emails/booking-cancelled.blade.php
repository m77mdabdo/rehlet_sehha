<x-mail::message>
# {{ __('mail.cancelled.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.cancelled.lead') }}

@include('emails.partials.facts')

{{ __('mail.cancelled.rebook') }}

<x-mail::button :url="$bookingUrl">
{{ __('mail.cancelled.rebook_button') }}
</x-mail::button>

@if ($clinicPhone)
{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif
</x-mail::message>
