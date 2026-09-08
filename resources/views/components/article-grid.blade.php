@props(['posts'])

{{--
    The article card grid, shared by the index and the taxonomy pages.

    One component so a change to how an article is summarised lands everywhere
    at once. The first card is NOT given a larger treatment here: that belongs
    to the index, where there is a clear lead article, and repeating it on a tag
    page would imply an editorial ranking that nobody made.

    ---------------------------------------------------------------------------
    THE REVIEWER LINE SITS ON THE COVER, AND IT IS THE POINT OF THE CARD
    ---------------------------------------------------------------------------

    "Clinically reviewed by Dr Rana Salem" is the one thing this blog has that a
    content farm does not, and until now it appeared only after you had opened
    the article. On the index it was invisible, so the cards competed with every
    other nutrition listicle on their own terms — a picture and a headline.

    It is on the image rather than under it because there is no room under it: a
    card with category, title, excerpt, date, reading time AND a review line has
    six things stacked in a column and reads as a receipt. On the cover it costs
    no vertical space at all.

    THE SCRIM UNDER IT IS NOT DECORATION. Fourteen covers, each vetted for its
    own article, and several are bright — a white plate, a pale table. White
    text on a light cover is unreadable, so a gradient runs beneath it, dense at
    the very top and gone by a third of the way down. Measured against the
    brightest pixel of the brightest cover; the numbers are in the task report.

    ---------------------------------------------------------------------------
    THE IMAGE SCALES, THE CARD DOES NOT MOVE
    ---------------------------------------------------------------------------

    The frame has a fixed aspect ratio and overflow-hidden, and the transform is
    on the <img> inside it. Nothing about the hover can change the size of the
    card, so a grid of these cannot reflow while somebody is reading it — the
    failure that made the specialty cards use transform-only hovers too.

    Only the first image loads eagerly, and only on the first page — it is the
    one above the fold, and the lazy-loading rule allows exactly one exception.
--}}

<ul class="grid gap-8 sm:grid-cols-2 lg:grid-cols-3">
    @foreach ($posts as $index => $post)
        @php
            $hasCover = $post->cover_path && App\Support\Photo::has($post->cover_path);
            $eager = $index === 0 && ($posts instanceof Illuminate\Contracts\Pagination\Paginator ? $posts->onFirstPage() : true);
        @endphp

        <li class="reveal flex">
            <article class="group flex w-full flex-col overflow-hidden rounded-xl border border-line bg-white transition-[box-shadow] duration-300 hover:shadow-lg">
                @if ($hasCover)
                    <div class="relative overflow-hidden">
                        <a href="{{ route('posts.show', ['slug' => $post->slug]) }}" tabindex="-1" aria-hidden="true" class="block">
                            <x-photo
                                :slug="$post->cover_path"
                                :alt="''"
                                :eager="$eager"
                                sizes="(min-width: 1024px) 22rem, (min-width: 640px) 45vw, 100vw"
                                class="aspect-4/3 rounded-none ring-0 transition-transform duration-500 ease-out motion-safe:group-hover:scale-[1.04]"
                            />
                        </a>

                        {{-- The scrim. Dense at the top edge where the line
                             sits, gone by a third down, so the cover is a cover
                             everywhere below it. --}}
                        <div
                            aria-hidden="true"
                            data-cover-scrim
                            class="pointer-events-none absolute inset-x-0 top-0 h-24"
                            style="background: linear-gradient(to bottom, rgb(14 46 77 / 0.88) 0%, rgb(14 46 77 / 0.72) 38%, rgb(14 46 77 / 0.32) 70%, rgb(14 46 77 / 0) 100%)"
                        ></div>

                        <div class="pointer-events-none absolute inset-x-0 top-0 flex flex-wrap items-center gap-x-2 gap-y-1 p-3 text-[0.6875rem] leading-tight text-white/90">
                            @if ($post->reviewed_by)
                                <span class="inline-flex items-center gap-1.5 font-medium">
                                    <svg class="size-3.5 shrink-0 text-teal" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                        <path d="m4 10.5 4 4 8-9" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    {{-- reviewerDisplayName(), not reviewedBy — there is no such relation on
                                         Post, so this rendered "روجعت إكلينيكيًا بمعرفة" with an empty name on
                                         every card. The guard above reads the COLUMN and was always right. --}}
                                    {{ __('articles.reviewed_by', ['name' => $post->reviewerDisplayName()]) }}
                                </span>
                            @endif

                            @if ($post->published_at)
                                <span aria-hidden="true" class="text-white/40">·</span>
                                <bdi dir="auto">{{ $post->published_at->translatedFormat('j F Y') }}</bdi>
                            @endif
                        </div>
                    </div>
                @endif

                <div class="flex flex-1 flex-col p-5 sm:p-6">
                    @if ($post->category)
                        <a
                            href="{{ route('articles.category', ['slug' => $post->category->slug]) }}"
                            class="tap-target text-xs font-semibold tracking-wide text-accent-dark uppercase hover:underline"
                        >{{ $post->category->name }}</a>
                    @endif

                    <h3 class="mt-2 font-display text-xl font-semibold text-balance text-ink">
                        <a href="{{ route('posts.show', ['slug' => $post->slug]) }}" class="tap-target rounded-sm transition-colors hover:text-accent-dark">
                            {{ $post->title }}
                        </a>
                    </h3>

                    <p class="mt-3 flex-1 text-sm leading-relaxed text-pretty text-muted">{{ $post->excerpt }}</p>

                    <p class="mt-5 inline-flex items-center gap-2 text-sm font-medium text-accent-dark" aria-hidden="true">
                        {{ __('articles.read_more') }}
                        <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 10h11M11 5l5 5-5 5" stroke-linecap="round" stroke-linejoin="round" />
                        </svg>
                    </p>

                    @if ($post->reading_minutes)
                        <p class="mt-3 text-xs text-muted">
                            {{ __('articles.reading_time', ['minutes' => $post->reading_minutes]) }}
                        </p>
                    @endif
                </div>
            </article>
        </li>
    @endforeach
</ul>
