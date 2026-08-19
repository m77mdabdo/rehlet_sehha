@props(['posts'])

{{--
    The three newest published articles.

    Category and reading time. posts.category used to be a plain string holding
    Arabic, which is why this card once showed reading time alone; it is a
    translatable JSON column now, so the chip works in both languages.
--}}

<section id="articles" class="py-20 sm:py-24" aria-labelledby="articles-heading">
    <x-container>
        <x-section-heading
            id="articles-heading"
            :eyebrow="__('home.articles.eyebrow')"
            :title="__('home.articles.title')"
            :lead="__('home.articles.lead')"
        />

        @if ($posts->isEmpty())
            <p class="mt-10 text-muted">{{ __('home.articles.empty') }}</p>
        @else
            <ul class="mt-12 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
                @foreach ($posts as $post)
                    <li class="reveal flex">
                        <x-card as="article" class="flex w-full flex-col">
                            <p class="flex flex-wrap items-center gap-x-3 gap-y-1 text-xs font-medium tracking-wide text-accent-dark uppercase">
                                @if ($post->category)
                                    <span>{{ $post->category }}</span>
                                @endif

                                @if ($post->category && $post->reading_minutes)
                                    <span class="text-line" aria-hidden="true">•</span>
                                @endif

                                @if ($post->reading_minutes)
                                    <span>{{ __('home.articles.reading_time', ['count' => $post->reading_minutes]) }}</span>
                                @endif
                            </p>

                            <h3 class="mt-2 font-display text-lg font-semibold text-balance text-ink">
                                {{-- The whole card is not a link: a block-level
                                     anchor makes a screen reader read the entire
                                     card as one link name. The title is the link,
                                     and the rest is text. --}}
                                <a
                                    href="{{ route('posts.show', ['slug' => $post->slug]) }}"
                                    class="rounded-sm transition-colors hover:text-accent"
                                >
                                    {{ $post->title }}
                                </a>
                            </h3>

                            <p class="mt-3 flex-1 text-sm leading-relaxed text-muted">
                                {{ $post->excerpt }}
                            </p>

                            <p class="mt-5 border-t border-line pt-5">
                                <a
                                    href="{{ route('posts.show', ['slug' => $post->slug]) }}"
                                    class="inline-flex items-center gap-2 text-sm font-medium text-accent-dark hover:text-ink"
                                    tabindex="-1"
                                    aria-hidden="true"
                                >
                                    {{ __('home.articles.read_more') }}
                                    {{-- scale-x-[-1] in RTL: the arrow must point
                                         the way reading travels. --}}
                                    <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                        <path d="M4 10h11M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                </a>
                            </p>
                        </x-card>
                    </li>
                @endforeach
            </ul>
        @endif
    </x-container>
</section>
