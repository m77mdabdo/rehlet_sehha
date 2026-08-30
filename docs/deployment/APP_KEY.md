# APP_KEY: the one secret that cannot be regenerated

## Why this document exists

Rehlet Sehha encrypts patient health data at the application layer. These
columns hold ciphertext and nothing else:

| table | columns |
|---|---|
| `intake_forms` | `medications`, `conditions`, `avoid_foods`, `note` |
| `notification_logs` | `recipient` |

All of them are encrypted with `APP_KEY`.

**If `APP_KEY` changes, that data is gone.** Not corrupted — gone. The
ciphertext is intact and mathematically sound; the key that opens it no longer
exists. There is no brute-force path, no vendor recovery, no support ticket.
A database backup does not help, because the backup contains the same
ciphertext. Only a backup of the *key* helps.

The realistic way this happens is not an attack. It is a deploy script running
`php artisan key:generate` on a server that already has data.

## Rules

### 1. Never run `key:generate` against an existing database

`key:generate` is a *project initialisation* command. It belongs to the moment a
project is first created, and nowhere else.

It has been removed from every Composer script in this repository — the `setup`
script and `post-create-project-cmd` no longer call it. Do not add it back, do
not put it in a deploy script, and do not run it manually "to fix" a decryption
error. A decryption error means the key is already wrong; generating a new one
guarantees the data can never be recovered.

### 2. Back the key up outside the server

`APP_KEY` lives in `.env`, which is gitignored and therefore **not** in any code
backup. If the server is lost or rebuilt, the key is lost with it unless it was
stored somewhere else first.

Store it in at least two of:

- the team password manager, in an entry named for this project;
- an encrypted note held by the clinic owner, offline;
- a sealed secret in whatever CI system deploys the project.

Record it **before first deploy**, and re-verify after any key rotation. Never
commit it, never paste it into a ticket, never send it over chat.

**Store `BACKUP_ARCHIVE_PASSWORD` in the same entry, at the same time.** The two
secrets are useless apart: a nightly database dump you cannot open is not a
backup, and one you *can* open without the key is a table of unreadable strings
where the intake forms used to be. Losing either one loses the same thing.

Neither may be rotated casually. Regenerating `APP_KEY` destroys every
encrypted clinical field; changing `BACKUP_ARCHIVE_PASSWORD` makes every
archive written before the change permanently unopenable.

### 3. Verify the key as the first step of every deploy

```bash
php artisan clinic:verify-key || exit 1
```

The command compares `sha256(APP_KEY)` against the fingerprint stored in
`settings.app_key_fingerprint`, which was recorded on the very first seed. It
exits non-zero on a mismatch, so the `|| exit 1` aborts the deploy **before**
migrations or seeders touch the database.

Put it first, ahead of `migrate`. A mismatch caught before the deploy is an
inconvenience; the same mismatch caught after is a data-loss incident.

If no fingerprint is stored yet, the command records the current key and passes.
That makes it safe to introduce into an existing environment.

### 4. It also runs daily

`routes/console.php` schedules `clinic:verify-key` daily at 03:00 Cairo time.

This is a canary, not a safety net. A wrong key produces **no symptom** until
someone happens to open an affected record, which could be weeks later. The
daily check turns a silent catastrophe into a failed scheduled task on the day
it happens, while a key backup is still findable.

The real protection is rule 3.

## Deploying to Hostinger

Hostinger deploys are typically a git pull plus a handful of Artisan commands.
Order matters:

```bash
cd ~/domains/DOMAIN/public_html

php artisan clinic:verify-key || exit 1     # FIRST. Aborts on a wrong key.
php artisan down

git pull origin main
composer install --no-dev --optimize-autoloader

php artisan migrate --force                 # never migrate:fresh in production
php artisan config:cache
php artisan route:cache
php artisan view:cache

php artisan up
```

Two things to watch on shared hosting:

- **`.env` is not in git.** A fresh checkout has no `.env` at all. Restore it
  from your backup rather than copying `.env.example` and generating a new key.
- **`migrate:fresh` and `db:wipe` drop every table.** They are for local use.
  Production migrations are `migrate --force`, nothing else.

The scheduler needs one cron entry:

```
* * * * * cd /home/USER/domains/DOMAIN/public_html && /usr/bin/php artisan schedule:run >> /dev/null 2>&1
```

## Intentional rotation with `APP_PREVIOUS_KEYS`

Sometimes you genuinely do need a new key — a suspected leak, or staff
offboarding. Laravel supports this **without** losing data, via
`APP_PREVIOUS_KEYS`.

Encryption always uses `APP_KEY`. Decryption tries `APP_KEY` first, then each
key in `APP_PREVIOUS_KEYS` in turn. So old ciphertext stays readable while new
writes use the new key.

**Never rotate without putting the old key in `APP_PREVIOUS_KEYS`. That is the
difference between a rotation and a data-loss event.**

Procedure:

1. **Back up the database and the current `.env`.** Both, before anything else.
2. Copy the current key somewhere safe. It is about to become the *previous* key.
3. Generate a new key **without writing it to `.env` yet**:
   ```bash
   php artisan key:generate --show
   ```
   `--show` prints the key and changes nothing. This is the only form of
   `key:generate` that is safe to run against a live project.
4. Edit `.env` by hand:
   ```dotenv
   APP_KEY=base64:NEW_KEY_HERE
   APP_PREVIOUS_KEYS=base64:OLD_KEY_HERE
   ```
   `APP_PREVIOUS_KEYS` is comma-separated; the most recent old key goes first.
5. Clear the config cache — a cached config will keep serving the old values:
   ```bash
   php artisan config:clear
   ```
6. Re-encrypt existing rows onto the new key by re-saving them, so the old key
   can eventually be dropped. Until you do, `APP_PREVIOUS_KEYS` must stay.
7. Update the stored fingerprint, which still describes the old key:
   ```bash
   php artisan tinker --execute="App\Models\Setting::where('key','app_key_fingerprint')->delete();"
   php artisan clinic:verify-key    # records the new key and passes
   ```
8. Store the new key in the password manager. Keep the old one for as long as
   `APP_PREVIOUS_KEYS` references it.

## If the key is already lost

Be honest about the position rather than hunting for a trick:

1. **Do not run `key:generate`.** It cannot help and closes off recovery.
2. Search every place `.env` may have been copied: local machines, `.env.backup`
   files on the server, old deploy archives, CI secrets, the password manager,
   an old laptop. A key recovered from anywhere works — it is just 32 bytes.
3. If it truly cannot be found, only the *encrypted* columns are lost. Names,
   phone numbers, appointments, services, posts and the schedule are all
   plaintext and unaffected. The clinic loses intake answers and delivery-log
   recipients, and must re-collect intake data from patients.
4. Clear the dead ciphertext deliberately rather than leaving rows that throw
   `DecryptException` on every read, and record the new key's fingerprint
   afterwards.

## Related

- `app/Console/Commands/VerifyAppKey.php` — the command
- `app/Console/Commands/UnpackBackup.php` — opens an archive; needs both secrets
- `database/seeders/AppKeyFingerprintSeeder.php` — records the baseline
- `routes/console.php` — the daily schedule
- `config/app.php` — `key` and `previous_keys`
- [hostinger.md § 14](hostinger.md) — backups, and the restore procedure that
  checks the key before believing the data
