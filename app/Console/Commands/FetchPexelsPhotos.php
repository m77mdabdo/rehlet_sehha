<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Search Pexels, download candidates, and stage them for a human to look at.
 *
 * IT DOES NOT ADD ANYTHING TO THE LIBRARY. That is the whole design. Images
 * land in a staging directory with their attribution recorded, and a person
 * opens every one before it becomes a config entry that clinic:process-photos
 * will build and the site will serve.
 *
 * The reason is 2.mp4. A stock clip was taken on the strength of its search
 * result and its thumbnail; opened frame by frame it turned out to hold a
 * glucose meter, a medication record, a printed list of "the 21 best snacks if
 * you have diabetes" and a Portuguese leaflet. None of that is visible in a
 * grid of results, and all of it would have shipped on a clinic's front page.
 *
 * So: this command fetches and describes. A human accepts.
 *
 * SEARCH TERMS ARE POLICED HERE, not left to whoever types the command. The
 * standing rule is to search for DEVICES, KITCHENS AND FOOD — "glucometer",
 * "blood pressure cuff", "vegetable market" — and never for the body or the
 * outcome. "Weight loss" returns tape measures and bathroom scales because
 * that is what the phrase means to a stock library, and every one of those is
 * a rejection waiting to happen.
 */
class FetchPexelsPhotos extends Command
{
    protected $signature = 'clinic:fetch-pexels
                            {query : What to search for — a device, a kitchen, a food. Never a body or an outcome}
                            {--count=8 : How many candidates to download}
                            {--orientation=landscape : landscape, portrait or square}';

    protected $description = 'Search Pexels and stage candidate photographs for review';

    /**
     * Search terms this clinic does not use.
     *
     * Not a content filter on the results — a filter on the QUESTION. Asking a
     * stock library for "weight loss" is asking it for tape measures, bathroom
     * scales and waistbands, and the rejection list in config/photos.php is
     * almost entirely made of exactly that. The fastest way to stop rejecting
     * those images is to stop requesting them.
     *
     * @var list<string>
     */
    public const FORBIDDEN_TERMS = [
        'weight loss', 'weightloss', 'slimming', 'diet', 'dieting', 'fat loss',
        'before and after', 'transformation', 'obesity', 'overweight', 'skinny',
        'scale', 'weighing', 'tape measure', 'measuring tape', 'waist', 'belly',
        'gym', 'workout', 'fitness model', 'bikini', 'six pack', 'calorie',
    ];

    /**
     * Terms refused for a different reason: not what they return, but what
     * they would CLAIM.
     *
     * Rana is a licensed nutritionist and not a physician. Imagery that reads
     * as a doctor's surgery — a stethoscope, a white coat, an examination
     * room — makes a statement about her scope of practice that is not true,
     * and it does not stop being untrue because the picture is pretty.
     *
     * It is the same failure that killed 2.mp4, whose frame 0 carried an
     * insulin diagram and a medication record. That one was caught by a human
     * opening the file frame by frame. This list is so that the next one does
     * not have to be.
     *
     * @var list<string>
     */
    public const CLINICAL_TERMS = [
        'stethoscope', 'white coat', 'lab coat', 'scrubs', 'doctor', 'physician',
        'nurse', 'hospital', 'clinic room', 'examination', 'surgery', 'patient',
        'medical equipment', 'syringe', 'prescription', 'medication', 'pills',
    ];

    /** Where candidates land. Not the source directory: nothing is committed yet. */
    private const STAGING = 'photos/inbox';

