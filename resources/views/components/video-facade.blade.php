@props(['video', 'featured' => false])

{{--
    One video card: a thumbnail and a button, and nothing of YouTube's.

    The button carries the embed URL in a data attribute rather than the script
    building it from an id, so the ONE place that decides what a YouTube URL
    looks like stays App\Models\Video::embedUrl() — the nocookie host, the
    absent autoplay flag and rel=0 are all decided in PHP where they are
    commented, not scattered in a script.
--}}

@php
    $thumbnail = $video->thumbnailUrl();

    $minutes = $video->duration_seconds
        ? \Illuminate\Support\Carbon::createFromTimestampUTC($video->duration_seconds)->format(
            $video->duration_seconds >= 3600 ? 'H:i:s' : 'i:s'
        )
        : null;
@endphp

<button
    type="button"
    data-video-play
    data-video-embed="{{ $video->embedUrl() }}"
    data-video-title="{{ $video->title }}"
    class="group relative block w-full overflow-hidden rounded-lg bg-sage text-start ring-1 ring-line transition focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent-dark motion-safe:hover:-translate-y-0.5"
>
    <span class="sr-only">{{ __('home.videos.play', ['title' => $video->title]) }}</span>

    <span class="relative block {{ $featured ? 'aspect-video' : 'aspect-video' }} w-full overflow-hidden bg-ink/5">
        @if ($thumbnail)
            <img
                src="{{ $thumbnail }}"
                alt=""
                {{-- Decorative: the accessible name is the sr-only span above,
                     which says "Play: <title>" rather than describing a frame
                     of video nobody can act on. --}}
                width="1280"
                height="720"
                {{-- The featured video is above the fold on a phone; the rest
                     are not, so they wait. --}}
                loading="{{ $featured ? 'eager' : 'lazy' }}"
                decoding="async"
                class="size-full object-cover transition motion-safe:group-hover:scale-[1.02]"
            >
        @else
            {{-- No stored thumbnail: a brand placeholder, never a hotlink to
                 YouTube's CDN. A missing image is a smaller problem than
                 telling Google about every visitor to save it. --}}
            <span class="flex size-full items-center justify-center bg-sage">
                <svg class="size-12 text-accent-dark/40" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M8 5v14l11-7z" />
                </svg>
            </span>
        @endif

        {{-- The play affordance. aria-hidden because the button already says
             what it does. --}}
        <span
            class="absolute inset-0 flex items-center justify-center bg-ink/20 transition group-hover:bg-ink/30"
            aria-hidden="true"
        >
            <span class="flex size-14 items-center justify-center rounded-pill bg-white/90 shadow-sm">
                <svg class="ms-1 size-6 text-ink" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z" />
                </svg>
            </span>
        </span>

        @if ($minutes)
            <span
                class="absolute bottom-2 end-2 rounded-md bg-ink/80 px-2 py-0.5 text-xs font-medium text-white"
                aria-hidden="true"
            >
                <bdi dir="ltr">{{ $minutes }}</bdi>
            </span>
        @endif
    </span>

    <span class="block p-4">
        @if ($featured)
            <span class="mb-1 block text-xs font-medium text-accent-dark">{{ __('home.videos.featured') }}</span>
        @endif

        <span class="block font-display {{ $featured ? 'text-lg' : 'text-base' }} font-semibold text-ink">
            {{ $video->title }}
        </span>

        @if ($video->description)
            <span class="mt-1 block text-sm leading-relaxed text-muted">{{ $video->description }}</span>
        @endif
    </span>
</button>
