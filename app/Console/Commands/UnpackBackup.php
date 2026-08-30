<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * OPEN A BACKUP ARCHIVE.
 *
 * This command exists because of one specific trap.
 *
 * When BACKUP_ARCHIVE_PASSWORD is set, spatie/laravel-backup encrypts the zip
 * with AES-256 — and Info-ZIP `unzip`, which is the `unzip` on Hostinger, on
 * macOS and on most Linux boxes, CANNOT READ AES ZIPS. It does not error in a
 * way that reads as a failure either; it prints
 *
 *     skipping: db-dumps/….sql  need PK compat. v5.1 (can do v4.5)
 *
 * and exits, leaving an empty directory. On the day somebody actually needs a
 * restore, that line is indistinguishable from a corrupt backup, and the
 * reasonable conclusion — "our backups are broken" — is wrong.
 *
 * PHP's ZipArchive reads them correctly, and PHP is by definition installed.
 * So the documented restore path goes through this command rather than through
 * a shell tool that may or may not be able to open the file.
 *
 * IT ONLY UNPACKS. Importing the dump is a separate, manual, deliberate step —
 * a command that overwrites the live database on one keystroke is a command
 * that will one day be run against the wrong one. See docs/deployment/hostinger.md.
 */
class UnpackBackup extends Command
{
    protected $signature = 'clinic:unpack-backup
                            {archive? : Path to the .zip. Defaults to the newest backup on disk.}
                            {--to= : Directory to unpack into. Defaults to storage/app/private/restore.}';

    protected $description = 'Unpack a backup archive (handles AES-encrypted zips that unzip cannot read)';

    public function handle(): int
    {
        $archive = $this->argument('archive') ?? $this->newestArchive();

        if ($archive === null) {
            $this->error('No backup archive found. Looked in: '.$this->backupDirectory());

            return self::FAILURE;
        }

        if (! is_file($archive)) {
            $this->error("Not a file: {$archive}");

            return self::FAILURE;
        }

        $destination = $this->option('to') ?: storage_path('app/private/restore');

        $zip = new ZipArchive;
        $opened = $zip->open($archive);

        if ($opened !== true) {
            $this->error("Could not open the archive (ZipArchive code {$opened}). The file may be truncated.");

            return self::FAILURE;
        }

        $password = (string) config('backup.backup.password');

        if ($password !== '') {
            $zip->setPassword($password);
        }

        if (! is_dir($destination) && ! mkdir($destination, 0700, true) && ! is_dir($destination)) {
            $this->error("Could not create {$destination}");

            return self::FAILURE;
        }

        $extracted = $zip->extractTo($destination);
        $zip->close();

        if (! $extracted) {
            $this->newLine();
            $this->error('  EXTRACTION FAILED  ');
            $this->newLine();

            if ($password === '') {
                $this->line('BACKUP_ARCHIVE_PASSWORD is empty in this environment. If the archive was');
                $this->line('written on a server where it was set, put that same password in .env here.');
            } else {
                $this->line('The archive did not open with the current BACKUP_ARCHIVE_PASSWORD.');
                $this->line('An archive is encrypted with whatever password was set the night it was');
                $this->line('written — an older dump needs the older password.');
            }

            $this->newLine();
            $this->line('The password is stored with APP_KEY. See docs/deployment/APP_KEY.md.');

            return self::FAILURE;
        }

        $this->info('Unpacked.');
        $this->line('  archive : '.$archive);
        $this->line('  into    : '.$destination);
        $this->newLine();

        foreach ($this->dumpsIn($destination) as $dump) {
            $this->line('  dump    : '.$dump.'  ('.$this->humanSize(filesize($dump) ?: 0).')');
        }

        $this->newLine();
        $this->warn('This directory now holds every patient record in plain text. Delete it when you are done.');

        return self::SUCCESS;
    }

    private function backupDirectory(): string
    {
        return storage_path('app/private/'.config('backup.backup.name'));
    }

    private function newestArchive(): ?string
    {
        $disk = Storage::disk('local');
        $directory = (string) config('backup.backup.name');

        $newest = null;
        $newestTime = null;

        foreach ($disk->files($directory) as $file) {
            if (! str_ends_with($file, '.zip')) {
                continue;
            }

            $modified = $disk->lastModified($file);

            if ($newestTime === null || $modified > $newestTime) {
                $newestTime = $modified;
                $newest = $disk->path($file);
            }
        }

        return $newest;
    }

    /**
     * @return list<string>
     */
    private function dumpsIn(string $directory): array
    {
        $found = glob($directory.'/db-dumps/*.sql');

        return $found === false ? [] : $found;
    }

    private function humanSize(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes.' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024).' KB';
        }

        return round($bytes / 1024 / 1024, 1).' MB';
    }
}
