<x-mail::message>
# {{ __('mail.reminder_24h.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.reminder_24h.lead') }}

@include('emails.partials.facts')

{{ __('mail.reminder_24h.note') }}

<x-mail::button :url="$manageUrl">
{{ __('mail.manage.button') }}
</x-mail::button>

@if ($clinicPhone)
{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif

<x-slot:subcopy>
{{ __('mail.manage.hint') }}
</x-slot:subcopy>
</x-mail::message>
