@props([
    'url',
    'color' => 'primary',
    'align' => 'center',
])
@php use App\Support\Locales; $dir = Locales::direction(app()->getLocale()); @endphp
{{--
    dir on all three nested tables, not just the outer one. Gmail keeps the
    tables and drops the document direction, and a button whose label is Arabic
    renders its text backwards without one.
--}}
<table class="action" align="{{ $align }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $dir }}">
<tr>
<td align="{{ $align }}">
<table width="100%" border="0" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $dir }}">
<tr>
<td align="{{ $align }}">
<table border="0" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $dir }}">
<tr>
<td dir="{{ $dir }}">
<a href="{{ $url }}" class="button button-{{ $color }}" target="_blank" rel="noopener">{!! $slot !!}</a>
</td>
</tr>
</table>
</td>
</tr>
</table>
</td>
</tr>
</table>
