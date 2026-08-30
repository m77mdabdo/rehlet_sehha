@props(['code', 'title', 'body', 'reassure' => null])

{{--
    THE ERROR PAGE A PATIENT ACTUALLY MEETS.

    Self-contained on purpose: no @vite, no layout component, no database. An
    error page that depends on the thing that just failed is not an error page.
    A 500 raised because the database is unreachable must not try to load the
    footer's services, and a 503 during a deploy has no built asset manifest to
    read — Vite would throw inside the error handler and the visitor would get
    a blank white screen instead.

    So: inline critical CSS, system fonts, and every value either a literal or
    a config read. Nothing here can fail.

    The copy answers "what do I do now" rather than "what went wrong". A
    patient meeting a 404 does not care about status codes; she cares whether
    her appointment still exists and how to reach a person. Every page carries
    the booking link and the telephone number for that reason.
--}}

@php
    $locale = app()->getLocale();
    $rtl = $locale === 'ar';
    $phone = App\Support\Contact::phoneDisplay();
    $tel = App\Support\Contact::telHref();
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} — {{ __('common.brand') }}</title>

    <style>
        :root {
            --ink: #0E2E4D; --muted: #4A6684; --line: #DBE0E6;
            --paper: #EEF3F8; --accent: #1A6DA6; --gold: #E8A94A;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; background: var(--paper); color: var(--ink);
            font-family: "Tajawal", system-ui, -apple-system, "Segoe UI", sans-serif;
            display: flex; align-items: center; justify-content: center; padding: 24px;
            line-height: 1.6;
        }
        .card {
            background: #fff; border: 1px solid var(--line); border-radius: 16px;
            padding: 40px 32px; max-width: 34rem; width: 100%;
            box-shadow: 0 1px 3px rgb(14 46 77 / 6%);
        }
        .code { font-size: 13px; letter-spacing: .08em; color: var(--muted); text-transform: uppercase; }
        h1 { font-size: 28px; line-height: 1.25; margin: 10px 0 0; }
        p { color: var(--muted); margin: 16px 0 0; }
        .actions { margin-top: 28px; display: flex; flex-wrap: wrap; gap: 12px; }
        a.btn {
            display: inline-block; padding: 12px 22px; border-radius: 999px;
            text-decoration: none; font-weight: 600; font-size: 15px;
        }
        .primary { background: var(--accent); color: #fff; }
        .ghost { border: 1px solid var(--line); color: var(--ink); }
        .contact { margin-top: 26px; padding-top: 20px; border-top: 1px solid var(--line); font-size: 14px; }
        .contact a { color: var(--accent); }
        .num { unicode-bidi: isolate; direction: ltr; display: inline-block; }
    </style>
</head>
<body>
    <main class="card">
        <p class="code">{{ __('errors.code', ['code' => $code]) }}</p>

        <h1>{{ $title }}</h1>

        <p>{{ $body }}</p>

        @if ($reassure)
            <p><strong>{{ $reassure }}</strong></p>
        @endif

        <div class="actions">
            <a class="btn primary" href="{{ url('/'.$locale) }}">{{ __('errors.home') }}</a>
            <a class="btn ghost" href="{{ url('/'.$locale.'/booking') }}">{{ __('errors.book') }}</a>
        </div>

        @if ($phone)
            <p class="contact">
                {{ __('errors.call_us') }}
                <a href="{{ $tel }}" class="num">{{ $phone }}</a>
            </p>
        @endif
    </main>
</body>
</html>
