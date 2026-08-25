@php
    use Illuminate\Support\Carbon;

    /*
     * THE PRACTICE IS ONLINE AND HAS NO PREMISES, so there is no address block
     * on this page. config/clinic.php holds a null address deliberately — a
     * published address for a practice with nowhere to go is worse than none,
     * because it looks authoritative and sends somebody to a door that is not
     * there.
     *
     * What replaces it is the platform list, read from config so this page and
     * the structured data cannot disagree about what a session runs on.
     */
    $platforms = config('clinic.platforms', []);

    /*
     * Day names come from Carbon in the current locale rather than a table of
     * translated strings, so the seven days cannot drift out of step with the
     * rest of the site or with each other.
     *
     * DE-DUPLICATED PER DAY. working_hours rows belong to a STAFF MEMBER, so a
     * day worked by two practitioners has two identical rows in it — and this
     * block is the CLINIC's opening hours, not a roster. Same treatment as
     * ClinicSchema gives the JSON-LD, so the page and the structured data
     * cannot disagree about when the clinic is open.
     */
    $hoursByDay = $hours
        ->groupBy('day_of_week')
        ->map(fn ($blocks) => $blocks
            ->unique(fn ($block): string => $block->start_time.'-'.$block->end_time)
            ->sortBy('start_time')
            ->values());
@endphp

