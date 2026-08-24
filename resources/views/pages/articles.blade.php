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
        THREE ARTICLES, SO THIS IS A LIST.

        No category filter and no pagination. Filters over three items are
        scaffolding for content that does not exist, and a dropdown with one
        item behind it announces the emptiness far more loudly than three
        honest entries do. This page grows controls when there is something to
        control.

        The first article is given the larger treatment and its image loads
        eagerly — it is the only above-fold image on this page, and the only
        exception the lazy-loading rule allows.
    --}}
    <section class="py-16 sm:py-24" aria-labelledby="list-heading">
        <x-container>
            <h2 id="list-heading" class="sr-only">{{ __('articles.title') }}</h2>

            @if ($posts->isEmpty())
                <p class="text-muted">{{ __('articles.empty') }}</p>
            @else
                <ul class="space-y-14 sm:space-y-20">
                    @foreach ($posts as $index => $post)
                        @php
                            $lead = $index === 0;
                            $hasCover = $post->cover_path && Photo::has($post->cover_path);
                        @endphp

                        <li class="reveal">
                            <article class="grid items-start gap-6 sm:gap-10 lg:grid-cols-12">
                                @if ($hasCover)
                                    <div @class([
                                        'lg:col-span-7' => $lead,
                                        'lg:col-span-4' => ! $lead,
                                    ])>
                                        <a href="{{ route('posts.show', ['slug' => $post->slug]) }}" class="block overflow-hidden rounded-2xl">
                                            <x-photo
                                                :slug="$post->cover_path"
                                                :alt="__('articles.cover_alt.'.$post->slug)"
                                                :eager="$lead"
                                                :sizes="$lead ? '(min-width: 1024px) 55vw, 100vw' : '(min-width: 1024px) 30vw, 100vw'"
                                                class="ring-1 ring-line transition-shadow hover:shadow-lg"
                                            />
                                        </a>
                                    </div>
                                @endif

                                <div @class([
                                    'lg:col-span-5' => $lead && $hasCover,
                                    'lg:col-span-8' => ! $lead && $hasCover,
                                    'lg:col-span-12' => ! $hasCover,
                                    'lg:mt-6' => $lead,
                                ])>
                                    <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
                                        @if ($post->category)
                                            <span class="rounded-pill bg-sage px-2.5 py-1 font-medium text-accent-dark">{{ $post->category }}</span>
                                        @endif

                                        {{-- dir=auto, NOT ltr. A translated date carries an
                                             Arabic month name, and forcing LTR reclassifies the
                                             digits beside it (UAX #9 W2) and tears the day away
                                             from the month. auto follows the content. --}}
                                        <span><bdi dir="auto">{{ $post->published_at?->translatedFormat('j F Y') }}</bdi></span>

                                        @if ($post->reading_minutes)
                                            <span>{{ __('articles.reading_time', ['minutes' => $post->reading_minutes]) }}</span>
                                        @endif
                                    </p>

                                    <h3 @class([
                                        'mt-3 font-display font-semibold text-balance text-ink',
                                        'text-2xl sm:text-3xl' => $lead,
                                        'text-xl sm:text-2xl' => ! $lead,
                                    ])>
                                        <a href="{{ route('posts.show', ['slug' => $post->slug]) }}" class="hover:text-accent-dark">
                                            {{ $post->title }}
                                        </a>
                                    </h3>

                                    <p class="mt-3 leading-relaxed text-pretty text-muted">{{ $post->excerpt }}</p>

                                    <a
                                        href="{{ route('posts.show', ['slug' => $post->slug]) }}"
                                        class="mt-4 inline-flex items-center gap-1.5 text-sm font-medium text-accent-dark underline-offset-4 hover:underline"
                                    >
                                        {{ __('common.read_more') }}
                                        <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                            <path d="M7 4l6 6-6 6" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </a>
                                </div>
                            </article>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-container>
    </section>
</x-page-shell>
