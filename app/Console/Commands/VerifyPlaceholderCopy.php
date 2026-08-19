<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Support\Locales;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Symfony\Component\Finder\Finder;

/**
 * Blocks a deploy while placeholder copy is still in the translation files.
 *
 * The practitioner section ships with its words marked TODO_COPY because its
 * content is a set of claims about a real person's qualifications — a degree,
 * a membership, a syndicate registration number. Those were not ours to
 * invent, so the structure was built and the copy was left for the clinic.
 *
 * That leaves an obvious way to embarrass the practice: the section looks
 * finished in review, two people read past the placeholder, and the site goes
 * live with "TODO_COPY — the doctor's full name" under a heading that says
 * About the practitioner.
 *
 * A command rather than a failing test, deliberately. A test that fails today
 * would be red from the moment it was written until the clinic sends its
 * copy — and a suite that is expected to be red teaches everyone to ignore it,
 * which costs more than this check is worth. A deploy gate fails at the only
 * moment the problem is real. Same pattern as clinic:verify-key.
 *
 * Run it in the deploy pipeline, before the release goes live:
 *
 *     php artisan clinic:verify-copy
 */
class VerifyPlaceholderCopy extends Command
{
    /**
     * One greppable token. `grep -rn TODO_COPY lang/` is the whole handover.
     */
    public const MARKER = 'TODO_COPY';

    protected $signature = 'clinic:verify-copy
                            {--strict : Fail on any placeholder, regardless of environment}';

    protected $description = 'Fail if placeholder copy would be published';

    public function handle(): int
    {
        $outstanding = self::outstanding();

        if ($outstanding === []) {
            $this->info('No placeholder copy. Safe to publish.');

            return self::SUCCESS;
        }

        $blocking = $this->option('strict') || app()->isProduction();

        $this->newLine();
        $this->line(sprintf('  %d translation value(s) still contain %s:', count($outstanding), self::MARKER));
        $this->newLine();

        foreach ($outstanding as $key => $value) {
            $this->line('  · lang/'.$key);
            $this->line('      '.mb_strimwidth(trim($value), 0, 92, '…'));
        }

        $this->newLine();

        if (! $blocking) {
            // Outside production this is information, not an error: the whole
            // point of the placeholder is that it is visible while the site is
            // being built and reviewed.
            $this->comment('Environment is '.app()->environment().', so this is a warning.');
            $this->comment('It becomes a hard failure in production, or with --strict.');

            return self::SUCCESS;
        }

        $this->error('  PLACEHOLDER COPY WOULD BE PUBLISHED — DEPLOY BLOCKED  ');
        $this->newLine();
        $this->line('  These are claims about a real practitioner. Get the real values from');
        $this->line('  the clinic; do not fill them in by guessing.');
        $this->newLine();

        return self::FAILURE;
    }

    /**
     * Every translation value still carrying the marker, keyed by
     * "{locale}/{group}.{dotted.key}".
     *
     * @return array<string, string>
     */
    public static function outstanding(): array
    {
        $found = [];

        foreach (Locales::all() as $locale) {
            $path = lang_path($locale);

            if (! is_dir($path)) {
                continue;
            }

            foreach (Finder::create()->files()->in($path)->name('*.php')->depth(0) as $file) {
                /** @var array<array-key, mixed> $translations */
                $translations = require $file->getRealPath();

                foreach (Arr::dot($translations) as $key => $value) {
                    if (is_string($value) && str_contains($value, self::MARKER)) {
                        $found[$locale.'/'.$file->getBasename('.php').'.'.$key] = $value;
                    }
                }
            }
        }

        ksort($found);

        return $found;
    }
}
