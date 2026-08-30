@php
    use App\Support\Photo;
@endphp

<x-page-shell
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
                    <nav class="mb-12 flex flex-wrap gap-2" aria-label="{{ __('articles.filter_heading') }}">
                        <span class="rounded-pill bg-ink px-4 py-2 text-sm font-medium text-white">
                            {{ __('articles.filter_all') }}
                        </span>

                        @foreach ($categories as $category)
                            @continue($category->posts_count === 0)

                            <a
                                href="{{ route('articles.category', ['slug' => $category->slug]) }}"
                                class="rounded-pill px-4 py-2 text-sm font-medium text-ink ring-1 ring-line transition hover:bg-sage/60"
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
