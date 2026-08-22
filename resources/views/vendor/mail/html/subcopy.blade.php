@php use App\Support\Locales; $dir = Locales::direction(app()->getLocale()); @endphp
<table class="subcopy" width="100%" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $dir }}">
<tr>
<td dir="{{ $dir }}">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
