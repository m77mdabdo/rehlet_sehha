# Pre-launch security checklist

Run through this on launch day, and again after any hosting change. Every item
is checkable in under a minute — there is a command for each.

Everything here was found the hard way on a development machine, where the
blast radius was one LAN. On a public host the same mistakes are open to the
internet.

---

## 1. `.env` must not be web-reachable

- [ ] **Hostinger's document root points at `/public`, not the project root.**

Set the domain's document root to `.../rehletsehha.com/public` in hPanel
(Websites → Domains → Document root). Verify from off the server:

```
curl -s -o /dev/null -w "%{http_code}\n" https://rehletsehha.com/.env
curl -s -o /dev/null -w "%{http_code}\n" https://rehletsehha.com/storage/logs/laravel.log
curl -s -o /dev/null -w "%{http_code}\n" https://rehletsehha.com/composer.json
```

**All three must return 404.** A `200` on any of them is a live incident: see
§5 and rotate everything before doing anything else.

**What happens if the root is wrong:** the whole project becomes browsable.
With directory indexing on you get a file listing; `.env` downloads as plain
text, carrying `APP_KEY`, the database password and `MAIL_PASSWORD`. That is
not theoretical — it is exactly what a local XAMPP install did during
development, serving `.env` over HTTP 200 to anyone on the same network.
`APP_KEY` alone decrypts every intake record in the database.

- [ ] Directory indexing is off (`Options -Indexes`), so a misconfigured root
      degrades to 403 rather than a browsable listing.

---

## 2. `APP_KEY` is generated fresh on the server

- [ ] **Production has its own key. It must never equal the local one.**

```
php artisan key:generate --force     # on the server, once, before first boot
php artisan clinic:verify-key        # records the fingerprint
```

Compare the two by fingerprint, never by pasting keys around:

```
php artisan tinker --execute="echo substr(hash('sha256', config('app.key')), 0, 16);"
```

Run it locally and on the server. **The two must differ.**

Reusing the local key means every laptop that ever had the repo can decrypt
production's clinical data. Losing or changing it after go-live means nobody
can — the intake fields become permanently unreadable.

See [APP_KEY.md](APP_KEY.md) for the full rationale, the backup procedure, and
why `clinic:verify-key` runs as the first step of every deploy.

---

## 3. Credentials differ between local and production

- [ ] Database name, user and password are **not** the local ones.
- [ ] `MAIL_PASSWORD` is **not** the local one.
- [ ] No production credential appears anywhere in the repository.

```
git log -p --all -- .env .env.production 2>/dev/null | head    # must be empty
grep -rnE "MAIL_PASSWORD=.+|DB_PASSWORD=.+" .env.example       # must be empty
```

`.env.example` ships with blank secrets on purpose. If a value ever appears
there, it is committed, and committed means public — see §5.

Shared credentials mean a development mistake is a production breach. It also
means the throwaway MySQL password from a local XAMPP install — reachable on
`*:3306` by default — is the one guarding patient records.

---

## 4. Nothing else is exposed

- [ ] Only 80 and 443 are open to the internet. Not 3306, not 21, not 8000.

```
# from a machine that is NOT the server
nmap -Pn -p 21,80,443,3306,3307,8000 rehletsehha.com
```

- [ ] `APP_DEBUG=false` and `APP_ENV=production` in the server's `.env`.

```
php artisan tinker --execute="echo config('app.env').' debug='.var_export(config('app.debug'), true);"
```

`APP_DEBUG=true` renders a stack trace on every error, and Laravel's error page
prints environment variables — which republishes `.env` to anyone who can
trigger an exception, whatever the document root is set to.

---

## 5. Rotate, never reuse

- [ ] **Any credential that has ever been committed, logged, or served over
      HTTP is burned. Replace it — do not reuse it.**

This applies even when the exposure looks harmless:

- committed to git, even if the commit was amended or force-pushed — it is
  still in the reflog, in every clone, and in any fork
- written to `storage/logs/`, which `APP_DEBUG` and unhandled exceptions do
  freely
- served by a misconfigured document root, however briefly
- pasted into an issue, a chat, or a screenshot

"Nobody saw it" is not a finding, it is an absence of evidence. The check is
whether it *could* have been read, and access logs only prove what was
requested, not what was copied.

What rotation means here:

| Credential | How |
| --- | --- |
| `APP_KEY` | **Do not rotate after go-live** without re-encrypting — it would orphan every intake record. Back it up instead, and treat exposure as an incident requiring a planned re-encryption. |
| `MAIL_PASSWORD` | Change the mailbox password in hPanel, update `.env`, `php artisan config:clear`. |
| `DB_PASSWORD` | Change in hPanel → Databases, update `.env`, `php artisan config:clear`. |

`APP_KEY` is the one that cannot simply be swapped, which is exactly why §1 and
§2 matter more than the rest of this list.

---

## 6. The admin panel

- [ ] `/admin` returns a login page and nothing else to an anonymous visitor.
- [ ] It carries `X-Robots-Tag: noindex` — on the login page AND on the
      redirect an unauthenticated request receives.

```
curl -sI https://rehletsehha.com/admin | grep -i x-robots-tag
```

- [ ] Every administrator has two-factor set up. The panel forces enrolment at
      login, and the users list shows a tick per account.
- [ ] `php artisan filament:assets` has run on the server. The published CSS
      and JS are gitignored build artefacts (see .gitignore); `composer install`
      regenerates them via `filament:upgrade`. A panel that loads unstyled means
      it did not.

---

## After launch

- [ ] `php artisan schedule:list` shows the queue worker, reminders and daily
      schedule — see [cron.md](cron.md). Without the cron entry no mail is
      delivered at all, and every `notification_logs` row sits at `queued`.
- [ ] DMARC moves to `p=quarantine` after two clean weeks — see
      [mail-authentication.md](mail-authentication.md).
