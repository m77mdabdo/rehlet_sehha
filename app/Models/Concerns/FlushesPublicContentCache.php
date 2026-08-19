<?php

declare(strict_types=1);

namespace App\Models\Concerns;

use App\Support\PublicContent;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Clears the cached public content whenever this model changes.
 *
 * Applied to the five models the homepage reads. Without it, editing a package
 * price or publishing an article would leave the old version on the site for
 * up to a day — the exact failure that makes people distrust caching and rip
 * it out.
 *
 * saved covers create and update; deleted covers removal. Both route to the
 * same flush, because the question "which key did this model affect?" is not
 * worth answering for five key deletes a month.
 *
 * `restored` is registered only when the model actually soft-deletes: that
 * event is declared by the SoftDeletes trait, not by Model, so registering it
 * unconditionally throws BadMethodCallException the first time any model
 * without SoftDeletes boots.
 */
trait FlushesPublicContentCache
{
    public static function bootFlushesPublicContentCache(): void
    {
        $forget = static function (): void {
            PublicContent::flush();
        };

        static::saved($forget);
        static::deleted($forget);

        if (in_array(SoftDeletes::class, class_uses_recursive(static::class), true)) {
            // registerModelEvent rather than static::restored(): that helper is
            // declared by the SoftDeletes trait, so calling it here is invisible
            // to static analysis even when the runtime guard above is correct.
            static::registerModelEvent('restored', $forget);
        }
    }
}
