@props([
    'showAddress' => true,
])

@php
    use App\Support\Contact;

    $phoneHref = Contact::telHref();
    $whatsappHref = Contact::whatsappHref();
    $email = Contact::email();
    $address = $showAddress ? Contact::address() : null;
@endphp

{{--
    The clinic's contact details, rendered from config/clinic.php.

    Nothing here is hardcoded and nothing here is conditional on "is it filled
    in yet" beyond a null check: a detail that is not configured renders as
    absolutely nothing — no row, no empty link, no placeholder. If none of them
    are configured, the list itself does not appear.

    Colours are inherited rather than set, so the same component works on the
    navy footer and on a paper-background contact section without a variant
    prop. Only the hover state is stated, and it is stated in current-colour
    terms the surface can define.
--}}

@if ($phoneHref || $whatsappHref || $email || $address)
    <ul {{ $attributes->merge(['class' => 'space-y-3 text-sm']) }}>
        @if ($phoneHref)
            <li>
                <a href="{{ $phoneHref }}" class="inline-flex items-center gap-2 transition-opacity hover:opacity-100 opacity-90">
                    <span class="sr-only">{{ __('footer.phone') }}</span>
                    {{--
                        dir="ltr" is not optional. Inside an Arabic paragraph the
                        bidi algorithm reorders a run that starts with + and
                        contains spaces, and the number a visitor reads stops
                        matching the number we dial. Latin digits for the same
                        reason people copy these into dialers: Arabic-Indic
                        numerals fail to parse on paste in a lot of contact apps.
                    --}}
                    <bdi dir="ltr" class="tabular-nums">{{ Contact::phoneDisplay() }}</bdi>
                </a>
            </li>
        @endif

        @if ($whatsappHref)
            <li>
                <a
                    href="{{ $whatsappHref }}"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="inline-flex items-center gap-2 opacity-90 transition-opacity hover:opacity-100"
                >
                    {{ __('footer.whatsapp') }}
                </a>
            </li>
        @endif

        @if ($email)
            <li>
                <a href="mailto:{{ $email }}" class="inline-flex items-center gap-2 opacity-90 transition-opacity hover:opacity-100">
                    <span class="sr-only">{{ __('footer.email') }}</span>
                    <bdi dir="ltr">{{ $email }}</bdi>
                </a>
            </li>
        @endif

        @if ($address)
            <li class="opacity-90">
                <span class="sr-only">{{ __('footer.address') }}</span>
                {{ $address }}
            </li>
        @endif
    </ul>
@endif
