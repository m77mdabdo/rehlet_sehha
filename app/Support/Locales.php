<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\URL;

/**
 * Everything the application knows about its two languages.
 *
 * Locale lives in the URL (/ar/..., /en/...) rather than in the session. That
 * is a deliberate SEO decision: a session-based switcher gives one URL two
 * different contents, so a search engine can only ever index one of them, and
 * a visitor who shares a link has no way to say which language they meant.
 * With the locale in the path, the Arabic and English pages are separate
 * documents that rank separately and share correctly.
 */
final class Locales
{
    public const DEFAULT = 'ar';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        /** @var list<string> $locales */
        $locales = config('app.supported_locales', ['ar']);

        return $locales;
    }

    public static function isSupported(?string $locale): bool
    {
        return $locale !== null && in_array($locale, self::all(), true);
    }

    public static function current(): string
    {
        return App::getLocale();
    }

    /**
     * The document direction for a locale. Arabic reads right to left.
     */
    public static function direction(?string $locale = null): string
    {
        return self::isRtl($locale) ? 'rtl' : 'ltr';
    }

    public static function isRtl(?string $locale = null): bool
    {
        return ($locale ?? self::current()) === 'ar';
    }

    /**
     * The language's own name, always written in that language — a switcher
     * that says "Arabic" to someone who only reads Arabic is useless.
     */
    public static function nativeName(string $locale): string
    {
        return match ($locale) {
            'ar' => 'العربية',
            'en' => 'English',
            default => $locale,
        };
    }

    /**
     * Short label for the compact switcher.
     */
    public static function shortLabel(string $locale): string
    {
        return match ($locale) {
            'ar' => 'ع',
            'en' => 'EN',
            default => strtoupper($locale),
        };
    }

    /**
     * The URL of the CURRENT page in another locale.
     *
     * Not the homepage. Sending someone back to the top of the site because
     * they wanted to read the page they were already on in another language is
     * the single most common bug in a bilingual site, and the most annoying.
     *
     * Resolved from the matched route where possible, so route parameters (a
     * post slug, a service) survive the switch. The path fallback exists for
     * anything unrouted — a 404 page still gets a working switcher.
     */
    public static function alternateUrl(string $locale, ?string $fallbackPath = null): string
    {
        $request = request();
        $route = $request->route();

        if ($route !== null && $route->getName() !== null) {
            $parameters = array_merge($route->parameters(), ['locale' => $locale]);

            $url = route($route->getName(), $parameters);

            $query = $request->getQueryString();

            return $query === null ? $url : $url.'?'.$query;
        }

        return self::swapLocaleInPath($fallbackPath ?? $request->getRequestUri(), $locale);
    }

    /**
     * Replace the leading locale segment of a path, adding one if absent.
     */
    public static function swapLocaleInPath(string $requestUri, string $locale): string
    {
        $parts = explode('?', $requestUri, 2);
        $path = trim($parts[0], '/');

        $segments = $path === '' ? [] : explode('/', $path);

        if ($segments !== [] && self::isSupported($segments[0])) {
            $segments[0] = $locale;
        } else {
            array_unshift($segments, $locale);
        }

        $swapped = '/'.implode('/', $segments);

        return isset($parts[1]) ? $swapped.'?'.$parts[1] : $swapped;
    }

    /**
     * Make {locale} implicit in every route() call for the rest of the request.
     */
    public static function applyToUrlGenerator(string $locale): void
    {
        URL::defaults(['locale' => $locale]);
    }
}
