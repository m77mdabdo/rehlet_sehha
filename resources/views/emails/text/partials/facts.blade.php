{{ __('mail.facts.reference') }}: {{ $reference }}
{{ __('mail.facts.service') }}: {{ $service }}
{{ __('mail.facts.when') }}: {{ $startsAt->translatedFormat('l j F Y — H:i') }}
{{ __('mail.facts.timezone', ['zone' => $timezone]) }}
{{ __('mail.facts.mode') }}: {{ $mode }}
{{ __('mail.facts.price') }}: {{ number_format((float) $price) }} {{ __('common.currency') }}
