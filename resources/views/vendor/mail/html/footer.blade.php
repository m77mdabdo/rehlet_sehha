@php use App\Support\Locales; $dir = Locales::direction(app()->getLocale()); @endphp
<tr>
<td>
<table class="footer" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation" dir="{{ $dir }}">
<tr>
<td class="content-cell" align="center" dir="{{ $dir }}">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</td>
</tr>
</table>
</td>
</tr>
