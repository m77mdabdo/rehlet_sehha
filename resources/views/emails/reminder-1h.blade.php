<x-mail::message>
# {{ __('mail.reminder_1h.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.reminder_1h.lead') }}

@include('emails.partials.facts')

@if ($isOnline)
<x-mail::panel>
{{ __('mail.reminder_1h.online_note') }}
</x-mail::panel>
@endif

@if ($clinicPhone)
{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif

<x-slot:subcopy>
{{ __('mail.manage.label') }}: [{{ $manageUrl }}]({{ $manageUrl }})
</x-slot:subcopy>
</x-mail::message>
