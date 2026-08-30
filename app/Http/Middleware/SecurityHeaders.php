<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

/**
 * The headers a browser needs in order to defend a patient on our behalf.
 *
 * TUNED AGAINST WHAT THIS SITE ACTUALLY LOADS, not copied from a template.
 * The whole public site loads from one origin: fonts are self-hosted (see the
 * note in app.css), there is no analytics, no tag manager, no CDN, and no
 * third-party script of any kind. The single exception is the YouTube facade,
 * which inserts a youtube-nocookie.com iframe only after a deliberate click.
 *
 * That is a rare position to be in and the policy is written to keep it: a
 * default-src of 'self' means the day somebody adds a tracking pixel, it does
 * not load, and they find out immediately rather than a patient's reading
 * history being disclosed silently.
 *
 * TWO POLICIES, PUBLIC AND ADMIN. The public site carries exactly one inline
 * script — the motion bootstrap — so it gets a nonce and no 'unsafe-inline'.
 * Filament emits eleven, generated at render time and not nonce-able without
 * patching the package, so the panel gets 'unsafe-inline' for scripts. That is
 * a real weakening and it is confined to a surface which is behind
 * authentication, noindex, two-factor for administrators, and reachable by
 * nobody the site does not already trust.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        /*
         * The nonce is generated BEFORE the response so Blade can read it
         * while rendering. Once per request, never reused: a predictable or
         * shared nonce is the same as no nonce at all.
         */
        $nonce = Str::random(24);
        $request->attributes->set('csp-nonce', $nonce);

        $response = $next($request);

        $isAdmin = $request->is('admin', 'admin/*');

        foreach ($this->headers($nonce, $isAdmin) as $name => $value) {
            // Never overwrite a header a route set deliberately — the token
            // pages set their own Referrer-Policy and X-Robots-Tag, and they
            // are stricter than these.
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }

    /**
     * @return array<string, string>
     */
    private function headers(string $nonce, bool $isAdmin): array
    {
        $headers = [
            'Content-Security-Policy' => $this->contentSecurityPolicy($nonce, $isAdmin),

            // Never let another site frame us. clickjacking a booking form is
            // how somebody cancels an appointment they cannot see.
            'X-Frame-Options' => 'DENY',

            'X-Content-Type-Options' => 'nosniff',

            /*
             * strict-origin-when-cross-origin, not no-referrer.
             *
             * The token pages already force no-referrer, which is what matters.
             * Everywhere else, sending the origin to an outbound link is
             * useful and harmless — and a blanket no-referrer would strip it
             * from our own navigations too.
             */
            'Referrer-Policy' => 'strict-origin-when-cross-origin',

            /*
             * A clinic site needs none of these. Denying them means a future
             * embed cannot quietly ask a patient for her camera or location.
             */
            'Permissions-Policy' => implode(', ', [
                'accelerometer=()', 'camera=()', 'geolocation=()', 'gyroscope=()',
                'magnetometer=()', 'microphone=()', 'payment=()', 'usb=()',
                'interest-cohort=()',
            ]),

            'Cross-Origin-Opener-Policy' => 'same-origin',
        ];

        /*
         * HSTS only over HTTPS, and only in production.
         *
         * Sending it over plain http is meaningless, and sending it from a
         * local machine poisons the developer's browser for the whole
         * domain — including any other project on localhost — for a year.
         */
        if (app()->isProduction() && request()->secure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        return $headers;
    }

    private function contentSecurityPolicy(string $nonce, bool $isAdmin): string
    {
        /*
         * 'unsafe-eval' IS REQUIRED, EVERYWHERE, AND IT IS NOT OPTIONAL.
         *
         * Livewire compiles every wire:model and every Alpine expression into
         * a function at runtime via `new AsyncFunction`. Without unsafe-eval
         * the browser refuses, Alpine throws on the first directive it meets,
         * and every Livewire component on the site is inert — which means the
         * BOOKING WIZARD does not work at all. It fails silently: the page
         * renders, the buttons do nothing.
         *
         * That was found by driving the site with the policy on, not by
         * reading it. A CSP that looks stricter on paper and takes the booking
         * form down is worse than no CSP.
         *
         * What is still gained, and it is most of the value: an injected
         * <script> tag has no nonce and does not execute. unsafe-eval lets
         * ALREADY-TRUSTED script call eval; unsafe-inline would let anything
         * the page happens to contain run. Keeping the nonce and dropping
         * unsafe-inline on the public site is the meaningful half.
         *
         * The panel still needs unsafe-inline on top: Filament emits eleven
         * inline scripts generated at render time, none of them nonce-able
         * without patching the package. That is confined to a surface behind
         * authentication, two-factor for administrators, and noindex.
         */
        $script = $isAdmin
            ? "'self' 'unsafe-inline' 'unsafe-eval'"
            : "'self' 'nonce-{$nonce}' 'unsafe-eval'";

        return implode('; ', [
            "default-src 'self'",
            "script-src {$script}",

            /*
             * 'unsafe-inline' for styles, unavoidably. The hero sets
             * object-position and the specialty cards set a brand colour as
             * inline attributes, and style-src has no attribute-level nonce
             * that browsers agree on. Inline style is a far weaker vector than
             * inline script: it cannot exfiltrate.
             */
            "style-src 'self' 'unsafe-inline'",

            // data: for the one inline SVG placeholder; no remote image host.
            "img-src 'self' data:",

            "font-src 'self'",

            // No XHR anywhere but our own origin. Livewire posts to /livewire.
            "connect-src 'self'",

            // The YouTube facade, and only after a click. nocookie, so no
            // profiling cookie is set for a patient who watches a video.
            'frame-src https://www.youtube-nocookie.com',

            "media-src 'self'",

            // A booking form must not be able to post anywhere else.
            "form-action 'self'",

            "base-uri 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
        ]);
    }
}