    public function handle(): int
    {
        $query = trim((string) $this->argument('query'));

        if ($problem = self::rejection($query)) {
            $this->error("Refusing to search for «{$query}».");
            $this->line("  It contains «{$problem['term']}».");
            $this->newLine();
            $this->line('  '.$problem['why']);
            $this->newLine();
            $this->line('  Search for the DEVICE, the KITCHEN, the FOOD or the DESK instead:');
            $this->line('    glucometer · vegetable market · lentils · olive oil');
            $this->line('    cooking hands · fresh herbs · consultation desk · writing notes');

            return self::FAILURE;
        }

        $key = (string) config('services.pexels.key');

        if ($key === '') {
            $this->error('PEXELS_API_KEY is not set (config: services.pexels.key).');

            return self::FAILURE;
        }

        $response = Http::withHeaders(['Authorization' => $key])
            ->timeout(30)
            ->get('https://api.pexels.com/v1/search', [
                'query' => $query,
                'per_page' => (int) $this->option('count'),
                'orientation' => (string) $this->option('orientation'),
            ]);

        if (! $response->successful()) {
            $this->error('Pexels returned '.$response->status().'.');

            return self::FAILURE;
        }

        /** @var list<array<string, mixed>> $photos */
        $photos = $response->json('photos') ?? [];

        if ($photos === []) {
            $this->warn('No results.');

            return self::SUCCESS;
        }

        $staging = public_path(self::STAGING);
        File::ensureDirectoryExists($staging);

        $notes = [];

        foreach ($photos as $photo) {
            $id = (int) $photo['id'];
            $photographer = (string) ($photo['photographer'] ?? 'unknown');
            $slug = Str::slug($query).'-'.$id;
            $target = $staging."/{$slug}.jpg";

            /*
             * The `original` size, not a web-sized rendition. The pipeline
             * budgets by megapixel and crops before resizing, so it wants the
             * largest source available; handing it a pre-shrunk copy would
             * bake somebody else's compression into ours.
             */
            $image = Http::timeout(60)->get((string) $photo['src']['original']);

            if (! $image->successful()) {
                $this->warn("  could not download {$id}");

                continue;
            }

            File::put($target, $image->body());

            $notes[$slug] = [
                'photographer' => $photographer,
                'photographer_url' => (string) ($photo['photographer_url'] ?? ''),
                'source' => (string) ($photo['url'] ?? ''),
                'pexels_id' => $id,
                'downloaded_at' => now()->toDateString(),
                'alt' => (string) ($photo['alt'] ?? ''),
                'dimensions' => ($photo['width'] ?? '?').'x'.($photo['height'] ?? '?'),
            ];

            $this->line(sprintf('  %-46s %s', $slug.'.jpg', $photographer));
        }

        $this->writeReviewSheet($staging, $query, $notes);

        $this->newLine();
        $this->info(sprintf('%d candidate(s) staged in public/%s.', count($notes), self::STAGING));
        $this->newLine();
        $this->line('  NOTHING IS IN THE LIBRARY YET. Next:');
        $this->line('    1. OPEN EVERY ONE. Reject anything with legible text, a');
        $this->line('       prescription sheet, a medication record, foreign-language');
        $this->line('       material, a scale, a tape measure, a numeric readout, a gym,');
        $this->line('       or an identifiable face.');
        $this->line('    2. Move the keepers to public/photos/ and add them to');
        $this->line('       config/photos.php with their attribution from candidates.json.');
        $this->line('    3. Record every rejection in the `rejected` list, with the reason.');
        $this->line('    4. php artisan clinic:process-photos');

        return self::SUCCESS;
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

    /**
     * The term this query is refused for, or null if it is allowed.
     *
     * Public and static because clinic:fetch-pexels-videos enforces the same
     * list. A term added here has to apply to both commands, and the only way
     * to guarantee that is for there to be one list rather than two that agree
     * on the day they were written.
     */
    public static function rejectedQuery(string $query): ?string
    {
        return self::rejection($query)['term'] ?? null;
    }

    /**
     * The term this query is refused for and WHY, so the message can say the
     * right thing. Refusing a search for "stethoscope" on the grounds that
     * stock libraries return bathroom scales would be both wrong and
     * confusing.
     *
     * @return array{term: string, why: string}|null
     */
    public static function rejection(string $query): ?array
    {
        $haystack = Str::lower($query);

        foreach (self::FORBIDDEN_TERMS as $term) {
            if (str_contains($haystack, $term)) {
                return [
                    'term' => $term,
                    'why' => 'A stock library reads that as tape measures and bathroom scales.',
                ];
            }
        }

        foreach (self::CLINICAL_TERMS as $term) {
            if (str_contains($haystack, $term)) {
                return [
                    'term' => $term,
                    'why' => 'That returns a doctor\'s surgery, and this clinic is a nutritionist\'s.'
                        .' The imagery would claim a scope of practice she does not have.',
                ];
            }
        }

        return null;
    }
}
