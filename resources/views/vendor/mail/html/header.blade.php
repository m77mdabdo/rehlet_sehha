@props(['url'])
@php
    use App\Support\Locales;

    $locale = app()->getLocale();

    /*
     * A hosted PNG, not an SVG and not an embedded data URI.
     *
     * Outlook renders SVG as nothing at all, and several clients — Gmail's web
     * interface among them — strip data: URIs on images, so an inline logo
     * arrives as a broken-image icon at the top of the message. A PNG served
     * over https from our own domain is the only form every client draws.
     *
     * It is also the ONLY remote request this mail makes. There is no tracking
     * pixel here and there will not be one: a 1×1 beacon in a clinic's mail
     * reports back when a patient opened a message about her own health, from
     * roughly where, on what device. Open rates are not worth that.
     *
     * Width AND height are set as attributes as well as in the style, because
     * Outlook ignores CSS dimensions on images. Each file is twice its display
     * size so it stays sharp on a phone.
     *
     * The height is per-locale rather than shared: the two lockups are not the
     * same shape — the English wordmark is wider and shorter than the Arabic —
     * and one hardcoded height would stretch whichever it was not measured
     * from. Intrinsic dimensions are read from the files themselves so the
     * pair cannot drift out of step with these numbers.
     */
    $file = 'brand/email-logo-'.(Locales::isRtl($locale) ? 'ar' : 'en').'.png';
    $logo = asset($file);

    $width = 209;
    [$intrinsicWidth, $intrinsicHeight] = getimagesize(public_path($file));
    $height = (int) round($intrinsicHeight * $width / $intrinsicWidth);
@endphp
<tr>
<td class="header">
<a href="{{ $url }}" style="display: inline-block;">
<img src="{{ $logo }}" class="logo" width="{{ $width }}" height="{{ $height }}" alt="{{ config('app.name') }}" style="width: {{ $width }}px; max-width: {{ $width }}px; height: {{ $height }}px; border: none;">
</a>
</td>
</tr>
