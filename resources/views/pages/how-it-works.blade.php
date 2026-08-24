<x-page-shell
    :eyebrow="__('how-it-works.eyebrow')"
    :title="__('how-it-works.title')"
    :lead="__('how-it-works.lead')"
    :meta-title="__('how-it-works.meta_title')"
    :meta-description="__('how-it-works.meta_description')"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.how_it_works'), 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('how-it-works.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('how-it-works.cta.lead') }}</x-slot:cta-lead>

    {{--
        The four steps at length.

        An ordered list, because the order is the meaning — and the numbers are
        the visual device, which is what lets this page carry only two
        photographs without looking bare. There is nothing in the library that
        shows a booking form or a written plan arriving, and a stock photo
        stretched over either would be decoration pretending to be
        illustration.
    --}}
    <section class="py-20 sm:py-28" aria-labelledby="steps-heading">
        <x-container>
            <h2 id="steps-heading" class="sr-only">{{ __('how-it-works.title') }}</h2>

            <ol class="space-y-16 sm:space-y-20">
                @foreach (__('how-it-works.steps') as $index => $step)
                    <li class="reveal grid gap-8 lg:grid-cols-12 lg:gap-12">
                        <div class="lg:col-span-4">
                            <p class="text-sm font-semibold tracking-wide text-accent-dark uppercase">{{ $step['number'] }}</p>
                            <h3 class="mt-3 font-display text-2xl font-semibold text-ink sm:text-3xl">{{ $step['title'] }}</h3>
                        </div>

                        <div class="lg:col-span-8">
                            <p class="max-w-2xl leading-relaxed text-pretty text-muted">{{ $step['body'] }}</p>

                            <dl class="mt-8 grid gap-x-8 gap-y-5 border-t border-line pt-6 sm:grid-cols-3">
                                @foreach (['duration', 'bring', 'leave'] as $key)
                                    <div>
                                        <dt class="text-xs font-semibold tracking-wide text-accent-dark uppercase">
                                            {{ __("how-it-works.{$key}_label") }}
                                        </dt>
                                        <dd class="mt-2 text-sm leading-relaxed text-muted">{{ $step[$key] }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                        </div>
                    </li>
                @endforeach
            </ol>
        </x-container>
    </section>

    <x-statement>{{ __('how-it-works.statement') }}</x-statement>

    {{-- The section that makes this page worth having. --}}
    <section class="py-20 sm:py-28" aria-labelledby="between-heading">
        <x-container>
            <div class="grid items-start gap-10 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-7">
                    <x-section-heading
                        id="between-heading"
                        :eyebrow="__('how-it-works.between.eyebrow')"
                        :title="__('how-it-works.between.title')"
                        :lead="__('how-it-works.between.lead')"
                    />

                    <ul class="mt-10 space-y-8">
                        @foreach (__('how-it-works.between.items') as $item)
                            <li class="reveal border-s-2 border-sage ps-5">
                                <h3 class="font-display text-lg font-semibold text-ink">{{ $item['title'] }}</h3>
                                <p class="mt-2 leading-relaxed text-pretty text-muted">{{ $item['body'] }}</p>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{-- Offset and overlapping: the two images sit at different
                     sizes and start at different heights, which is what keeps
                     this from reading as a column of stacked pictures. --}}
                <div class="lg:col-span-5 lg:mt-8">
                    <x-photo
                        slug="consultation-meal-plan"
                        :alt="__('how-it-works.photo_alt.plan')"
                        :caption="__('how-it-works.photo_caption.plan')"
                        sizes="(min-width: 1024px) 36vw, 100vw"
                        class="shadow-sm ring-1 ring-line"
                    />

                    <x-photo
                        slug="food-kitchen-still-life"
                        :alt="__('how-it-works.photo_alt.kitchen')"
                        sizes="(min-width: 1024px) 26vw, 60vw"
                        class="relative -mt-10 ms-auto w-2/3 shadow-lg ring-4 ring-paper"
                    />
                </div>
            </div>
        </x-container>
    </section>

    {{-- Privacy, because "what happens" includes what happens to what you say. --}}
    <section class="bg-sage/50 py-16 sm:py-20" aria-labelledby="privacy-heading">
        <x-container size="narrow">
            <h2 id="privacy-heading" class="font-display text-xl font-semibold text-ink">{{ __('how-it-works.privacy.title') }}</h2>
            <p class="mt-3 leading-relaxed text-pretty text-muted">{{ __('how-it-works.privacy.body') }}</p>
            <a href="{{ route('privacy') }}" class="mt-4 inline-flex text-sm font-medium text-accent-dark underline-offset-4 hover:underline">
                {{ __('how-it-works.privacy.link') }}
            </a>
        </x-container>
    </section>
</x-page-shell>
