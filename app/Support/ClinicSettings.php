<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

/**
 * Clinic settings the staff can change, overlaid on top of config/clinic.php.
 *
 * The design goal is that NOTHING ELSE HAS TO KNOW. Every existing caller —
 * the availability engine, the Contact helper, the booking wizard, the mail
 * templates — reads config('clinic.…'), and they all keep doing exactly that.
 * apply() runs once per request during boot and writes the stored values over
 * the config defaults, so a setting changed in the panel takes effect
 * everywhere without a single call site being rewritten.
 *
 * The alternative was to introduce a settings read helper and change ~40 call
 * sites to use it, which would have meant every future call site being a
 * chance to read the default instead of the setting.
 *
 * Cached, because this runs on every request including the public site's. The
 * cache is busted on write; the TTL is only a backstop for a row edited
 * directly in the database.
 */
final class ClinicSettings
{
    private const CACHE_KEY = 'clinic-settings';

    private const TTL_SECONDS = 86400;

    /**
     * The config keys staff may change, and nothing else.
     *
     * An allow-list rather than "whatever is in the table", because this
     * writes into the application's configuration: a stray row keyed
     * 'app.debug' must not be able to turn debug mode on in production.
     *
     * @var array<int, string>
     */
    public const EDITABLE = [
        'clinic.contact.email',
        'clinic.contact.phone',
        'clinic.contact.phone_display',
        'clinic.contact.whatsapp',
        'clinic.booking.lead_time_hours',
        'clinic.booking.horizon_days',
        'clinic.booking.buffer_minutes',
        'clinic.booking.reschedule_min_hours',
    ];

    /**
     * Overlay the stored settings onto config. Called from AppServiceProvider.
     */
    public static function apply(): void
    {
        foreach (self::all() as $key => $value) {
            if (in_array($key, self::EDITABLE, true)) {
                config()->set($key, $value);
            }
        }
    }

    /**
     * @return array<string, mixed>
     */
    public static function all(): array
    {
        return Cache::remember(self::CACHE_KEY, self::TTL_SECONDS, function (): array {
            /*
             * Guarded because this runs during boot, which includes the boot
             * that happens while `migrate` is creating the settings table in
             * the first place. A missing table must degrade to "no overrides",
             * not to a fatal error that makes the application unbootable.
             */
            try {
                if (! Schema::hasTable('settings')) {
                    return [];
                }

                return Setting::query()
                    ->pluck('value', 'key')
                    ->map(fn (mixed $value): mixed => is_array($value) ? ($value['value'] ?? null) : $value)
                    ->all();
            } catch (Throwable) {
                return [];
            }
        });
    }

    /**
     * Write settings and invalidate everything that depends on them.
     *
     * @param  array<string, mixed>  $values
     */
    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            if (! in_array($key, self::EDITABLE, true)) {
                continue;
            }

            Setting::query()->updateOrCreate(
                ['key' => $key],
                // Wrapped because the column is cast to array; storing a bare
                // scalar would come back as a one-element list.
                ['value' => ['value' => $value]],
            );
        }

        self::flush();
    }

    /**
     * Busts BOTH caches, and that pairing is the point.
     *
     * These settings feed two different things: the config values the booking
     * engine reads, and the rendered public content that has the clinic's
     * phone number and opening hours baked into it. Clearing one and not the
     * other leaves the site showing an old telephone number for a day while
     * the booking rules have already changed — the confusing half-state that
     * makes people distrust the panel.
     */
    public static function flush(): void
    {
        Cache::forget(self::CACHE_KEY);

        PublicContent::flush();
    }
}
