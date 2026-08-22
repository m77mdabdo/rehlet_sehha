<x-mail::message>
# {{ __('mail.confirmed.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.confirmed.lead') }}

@include('emails.partials.facts')

{{ __('mail.confirmed.pending_note') }}

<x-mail::button :url="$manageUrl">
{{ __('mail.manage.button') }}
</x-mail::button>

<x-mail::panel>
{{ __('mail.manage.hint') }}
</x-mail::panel>

@if ($clinicPhone)
{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif

<x-slot:subcopy>
{{ __('mail.footer.rights') }}
</x-slot:subcopy>
</x-mail::message>
