@php
    use App\Support\Contact;

    /*
     * The footer states, in the patient's own language, that this address does
     * not read replies and names one that does. A patient who answers a
     * confirmation with "can I move this to Thursday?" must not be answering
     * into a mailbox nobody opens — and the Reply-To header alone does not
     * tell her that, because she cannot see headers.
     */
    $from = config('mail.from.address');
    $reply = Contact::email();
@endphp
<x-mail::layout>
{{-- Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
{{ config('app.name') }}
</x-mail::header>
</x-slot:header>

{{-- Body --}}
{!! $slot !!}

{{-- Subcopy --}}
@isset($subcopy)
<x-slot:subcopy>
<x-mail::subcopy>
{!! $subcopy !!}
</x-mail::subcopy>
</x-slot:subcopy>
@endisset

{{-- Footer --}}
<x-slot:footer>
<x-mail::footer>
@if ($reply)
{{ __('mail.footer.automated', ['address' => $from, 'reply' => $reply]) }}
@endif

© {{ date('Y') }} {{ config('app.name') }}
</x-mail::footer>
</x-slot:footer>
</x-mail::layout>
