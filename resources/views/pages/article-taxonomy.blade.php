{{--
    One category or one tag, as a page of its own.

    Shared between the two because they differ only in what fills the heading
    and the lead — the list beneath is identical, and two near-identical
    templates drift within a month.

    A real page rather than a query string on the index: somebody searching
    «تغذية الحمل» should land on something with its own title, its own meta
    description and its own place in the sitemap, not on a filtered view whose
    URL a search engine treats as a duplicate of the index.
--}}

<x-page-shell
    :eyebrow="__('articles.eyebrow')"
    :title="$heading"
    :lead="$lead"
    :meta-title="$heading.' — '.__('articles.title')"
    :meta-description="$metaDescription"
    :footer-services="$footerServices"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.articles'), 'url' => route('articles')],
        ['label' => $crumb, 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('articles.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('articles.cta.lead') }}</x-slot:cta-lead>

    <section class="py-16 sm:py-24" aria-labelledby="list-heading">
        <x-container>
            <h2 id="list-heading" class="sr-only">{{ $heading }}</h2>

            @if ($posts->isEmpty())
                <p class="text-muted">{{ __('articles.empty') }}</p>
            @else
                <x-article-grid :posts="$posts" />

                @if ($posts->hasPages())
                    <div class="mt-14">{{ $posts->links() }}</div>
                @endif
            @endif
        </x-container>
    </section>
</x-page-shell>
