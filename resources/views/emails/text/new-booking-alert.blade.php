{{ __('mail.new_booking.heading') }}

{{ __('mail.new_booking.lead') }}

@include('emails.text.partials.facts')

{{ __('mail.new_booking.patient') }}
--
{{ __('mail.new_booking.patient_name') }}: {{ $patient->name }}
{{ __('mail.new_booking.patient_phone') }}: {{ App\Support\PhoneNumber::forDisplay($patient->phone) }}
{{ __('mail.new_booking.patient_email') }}: {{ $patient->email ?: __('mail.new_booking.no_email') }}
{{ __('mail.new_booking.booked_at') }}: {{ $bookedAt?->translatedFormat('j F Y — H:i') }}
{{ __('mail.new_booking.locale') }}: {{ App\Support\Locales::nativeName($appointment->locale) }}

{{ __('mail.new_booking.intake') }}
--
@if ($intake === null)
{{ __('mail.new_booking.no_intake') }}
@else
{{ __('booking.fields.goal') }}: {{ $goalLabel ?: __('booking.rights.blank') }}
{{ __('booking.fields.medications') }}: {{ $intake->medications ?: __('booking.rights.blank') }}
{{ __('booking.fields.conditions') }}: {{ $intake->conditions ?: __('booking.rights.blank') }}
{{ __('booking.fields.avoid_foods') }}: {{ $intake->avoid_foods ?: __('booking.rights.blank') }}
{{ __('booking.fields.note') }}: {{ $intake->note ?: __('booking.rights.blank') }}
@endif
