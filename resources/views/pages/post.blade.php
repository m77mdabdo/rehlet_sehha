{{--
    A single article, deliberately plain. It exists so the homepage's article
    cards lead somewhere real rather than at a guaranteed 404; typography,
    related posts and sharing belong to whichever task owns the blog.
--}}

<x-layouts.app :title="$post->title.' — '.__('common.brand')" :description="$post->excerpt">
    <article class="py-16 sm:py-20">
        <x-container size="narrow">
            <p class="text-sm text-muted">
                @if ($post->published_at)
                    <time datetime="{{ $post->published_at->toDateString() }}">
                        {{ $post->published_at->translatedFormat('j F Y') }}
                    </time>
                @endif

                @if ($post->reading_minutes)
                    <span aria-hidden="true">·</span>
                    {{ __('home.articles.reading_time', ['count' => $post->reading_minutes]) }}
                @endif
            </p>

            <h1 class="mt-3 font-display text-3xl font-semibold text-balance text-ink sm:text-4xl">
                {{ $post->title }}
            </h1>

            @if ($post->excerpt)
                <p class="mt-4 text-lg leading-relaxed text-pretty text-muted">{{ $post->excerpt }}</p>
            @endif

            {{-- Escaped, not {!! !!}. The body is authored content and may one
                 day come from an editor; rendering it raw would make a stored
                 XSS out of a CMS field the moment one exists. --}}
            <div class="mt-8 space-y-4 leading-relaxed text-ink">
                @foreach (preg_split('/\R{2,}/', (string) $post->body) as $paragraph)
                    <p>{{ $paragraph }}</p>
                @endforeach
            </div>

            <p class="mt-12 border-t border-line pt-8">
                <a href="{{ route('home') }}#articles" class="inline-flex items-center gap-2 text-sm font-medium text-accent-dark hover:text-ink">
                    <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <path d="M16 10H5M9 5l-5 5 5 5" stroke-linecap="round" stroke-linejoin="round" />
                    </svg>
                    {{ __('home.articles.eyebrow') }}
                </a>
            </p>
        </x-container>
    </article>
</x-layouts.app>
