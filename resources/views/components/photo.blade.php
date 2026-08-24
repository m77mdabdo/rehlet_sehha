@props([
    'slug',
    'alt',
    'caption' => null,
    /**
     * How wide this renders, as a CSS `sizes` value. Getting it roughly right
     * is what makes the srcset worth having: too generous and a phone
     * downloads the desktop variant anyway.
     */
    'sizes' => '(min-width: 1024px) 40vw, 100vw',
    /**
     * The first image above the fold on a page, and nothing else. Every other
     * image is lazy — this is the one exception the performance rule allows.
     */
    'eager' => false,
])

@php
    use App\Support\Photo;

    $largest = Photo::largest($slug);
    $size = Photo::get($slug)['variants'][$largest];
@endphp

{{--
    A photograph from the processed library.

    WIDTH AND HEIGHT ARE ALWAYS PRESENT. They come from the generated manifest,
    are the real pixels of the largest variant, and are what lets the browser
    reserve the correct box before a byte arrives. Without them every image on
    the page is a layout shift, and this site is at CLS 0.0000.

    ALT IS REQUIRED AND IS NEVER THE SECTION TITLE. It describes what is in the
    frame, in the reader's own language, because a blind patient is entitled to
    the same information a sighted one gets from looking. config/photos.php
    carries a factual `describes` note for whoever writes it; the alt itself is
    copy and lives in the translation files beside the section, because a good
    alt depends on what the surrounding text has already said.

    A CAPTION is separate and visible, for where the image carries clinical
    meaning that the body text does not already state.
--}}

<figure {{ $attributes->merge(['class' => 'overflow-hidden rounded-2xl bg-sage']) }}>
    <img
        src="{{ Photo::url($slug, $largest) }}"
        srcset="{{ Photo::srcset($slug) }}"
        sizes="{{ $sizes }}"
        width="{{ $size['width'] }}"
        height="{{ $size['height'] }}"
        alt="{{ $alt }}"
        loading="{{ $eager ? 'eager' : 'lazy' }}"
        {{ $eager ? 'fetchpriority=high' : '' }}
        decoding="async"
        class="size-full object-cover"
    >

    @if ($caption)
        <figcaption class="bg-white px-4 py-3 text-xs leading-relaxed text-muted">
            {{ $caption }}
        </figcaption>
    @endif
</figure>
