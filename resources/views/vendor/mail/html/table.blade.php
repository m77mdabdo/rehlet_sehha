@php use App\Support\Locales; $dir = Locales::direction(app()->getLocale()); @endphp
<div class="table" dir="{{ $dir }}">
{{ Illuminate\Mail\Markdown::parse($slot) }}
</div>
