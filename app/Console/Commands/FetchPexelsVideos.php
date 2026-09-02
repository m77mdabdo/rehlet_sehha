<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * The video sibling of clinic:fetch-pexels. Same rule, higher stakes.
 *
 * A photograph is one frame and a person can look at it. A clip is several
 * hundred, and the ONE frame a reviewer is shown by a stock library — the
 * thumbnail — is chosen to sell the clip rather than to describe it.
 *
 * THIS IS NOT HYPOTHETICAL. 2.mp4 was taken on the strength of its search
 * result and its thumbnail. Opened frame by frame it held a glucose meter, an
 * insulin diagram, a medication record and a printed list of named foods to
 * eat. None of that appears in a grid of results, and all of it would have
 * autoplayed on the front page of a clinic.
 *
 * So this command downloads and MEASURES. It does not accept anything. What it
 * reports is what a reviewer needs in order to reject quickly:
 *
 *   - the scene boundaries, so a clip that claims to be twenty seconds of
 *     narrative can be shown to be one long shot
 *   - the duration of the shortest scene, because copy synced to a beat needs
 *     that beat to hold
 *   - mean luminance per scene, because text has to sit on top of this
 *
 * Frames still have to be looked at by a person. The command's job is to make
 * that cheap enough that all of them get looked at.
 *
 * SEARCH TERMS ARE POLICED, exactly as in the photo command and for the same
 * reason: asking a stock library for "diet" returns bathroom scales, and the
 * cheapest way to stop rejecting scales is to stop asking for them. The list
 * lives in FetchPexelsPhotos and is shared rather than copied, so a term added
 * there applies here too.
 */
class FetchPexelsVideos extends Command
{
    protected $signature = 'clinic:fetch-pexels-videos
                            {query : A kitchen, a food, a preparation. Never a body or an outcome}
                            {--count=8 : How many candidates to consider}
                            {--min-duration=12 : Shortest clip worth downloading, seconds}
                            {--max-duration=45 : Longest clip worth downloading, seconds}';

    protected $description = 'Search Pexels Videos, download candidates and measure their scenes';

    /** Where candidates land. Nothing here is served or committed. */
    private const STAGING = 'video/inbox';

    /**
     * A cut is a frame that differs from the one before it by more than this.
     *
     * ffmpeg's scene score is 0..1 over the whole frame. 0.3 is high enough to
     * ignore a pan, a rack focus or a hand crossing the frame, and low enough
     * to catch a genuine cut between two kitchen shots that share a palette —
     * which is the case that matters here, because stock food clips are grade
     * to look like one another on purpose.
     */
    private const SCENE_THRESHOLD = 0.3;

    public function handle(): int
    {
        $query = trim((string) $this->argument('query'));

        if ($problem = FetchPexelsPhotos::rejection($query)) {
            $this->error("Refusing to search for «{$query}».");
            $this->line("  It contains «{$problem['term']}».");
            $this->line('  '.$problem['why']);

            return self::FAILURE;
        }

        $key = (string) config('services.pexels.key');

        if ($key === '') {
            $this->error('PEXELS_API_KEY is not set (config: services.pexels.key).');

            return self::FAILURE;
        }

        $response = Http::withHeaders(['Authorization' => $key])
            ->timeout(30)
            ->get('https://api.pexels.com/videos/search', [
                'query' => $query,
                'per_page' => (int) $this->option('count'),
                'orientation' => 'landscape',
                'size' => 'medium',
            ]);

        if (! $response->successful()) {
            $this->error('Pexels returned '.$response->status().'.');

            return self::FAILURE;
        }

        /** @var list<array<string, mixed>> $videos */
        $videos = $response->json('videos') ?? [];

        if ($videos === []) {
            $this->warn('No results.');

            return self::SUCCESS;
        }

        $staging = public_path(self::STAGING);
        File::ensureDirectoryExists($staging);

        $min = (float) $this->option('min-duration');
        $max = (float) $this->option('max-duration');

        $notes = [];

        foreach ($videos as $video) {
            $id = (int) $video['id'];
            $duration = (float) ($video['duration'] ?? 0);

            if ($duration < $min || $duration > $max) {
                $this->line(sprintf('  skip %-10d %.0fs — outside %.0f-%.0fs', $id, $duration, $min, $max));

                continue;
            }

            $file = $this->bestFile((array) ($video['video_files'] ?? []));

            if ($file === null) {
                $this->line(sprintf('  skip %-10d no h264 rendition at an acceptable size', $id));

                continue;
            }

            $slug = Str::slug($query).'-'.$id;
            $target = $staging."/{$slug}.mp4";

            if (! File::exists($target)) {
                $bytes = Http::timeout(180)->get((string) $file['link']);

                if (! $bytes->successful()) {
                    $this->warn("  could not download {$id}");

                    continue;
                }

                File::put($target, $bytes->body());
            }

            $notes[$slug] = [
                'pexels_id' => $id,
                'photographer' => (string) ($video['user']['name'] ?? 'unknown'),
                'photographer_url' => (string) ($video['user']['url'] ?? ''),
                'source' => (string) ($video['url'] ?? ''),
                'downloaded_at' => now()->toDateString(),
                'api_duration' => $duration,
                'dimensions' => ($file['width'] ?? '?').'x'.($file['height'] ?? '?'),
            ];

            $notes[$slug] += $this->measure($target);

            $this->line(sprintf(
                '  %-40s %4.0fs  %sx%s  %d scene(s), shortest %.1fs  %s',
                $slug.'.mp4',
                $duration,
                $file['width'] ?? '?',
                $file['height'] ?? '?',
                count($notes[$slug]['scenes']),
                $notes[$slug]['shortest_scene'],
                $notes[$slug]['photographer'],
            ));
        }

        $this->writeReviewSheet($staging, $query, $notes);

        $this->newLine();
        $this->info(sprintf('%d candidate(s) staged in public/%s.', count($notes), self::STAGING));
        $this->line('  NOTHING IS ACCEPTED. Next: extract frames across the WHOLE clip and');
        $this->line('  look at every one. Judge frame 0 on its own first — it is the poster,');
        $this->line('  and it is the only image a reduced-motion visitor ever sees.');

        return self::SUCCESS;
    }

