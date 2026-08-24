<?php

declare(strict_types=1);

namespace App\Models;

use App\Services\Video\ThumbnailFetcher;
use Database\Factories\VideoFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Spatie\Translatable\HasTranslations;

/**
 * @property int $id
 * @property string $youtube_id
 * @property array<string, string>|string $title
 * @property array<string, string>|string|null $description
 * @property int|null $duration_seconds
 * @property string|null $category
 * @property bool $is_featured
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 *
 * @method static \Database\Factories\VideoFactory factory($count = null, $state = [])
 */
class Video extends Model
{
    /** @use HasFactory<VideoFactory> */
    use HasFactory;

    use HasTranslations;

    /** @var list<string> */
    protected $fillable = [
        'youtube_id',
        'title',
        'description',
        'duration_seconds',
        'category',
        'is_featured',
        'is_active',
        'sort_order',
    ];

    /** @var array<int, string> */
    public array $translatable = ['title', 'description'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'duration_seconds' => 'integer',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        /*
         * Fetch the thumbnail when a video is created or its id changes.
         *
         * Queued rather than done inline: the panel's save must not wait on a
         * request to YouTube, and must not fail because YouTube was slow. If
         * the fetch does not happen the gallery falls back to a placeholder,
         * and `clinic:fetch-video-thumbnails` picks it up later.
         */
        static::saved(function (self $video): void {
            if (! $video->wasChanged('youtube_id') && ! $video->wasRecentlyCreated) {
                return;
            }

            dispatch(function () use ($video): void {
                app(ThumbnailFetcher::class)->fetch($video->fresh() ?? $video);
            })->afterResponse();
        });
    }

    /**
     * The thumbnail to draw, or null to fall back to a placeholder.
     *
     * Never img.youtube.com — see ThumbnailFetcher for why hotlinking would
     * undo the entire point of the facade.
     */
    public function thumbnailUrl(): ?string
    {
        if ($this->thumbnail_path === null) {
            return null;
        }

        return Storage::disk(ThumbnailFetcher::DISK)->url($this->thumbnail_path);
    }

    /**
     * The privacy-preserving embed URL, used only after a click.
     *
     * youtube-nocookie.com rather than youtube.com: it defers the tracking
     * cookies until playback actually begins. rel=0 keeps the end screen from
     * recommending other channels' videos on a clinic's page.
     *
     * NO autoplay parameter, deliberately, and it costs the patient a second
     * click. Most facade implementations set autoplay=1 on the grounds that
     * clicking the thumbnail already expressed intent — which is a fair
     * argument, and it is not the one that wins here. Sound starting by itself
     * is a genuinely unpleasant surprise on a phone in a waiting room or an
     * open office, and somebody who tapped a card to read its title did not ask
     * to be heard doing it. On a medical site the safer default is that nothing
     * makes a noise until she presses play herself.
     */
    public function embedUrl(): string
    {
        return sprintf(
            'https://www.youtube-nocookie.com/embed/%s?rel=0&modestbranding=1',
            $this->youtube_id,
        );
    }

    /**
     * @param  Builder<self>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order');
    }
}
