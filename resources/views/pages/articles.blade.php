@php
    use App\Support\Photo;
@endphp

<x-page-shell
    header-page="articles"
    :eyebrow="__('articles.eyebrow')"
    :title="__('articles.title')"
    :lead="__('articles.lead')"
    :meta-title="__('articles.meta_title')"
    :meta-description="__('articles.meta_description')"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.articles'), 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('articles.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('articles.cta.lead') }}</x-slot:cta-lead>

    {{--
        THE CONTROLS APPEAR WHEN THERE IS SOMETHING TO CONTROL.

        Below ArticlesController::CONTROLS_APPEAR_AT the page is a plain list:
        a category filter over four articles is scaffolding for content that
        does not exist, and a dropdown with two entries announces the emptiness
        far more loudly than the list does.

        At and above it, the filter bar and pagination appear together. The
        category and tag PAGES exist either way — those are landing pages for
        somebody arriving from a search, and they are useful from the first
        article.
    --}}
    <section class="py-16 sm:py-24" aria-labelledby="list-heading">
        <x-container>
            <h2 id="list-heading" class="sr-only">{{ __('articles.title') }}</h2>

            @if ($posts->isEmpty())
                <p class="text-muted">{{ __('articles.empty') }}</p>
            @else
                @if ($paginated && $categories->isNotEmpty())
                    {{--
                        ONE ROW THAT SCROLLS, NOT A BLOCK THAT WRAPS.

                        This was flex-wrap and it was the largest layout shift
                        on the site: 0.16 measured at 1440, and 0.24 in Arabic.
                        The pills get wider when the webfont swaps in, the row
                        wraps one filter onto a second line, and the entire
                        article grid below drops by a row's height while
                        somebody is looking at it.

                        Nothing about reserving a height fixes that honestly —
                        the wrapped height differs per locale and per category
                        count, so any number here would be right for one page
                        and wrong for the next. A single non-wrapping row cannot
                        change height at all, whatever the font does to the
                        words inside it.

                        It is also the better mobile pattern: a scrollable chip
                        row rather than three stacked lines of pills. The
                        scroll is INSIDE this element, so it never becomes a
                        horizontal scrollbar on the page.

                        -mx-* and matching px-* let the row bleed to the screen
                        edges while its first and last chips still align with
                        the container, so a chip scrolled half out of view is
                        obviously scrollable rather than looking clipped.
                    --}}
                    <nav
                        class="relative -mx-5 mb-12 flex snap-x snap-mandatory gap-2 overflow-x-auto px-5 pb-1 [scrollbar-width:none] sm:-mx-8 sm:px-8 [&::-webkit-scrollbar]:hidden"
                        aria-label="{{ __('articles.filter_heading') }}"
                    >
                        <span class="tap-target shrink-0 snap-start rounded-pill bg-ink px-4 text-sm font-medium text-white">
                            {{ __('articles.filter_all') }}
                        </span>

                        @foreach ($categories as $category)
                            @continue($category->posts_count === 0)

                            <a
                                href="{{ route('articles.category', ['slug' => $category->slug]) }}"
                                class="tap-target shrink-0 snap-start rounded-pill px-4 text-sm font-medium text-ink ring-1 ring-line transition hover:bg-sage/60"
                            >
                                {{ $category->name }}
                                <span class="text-muted">({{ $category->posts_count }})</span>
                            </a>
                        @endforeach
                    </nav>
                @endif

                <x-article-grid :posts="$posts" />

                @if ($posts->hasPages())
                    <div class="mt-14">{{ $posts->links() }}</div>
                @endif
            @endif
        </x-container>
    </section>
</x-page-shell>
