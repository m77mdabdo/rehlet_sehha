<?php

declare(strict_types=1);

namespace App\Console\Commands;

use GdImage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use RuntimeException;

/**
 * Turn the photography originals into the small WebP set the site serves.
 *
 * WHY A COMMAND RATHER THAN A ONE-OFF. Three reasons, in order of how much
 * they matter.
 *
 * The crops are RULES, not taste. Three of the four exist to keep an
 * identifiable face or another clinic's textbooks off this site, and a crop
 * done by hand in an image editor is a decision nobody can see, re-check or
 * reproduce. Here the rectangle sits in config/photos.php next to the sentence
 * explaining it, and re-running produces the identical file.
 *
 * The originals do not ship. public/photos is 48 MB of licensed stock, is
 * gitignored, and is not served. This is the only path from there to what the
 * browser gets, so there is no way to accidentally reference a 3 MB JPEG.
 *
 * And the quality is searched, not guessed. A busy vegetable flat-lay and a
 * plain studio shot do not compress alike — at one fixed quality the first was
 * 237 KB and the second 32 KB. Each output is compressed down until it fits
 * the width's byte budget, so the page-weight ceiling holds by construction
 * rather than by somebody remembering to check.
 *
 * NO NEW DEPENDENCY AND NO EXTERNAL BINARY. GD with WebP support is already
 * present, which matters because this has to be runnable on the same shared
 * hosting the rest of the site deploys to.
 */
class ProcessPhotos extends Command
{
    protected $signature = 'clinic:process-photos
                            {--force : Rebuild every output even if it is up to date}
                            {--check : Verify the manifest without writing anything}';

    protected $description = 'Resize, crop and convert the photography originals into the served WebP set';

    /**
     * Decoding a 4958x7375 JPEG costs GD roughly 146 MB as a truecolour
     * resource, which is more than the default limit. This is a build-time
     * command run by a person at a terminal, never inside a request, so
     * raising it here is bounded and safe.
     */
    private const MEMORY_LIMIT = '768M';

