@props(['text', 'class' => ''])

{{--
    A block of copy that is several paragraphs, rendered as several paragraphs.

    Exists because the practitioner's philosophy paragraph is four of them and
    both places that render it printed it inside a single <p> — which in RTL
    produces one unbroken slab of Arabic with no breathing space, and loses the
    beats the writing was built on. Her second paragraph turns on the first;
    running them together throws that away.

    Blank-line separated, the same convention the article bodies already use,
    and the same reason: the column holds plain text so that copy can never
    carry markup into a page.

    NOT A MARKDOWN RENDERER. It splits on blank lines and escapes everything
    else. There is deliberately no way to get a link, an emphasis or a list
    out of it — copy that needs those is a different problem and should not be
    solved by loosening this.
--}}

@php
    $paragraphs = array_values(array_filter(
        array_map('trim', preg_split('/\R{2,}/u', (string) $text) ?: []),
        fn (string $paragraph): bool => $paragraph !== '',
    ));
@endphp

@foreach ($paragraphs as $paragraph)
    <p @class([$class, 'mt-4' => ! $loop->first])>{{ $paragraph }}</p>
@endforeach
