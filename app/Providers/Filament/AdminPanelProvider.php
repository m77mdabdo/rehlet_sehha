<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Filament\Avatars\InitialsAvatarProvider;
use App\Http\Middleware\ExpireAdminSession;
use App\Http\Middleware\NoIndexAdminPanel;
use App\Http\Middleware\SetAdminLocale;
use Filament\Auth\MultiFactor\App\AppAuthentication;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

/**
 * The clinic's own screen.
 *
 * ARABIC ONLY, AND DELIBERATELY SO. The public site is bilingual because its
 * visitors are: a patient may read Arabic or English, and serving her the
 * wrong one costs a booking. The clinic is not that audience. It is one team,
 * in one room, working in one language — the doctor, and whoever is on
 * reception. Nobody has asked for an English admin panel and nobody would use
 * one.
 *
 * Translating it anyway would mean every future label, validation message,
 * empty state and confirmation written twice, reviewed twice, and kept in step
 * forever, so that a screen with two regular users can be read in a language
 * neither of them works in. That is cost with no value attached, and the cost
 * is not one-off — it is paid again on every change.
 *
 * There is therefore no locale prefix on /admin and no language switcher in it.
 * If the clinic ever hires a non-Arabic-speaking administrator, this decision
 * gets revisited with a real reason behind it.
 */
class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            /*
             * NOT locale-prefixed. The public routes live under /{locale}/ so
             * that Arabic and English are separate, indexable documents; the
             * panel is neither indexable nor bilingual, so a prefix here would
             * be a segment that never varies and a switcher that never
             * switches.
             */
            ->path('admin')
            ->login()
            /*
             * The sign-in screen's appearance, and NOTHING ELSE.
             *
             * A render hook rather than an overridden view, deliberately.
             * Filament's login view renders the multi-factor challenge and the
             * password-reset link from the page object; a copy of it in this
             * repository stops matching the package on the next upgrade, and
             * the way that surfaces is somebody with 2FA enabled being unable
             * to get in. This adds markup and CSS beside the form and touches
             * no part of it — the validation, the throttle, the session guard
             * and the 2FA challenge are all still Filament's.
             */
            ->renderHook(
                PanelsRenderHook::SIMPLE_LAYOUT_START,
                fn (): string => view('filament.auth.login-brand')->render(),
            )
            /*
             * The panel's Content-Security-Policy, as a meta element.
             *
             * The public layout carries the same tag for the same reason: the
             * host replaces the CSP *header* on every response with a bare
             * `upgrade-insecure-requests`, so the header this application sends
             * never reaches a browser. See the note in SecurityHeaders.
             *
             * HEAD_START, not HEAD_END — a meta policy governs only what the
             * parser meets after it, and Filament's own styles and scripts are
             * emitted inside this head. Placed last they would be outside the
             * policy; placed first the panel is covered.
             *
             * The panel's policy is the weaker of the two — Filament emits
             * inline scripts that cannot be nonced without patching it — but
             * default-src, form-action, base-uri and object-src still hold,
             * and those are what stop a compromised admin session posting
             * anywhere but here.
             */
            ->renderHook(
                PanelsRenderHook::HEAD_START,
                fn (): string => '<meta http-equiv="Content-Security-Policy" content="'
                    .e(request()->attributes->get('csp-policy-meta')).'">',
            )
            ->passwordReset()
            ->profile(isSimple: false)
            ->brandName('رحلة صحة')
            ->favicon(asset('brand/favicon-32.png'))
            ->colors([
                // The site's ink and accent, so the panel and the public site
                // are recognisably the same product.
                'primary' => Color::hex('#1A6DA6'),
                'gray' => Color::Slate,
            ])
            /*
             * NO REMOTE FONT. ->font('Tajawal') alone makes Filament link
             * fonts.bunny.net, which was discovered by a Content-Security-
             * Policy blocking it — the panel had been reaching a third-party
             * CDN on every admin page load, disclosing staff activity to a
             * host nobody chose.
             *
             * The public site already self-hosts Tajawal (see app.css). The
             * panel now uses the same family through the local provider, so
             * the whole application loads from one origin.
             */
            ->font('Tajawal', provider: LocalFontProvider::class)
            /*
             * AND NO REMOTE AVATAR EITHER — the same defect, found the same
             * way, three weeks later.
             *
             * Filament's default provider builds an <img> pointing at
             * ui-avatars.com with the user's NAME in the query string, so
             * every admin page load sent «د. رنا سالم» to a company nobody
             * here has an agreement with, from the panel where patient
             * records are opened. The CSP refused it and logged the refusal,
             * which is the only reason it surfaced.
             *
             * A policy that happens to block a request is not the same as not
             * making it: loosen img-src for an unrelated reason and this comes
             * back on its own. See App\Filament\Avatars\InitialsAvatarProvider.
             */
            ->defaultAvatarProvider(InitialsAvatarProvider::class)
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            /*
             * Filament's stock AccountWidget and FilamentInfoWidget are gone.
             * The first restates the name of the person already shown in the
             * corner; the second advertises the framework to a doctor. The
             * dashboard is built from what the clinic actually needs to see
             * this morning — see App\Filament\Widgets.
             */
            ->widgets([])
            /*
             * TWO FACTOR: required for administrators, offered to everyone.
             *
             * An admin account can create users, change booking rules and read
             * every medical record in the system; a stolen password should not
             * be enough for that. Doctor and receptionist accounts are offered
             * the same protection but not forced into it, because forcing an
             * authenticator app onto whoever is covering reception today is how
             * a shared login gets written on a sticky note.
             *
             * The closure is evaluated per request against the signed-in user,
             * so promoting someone to admin starts requiring it of them on
             * their next visit rather than at their next deploy.
             */
            ->multiFactorAuthentication(
                AppAuthentication::make()->recoverable(),
                isRequired: fn (): bool => auth()->user()?->hasRole('admin') ?? false,
            )
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
                // Arabic and RTL for every panel request, whatever the browser
                // asks for and whatever the public site last set.
                SetAdminLocale::class,
                // Never indexed. See the middleware for why a robots.txt rule
                // would not be enough.
                NoIndexAdminPanel::class,
            ])
            ->authMiddleware([
                Authenticate::class,
                // Idle timeout, shorter than the public site's. Runs only for
                // authenticated requests, so the login page itself is exempt.
                ExpireAdminSession::class,
            ]);
    }
}