    public function handle(): int
    {
        if (! function_exists('imagewebp')) {
            $this->error('GD has no WebP support in this PHP build, so nothing can be encoded.');

            return self::FAILURE;
        }

        ini_set('memory_limit', self::MEMORY_LIMIT);

        $sourceDirectory = public_path((string) config('photos.source_directory'));
        $outputDirectory = public_path((string) config('photos.output_directory'));

        /** @var array<string, array<string, mixed>> $library */
        $library = config('photos.library');

        /** @var array<string, string> $rejected */
        $rejected = config('photos.rejected');

        if ($this->manifestHasProblems($library, $rejected, $sourceDirectory)) {
            return self::FAILURE;
        }

        if ($this->option('check')) {
            $this->info(sprintf('Manifest is consistent: %d images, %d rejected.', count($library), count($rejected)));

            return self::SUCCESS;
        }

        File::ensureDirectoryExists($outputDirectory);

        $rows = [];
        $total = 0;
        $failures = 0;

        foreach ($library as $slug => $entry) {
            $source = $sourceDirectory.'/'.$entry['source'];

            $written = $this->process($slug, $source, $entry, $outputDirectory, $failures);

            $sum = 0;

            foreach ($written as $variant) {
                $sum += $variant['bytes'];
            }

            $total += $sum;

            $rows[] = array_merge(
                [$slug, $entry['topic'], $entry['crop'] === null ? '—' : 'cropped'],
                array_map(
                    fn (array $v): string => $v['bytes'] === 0
                        ? 'FAILED'
                        : sprintf('%dx%d %.0fK', $v['width'], $v['height'], $v['bytes'] / 1024),
                    $written,
                ),
                [sprintf('%.0fK', $sum / 1024)],
            );
        }

        $this->newLine();
        $this->table(
            array_merge(['image', 'topic', 'crop'], array_keys((array) config('photos.variants')), ['all']),
            $rows,
        );

        $this->info(sprintf(
            '%d images, %d files, %.1f MB served total (originals were %.1f MB).',
            count($library),
            count($library) * count((array) config('photos.variants')),
            $total / 1048576,
            $this->sourceWeight($library, $sourceDirectory) / 1048576,
        ));

        if ($failures > 0) {
            $this->warn($failures.' output(s) could not reach their byte budget at the quality floor.');
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, array<string, mixed>>  $library
     * @param  array<string, string>  $rejected
     */
    private function manifestHasProblems(array $library, array $rejected, string $sourceDirectory): bool
    {
        $problems = [];

        foreach ($library as $slug => $entry) {
            /*
             * THE RULE, enforced rather than remembered. See the header of
             * config/photos.php: a recognisable person beside condition
             * content asserts that person has the condition, and no stock
             * licence here evidences a release for that use.
             */
            if (($entry['faces'] ?? false) === true) {
                $problems[] = "{$slug}: marked as containing an identifiable face. Crop it out or drop the image.";
            }

            if (isset($rejected[$entry['source']])) {
                $problems[] = "{$slug}: uses «{$entry['source']}», which is on the rejected list — {$rejected[$entry['source']]}";
            }

            if (! File::exists($sourceDirectory.'/'.$entry['source'])) {
                $problems[] = "{$slug}: source file «{$entry['source']}» is missing. See docs/media/photography.md.";
            }
        }

        foreach ($problems as $problem) {
            $this->error('  '.$problem);
        }

        return $problems !== [];
    }

    /**
     * @param  array<string, mixed>  $entry
     * @return array<string, array{width: int, height: int, bytes: int}>
     */
    private function process(string $slug, string $source, array $entry, string $outputDirectory, int &$failures): array
    {
        /** @var array<string, array{max_pixels: int, max_bytes: int}> $variants */
        $variants = config('photos.variants');

        $written = [];
        $image = null;

        foreach ($variants as $name => $budget) {
            $target = $outputDirectory."/{$slug}-{$name}.webp";

            if (! $this->option('force') && File::exists($target) && File::lastModified($target) >= File::lastModified($source)) {
                [$w, $h] = (array) getimagesize($target);
                $written[$name] = ['width' => (int) $w, 'height' => (int) $h, 'bytes' => File::size($target)];

                continue;
            }

            // Decode once, and only if something actually needs rebuilding.
            $image ??= $this->load($source, $entry['crop'] ?? null);

            $result = $this->encode($image, $budget['max_pixels'], $budget['max_bytes'], $target);

            if ($result === null) {
                $failures++;
                $this->warn("  {$slug} [{$name}] could not fit ".round($budget['max_bytes'] / 1024).'K at the quality floor.');
                $written[$name] = ['width' => 0, 'height' => 0, 'bytes' => 0];

                continue;
            }

            $written[$name] = $result;
        }

        return $written;
    }

    /**
     * @param  list<int>|null  $crop  [x, y, width, height] in source pixels
     */
    private function load(string $source, ?array $crop): GdImage
    {
        $image = @imagecreatefromjpeg($source);

        if ($image === false) {
            throw new RuntimeException("Could not decode {$source}.");
        }

        if ($crop === null) {
            return $image;
        }

        [$x, $y, $width, $height] = $crop;

        $cropped = imagecrop($image, ['x' => $x, 'y' => $y, 'width' => $width, 'height' => $height]);

        if ($cropped === false) {
            throw new RuntimeException("Crop [{$x}, {$y}, {$width}, {$height}] is outside {$source}.");
        }

        return $cropped;
    }

    /**
     * Scale to the variant's pixel budget, then encode at the best quality
     * that fits its byte budget.
     *
     * Downwards from the start quality rather than a fixed number, because the
     * same setting produces a 32 KB studio shot and a 237 KB vegetable
     * flat-lay. The budget is the thing that must hold; the quality is
     * whatever buys it.
     *
     * @return array{width: int, height: int, bytes: int}|null
     */
    private function encode(GdImage $image, int $maxPixels, int $maxBytes, string $target): ?array
    {
        $sourceWidth = imagesx($image);
        $sourceHeight = imagesy($image);
        $sourcePixels = $sourceWidth * $sourceHeight;

        // Never upscale: an image smaller than the budget is served at its own
        // size rather than blown up into softness.
        $scale = $sourcePixels <= $maxPixels ? 1.0 : sqrt($maxPixels / $sourcePixels);

        $width = max(1, (int) round($sourceWidth * $scale));
        $height = max(1, (int) round($sourceHeight * $scale));

        $resized = imagecreatetruecolor($width, $height);

        imagecopyresampled($resized, $image, 0, 0, 0, 0, $width, $height, $sourceWidth, $sourceHeight);

        $quality = (int) config('photos.quality.start');
        $floor = (int) config('photos.quality.floor');
        $step = (int) config('photos.quality.step');

        while ($quality >= $floor) {
            ob_start();
            imagewebp($resized, null, $quality);
            $encoded = (string) ob_get_clean();

            if (strlen($encoded) <= $maxBytes) {
                File::put($target, $encoded);

                return ['width' => $width, 'height' => $height, 'bytes' => strlen($encoded)];
            }

            $quality -= $step;
        }

        return null;
    }

    /**
     * @param  array<string, array<string, mixed>>  $library
     */
    private function sourceWeight(array $library, string $sourceDirectory): int
    {
        $total = 0;

        foreach ($library as $entry) {
            $path = $sourceDirectory.'/'.$entry['source'];

            if (File::exists($path)) {
                $total += File::size($path);
            }
        }

        return $total;
    }
}