<x-page-shell
    :eyebrow="__('contact.eyebrow')"
    :title="__('contact.title')"
    :lead="__('contact.lead')"
    :meta-title="__('contact.meta_title')"
    :meta-description="__('contact.meta_description')"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.contact'), 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('contact.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('contact.cta.lead') }}</x-slot:cta-lead>

    <section class="py-16 sm:py-20" aria-labelledby="reach-heading">
        <x-container>
            <h2 id="reach-heading" class="sr-only">{{ __('contact.channels_heading') }}</h2>

            <div class="grid gap-8 lg:grid-cols-12 lg:gap-12">
                {{-- Booking first and largest, because it is the thing that
                     actually gets a patient an answer. --}}
                <div class="lg:col-span-7">
                    <x-card class="reveal bg-ink text-white ring-0">
                        <h3 class="font-display text-2xl font-semibold sm:text-3xl">{{ __('contact.book_first.title') }}</h3>
                        <p class="mt-3 leading-relaxed text-pretty text-white/75">{{ __('contact.book_first.body') }}</p>

                        <div class="mt-7">
                            <x-button :href="route('booking')" variant="light" size="lg">
                                {{ __('contact.book_first.cta') }}
                            </x-button>
                        </div>
                    </x-card>

                    {{-- Saying why there is no form, rather than letting
                         somebody hunt for one and conclude we forgot. --}}
                    <div class="mt-6 rounded-xl border border-line p-5">
                        <h3 class="text-sm font-semibold text-ink">{{ __('contact.no_form.title') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('contact.no_form.body') }}</p>
                    </div>
                </div>

                <div class="lg:col-span-5">
                    <x-card class="reveal">
                        <h3 class="font-display text-lg font-semibold text-ink">{{ __('contact.channels_heading') }}</h3>

                        <x-contact-details class="mt-5 text-ink" :show-address="false" />

                        <dl class="mt-6 space-y-3 border-t border-line pt-5 text-xs leading-relaxed text-muted">
                            <div><dd>{{ __('contact.whatsapp_note') }}</dd></div>
                            <div><dd>{{ __('contact.phone_note') }}</dd></div>
                            <div><dd>{{ __('contact.email_note') }}</dd></div>
                        </dl>
                    </x-card>
                </div>
            </div>
        </x-container>
    </section>

    {{-- Where the practice actually is: online. And the hours it keeps. --}}
    <section class="bg-sage/50 py-16 sm:py-24" aria-labelledby="where-heading">
        <x-container>
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-5">
                    <h2 id="where-heading" class="font-display text-2xl font-semibold text-ink sm:text-3xl">
                        {{ __('contact.online_title') }}
                    </h2>

                    <p class="mt-4 leading-relaxed text-pretty text-muted">{{ __('contact.online_body') }}</p>

                    <h3 class="mt-8 text-sm font-semibold tracking-wide text-accent-dark uppercase">
                        {{ __('contact.platforms_heading') }}
                    </h3>

                    {{-- From config, so the page, the booking wizard and the
                         schema cannot disagree about what is on offer. --}}
                    <ul class="mt-4 flex flex-wrap gap-2">
                        @foreach ($platforms as $platform)
                            <li class="rounded-pill bg-white px-4 py-2 text-sm font-medium text-ink ring-1 ring-line">
                                {{ __('contact.platforms.'.$platform) }}
                            </li>
                        @endforeach
                    </ul>

                    <p class="mt-4 text-sm leading-relaxed text-muted">{{ __('contact.platforms_note') }}</p>
                </div>

                <div class="lg:col-span-7">
                    <h2 class="font-display text-2xl font-semibold text-ink sm:text-3xl">{{ __('contact.hours_heading') }}</h2>

                    @if ($hours->isEmpty())
                        <p class="mt-4 text-muted">{{ __('contact.hours_empty') }}</p>
                    @else
                        <dl class="mt-6 divide-y divide-line border-y border-line">
                            @for ($day = 0; $day < 7; $day++)
                                @php $blocks = $hoursByDay->get($day); @endphp

                                <div class="flex items-baseline justify-between gap-6 py-3">
                                    <dt class="text-sm font-medium text-ink">
                                        {{ Carbon::now()->startOfWeek(Carbon::SUNDAY)->addDays($day)->translatedFormat('l') }}
                                    </dt>

                                    <dd class="text-end text-sm text-muted">
                                        @if ($blocks === null || $blocks->isEmpty())
                                            {{ __('contact.hours_closed') }}
                                        @else
                                            @foreach ($blocks as $block)
                                                {{-- dir=ltr: a time range reorders
                                                     inside Arabic without it. --}}
                                                <bdi dir="ltr" class="tabular-nums">{{ substr((string) $block->start_time, 0, 5) }}–{{ substr((string) $block->end_time, 0, 5) }}</bdi>@if (! $loop->last), @endif
                                            @endforeach
                                        @endif
                                    </dd>
                                </div>
                            @endfor
                        </dl>

                        <p class="mt-4 text-sm leading-relaxed text-muted">{{ __('contact.hours_note') }}</p>
                    @endif
                </div>
            </div>
        </x-container>
    </section>

    {{-- What actually happens when you get in touch. --}}
    <section class="py-16 sm:py-24" aria-labelledby="expect-heading">
        <x-container>
            <div class="grid gap-10 lg:grid-cols-12 lg:gap-16">
                <div class="lg:col-span-6">
                    <h2 id="expect-heading" class="font-display text-2xl font-semibold text-ink sm:text-3xl">
                        {{ __('contact.expect_heading') }}
                    </h2>

                    <ul class="mt-6 space-y-4">
                        @foreach (__('contact.expect') as $item)
                            <li class="flex items-start gap-2.5 leading-relaxed text-muted">
                                <svg class="mt-1.5 size-4 shrink-0 text-accent" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <span>{{ $item }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>

                {{--
                    No reserved photograph here any more.

                    This slot used to hold space for a picture of the clinic
                    interior. There is no interior: the practice is online. A
                    frame waiting for a photograph of premises that do not
                    exist is a promise nobody can keep, so it is gone rather
                    than left hopeful.
                --}}
                <div class="lg:col-span-6 lg:mt-6">
                    <x-card>
                        <h3 class="font-display text-lg font-semibold text-ink">{{ __('contact.book_first.title') }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('contact.book_first.body') }}</p>

                        <div class="mt-5">
                            <x-button :href="route('booking')" size="lg" class="w-full">{{ __('contact.book_first.cta') }}</x-button>
                        </div>
                    </x-card>
                </div>
            </div>
        </x-container>
    </section>
</x-page-shell>
