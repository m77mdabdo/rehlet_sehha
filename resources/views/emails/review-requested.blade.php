<x-mail::message>
# {{ __('mail.review_requested.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.review_requested.lead') }}

<x-mail::button :url="$reviewUrl">
{{ __('mail.review_requested.button') }}
</x-mail::button>

<x-mail::panel>
{{ __('mail.review_requested.consent_note') }}
</x-mail::panel>

{{ __('mail.review_requested.no_obligation') }}

<x-slot:subcopy>
{{ __('mail.footer.rights') }}
</x-slot:subcopy>
</x-mail::message>
