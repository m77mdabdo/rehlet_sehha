<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Tag;
use App\Models\User;

/**
 * Article taxonomy: site content, and the same rule as the articles.
 *
 * Admin and doctor. Not reception — a category page is a published page with
 * its own title and its own meta description, and editing one is publishing.
 */
class TagPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function view(User $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function update(User $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    /**
     * Deleting a category does NOT delete its articles — the foreign key is
     * nullOnDelete, so they become uncategorised. That is a tidying job; the
     * alternative would throw away clinical review.
     */
    public function delete(User $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function restore(User $user, Tag $tag): bool
    {
        return $user->hasAnyRole(['admin', 'doctor']);
    }

    public function forceDelete(User $user, Tag $tag): bool
    {
        return $user->hasRole('admin');
    }
}
