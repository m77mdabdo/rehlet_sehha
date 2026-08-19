{{--
    A small contact block. Every detail comes from config through
    x-contact-details, which renders nothing at all for anything unset — so
    this section cannot show an empty link or a placeholder.
--}}

<section id="contact" class="py-20 sm:py-24" aria-labelledby="contact-heading">
    <x-container>
        <div class="grid gap-10 md:grid-cols-2 md:items-center">
            <x-section-heading
                id="contact-heading"
                :eyebrow="__('home.contact.eyebrow')"
                :title="__('home.contact.title')"
                :lead="__('home.contact.lead')"
            />

            <x-card class="reveal">
                <x-contact-details class="text-ink" />
            </x-card>
        </div>
    </x-container>
</section>
