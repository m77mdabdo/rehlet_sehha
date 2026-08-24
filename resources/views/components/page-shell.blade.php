@props([
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'metaTitle' => null,
    'metaDescription' => null,
    /**
     * The breadcrumb trail, root first, current page last.
     *
     * @var list<array{label: string, url?: string|null}>
     */
    'trail' => [],
    'footerServices' => null,
])

{{--
    The shell every standalone page sits in.

    Breadcrumb, page header, content, booking band — in that order, once, so
    the remaining seven pages are a content file and nothing else. If a page
    needs a different shape, that is a conversation about the page, not a
    reason to fork this.

    WHY A HEADER THIS LARGE. The h1 here is deliberately bigger than the h2s
    that open homepage sections. On the homepage a section heading is one of
    nine and has to sit in a rhythm; on a page of its own the heading IS the
    page's opening, and hedging its size makes a standalone page read like a
    fragment that escaped from somewhere else.

    THE BREADCRUMB IS UNDERSTATED ON PURPOSE. It is wayfinding for someone who
    arrived from a search and does not yet know what the rest of the site is —
    not navigation we are inviting anybody to use. It gets small muted text and
    no decoration, and the trail it shows is the same trail the page emits as
    BreadcrumbList data, because a visible path that disagrees with the markup
    is worse than neither.
--}}

<x-layouts.app
    :title="$metaTitle"
    :description="$metaDescription"
    :footer-services="$footerServices"
    :schema="\App\Support\PageSchema::toJson($trail)"
>
    <header class="border-b border-line bg-linear-to-b from-sage to-paper pt-10 pb-16 sm:pt-14 sm:pb-24">
        <x-container>
            @if ($trail !== [])
                <nav aria-label="{{ __('common.breadcrumb') }}">
                    {{-- An ordered list, because the order is the meaning. --}}
                    <ol class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm text-muted">
                        @foreach ($trail as $index => $crumb)
                            <li class="flex items-center gap-2">
                                @if ($index > 0)
                                    {{-- Decorative for a screen reader — the list
                                         already carries the relationship — but it
                                         is still a visible boundary between two
                                         links, so it takes muted rather than a
                                         hairline colour that measured 1.30:1. --}}
                                    <span aria-hidden="true" class="text-muted">/</span>
                                @endif

                                @if (($crumb['url'] ?? null) !== null)
                                    <a href="{{ $crumb['url'] }}" class="rounded-sm hover:text-accent-dark">
                                        {{ $crumb['label'] }}
                                    </a>
                                @else
                                    {{-- The current page. aria-current tells a
                                         screen reader where the trail stops. --}}
                                    <span aria-current="page" class="text-ink">{{ $crumb['label'] }}</span>
                                @endif
                            </li>
                        @endforeach
                    </ol>
                </nav>
            @endif

            <div class="mt-8 max-w-4xl sm:mt-10">
                @if ($eyebrow)
                    <p class="text-sm font-medium tracking-wide text-accent-dark uppercase">{{ $eyebrow }}</p>
                @endif

                {{-- The one h1 on the page. Sections below use h2. --}}
                <h1 class="mt-4 font-display text-4xl leading-[1.08] font-semibold text-balance text-ink sm:text-5xl lg:text-6xl">
                    {{ $title }}
                </h1>

                @if ($lead)
                    <p class="mt-6 max-w-2xl text-lg leading-relaxed text-pretty text-muted sm:text-xl">
                        {{ $lead }}
                    </p>
                @endif

                {{ $actions ?? '' }}
            </div>
        </x-container>
    </header>

    {{ $slot }}

    {{--
        The closing band, identical in shape to the homepage's so the two do
        not read as different sites. Its copy comes from the page, though: a
        booking prompt that follows a price comparison should not say the same
        thing as one that follows a list of clinical areas.
    --}}
    <section class="bg-ink py-20 text-white sm:py-24" aria-labelledby="page-cta-heading">
        <x-container size="narrow" class="text-center">
            <h2 id="page-cta-heading" class="font-display text-3xl font-semibold text-balance sm:text-4xl">
                {{ $ctaTitle ?? __('home.booking_cta.title') }}
            </h2>

            <p class="mt-4 text-base leading-relaxed text-pretty text-white/75 sm:text-lg">
                {{ $ctaLead ?? __('home.booking_cta.lead') }}
            </p>

            <div class="mt-8 flex justify-center">
                <x-button :href="route('booking')" variant="light" size="lg">
                    {{ __('home.booking_cta.cta') }}
                </x-button>
            </div>

            <p class="mt-5 text-sm text-white/55">{{ __('home.booking_cta.note') }}</p>
        </x-container>
    </section>
</x-layouts.app>
