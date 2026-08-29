{{--
    A LINK THAT HAS RUN OUT, EXPLAINED — not a 404.

    A 404 is the right answer to a token that never existed: it makes a guessed
    URL indistinguishable from a wrong one, which is what stops somebody
    probing for valid tokens. It is the WRONG answer to a link that used to be
    hers and has simply aged out. She is not an attacker, she is a patient
    holding an email the clinic sent her, and "not found" tells her she did
    something wrong.

    So an unknown token still 404s. Only a token we recognise and know to be
    past its date reaches this page, and it says so plainly and offers the one
    thing that helps: a way to reach a person.

    indexable=false for the same reason every token page is: the URL in the
    address bar is still a credential, even a spent one.
--}}

<x-layouts.app
    :title="__('tokens.expired.title').' — '.__('common.brand')"
    :footer-services="$footerServices"
    :indexable="false"
>
    <section class="py-16 sm:py-24">
        <x-container size="narrow">
            <x-card class="p-8 sm:p-10">
                <p class="text-sm font-semibold tracking-wide text-accent-dark uppercase">
                    {{ __('tokens.expired.eyebrow') }}
                </p>

                <h1 class="mt-3 font-display text-2xl font-semibold text-ink sm:text-3xl">
                    {{ __('tokens.expired.title') }}
                </h1>

                <p class="mt-5 text-base leading-relaxed text-pretty text-muted">
                    {{ __('tokens.expired.lead') }}
                </p>

                <p class="mt-4 text-base leading-relaxed text-pretty text-muted">
                    {{ __($reason) }}
                </p>

                <div class="mt-8 flex flex-wrap gap-3">
                    <x-button :href="route('booking')" size="lg">
                        {{ __('tokens.expired.book') }}
                    </x-button>

                    @if (App\Support\Contact::whatsappHref())
                        <x-button :href="App\Support\Contact::whatsappHref()" variant="ghost" size="lg">
                            {{ __('tokens.expired.whatsapp') }}
                        </x-button>
                    @endif
                </div>

                <p class="mt-6 text-sm leading-relaxed text-muted">
                    {{ __('tokens.expired.note') }}
                </p>
            </x-card>
        </x-container>
    </section>
</x-layouts.app>
