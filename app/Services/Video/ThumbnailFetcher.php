<?php

declare(strict_types=1);

namespace App\Services\Video;

use App\Models\Video;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * Pulls a video's thumbnail off YouTube ONCE and stores it locally.
 *
 * WHY NOT HOTLINK img.youtube.com — and this is the question the whole video
 * feature turns on.
 *
 * The gallery uses a facade: no YouTube iframe and no YouTube script until a
 * patient actually clicks play. The reason is that an embedded player loads
 * Google's tracking on every homepage visit, for every visitor, whether or not
 * they watch — Google learning who visits a nutrition clinic.
 *
 * Hotlinking the thumbnail would undo precisely that. An <img> pointing at
 * Google's CDN is still a request to Google on every homepage load, carrying
 * the visitor's IP, User-Agent and Referer — the referer naming the clinic. It
 * would be a facade in appearance only: fewer bytes from Google, the same
 * disclosure to Google. Fetching once at the server and serving from our own
 * origin is what actually keeps the visitor's visit between her and the clinic.
 *
 * Two smaller reasons follow: the homepage stops depending on a third party's
 * uptime for its images, and a thumbnail cannot be swapped underneath us.
 *
 * The cost is a stored file per video and a command to run. That is the price
 * of the privacy claim being true rather than decorative.
 */
class ThumbnailFetcher
{
    public const DISK = 'public';

    public const DIRECTORY = 'video-thumbnails';

    /**
     * YouTube's thumbnail sizes, best first.
     *
     * maxresdefault does not exist for every video — YouTube only generates it
     * above a certain source resolution, and asking for a missing one returns
     * either a 404 or, worse, a 120×90 grey placeholder with a 200. So the list
     * is walked in order and the first genuinely large image wins.
     *
     * @var list<string>
     */
    private const VARIANTS = ['maxresdefault', 'sddefault', 'hqdefault'];

    /**
     * A YouTube placeholder is 120×90; anything at least this wide is real.
     */
    private const MINIMUM_WIDTH = 320;

    /**
     * @return bool whether a thumbnail was stored
     */
    public function fetch(Video $video): bool
    {
        foreach (self::VARIANTS as $variant) {
            $url = sprintf('https://i.ytimg.com/vi/%s/%s.jpg', $video->youtube_id, $variant);

            try {
                $response = Http::timeout(15)->get($url);
            } catch (Throwable) {
                // A network failure on one variant is not fatal; try the next.
                continue;
            }

            if (! $response->successful()) {
                continue;
            }

            $body = $response->body();

            if (! $this->isUsable($body)) {
                continue;
            }

            $path = self::DIRECTORY.'/'.$video->youtube_id.'.jpg';

            Storage::disk(self::DISK)->put($path, $body);

            /*
             * saveQuietly: storing a thumbnail is not a content change, and
             * letting it fire the model's save hooks would bust the public
             * content cache once per video for no reason.
             */
            $video->thumbnail_path = $path;
            $video->saveQuietly();

            return true;
        }

        return false;
    }

    /**
     * Is this a real thumbnail, or YouTube's grey "no image" placeholder?
     */
    private function isUsable(string $body): bool
    {
        if ($body === '') {
            return false;
        }

        $size = @getimagesizefromstring($body);

        return $size !== false && $size[0] >= self::MINIMUM_WIDTH;
    }
}
