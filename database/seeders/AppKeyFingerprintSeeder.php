<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Console\Commands\VerifyAppKey;
use App\Models\Setting;
use Illuminate\Database\Seeder;

class AppKeyFingerprintSeeder extends Seeder
{
    /**
     * Record which APP_KEY encrypted this database.
     *
     * Stored on the very first seed, before any encrypted row exists, so that
     * `clinic:verify-key` has something to compare against from day one. It is
     * a SHA-256 of the key, never the key itself — the settings table is in
     * every backup and every mysqldump.
     *
     * updateOrCreate is deliberately NOT used: if a fingerprint already exists
     * and the key has since changed, silently rewriting it would destroy the
     * only evidence that the data is no longer decryptable.
     */
    public function run(): void
    {
        $existing = Setting::query()->where('key', VerifyAppKey::FINGERPRINT_KEY)->first();

        if ($existing !== null) {
            $this->command?->line('APP_KEY fingerprint already recorded; left untouched.');

            return;
        }

        Setting::query()->create([
            'key' => VerifyAppKey::FINGERPRINT_KEY,
            'value' => [
                'sha256' => hash('sha256', (string) config('app.key')),
                'recorded_at' => now()->toIso8601String(),
            ],
        ]);

        $this->command?->info('Recorded APP_KEY fingerprint.');
    }
}
