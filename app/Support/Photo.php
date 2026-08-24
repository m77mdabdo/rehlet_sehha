<?php

declare(strict_types=1);

namespace App\Support;

use RuntimeException;

/**
 * A photograph from the processed library.
 *
 * Reads the generated manifest once per process. No filesystem work per
 * render: the dimensions cannot change without re-running the command that
 * wrote them, so measuring a file on every request would be a stat to learn
 * something already known.
 */
final class Photo
{
    /** @var array<string, array{topic: string, describes: string, variants: array<string, array{width: int, height: int, bytes: int}>}>|null */
    private static ?array $manifest = null;

    /**
     * @return array{topic: string, describes: string, variants: array<string, array{width: int, height: int, bytes: int}>}
     */
    public static function get(string $slug): array
    {
        $manifest = self::manifest();

        if (! isset($manifest[$slug])) {
            /*
             * Loud rather than a broken frame. A missing image is a typo in a
             * Blade file or an image that was never processed, and both are
             * fixed in seconds if the page says so — and silently ship a hole
             * in the layout if it does not.
             */
            throw new RuntimeException(
                "No processed photo «{$slug}». Check config/photos.php and run `php artisan clinic:process-photos`."
            );
        }

        return $manifest[$slug];
    }

    public static function has(string $slug): bool
    {
        return isset(self::manifest()[$slug]);
    }

    /**
     * The URL of one variant.
     */
    public static function url(string $slug, string $variant): string
    {
        return asset(config('photos.output_directory')."/{$slug}-{$variant}.webp");
    }

    /**
     * The srcset, widest last, described by real pixel widths.
     */
    public static function srcset(string $slug): string
    {
        $parts = [];

        foreach (self::get($slug)['variants'] as $variant => $size) {
            $parts[] = self::url($slug, $variant).' '.$size['width'].'w';
        }

        return implode(', ', $parts);
    }

    /**
     * The largest variant, used for the src fallback and for width/height.
     */
    public static function largest(string $slug): string
    {
        $variants = array_keys(self::get($slug)['variants']);

        return (string) end($variants);
    }

    /**
     * @return array<string, array{topic: string, describes: string, variants: array<string, array{width: int, height: int, bytes: int}>}>
     */
    private static function manifest(): array
    {
        return self::$manifest ??= require resource_path('photos-manifest.php');
    }
}
