@php
    use App\Support\Locales;

    $dir = Locales::direction(app()->getLocale());

    /*
     * A hand-written table rather than <x-mail::table> with Markdown pipes.
     *
     * The Markdown table generates cells with no column widths and no
     * horizontal padding, so the label and the value end up touching —
     * "الباقةاستشارة تغذية فردية" reads as one run-on word. Widths cannot be
     * expressed in Markdown table syntax at all, so fixing it there is not
     * possible; this is the "rather than fighting them" line, and the table
     * component is the one place it falls the wrong side of it.
     *
     * Styles are inline on every cell. Email clients discard <style> blocks at
     * different rates, and a layout that depends on a class surviving is a
     * layout that renders differently in Gmail than in Apple Mail.
     *
     * dir is repeated on the table as well as the cells, because Gmail
     * rewrites the DOM and keeps the tables while dropping the document's own
     * direction.
     */
    $label = 'padding: 11px 0; color: #4A6684; font-size: 13px; line-height: 1.6; vertical-align: top; width: 38%; border-bottom: 1px solid #EEF3F8;';
    $value = 'padding: 11px 0; color: #0E2E4D; font-size: 15px; font-weight: 600; line-height: 1.6; vertical-align: top; border-bottom: 1px solid #EEF3F8;';

    // The gap between the two columns has to be a padding on the side the text
    // runs towards, which flips with the language.
    $gap = $dir === 'rtl' ? 'padding-left: 14px;' : 'padding-right: 14px;';
@endphp
<table dir="{{ $dir }}" width="100%" cellpadding="0" cellspacing="0" role="presentation" style="width: 100%; margin: 22px 0; border-collapse: collapse;">
<tr>
<td dir="{{ $dir }}" style="{{ $label }} {{ $gap }}">{{ __('mail.facts.reference') }}</td>
<td dir="{{ $dir }}" style="{{ $value }}"><bdi dir="ltr">{{ $reference }}</bdi></td>
</tr>
<tr>
<td dir="{{ $dir }}" style="{{ $label }} {{ $gap }}">{{ __('mail.facts.service') }}</td>
<td dir="{{ $dir }}" style="{{ $value }}">{{ $service }}</td>
</tr>
<tr>
<td dir="{{ $dir }}" style="{{ $label }} {{ $gap }}">{{ __('mail.facts.when') }}</td>
<td dir="{{ $dir }}" style="{{ $value }}"><bdi dir="auto">{{ $startsAt->translatedFormat('l j F Y — H:i') }}</bdi><br><span style="font-weight: 400; color: #4A6684; font-size: 12px;">{{ __('mail.facts.timezone', ['zone' => $timezone]) }}</span></td>
</tr>
<tr>
<td dir="{{ $dir }}" style="{{ $label }} {{ $gap }}">{{ __('mail.facts.mode') }}</td>
<td dir="{{ $dir }}" style="{{ $value }}">{{ $mode }}</td>
</tr>
<tr>
<td dir="{{ $dir }}" style="{{ $label }} {{ $gap }} border-bottom: 0;">{{ __('mail.facts.price') }}</td>
<td dir="{{ $dir }}" style="{{ $value }} border-bottom: 0;"><bdi dir="ltr">{{ number_format((float) $price) }}</bdi> {{ __('common.currency') }}</td>
</tr>
</table>
