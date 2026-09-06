@props([
    'title',
    'eyebrow' => null,
    'lead' => null,
    /** @var list<array{label: string, url?: string|null}> */
    'trail' => [],
    /** A key in config('page-headers.pages'), or null for the navy fallback. */
    'page' => null,
])

@php
    use App\Support\Photo;

    $config = $page ? (config('page-headers.pages')[$page] ?? null) : null;
    $slug = $config['photo'] ?? null;

    // A slug that names a photo the pipeline never built must not blank the
    // header: fall through to the navy ground, which is a design rather than
    // a failure state.
    $hasPhoto = $slug !== null && Photo::has($slug);
    $focus = $config['focus'] ?? '50% 50%';

    /*
     * The real pixels of the largest variant. This image is absolutely
     * positioned and cannot shift anything, but every other <img> on the site
     * carries its dimensions and StandalonePagesTest checks all of them —
     * exempting this one would mean teaching that test to ignore a class of
     * image, which is a worse trade than two attributes.
     */
    $size = $hasPhoto ? Photo::get($slug)['variants'][Photo::largest($slug)] : null;
@endphp

{{--
    The page header.

    Full-bleed picture, breadcrumb above, oversized centred title. Every
    standalone page gets one; the homepage has its own hero and does not.

    ---------------------------------------------------------------------------
    WHY THE SCRIM IS A GRADIENT AND NOT A FILTER
    ---------------------------------------------------------------------------

    The site this composition is borrowed from lays a flat green wash over its
    header photographs, and the photograph stops existing — you are looking at a
    green rectangle with a shape in it. Having chosen an image on purpose, that
    throws away the reason for choosing it.

    So the same technique as the hero: a gradient dense across the band the
    words occupy and thin at the top, where the picture is left alone. The
    densities are in config/page-headers.php and were measured against the
    brightest region of every image that uses them, not picked by eye.

    ---------------------------------------------------------------------------
    NO PHOTO IS A DESIGN, NOT A GAP
    ---------------------------------------------------------------------------

    A page with no entry in the config gets the navy ground and the brand
    pattern. That is deliberate and it is the whole reason the mapping is a
    lookup: the alternative — reaching for the nearest available photograph —
    puts an off-topic image at the top of a page, where it is the first thing
    anybody sees and it makes a claim about what the page is about.

    ---------------------------------------------------------------------------

    THE HEIGHT IS IN REM, NOT VH. Mobile browsers resize the viewport as the URL
    bar hides, so a vh-sized header reflows the entire page while somebody is
    reading it. This site is at CLS 0.0000 and that would be the thing that
    ended it.

    THE BREADCRUMB IS UNDERSTATED ON PURPOSE. It is wayfinding for someone who
    arrived from a search and does not yet know what the rest of the site is —
    not navigation we are inviting anybody to use. The trail it shows is the
    same trail the page emits as BreadcrumbList data, because a visible path
    that disagrees with the markup is worse than neither.
--}}
<header
    @class([
        'relative isolate flex items-center overflow-hidden bg-ink py-14 sm:py-16',
        config('page-headers.height'),
    ])
    data-page-header
>
    @if ($hasPhoto)
        <img
            src="{{ Photo::url($slug, Photo::largest($slug)) }}"
            srcset="{{ Photo::srcset($slug) }}"
            sizes="100vw"
            width="{{ $size['width'] }}"
            height="{{ $size['height'] }}"
            {{--
                EMPTY ALT AND aria-hidden ARE BOTH CORRECT HERE, and this is
                the one place on the site where an empty alt is not a defect.

                The photograph is decoration behind a heading that already says
                what the page is. Describing it to a screen reader would
                announce "hands slicing a tomato on a board" before the page
                title on every single specialty page — noise in front of the
                content, not access to it.
            --}}
            alt=""
            aria-hidden="true"
            fetchpriority="high"
            decoding="async"
            style="object-position: {{ $focus }}"
            class="absolute inset-0 -z-20 size-full object-cover"
            data-page-header-photo
        >

        <div
            aria-hidden="true"
            data-page-header-scrim
            class="absolute inset-0 -z-10"
            style="background: {{ config('page-headers.scrim') }}"
        ></div>
    @else
        {{-- The navy ground. The pattern is the brand's own, at low contrast:
             present enough that the block is not a flat rectangle, quiet
             enough that it never competes with the title. --}}
        <div
            aria-hidden="true"
            data-page-header-pattern
            class="absolute inset-0 -z-10 bg-linear-to-b from-ink-soft/45 to-ink"
        ></div>
    @endif

    <x-container class="relative w-full text-center">
        @if ($trail !== [])
            <nav aria-label="{{ __('common.breadcrumb') }}">
                {{-- An ordered list, because the order is the meaning. --}}
                <ol class="flex flex-wrap items-center justify-center gap-x-2 gap-y-1 text-sm text-white/85">
                    @foreach ($trail as $index => $crumb)
                        <li class="flex items-center gap-2">
                            @if ($index > 0)
                                <span aria-hidden="true" class="text-white/60">/</span>
                            @endif

                            @if (($crumb['url'] ?? null) !== null)
                                <a href="{{ $crumb['url'] }}" class="tap-target-row rounded-sm underline-offset-4 transition-colors hover:text-white hover:underline">
                                    {{ $crumb['label'] }}
                                </a>
                            @else
                                {{-- The current page. aria-current tells a screen
                                     reader where the trail stops. --}}
                                <span aria-current="page" class="text-white">{{ $crumb['label'] }}</span>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </nav>
        @endif

        <div class="mx-auto mt-6 max-w-3xl [text-shadow:0_1px_2px_rgb(2_12_24_/_0.5),0_2px_10px_rgb(2_12_24_/_0.28)]">
            @if ($eyebrow)
                <p class="text-sm font-medium tracking-wide text-teal uppercase">{{ $eyebrow }}</p>
            @endif

            {{-- The one h1 on the page. Sections below use h2. --}}
            <h1 @class([
                'font-display font-bold text-balance text-white',
                'text-[2rem] leading-[1.1] sm:text-5xl lg:text-[3.5rem]',
                'mt-3' => $eyebrow,
            ])>
                {{ $title }}
            </h1>

            @if ($lead)
                <p class="mx-auto mt-5 max-w-2xl text-base leading-relaxed text-pretty text-white/85 sm:text-lg">
                    {{ $lead }}
                </p>
            @endif

            {{ $slot }}
        </div>
    </x-container>
</header>
