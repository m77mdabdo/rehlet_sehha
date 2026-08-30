@props(['videos'])

{{--
    The video library, as a FACADE.

    Nothing on this page belongs to YouTube. Each card is a locally stored
    thumbnail and a button; the <iframe> is created only when a patient presses
    play, and only for the video she pressed.

    WHY THIS IS NOT A DETAIL. An embedded YouTube iframe executes Google's
    script on page load, for every visitor, whether or not they ever watch. On a
    nutrition clinic's homepage that means Google learns who is looking at a
    nutrition clinic — an inference about someone's health, made from a page
    they only read. The video is worth having; that price is not worth paying
    for a visitor who never pressed play.

    The thumbnails are stored on our own disk rather than hotlinked from
    img.youtube.com, because hotlinking would leak exactly the same visit to
    exactly the same company while looking careful. See
    App\Services\Video\ThumbnailFetcher.

    Once opened, the embed is youtube-nocookie.com, and there is no autoplay —
    see App\Models\Video::embedUrl() for why the extra click is deliberate.
--}}

@php
    use App\Support\Locales;

    $featured = $videos->first();
    $rest = $videos->skip(1);
@endphp

<section id="videos" class="bg-white py-20 sm:py-24" aria-labelledby="videos-heading">
    <x-container>
        <x-section-heading
            id="videos-heading"
            :eyebrow="__('home.videos.eyebrow')"
            :title="__('home.videos.title')"
            :lead="__('home.videos.lead')"
        />

        @if ($videos->isEmpty())
            <p class="mt-10 text-muted">{{ __('home.videos.empty') }}</p>
        @else
            {{-- The whole gallery is one delegated-click root, so the script
                 binds once rather than per card. --}}
            <div class="mt-12" data-video-gallery>
                <div class="grid gap-6 lg:grid-cols-3">
                    {{-- The featured video, larger. --}}
                    <div class="lg:col-span-2">
                        <x-video-facade :video="$featured" :featured="true" />
                    </div>

                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-1">
                        @foreach ($rest->take(2) as $video)
                            <x-video-facade :video="$video" />
                        @endforeach
                    </div>
                </div>

                @if ($rest->count() > 2)
                    <div class="mt-6 grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach ($rest->skip(2) as $video)
                            <x-video-facade :video="$video" />
                        @endforeach
                    </div>
                @endif

                <p class="mt-6 text-xs leading-relaxed text-muted">{{ __('home.videos.privacy') }}</p>

                {{--
                    The player dialog. ONE per page, reused by every card.

                    A native <dialog> rather than a div with role="dialog":
                    the browser then owns the focus trap, the Escape key and the
                    inert backdrop, and it gets all three right in cases
                    hand-written traps miss — a screen reader's virtual cursor,
                    a form control inside the backdrop, iOS Safari's rubber
                    banding. Less script, better behaviour.
                --}}
                <dialog
                    data-video-dialog
                    aria-labelledby="video-dialog-title"
                    {{-- m-auto restores the centring Tailwind's preflight
                         takes away. A <dialog>[open] centres itself through the
                         UA rule `position: fixed; inset: 0; margin: auto`, and
                         preflight's blanket `margin: 0` kills the third part —
                         so the panel pinned itself to the inline-start edge and
                         the top, which in RTL put it in the top-right corner
                         over the header. --}}
                    class="m-auto w-full max-w-3xl rounded-lg bg-ink p-0 text-white backdrop:bg-ink/70 sm:rounded-xl"
                >
                    <div class="flex items-center justify-between gap-4 p-4">
                        <h3 id="video-dialog-title" data-video-dialog-title class="font-display text-base font-semibold"></h3>

                        <button
                            type="button"
                            data-video-close
                            class="rounded-pill px-3 py-1.5 text-sm text-white/80 ring-1 ring-white/25 hover:bg-white/10"
                        >
                            {{ __('home.videos.close') }}
                        </button>
                    </div>

                    {{-- The iframe is injected here on open and REMOVED on
                         close, so a closed dialog is not a YouTube connection
                         quietly still running in the background. --}}
                    <div class="aspect-video w-full bg-black" data-video-frame></div>
                </dialog>
            </div>
        @endif
    </x-container>
</section>
