{{ __('mail.confirmed.heading') }}

{{ __('mail.greeting', ['name' => $patientName]) }}

{{ __('mail.confirmed.lead') }}

@include('emails.text.partials.facts')

{{ __('mail.confirmed.pending_note') }}

--
{{ __('mail.manage.label') }}
{{ $manageUrl }}

{{ __('mail.manage.hint') }}
@if ($clinicPhone)

{{ __('mail.call_us', ['phone' => $clinicPhone]) }}
@endif

--
{{ __('mail.footer.rights') }}
@if (App\Support\Contact::email())
{{ __('mail.footer.automated', ['address' => config('mail.from.address'), 'reply' => App\Support\Contact::email()]) }}
@endif
