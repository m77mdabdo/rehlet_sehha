@props(['posts'])

{{--
    The article card grid, shared by the index and the taxonomy pages.

    One component so a change to how an article is summarised lands everywhere
    at once. The first card is NOT given a larger treatment here: that belongs
    to the index, where there is a clear lead article, and repeating it on a
    tag page would imply an editorial ranking that nobody made.

    Only the first image loads eagerly, and only on the first page — it is the
    one above the fold, and the lazy-loading rule allows exactly one exception.
--}}

<ul class="grid gap-10 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($posts as $index => $post)
        <li class="reveal flex">
            <article class="flex w-full flex-col">
                @if ($post->cover_path && App\Support\Photo::has($post->cover_path))
                    <a href="{{ route('posts.show', ['slug' => $post->slug]) }}" tabindex="-1" aria-hidden="true">
                        <x-photo
                            :slug="$post->cover_path"
                            :alt="''"
                            :eager="$index === 0 && ($posts instanceof Illuminate\Contracts\Pagination\Paginator ? $posts->onFirstPage() : true)"
                            sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 100vw"
                            class="aspect-4/3 rounded-lg ring-1 ring-line"
                        />
                    </a>
                @endif

                <div class="mt-5 flex flex-1 flex-col">
                    @if ($post->category)
                        <a
                            href="{{ route('articles.category', ['slug' => $post->category->slug]) }}"
                            class="text-xs font-semibold tracking-wide text-accent-dark uppercase hover:underline"
                        >{{ $post->category->name }}</a>
                    @endif

                    <h3 class="mt-2 font-display text-xl font-semibold text-balance text-ink">
                        <a href="{{ route('posts.show', ['slug' => $post->slug]) }}" class="hover:text-accent-dark">
                            {{ $post->title }}
                        </a>
                    </h3>

                    <p class="mt-3 flex-1 text-sm leading-relaxed text-pretty text-muted">{{ $post->excerpt }}</p>

                    <p class="mt-4 flex flex-wrap items-center gap-x-3 gap-y-1 text-xs text-muted">
                        <bdi dir="auto">{{ $post->published_at?->translatedFormat('j F Y') }}</bdi>

                        @if ($post->reading_minutes)
                            <span aria-hidden="true" class="text-line">·</span>
                            <span>{{ __('articles.reading_time', ['minutes' => $post->reading_minutes]) }}</span>
                        @endif
                    </p>
                </div>
            </article>
        </li>
    @endforeach
</ul>