    /**
     * Scene boundaries and per-scene luminance, from ffmpeg.
     *
     * WHY THE COMMAND MEASURES THIS AND DOES NOT LEAVE IT TO THE REVIEWER.
     * The thing that disqualifies most candidates is not what is in them — it
     * is that a clip sold as twenty seconds of kitchen narrative turns out to
     * be one continuous shot, or three cuts crammed into the first four
     * seconds and then a long hold. Neither is visible from a thumbnail, from
     * a duration, or from watching it once. Both are visible in this table.
     *
     * `shortest_scene` is the number that decides it. Copy synced to a beat
     * needs that beat to hold long enough to be read, so a clip whose
     * shortest scene is under that floor cannot carry the copy however good
     * it looks.
     *
     * Luminance is here because text sits on top of this. A scene at 200 and a
     * scene at 40 in the same clip cannot share one text colour, and finding
     * that out after the copy is written is finding it out too late.
     *
     * @return array{scenes: list<array{start: float, end: float, seconds: float, luma: float}>, shortest_scene: float, luma_range: string}
     */
    private function measure(string $path): array
    {
        $duration = (float) trim((string) shell_exec(sprintf(
            'ffprobe -v error -show_entries format=duration -of csv=p=0 %s',
            escapeshellarg($path),
        )));

        // Cut points, as timestamps. The first scene starts at 0 and the last
        // ends at the duration, so only the interior boundaries come from here.
        $raw = (string) shell_exec(sprintf(
            'ffmpeg -nostats -i %s -filter:v "select=\'gt(scene,%s)\',showinfo" -f null - 2>&1',
            escapeshellarg($path),
            self::SCENE_THRESHOLD,
        ));

        preg_match_all('/pts_time:([0-9.]+)/', $raw, $matches);

        $cuts = array_map('floatval', $matches[1]);
        $bounds = array_values(array_unique(array_merge([0.0], $cuts, [$duration])));
        sort($bounds);

        $scenes = [];

        for ($i = 0; $i < count($bounds) - 1; $i++) {
            $start = $bounds[$i];
            $end = $bounds[$i + 1];

            if ($end - $start < 0.4) {
                continue; // A flash, not a scene.
            }

            $scenes[] = [
                'start' => round($start, 2),
                'end' => round($end, 2),
                'seconds' => round($end - $start, 2),
                'luma' => $this->luminance($path, $start + (($end - $start) / 2)),
            ];
        }

        $lumas = array_column($scenes, 'luma');

        return [
            'seconds' => round($duration, 2),
            'scenes' => $scenes,
            'shortest_scene' => $scenes === [] ? 0.0 : min(array_column($scenes, 'seconds')),
            'luma_range' => $lumas === [] ? '-' : min($lumas).'-'.max($lumas),
        ];
    }

    /**
     * Mean luminance of one frame, 0..255.
     *
     * `signalstats` ON ITS OWN PRINTS NOTHING. It attaches its measurements to
     * the frame as metadata and stays silent, so a version of this that piped
     * it to /dev/null and grepped stderr reported 0.0 for every clip ever
     * measured — and 0.0 is a plausible-looking number for a dark kitchen,
     * which is why it survived a first pass. `metadata=print` is what turns
     * the measurement into output. The key is `lavfi.signalstats.YAVG`, not
     * the bare `YAVG:` that the old pattern looked for.
     */
    private function luminance(string $path, float $at): float
    {
        $out = (string) shell_exec(sprintf(
            'ffmpeg -nostats -ss %.2f -i %s -frames:v 1 -vf %s -f null - 2>&1',
            $at,
            escapeshellarg($path),
            escapeshellarg('signalstats,metadata=print:file=-'),
        ));

        preg_match('/lavfi\.signalstats\.YAVG=([0-9.]+)/', $out, $m);

        return isset($m[1]) ? round((float) $m[1], 1) : 0.0;
    }

    /**
     * The largest h264 rendition at or below 1080p.
     *
     * Above 1080p is wasted: the hero is re-encoded to 720p and a 4K source
     * costs minutes of download for detail that is thrown away. Below 720p is
     * refused outright rather than accepted and upscaled.
     *
     * @param  list<array<string, mixed>>  $files
     * @return array<string, mixed>|null
     */
    private function bestFile(array $files): ?array
    {
        $usable = array_filter($files, function (array $f): bool {
            $height = (int) ($f['height'] ?? 0);

            return ($f['file_type'] ?? '') === 'video/mp4' && $height >= 720 && $height <= 1080;
        });

        if ($usable === []) {
            return null;
        }

        usort($usable, fn (array $a, array $b): int => (int) $b['height'] <=> (int) $a['height']);

        return $usable[0];
    }

    /**
     * @param  array<string, array<string, mixed>>  $notes
     */
    private function writeReviewSheet(string $staging, string $query, array $notes): void
    {
        $path = $staging.'/candidates.json';

        $existing = File::exists($path)
            ? (array) json_decode((string) File::get($path), true)
            : [];

        File::put($path, (string) json_encode(
            array_merge($existing, [$query => $notes]),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
        ));
    }
}
