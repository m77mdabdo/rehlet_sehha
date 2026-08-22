{{ __('mail.delivery_failed.heading') }}

{{ __('mail.delivery_failed.lead') }}

{{ __('mail.delivery_failed.action') }}

{{ __('mail.facts.reference') }}: {{ $reference }}
{{ __('mail.facts.when') }}: {{ $startsAt->translatedFormat('l j F Y — H:i') }}
{{ __('mail.facts.timezone', ['zone' => $timezone]) }}
{{ __('mail.new_booking.patient_name') }}: {{ $patient->name }}
{{ __('mail.new_booking.patient_phone') }}: {{ App\Support\PhoneNumber::forDisplay($patient->phone) }}
{{ __('mail.delivery_failed.template') }}: {{ $failedTemplate }}
{{ __('mail.delivery_failed.error') }}: {{ $reason }}
