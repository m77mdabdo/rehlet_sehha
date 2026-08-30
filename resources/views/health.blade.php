{{--
    THE STATUS PAGE, SELF-CONTAINED.

    Same rule as the error pages: no @vite, no layout, no database read, no
    component that could itself be the broken thing. A health page that fails
    when the site is unhealthy is worse than no health page, because it turns a
    named failure into a blank screen.

    Everything below is inline CSS, system fonts and values already handed to
    the view. The only work this template does is decide between two colours.
--}}

@php
    $locale = app()->getLocale();
    $rtl = $locale === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    {{-- Never in an index. This page exists for a monitor and for the desk. --}}
    <meta name="robots" content="noindex, nofollow">
    <title>{{ __('health.title') }} — {{ __('common.brand') }}</title>

    <style>
        :root {
            --ink: #0E2E4D; --muted: #4A6684; --line: #DBE0E6;
            --paper: #EEF3F8; --good: #1B7A4B; --bad: #A6301A;
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
            padding: 36px 32px; max-width: 36rem; width: 100%;
            box-shadow: 0 1px 3px rgb(14 46 77 / 6%);
        }
        h1 { font-size: 26px; line-height: 1.25; margin: 0; }
        .lead { color: var(--muted); margin: 12px 0 0; }
        ul { list-style: none; margin: 28px 0 0; padding: 0; }
        li { padding: 14px 0; border-top: 1px solid var(--line); }
        .row { display: flex; align-items: baseline; justify-content: space-between; gap: 16px; }
        .name { font-weight: 600; }
        /* The state is a word as well as a colour: a red dot alone is not
           readable to a colour-blind receptionist, and this page has exactly
           one job. */
        .state { font-size: 14px; font-weight: 600; white-space: nowrap; }
        .state.ok { color: var(--good); }
        .state.bad { color: var(--bad); }
        .note { color: var(--muted); font-size: 14px; margin: 4px 0 0; }
        .stamp { margin: 26px 0 0; padding-top: 18px; border-top: 1px solid var(--line);
                 color: var(--muted); font-size: 13px; }
        .num { unicode-bidi: isolate; direction: ltr; display: inline-block; }
    </style>
</head>
<body>
    <main class="card">
        <h1>{{ $healthy ? __('health.healthy') : __('health.degraded') }}</h1>

        <p class="lead">{{ $healthy ? __('health.healthy_body') : __('health.degraded_body') }}</p>

        <ul>
            @foreach ($checks as $name => $passed)
                <li>
                    <div class="row">
                        <span class="name">{{ __("health.checks.{$name}.label") }}</span>
                        <span class="state {{ $passed ? 'ok' : 'bad' }}">
                            {{ $passed ? __('health.ok') : __('health.failed') }}
                        </span>
                    </div>
                    <p class="note">{{ __("health.checks.{$name}.".($passed ? 'ok' : 'failed')) }}</p>
                </li>
            @endforeach
        </ul>

        <p class="stamp">
            <span class="num">{{ now()->timezone(config('clinic.timezone'))->format('Y-m-d H:i') }}</span>
        </p>
    </main>
</body>
</html>
