@props([
    'title' => null,
    'description' => null,
    'footerServices' => null,
    'schema' => null,
    /*
     * Whether this page may be advertised to the outside world.
     *
     * false suppresses canonical, hreflang and og:url, and emits a robots
     * noindex. Those three tags all echo the CURRENT URL, and on a page whose
     * URL carries a bearer token — the appointment self-service page — that
     * publishes a working cancellation link to every search engine that reads
     * the canonical tag and to every chat app that renders a link preview.
     *
     * A test asserts the token never appears in any of them.
     */
    'indexable' => true,
])

@php
    use App\Support\Locales;

    $locale = Locales::current();
    $direction = Locales::direction($locale);

    /*
     * Preload only the two files the CURRENT script actually needs.
     *
     * Preloading all eight would be worse than preloading none: the browser
     * would fetch ~110KB of fonts on first paint, most of it a script the
     * visitor will never see. An Arabic reader gets the Arabic display face and
     * the Arabic body face; an English reader gets the Latin pair. The
     * unicode-range split in app.css keeps the rest from ever being requested.
     */
    $criticalFonts = Locales::isRtl($locale)
        ? [
            'node_modules/@fontsource-variable/readex-pro/files/readex-pro-arabic-wght-normal.woff2',
            'node_modules/@fontsource/tajawal/files/tajawal-arabic-400-normal.woff2',
        ]
        : [
            'node_modules/@fontsource-variable/readex-pro/files/readex-pro-latin-wght-normal.woff2',
            'node_modules/@fontsource/tajawal/files/tajawal-latin-400-normal.woff2',
        ];
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $pageTitle = $title ?? __('home.meta_title');
        $pageDescription = $description ?? __('home.meta_description');
    @endphp

    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $pageDescription }}">

    {{--
        OpenGraph and Twitter. No og:image yet — the 1200x630 file has not been
        exported (see docs/og-image.html). A tag pointing at a missing image is
        worse than no tag: WhatsApp and Facebook cache the failure, and the
        preview stays broken long after the file appears.

        og:locale uses the underscore form these consumers expect, which is not
        the same string as the html lang attribute.
    --}}
    <meta property="og:site_name" content="{{ __('common.brand') }}">
    <meta property="og:type" content="website">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $pageDescription }}">
    @if ($indexable)
        <meta property="og:url" content="{{ url()->current() }}">
    @endif
    <meta property="og:locale" content="{{ $locale === 'ar' ? 'ar_EG' : 'en_GB' }}">
    @foreach (Locales::all() as $alternate)
        @if ($alternate !== $locale)
            <meta property="og:locale:alternate" content="{{ $alternate === 'ar' ? 'ar_EG' : 'en_GB' }}">
        @endif
    @endforeach

    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $pageTitle }}">
    <meta name="twitter:description" content="{{ $pageDescription }}">

    {{--
        hreflang: tells a search engine these are the same page in two
        languages, not duplicate content competing with each other. x-default
        names the version to serve someone whose language we do not publish.
    --}}
    @if ($indexable)
        @foreach (Locales::all() as $alternate)
            <link rel="alternate" hreflang="{{ $alternate }}" href="{{ Locales::alternateUrl($alternate) }}">
        @endforeach
        <link rel="alternate" hreflang="x-default" href="{{ Locales::alternateUrl(Locales::DEFAULT) }}">
        <link rel="canonical" href="{{ url()->current() }}">
    @else
        <meta name="robots" content="noindex, nofollow, noarchive">
        <meta name="referrer" content="no-referrer">
    @endif

    {{--
        Brand assets. The SVG favicon is the real one — it stays sharp at any
        density and follows the tab bar's own scaling; the 32px PNG is the
        fallback for browsers that will not take an SVG icon.

        The manifest is a route, not a file, so its colours come from the same
        config mirror as the meta tag below rather than being a third copy.
    --}}
    <link rel="icon" href="{{ asset('brand/favicon.svg') }}" type="image/svg+xml">
    <link rel="icon" href="{{ asset('brand/favicon-32.png') }}" sizes="32x32" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('brand/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ route('manifest') }}">
    {{-- The one place a literal hex is unavoidable in markup: browser chrome
         reads this before any stylesheet is parsed, so a CSS variable would
         not resolve. Same config entry the manifest route reads. --}}
    <meta name="theme-color" content="{{ config('clinic.brand.ink') }}">

    {{-- Self-hosted, fingerprinted by Vite. No request ever leaves for Google. --}}
    @foreach ($criticalFonts as $font)
        <link rel="preload" as="font" type="font/woff2" href="{{ Vite::asset($font) }}" crossorigin>
    @endforeach

    {{--
        Turns the SCROLL REVEALS on.

        Only the reveals. The hero entrance is a CSS animation with no script
        behind it, and the counting figures degrade to the final number they
        already render — neither depends on this class. What it gates is the one
        effect that genuinely needs a script: elements below the fold start
        hidden so an IntersectionObserver can bring them in.

        Inline and in the head on purpose. Adding the class from the bundle
        would mean any reveal already on screen paints, vanishes, then animates
        back in, which is worse than not animating.

        THE TIMER IS THE POINT, NOT AN AFTERTHOUGHT. It covers the gap the head
        script opens: things are hidden, but the bundle has not arrived to
        un-hide them. 1200ms rather than something generous, because the cost of
        firing too early is nothing — the content simply appears without
        animating — while the cost of firing too late is a visitor looking at a
        blank section. Measured: this bundle initialises at about 2.1s on
        throttled slow 4G, so on those connections the reveals are deliberately
        skipped. That is the right way round. The animation is decoration and
        the people on the slowest connections should not be the ones waiting for
        it.
    --}}
    <script>
        (function () {
            var root = document.documentElement;

            if (!('IntersectionObserver' in window)) return;
            if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

            root.classList.add('js-motion');

            window.setTimeout(function () {
                if (!root.hasAttribute('data-motion-ready')) root.classList.remove('js-motion');
            }, 1200);
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{--
        JSON-LD. Built from config and the working_hours rows, as is the hours
        line in the footer — see App\Support\OpeningHours.

        This comment used to claim the hours "cannot be right on the page and
        wrong in the structured data". That was false: only this block was
        derived, and the footer beside it was a hand-typed sentence which went
        stale the first time the schedule was edited. Both now read the same
        rows, which is what the claim always assumed.
    --}}
    @isset($schema)
        <script type="application/ld+json">{!! $schema !!}</script>
    @endisset

    @stack('head')
</head>

<body class="min-h-dvh bg-paper text-ink antialiased">
    {{-- First tab stop on every page: let a keyboard user past the nav. --}}
    <a
        href="#main"
        class="sr-only-focusable absolute top-3 start-3 z-50 rounded-md bg-accent px-4 py-2 text-sm font-medium text-white"
    >
        {{ __('nav.skip_to_content') }}
    </a>

    {{--
        The header is transparent over a hero that has one, and solid
        everywhere else and after scroll. header.js adds data-solid; until it
        runs, and forever on pages without a hero, the solid treatment is what
        the markup already says — so a failed script leaves a readable header
        rather than white-on-white.

        The colours are driven by data-solid rather than by two class lists, so
        the transparent state cannot drift out of step with the solid one.
    --}}
    <header
        class="group sticky top-0 z-40 border-b border-line bg-paper/80 text-ink backdrop-blur-md motion-safe:transition-[background-color,border-color,color] motion-safe:duration-300
               data-transparent:border-transparent data-transparent:bg-transparent data-transparent:text-white data-transparent:backdrop-blur-none"
        data-menu-root
        data-header
    >
        <x-container>
            <div class="flex h-18 items-center justify-between gap-4">
                <a href="{{ route('home') }}" class="rounded-md" aria-label="{{ __('common.brand') }}">
                    <x-logo.lockup :size="38" />
                </a>

                <nav class="hidden items-center gap-7 lg:flex" aria-label="{{ __('nav.menu_label') }}">
                    @include('partials.nav-links')
                </nav>

                <div class="hidden items-center gap-3 lg:flex">
                    @include('partials.language-switcher', ['indexable' => $indexable])
                    <x-button :href="route('booking')">{{ __('nav.book') }}</x-button>
                </div>

                {{-- Mobile: switcher stays reachable without opening the menu. --}}
                <div class="flex items-center gap-2 lg:hidden">
                    @include('partials.language-switcher', ['compact' => true, 'indexable' => $indexable])

                    <button
                        type="button"
                        class="inline-flex size-11 items-center justify-center rounded-pill ring-1 ring-line transition-colors hover:bg-sage
                               group-data-transparent:ring-white/60 group-data-transparent:hover:bg-white/15"
                        aria-controls="mobile-menu"
                        aria-expanded="false"
                        data-menu-toggle
                        data-label-close="{{ __('nav.menu_close') }}"
                    >
                        <span class="sr-only" data-menu-label data-label-open="{{ __('nav.menu_open') }}">{{ __('nav.menu_open') }}</span>
                        <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path data-menu-icon-open stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16" />
                            <path data-menu-icon-close class="hidden" stroke-linecap="round" d="M6 6l12 12M18 6L6 18" />
                        </svg>
                    </button>
                </div>
            </div>
        </x-container>

        {{-- Always opaque: an open menu over the hero video would be unreadable. --}}
        <div id="mobile-menu" class="hidden border-t border-line bg-paper text-ink lg:hidden" data-menu-panel>
            <x-container>
                <nav class="flex flex-col py-2" aria-label="{{ __('nav.menu_label') }}">
                    @include('partials.nav-links', ['mobile' => true])
                </nav>
                <div class="pb-6">
                    <x-button :href="route('booking')" class="w-full">{{ __('nav.book') }}</x-button>
                </div>
            </x-container>
        </div>
    </header>

    <main id="main" tabindex="-1">
        {{ $slot }}
    </main>

    <footer class="mt-24 bg-ink text-white/80">
        <x-container class="py-16">
            <div class="grid gap-12 md:grid-cols-2 lg:grid-cols-4">
                <div class="lg:col-span-2">
                    {{-- currentColor makes the mark invert on the navy without a second asset. --}}
                    <x-logo.lockup :size="40" class="text-white" />
                    <p class="mt-5 max-w-md text-sm leading-relaxed text-pretty">
                        {{ __('footer.about') }}
                    </p>
                </div>

                <div>
                    <h2 class="font-display text-base font-semibold text-white">
                        {{ __('footer.services_heading') }}
                    </h2>
                    <ul class="mt-4 space-y-3 text-sm">
                        @foreach ($footerServices ?? [] as $service)
                            <li>
                                <a href="{{ route('home') }}#packages" class="transition-colors hover:text-white">
                                    {{ $service->name }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div>
                    <h2 class="font-display text-base font-semibold text-white">
                        {{ __('footer.contact_heading') }}
                    </h2>

                    {{-- Phone, WhatsApp, email and address, all from config. --}}
                    <x-contact-details class="mt-4" />

                    {{-- Derived from working_hours, not typed. See
                         App\Support\OpeningHours for why. --}}
                    <ul class="mt-4 space-y-3 text-sm opacity-90">
                        @foreach (App\Support\OpeningHours::summary() as $line)
                            <li>{{ $line }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>

            {{--
                The medical disclaimer. Given its own region rather than being
                buried in the copyright line: a nutrition site that discusses
                conditions and lab work has to say plainly that it does not
                replace the visitor's own doctor.
            --}}
            <div class="mt-14 rounded-lg bg-white/5 p-5 ring-1 ring-white/10" role="note">
                <p class="text-sm font-semibold text-white">{{ __('footer.disclaimer_heading') }}</p>
                <p class="mt-2 text-sm leading-relaxed">{{ __('footer.disclaimer') }}</p>
            </div>

            <p class="mt-10 border-t border-white/10 pt-8 text-sm">
                © {{ now()->year }} {{ __('common.brand') }} — {{ __('common.all_rights') }}
            </p>
        </x-container>
    </footer>

    @stack('scripts')
</body>
</html>
