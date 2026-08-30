<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Concerns\FlushesPublicContentCache;
use Database\Factories\TagFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Translatable\HasTranslations;

/**
 * A cross-cutting label. An article has any number; a tag has its own index.
 *
 * Deliberately thinner than Category: no description, no meta, no ordering.
 * A tag page is a list of articles sharing a subject, and inventing copy for
 * every one of them is how a tag index fills with empty pages.
 *
 * @property int $id
 * @property string $slug
 * @property array<string, string>|string $name
 *
 * @method static \Database\Factories\TagFactory factory($count = null, $state = [])
 */
class Tag extends Model
{
    use FlushesPublicContentCache;

    /** @use HasFactory<TagFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    protected $fillable = ['slug', 'name'];

    /** @var array<int, string> */
    public array $translatable = ['name'];

    /**
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class);
    }
}
