@props([
    'eyebrow' => null,
    'title' => null,
    'lead' => null,
    'metaTitle' => null,
    'metaDescription' => null,
    /**
     * The breadcrumb trail, root first, current page last.
     *
     * @var list<array{label: string, url?: string|null}>
     */
    'trail' => [],
    'footerServices' => null,

    /**
     * The article this page IS, when it is one.
     *
     * Only the article page passes it. Everything else emits the clinic and
     * the trail, which is all a service or FAQ page has to say about itself.
     */
    'article' => null,

    /**
     * Which entry in config('page-headers.pages') this page's header uses.
     *
     * Null means the navy ground rather than a borrowed photograph — see
     * components/page-header.blade.php for why that is a design and not a gap.
     */
    'headerPage' => null,
])

{{--
    The shell every standalone page sits in.

    Breadcrumb, page header, content, booking band — in that order, once, so
    the remaining seven pages are a content file and nothing else. If a page
    needs a different shape, that is a conversation about the page, not a
    reason to fork this.

    THE HEADER ITSELF LIVES IN ITS OWN COMPONENT as of Task 12, because the
    specialty page needs the same header and does not use this shell. The
    argument for its size, its scrim and its breadcrumb is there.
--}}

@php
    /*
     * THE LINK PREVIEW FOR AN ARTICLE IS THE ARTICLE'S OWN COVER.
     *
     * WhatsApp is how these get shared, and fourteen articles all previewing
     * as the same brand card is fourteen identical grey-blue rectangles in a
     * thread. The cover is already 1200-ish wide and already chosen for the
     * piece.
     *
     * WebP DELIBERATELY, and this is the one place it is a judgement call:
     * WhatsApp and Facebook both render WebP previews now, and the alternative
     * would be a second JPEG copy of every cover existing only for this tag.
     * If a preview ever comes back blank on a real device, this is the line to
     * suspect first — the brand card beneath it is a PNG and is not affected.
     */
    $shellOgImage = null;
    $shellOgWidth = null;
    $shellOgHeight = null;
    $shellOgAlt = null;

    if ($article?->cover_path && \App\Support\Photo::has((string) $article->cover_path)) {
        $slug = (string) $article->cover_path;
        $variant = \App\Support\Photo::largest($slug);
        $size = \App\Support\Photo::get($slug)['variants'][$variant];

        $shellOgImage = asset(\App\Support\Photo::url($slug, $variant));
        $shellOgWidth = $size['width'];
        $shellOgHeight = $size['height'];
        $shellOgAlt = __('articles.cover_alt.'.$article->slug);
    }
@endphp

<x-layouts.app
    :title="$metaTitle"
    :description="$metaDescription"
    :footer-services="$footerServices"
    :og-image="$shellOgImage"
    :og-image-width="$shellOgWidth"
    :og-image-height="$shellOgHeight"
    :og-image-alt="$shellOgAlt"
    :schema="\App\Support\PageSchema::toJson($trail, $article)"
>
    <x-page-header
        :page="$headerPage"
        :eyebrow="$eyebrow"
        :title="$title"
        :lead="$lead"
        :trail="$trail"
    >
        {{ $actions ?? '' }}
    </x-page-header>

    {{ $slot }}

    {{--
        The closing band, identical in shape to the homepage's so the two do
        not read as different sites. Its copy comes from the page, though: a
        booking prompt that follows a price comparison should not say the same
        thing as one that follows a list of clinical areas.
    --}}
    <section class="bg-ink py-20 text-white sm:py-24" aria-labelledby="page-cta-heading">
        <x-container size="narrow" class="text-center">
            <h2 id="page-cta-heading" class="font-display text-3xl font-semibold text-balance sm:text-4xl">
                {{ $ctaTitle ?? __('home.booking_cta.title') }}
            </h2>

            <p class="mt-4 text-base leading-relaxed text-pretty text-white/75 sm:text-lg">
                {{ $ctaLead ?? __('home.booking_cta.lead') }}
            </p>

            <div class="mt-8 flex justify-center">
                <x-button :href="route('booking')" variant="light" size="lg">
                    {{ __('home.booking_cta.cta') }}
                </x-button>
            </div>

            <p class="mt-5 text-sm text-white/55">{{ __('home.booking_cta.note') }}</p>
        </x-container>
    </section>
</x-layouts.app>
