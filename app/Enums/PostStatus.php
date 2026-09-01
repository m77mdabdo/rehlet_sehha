<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * WHERE AN ARTICLE IS, AS ONE WORD.
 *
 * DERIVED, NOT STORED. There is no status column and there must not be one:
 * `published_at` already carries the whole answer, and a stored status is a
 * second copy of it that can disagree. The disagreement is not hypothetical —
 * a status of "published" beside a null date is a row the site refuses to
 * serve while the admin list insists it is live, and somebody spends an
 * afternoon on it.
 *
 * SCHEDULED IS NOT A FLAG EITHER. scopePublished() requires the date to have
 * PASSED, so a future date already means "not yet". Naming that state is the
 * only thing this enum adds, and naming it matters: without it the list shows
 * a date beside an article that 404s, and the obvious conclusion is that
 * something is broken.
 */
enum PostStatus: string
{
    case Draft = 'draft';
    case Scheduled = 'scheduled';
    case Published = 'published';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'مسودة',
            self::Scheduled => 'مجدول',
            self::Published => 'منشور',
        };
    }

    /**
     * Colours carry the meaning for somebody scanning forty rows.
     *
     * Grey for a draft because it is neutral — most articles are drafts most
     * of the time and nothing is wrong. Amber for scheduled because it is the
     * one state that changes on its own, without anybody touching it.
     */
    public function colour(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Scheduled => 'warning',
            self::Published => 'success',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Draft => 'heroicon-o-pencil',
            self::Scheduled => 'heroicon-o-clock',
            self::Published => 'heroicon-o-globe-alt',
        };
    }
}
