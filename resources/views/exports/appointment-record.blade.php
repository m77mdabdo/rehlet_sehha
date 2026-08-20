@php
    use App\Support\Locales;
    use App\Support\PhoneNumber;

    $locale = Locales::current();
    $direction = Locales::direction($locale);
    $zone = config('clinic.timezone');
    $cairo = $appointment->starts_at->clone()->setTimezone($zone);

    /*
     * Styles are INLINE, not linked.
     *
     * This file leaves the server and has to keep working with no network,
     * years from now, opened from a USB stick or an email attachment. A
     * stylesheet link would make it depend on a site that may not exist by
     * then. The palette mirrors the @theme tokens; it is the one place a
     * duplicate is unavoidable, because a downloaded file cannot resolve a
     * CSS custom property served from elsewhere.
     *
     * The font stack falls back to whatever the reader has. Embedding the
     * brand fonts would multiply the file size by fifty for a document that
     * mostly needs to be legible and printable.
     */
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive">
<title>{{ __('export.title') }} — {{ $appointment->reference }}</title>
<style>
  :root { --ink:#0E2E4D; --muted:#4A6684; --line:#DBE0E6; --paper:#EEF3F8; --accent:#1A6DA6; }
  * { box-sizing: border-box; }
  body {
    margin: 0; padding: 32px 20px; background: var(--paper); color: var(--ink);
    font-family: "Tajawal", "Readex Pro", "Segoe UI", system-ui, -apple-system, sans-serif;
    line-height: 1.7; -webkit-font-smoothing: antialiased;
  }
  .sheet { max-width: 720px; margin: 0 auto; background: #fff; border: 1px solid var(--line); border-radius: 14px; padding: 32px; }
  header { display: flex; align-items: flex-start; justify-content: space-between; gap: 16px; border-bottom: 1px solid var(--line); padding-bottom: 20px; }
  .brand { font-size: 20px; font-weight: 700; }
  .tagline { color: var(--muted); font-size: 13px; margin-top: 2px; }
  .meta { color: var(--muted); font-size: 12px; text-align: end; }
  h1 { font-size: 22px; margin: 24px 0 4px; }
  .lead { color: var(--muted); font-size: 14px; margin: 0 0 8px; }
  h2 { font-size: 15px; margin: 28px 0 10px; padding-bottom: 6px; border-bottom: 1px solid var(--line); }
  dl { margin: 0; }
  .row { display: flex; gap: 16px; justify-content: space-between; padding: 7px 0; border-bottom: 1px dashed var(--line); }
  .row:last-child { border-bottom: 0; }
  dt { color: var(--muted); font-size: 13px; flex: 0 0 40%; }
  dd { margin: 0; font-size: 14px; font-weight: 600; text-align: end; flex: 1; }
  .free dd { font-weight: 400; text-align: start; white-space: pre-wrap; }
  .free .row { display: block; }
  .free dt { margin-bottom: 3px; }
  /*
   * .ltr is for values that are ENTIRELY Latin or numeric — the reference,
   * the phone number, the email, the price, the URL. Forced LTR is right for
   * those and wrong for anything containing Arabic.
   *
   * Dates use <bdi dir="auto"> instead, NOT this class. A date like
   * "21 أغسطس 2026 — 00:57" mixes scripts, and forcing it LTR strands the day
   * number at the far end, away from its own month: the digits after an
   * Arabic word are reclassified from European to Arabic numerals (UAX #9,
   * rule W2), which flips the rest of the string around the leading day.
   * dir="auto" takes the direction from the first strong character, so the
   * Arabic date reads right-to-left and the English one left-to-right,
   * without either being told which it is.
   */
  .ltr { direction: ltr; unicode-bidi: isolate; display: inline-block; }
  bdi { unicode-bidi: isolate; }
  .notice { margin-top: 22px; background: var(--paper); border: 1px solid var(--line); border-radius: 10px; padding: 14px 16px; font-size: 13px; color: var(--muted); }
  .erased { background: #FBF3E4; border-color: #E8A94A; color: var(--ink); }
  footer { margin-top: 26px; padding-top: 16px; border-top: 1px solid var(--line); font-size: 12px; color: var(--muted); }
  @media print {
    body { background: #fff; padding: 0; }
    .sheet { border: 0; border-radius: 0; padding: 0; max-width: none; }
    .notice { break-inside: avoid; }
  }
</style>
</head>
<body>
<div class="sheet">
  <header>
    <div>
      <div class="brand">{{ __('common.brand') }}</div>
      <div class="tagline">{{ __('common.brand_tagline') }}</div>
    </div>
    <div class="meta">
      {{ __('export.generated_on') }}<br>
      <bdi dir="auto">{{ now()->setTimezone($zone)->translatedFormat('j F Y — H:i') }}</bdi>
    </div>
  </header>

  <h1>{{ __('export.title') }}</h1>
  <p class="lead">{{ __('export.lead') }}</p>

  <h2>{{ __('export.sections.appointment') }}</h2>
  <dl>
    <div class="row"><dt>{{ __('booking.confirmation.reference') }}</dt><dd><span class="ltr">{{ $appointment->reference }}</span></dd></div>
    <div class="row"><dt>{{ __('booking.summary.service') }}</dt><dd>{{ $appointment->service->name }}</dd></div>
    <div class="row">
      <dt>{{ __('booking.confirmation.when') }}</dt>
      <dd><bdi dir="auto">{{ $cairo->translatedFormat('l j F Y — H:i') }}</bdi><br>
        <span style="font-weight:400;color:var(--muted);font-size:12px">{{ __('booking.confirmation.timezone', ['zone' => $zone]) }}</span></dd>
    </div>
    <div class="row"><dt>{{ __('booking.summary.duration') }}</dt><dd>{{ $appointment->service->duration_minutes }} {{ __('common.minutes') }}</dd></div>
    <div class="row"><dt>{{ __('booking.summary.mode') }}</dt><dd>{{ __('booking.mode.'.$appointment->mode->value) }}</dd></div>
    <div class="row"><dt>{{ __('booking.manage.status') }}</dt><dd>{{ $appointment->status->label() }}</dd></div>
    <div class="row"><dt>{{ __('booking.summary.price') }}</dt><dd><span class="ltr">{{ number_format((float) $appointment->price) }}</span> {{ __('common.currency') }}</dd></div>
  </dl>

  <h2>{{ __('export.sections.patient') }}</h2>
  <dl>
    <div class="row"><dt>{{ __('booking.fields.name') }}</dt><dd>{{ $appointment->patient->name }}</dd></div>
    <div class="row"><dt>{{ __('booking.fields.phone') }}</dt><dd><span class="ltr">{{ PhoneNumber::forDisplay($appointment->patient->phone) }}</span></dd></div>
    @if ($appointment->patient->email)
      <div class="row"><dt>{{ __('booking.fields.email') }}</dt><dd><span class="ltr">{{ $appointment->patient->email }}</span></dd></div>
    @endif
    @if ($appointment->patient->birth_date)
      <div class="row"><dt>{{ __('booking.fields.birth_date') }}</dt><dd><bdi dir="auto">{{ $appointment->patient->birth_date->translatedFormat('j F Y') }}</bdi></dd></div>
    @endif
  </dl>

  <h2>{{ __('export.sections.intake') }}</h2>

  @if ($intake === null)
    <p class="notice">{{ __('export.no_intake') }}</p>
  @elseif ($intake->isErased())
    {{-- An erased record exports AS ERASED, not as blank. A blank document
         reads like the clinic lost the data; this one records that the patient
         asked for it to go and when that was honoured. --}}
    <p class="notice erased">
      {{ __('export.erased', ['date' => $intake->erased_at->clone()->setTimezone($zone)->translatedFormat('j F Y')]) }}
    </p>
  @else
    <dl class="free">
      <div class="row"><dt>{{ __('booking.fields.goal') }}</dt><dd>{{ $intake->goal ? __('booking.goals.'.$intake->goal) : __('booking.rights.blank') }}</dd></div>
      @foreach (['medications' => 'medications', 'conditions' => 'conditions', 'avoid_foods' => 'avoid_foods', 'note' => 'note'] as $field => $key)
        <div class="row"><dt>{{ __('booking.fields.'.$key) }}</dt><dd>{{ $intake->{$field} ?: __('booking.rights.blank') }}</dd></div>
      @endforeach
    </dl>
  @endif

  @if ($intake)
    <h2>{{ __('export.sections.consent') }}</h2>
    <dl>
      <div class="row">
        <dt>{{ __('export.consent_given_on') }}</dt>
        <dd>
          @if ($intake->consent_at)
            <bdi dir="auto">{{ $intake->consent_at->clone()->setTimezone($zone)->translatedFormat('j F Y — H:i') }}</bdi>
          @else
            —
          @endif
        </dd>
      </div>
      @if ($intake->isErased())
        <div class="row">
          <dt>{{ __('export.erased_on') }}</dt>
          <dd><bdi dir="auto">{{ $intake->erased_at->clone()->setTimezone($zone)->translatedFormat('j F Y — H:i') }}</bdi></dd>
        </div>
      @endif
    </dl>
  @endif

  <p class="notice">{{ __('export.rights_note') }}</p>

  <footer>
    {{ __('footer.disclaimer') }}<br>
    {{ __('common.brand') }} — <span class="ltr">{{ config('app.url') }}</span>
  </footer>
</div>
</body>
</html>
