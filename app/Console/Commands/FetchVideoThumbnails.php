<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Video;
use App\Services\Video\ThumbnailFetcher;
use Illuminate\Console\Command;

/**
 * Backfills thumbnails for videos that have none.
 *
 * New videos fetch on save (see Video::booted). This exists for rows that were
 * already in the table when the column was added, and for retrying the ones
 * whose fetch failed.
 */
class FetchVideoThumbnails extends Command
{
    protected $signature = 'clinic:fetch-video-thumbnails {--all : Re-fetch even videos that already have one}';

    protected $description = 'Download YouTube thumbnails and store them locally.';

    public function handle(ThumbnailFetcher $fetcher): int
    {
        $videos = Video::query()
            ->when(! $this->option('all'), fn ($query) => $query->whereNull('thumbnail_path'))
            ->get();

        if ($videos->isEmpty()) {
            $this->info('Every video already has a stored thumbnail.');

            return self::SUCCESS;
        }

        $stored = 0;
        $failed = [];

        foreach ($videos as $video) {
            if ($fetcher->fetch($video)) {
                $stored++;
                $this->line("  stored  {$video->youtube_id}");

                continue;
            }

            $failed[] = $video->youtube_id;
            $this->line("  FAILED  {$video->youtube_id}");
        }

        $this->newLine();
        $this->info("Stored {$stored} of {$videos->count()}.");

        if ($failed !== []) {
            /*
             * Named rather than counted. A failure here is almost always a
             * youtube_id that is wrong or a video that has been taken down,
             * and the id is what somebody needs in order to fix it.
             */
            $this->warn('No thumbnail for: '.implode(', ', $failed));
            $this->warn('Check the id is right and the video is still public. The gallery falls back to a placeholder.');
        }

        return self::SUCCESS;
    }
}
