@php
    use App\Support\Contact;
    use App\Support\PageSchema;
    use App\Support\Photo;

    $hasCover = $post->cover_path && Photo::has($post->cover_path);
    $url = url()->current();
@endphp

<x-page-shell
    :eyebrow="$post->category"
    :title="$post->title"
    :meta-title="$post->title.' — '.__('common.brand')"
    :meta-description="$post->excerpt"
    :footer-services="$footerServices"
    :article="$post"
    :trail="[
        ['label' => __('nav.home'), 'url' => route('home')],
        ['label' => __('nav.articles'), 'url' => route('articles')],
        ['label' => $post->title, 'url' => null],
    ]"
>
    <x-slot:cta-title>{{ __('articles.cta.title') }}</x-slot:cta-title>
    <x-slot:cta-lead>{{ __('articles.cta.lead') }}</x-slot:cta-lead>

    <x-slot:actions>
        {{--
            Byline under the title: WHO IS ANSWERABLE FOR THIS, when it was
            checked, when it went up, and how long it takes to read.

            The reviewer is named, not the author. A reader arriving from a
            search for her own condition is not reading a blog post — she is
            reading what she reasonably takes to be advice from the clinician
            she is about to book with. «Who typed it» is not the question she
            needs answered; «who is professionally answerable for it» is.

            reviewer is never null on a page that renders, because the model
            refuses to publish an article without one and scopePublished
            refuses to serve one. Both guards exist so this line cannot quietly
            become blank.
        --}}
        <p class="mt-8 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted">
            <span class="font-medium text-ink">{{ __('articles.reviewed_by', ['name' => $post->reviewer?->name]) }}</span>
            <span aria-hidden="true" class="text-line">·</span>
            <span><bdi dir="auto">{{ __('articles.reviewed_on', ['date' => $post->reviewed_at?->translatedFormat('j F Y')]) }}</bdi></span>
            <span aria-hidden="true" class="text-line">·</span>
            <span aria-hidden="true" class="text-line">·</span>
            <span><bdi dir="auto">{{ $post->published_at?->translatedFormat('j F Y') }}</bdi></span>

            @if ($post->reading_minutes)
                <span aria-hidden="true" class="text-line">·</span>
                <span>{{ __('articles.reading_time', ['minutes' => $post->reading_minutes]) }}</span>
            @endif
        </p>
    </x-slot:actions>

    <article class="py-14 sm:py-20">
        <x-container size="narrow">
            @if ($hasCover)
                {{-- The only above-fold image on this page, so the only one
                     that loads eagerly. --}}
                <x-photo
                    :slug="$post->cover_path"
                    :alt="__('articles.cover_alt.'.$post->slug)"
                    :eager="true"
                    sizes="(min-width: 768px) 48rem, 100vw"
                    class="mb-12 ring-1 ring-line"
                />
            @endif

            {{--
                THE DISCLAIMER, ABOVE THE FIRST PARAGRAPH.

                There is one in the footer already, and it is worthless for
                this purpose: by the time a reader reaches the footer she has
                read the article. A disclaimer is only doing its job if it is
                read BEFORE the thing it qualifies.

                It is styled to be read rather than skipped — a bordered block
                in the body's own measure, not fine print. It says the three
                things that matter: this is not a diagnosis, it is not a plan,
                and do not change a medication because of it.
            --}}
            <aside class="mb-10 rounded-xl bg-sage/60 p-5 ring-1 ring-line" role="note">
                <h2 class="font-display text-base font-semibold text-ink">{{ __('articles.disclaimer_heading') }}</h2>
                <p class="mt-2 text-sm leading-relaxed text-muted">{{ __('articles.disclaimer_body') }}</p>
            </aside>

            {{--
                The body.

                prose-* is not used because this project has no typography
                plugin; the spacing is set here on the container so an article
                reads like prose without every paragraph carrying classes.
            --}}
            <div class="space-y-5 text-lg leading-relaxed text-pretty text-muted">
                @foreach (preg_split('/\R{2,}/u', (string) $post->body) as $paragraph)
                    @if (trim($paragraph) !== '')
                        <p>{{ trim($paragraph) }}</p>
                    @endif
                @endforeach
            </div>

            {{--
                Sharing, with no third party involved.

                A wa.me link and a copy-to-clipboard button. No Facebook SDK, no
                Twitter widget, no share-count service — every one of those is a
                script from another company that learns which article a patient
                read, on a site whose whole position is that it does not do
                that. The note says so, because a visitor cannot tell by
                looking.
            --}}
            <div class="mt-14 border-t border-line pt-8">
                <h2 class="text-sm font-semibold tracking-wide text-accent-dark uppercase">{{ __('articles.share_heading') }}</h2>

                <div class="mt-4 flex flex-wrap items-center gap-3">
                    <a
                        href="https://wa.me/?text={{ rawurlencode($post->title.' — '.$url) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center rounded-pill px-4 py-2 text-sm font-medium text-ink ring-1 ring-line transition hover:bg-sage"
                    >{{ __('articles.share_whatsapp') }}</a>

                    <button
                        type="button"
                        data-copy="{{ $url }}"
                        data-copy-label-copied="{{ __('articles.share_copied') }}"
                        class="inline-flex items-center rounded-pill px-4 py-2 text-sm font-medium text-ink ring-1 ring-line transition hover:bg-sage"
                    >
                        <span data-copy-label>{{ __('articles.share_copy') }}</span>
                    </button>
                </div>

                <p class="mt-3 text-xs leading-relaxed text-muted">{{ __('articles.share_note') }}</p>
            </div>

            <a
                href="{{ route('articles') }}"
                class="mt-10 inline-flex items-center gap-1.5 text-sm font-medium text-accent-dark underline-offset-4 hover:underline"
            >
                <svg class="size-4 rtl:-scale-x-100" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M13 4l-6 6 6 6" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
                {{ __('articles.back_to_index') }}
            </a>
        </x-container>
    </article>

    {{-- Related, by category. --}}
    <section class="bg-sage/50 py-16 sm:py-20" aria-labelledby="related-heading">
        <x-container>
            <h2 id="related-heading" class="font-display text-2xl font-semibold text-ink">{{ __('articles.related_heading') }}</h2>

            @if ($related->isEmpty())
                <p class="mt-4 text-muted">{{ __('articles.related_empty') }}</p>
            @else
                <ul class="mt-8 grid gap-6 sm:grid-cols-2">
                    @foreach ($related as $other)
                        <li class="reveal flex">
                            <x-card class="flex w-full flex-col">
                                <p class="text-xs text-muted"><bdi dir="auto">{{ $other->published_at?->translatedFormat('j F Y') }}</bdi></p>

                                <h3 class="mt-2 font-display text-lg font-semibold text-ink">
                                    <a href="{{ route('posts.show', ['slug' => $other->slug]) }}" class="hover:text-accent-dark">{{ $other->title }}</a>
                                </h3>

                                <p class="mt-2 text-sm leading-relaxed text-muted">{{ $other->excerpt }}</p>
                            </x-card>
                        </li>
                    @endforeach
                </ul>
            @endif
        </x-container>
    </section>
</x-page-shell>
