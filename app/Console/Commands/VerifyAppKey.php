<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Setting;
use Illuminate\Console\Command;

class VerifyAppKey extends Command
{
    /**
     * The settings row holding the fingerprint of the APP_KEY that encrypted
     * this database's clinical data.
     */
    public const FINGERPRINT_KEY = 'app_key_fingerprint';

    protected $signature = 'clinic:verify-key';

    protected $description = 'Verify APP_KEY still matches the key that encrypted the stored clinical data';

    public function handle(): int
    {
        $currentKey = (string) config('app.key');

        if ($currentKey === '') {
            $this->error('APP_KEY is empty. Nothing can be decrypted. Restore it from your key backup.');

            return self::FAILURE;
        }

        $fingerprint = hash('sha256', $currentKey);

        /** @var Setting|null $stored */
        $stored = Setting::query()->where('key', self::FINGERPRINT_KEY)->first();

        if ($stored === null) {
            Setting::query()->create([
                'key' => self::FINGERPRINT_KEY,
                'value' => ['sha256' => $fingerprint, 'recorded_at' => now()->toIso8601String()],
            ]);

            $this->info('No APP_KEY fingerprint was stored yet. Recorded the current key.');
            $this->line('  fingerprint: '.substr($fingerprint, 0, 16).'…');

            return self::SUCCESS;
        }

        $storedFingerprint = is_array($stored->value) ? ($stored->value['sha256'] ?? null) : null;

        if ($storedFingerprint === $fingerprint) {
            $this->info('APP_KEY matches the key that encrypted this database.');
            $this->line('  fingerprint: '.substr($fingerprint, 0, 16).'…');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->error('  APP_KEY MISMATCH — ENCRYPTED CLINICAL DATA IS UNREADABLE  ');
        $this->newLine();
        $this->line('  The APP_KEY currently configured is NOT the key that encrypted');
        $this->line('  this database.');
        $this->newLine();
        $this->line('  expected fingerprint: '.substr((string) $storedFingerprint, 0, 16).'…');
        $this->line('  current  fingerprint: '.substr($fingerprint, 0, 16).'…');
        $this->newLine();
        $this->line('  Every encrypted column is now undecryptable:');
        $this->line('    intake_forms.medications, .conditions, .avoid_foods, .note');
        $this->line('    notification_logs.recipient');
        $this->newLine();
        $this->line('  This is NOT corruption you can repair, and NOT something a');
        $this->line('  re-run will fix. The ciphertext is intact; the key that opens');
        $this->line('  it is gone. There is no brute-force path and no vendor recovery.');
        $this->newLine();
        $this->line('  THE ONLY RECOVERY IS RESTORING THE ORIGINAL APP_KEY FROM BACKUP.');
        $this->newLine();
        $this->line('  Do this now, in order:');
        $this->line('    1. STOP the deploy. Do not run migrations or seeders.');
        $this->line('    2. Do NOT run `php artisan key:generate` — that is almost');
        $this->line('       certainly what caused this.');
        $this->line('    3. Restore the original APP_KEY into .env from your key backup');
        $this->line('       (see docs/deployment/APP_KEY.md), then run this command again.');
        $this->line('    4. If you are deliberately rotating keys, the old key belongs in');
        $this->line('       APP_PREVIOUS_KEYS, not in the bin. See the same document.');
        $this->newLine();

        return self::FAILURE;
    }
}
