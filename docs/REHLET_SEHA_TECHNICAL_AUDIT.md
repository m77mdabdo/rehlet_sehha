# Rehlet Sehha — Technical Audit

**Date:** 2026-10-24 · **Audited commit:** `6df7ddd` (`main` = `origin/main`)
**Scope:** Git identity and history, GitHub and production deployment state,
user-facing branding and leftover artifacts, security, and validation.

This repository is **public**. Nothing in this report is a secret, and nothing
here should become one: no credentials, keys or panel details belong in this
file.

---

## Summary

| Area | Result |
|---|---|
| GitHub | `github.com/m77mdabdo/rehlet_sehha`, public, reachable, `main` in sync |
| Production | **Confirmed live** at `https://rehletsehha.com` on Hostinger; `/up` all 7 checks green |
| Deployment | Push to `main` **is** the deploy (hPanel auto-deploy + scheduled watcher) |
| Git authorship | 86 of 92 commits carried the wrong identity; **corrected and published 2026-10-24**, verified on GitHub (§9) |
| Future commits | Already resolve to `Mohamed Abdo <mohamedabdo2002815@gmail.com>` |
| Backups | Bundle, mirror and working-copy archive; checksummed and restore-tested |
| AI branding | None in code, UI or the live site. AI co-author trailers in history are accurate provenance and were kept |
| Security | 10 dependency advisories fixed; 1 JSON-LD markup injection fixed; 1 audit-log regression caught and fixed |
| Tests | **939 passed, 0 failed** (serial run), Pint and PHPStan clean, Vite build OK |

The authorship correction is the only change published so far (§9). The code
and documentation changes listed in §11 and §17 were made locally and are not
yet committed.

---

## 1. Project identification

| | |
|---|---|
| Name | Rehlet Sehha — رحلة صحة (clinical nutrition practice site + booking) |
| Laravel | 12.67.0 at audit → **12.70.1** after the security update (uncommitted) |
| PHP | 8.5.2 local (CLI); production reports PHP 8.5.11 |
| Composer | 2.9.5 |
| Node / npm | 20.20.0 / 10.8.2 |
| Main packages | Filament 5 (admin), Livewire 4, Tailwind 4 + Vite 7, Spatie permission / translatable / activitylog / backup |
| Routes | 60 application routes (`php artisan route:list --except-vendor`) |
| Languages | Arabic (RTL, default) and English — `lang/ar`, `lang/en` |

## 2. GitHub and Git remote status

- **Remote:** `origin` → `https://github.com/m77mdabdo/rehlet_sehha.git`
- **Owner:** `m77mdabdo` (verified through the GitHub API, not inferred from the remote name).
- **Visibility:** public. Default branch `main`.
- **Reachability:** `git ls-remote` succeeds. Remote has one branch (`main`) at `6df7ddd`.
- **Divergence:** none. Local `main` and `origin/main` are both `6df7ddd6c25b…`.
- **Branches / tags / stashes:** one branch (`main`), **no tags**, **no stashes**.
- **Working tree at audit start:** clean, nothing untracked.
- **CI/CD:** no `.github/workflows`, no Docker or other CI configuration. Deployment is done by the host (§4).
- **Branch protection:** could not be read anonymously (the API requires authentication).
- **Pushing:** done by the owner; there is no automated push or CI.

## 3. Production deployment status

**Confirmed production deployment.** Evidence gathered with read-only HTTP requests only:

| Check | Result |
|---|---|
| `GET https://rehletsehha.com/` | `302` to the locale; headers `platform: hostinger`, `panel: hpanel`, `server: hcdn` |
| `GET /up?format=json` | `status: ok`; database, storage, cache, scheduler, queue, backup, deploy all `true` |
| `GET /ar`, `GET /en` | Correct bilingual titles; no generator tag, credits or placeholders |
| Unknown URL | `404`, no stack trace or debug output — debug mode is off |
| `GET /.env` | `403` (blocked; not served) |
| `GET /admin` | `302` to `/admin/login`, `X-Robots-Tag: noindex, nofollow, …` |
| Security headers | HSTS, `X-Frame-Options: DENY`, `nosniff`, Referrer-Policy, Permissions-Policy, CSP (also as `<meta>`, because the host overwrites the header — see `docs/deployment/hostinger.md`) |

- **Domain:** `rehletsehha.com` (confirmed).
- **Hosting provider:** Hostinger shared hosting, managed through hPanel (confirmed by response headers and the project docs).
- **Production `APP_ENV` / `APP_URL`:** `.env.example` is deliberately the production template (`APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://rehletsehha.com`). The production `.env` was not read.

## 4. Deployment mechanism

From `docs/deployment/hostinger.md` and `app/Console/Commands/WatchDeployment.php`:

1. **hPanel Git auto-deploy** tracks `main`. Each push triggers a **fresh shallow clone** (`depth 1`) that is copied into the web root. hPanel runs no build step.
2. **`clinic:watch-deployment`** runs every minute from the scheduler. It reads `.git/HEAD`, compares it with the last commit it built (`storage/app/private/deployment-state.json`), and when they differ (and the commit has been stable for one cycle) it runs: `down` → `composer install --no-dev` → `migrate --force` → caches → `up`.
3. A push is fully live within about 60–120 seconds. `/up`'s `deploy` check reports whether the deployed commit was built.

**What production depends on in Git:** only the **tip of `main`**. Because the clone is fresh and shallow, the server never needs older commit IDs, tags or hooks. The watcher's state file stores the last-built SHA, so any new SHA, including a rewritten one, simply triggers one rebuild.

## 5. Deployment risks

| Risk | Detail |
|---|---|
| **Every push to `main` deploys** | There is no staging branch. Review before pushing, not after. |
| Rebuild window | Each new SHA causes a brief maintenance window (`down` → `up`) while composer and migrations run. |
| `composer.lock` changes | The pending security update changes `composer.lock`; the watcher's `composer install` applies it on the server and re-publishes Filament's assets. Verify the admin panel styling after that push (“The check that catches both” in `docs/deployment/hostinger.md`). |
| Single deploy path | If cron stops, the watcher stops; `/up` → `deploy: false` is the signal. |
| Server access | SSH uses password authentication; host, port and account name are published in this public repository (§13). |

## 6. Git authorship audit

| Metric | Value |
|---|---|
| Total commits (all refs) | **92**, linear history |
| Merge commits | 0 |
| Signed commits / signed tags | 0 / 0 (no tags) |
| `Mohamed Abdo <mohamedabdo2002815@gmail.com>` | 6 commits (author and committer) |
| `buildwithimad <kimad1728@gmail.com>` | **86 commits** (author and committer), 2026-08-18 → 2026-10-12 |
| Other human contributors | none |
| `Co-Authored-By` trailers | 81 commits: `Claude Opus 5 (1M context)` (61), `Claude Opus 5` (20) |

**Why the 86 are treated as misattributed, not as another contributor's work:**

- The owner states the identity was configured incorrectly.
- The **local reflog**, which records only operations performed in this working copy, shows all 86 commits being created *here*, interleaved with the owner's own commits. On 2026-08-18 the `buildwithimad` identity appears only for GitHub's standard setup snippet (`git commit -m "first commit"` then `git branch -M main`), minutes after and before commits made as Mohamed Abdo in the same repository.
- There are no merges, pull requests or pushes from elsewhere. Every commit was made in this one working copy.

The AI co-author trailers are **accurate provenance** and were deliberately left unchanged.

## 7. Git identity configuration

- `~/.gitconfig`: `user.name = Mohamed Abdo`, `user.email = mohamedabdo2002815@gmail.com`.
- No repository-local override and no `GIT_AUTHOR_*` / `GIT_COMMITTER_*` environment variables.
- `git var GIT_AUTHOR_IDENT` and `GIT_COMMITTER_IDENT` both resolve to `Mohamed Abdo <mohamedabdo2002815@gmail.com>`.

**Future commits are already correct.** No configuration change was needed.

## 8. Backup status and location

Stored outside the repository on the owner's workstation (owner-only permissions):

| File | Contents |
|---|---|
| `rehlet_sehha-all.bundle` | All refs; `git bundle verify` OK |
| `mirror.git/` | Bare mirror clone |
| `working-copy-full.tar.gz` | Full working copy including `.git` and ignored files, excluding `vendor/` and `node_modules/` (reproducible from the lock files). Contains local environment files; kept private. |
| `refs-original.txt` | Original ref → SHA |
| `remote-tips.txt` | `git ls-remote origin` at audit time |
| `commits-trees-original.txt` | Every commit: SHA, tree, parents, author, committer, date |
| `commit-map-old-to-new.txt` | 92 lines, original SHA → corrected SHA |
| `SHA256SUMS` | Checksums; `shasum -a 256 -c SHA256SUMS` passes |

**Restore test:** cloning the bundle into a temporary directory gave HEAD `6df7ddd`, 92 commits, and a clean `git fsck --full`.
There was no staged, unstaged, untracked or stashed work to preserve at audit start. Git LFS is not used.

## 9. Historical correction status — **applied 2026-10-24**

Prepared in an isolated clone, then published by the owner after explicit
approval.

- Rewrote only author/committer fields equal to `kimad1728@gmail.com` → `Mohamed Abdo <mohamedabdo2002815@gmail.com>`.
- **Verified before publishing:** each of the 92 commits compared with its original. Tree, author and committer dates, message and trailers are byte-identical, and the parent graph maps one-to-one. The two commits that already carried the correct identity kept their original IDs. The final tree is `bc110522` in both histories. `git fsck` clean. All 81 co-author trailers are intact.
- Local `main` was moved with a guarded ref update (no checkout, no `pull`), so uncommitted work was untouched; it was also saved separately beforehand.
- **Published** with `git push --force-with-lease=main:6df7ddd… origin main`. `main` went from `6df7ddd` to `9677b60`.
- **Verified after publishing:** the GitHub API reports 92 commits on `main`, all authored and committed by Mohamed Abdo. Production rebuilt the new commit (`/up` → all seven checks `true`, including `deploy`), and `/ar`, `/en`, `/ar/booking` and `/admin/login` respond normally.
- The original history is kept in an offline Git bundle. Clones and forks made before 2026-10-24 still carry the old commit IDs.

## 10. AI branding and artifact findings

Searched every tracked file (Blade, JS, CSS, translations, config, docs, public assets, email templates, error pages) and the rendered `/ar` and `/en` pages in production:

| Finding | Action |
|---|---|
| No AI or vendor branding (Claude, Anthropic, ChatGPT, “generated by…”) in code, UI, metadata or the live site | None needed |
| `README.md` was the unmodified Laravel skeleton README (framework marketing and sponsor list), describing nothing about this project | **Replaced** with an accurate project README |
| `public/brand/1.mp4`, `hero-poster.jpg`, `hero-poster-1280.webp` (524 KB): the superseded hero video and its posters, referenced nowhere but still publicly served | **Removed** (follows the project's own practice of deleting retired brand assets) |
| `TODO_COPY` markers | Intentional: `php artisan clinic:verify-copy` fails while placeholder copy exists (a manual release gate; the automatic deployment does not run it). No live translation value contains it. Kept. |
| `01xxxxxxxxx` in `lang/*/booking.php` | A phone-number format hint for the input. Kept. |
| `hello@example.com` in `config/mail.php`, `config/backup.php` | Framework fallback used only if `MAIL_FROM_ADDRESS` is unset; it does not appear in production output. Kept. |
| `composer.json` skeleton description, keywords and `"license": "MIT"` | Updated in the follow-up (§17). `name: laravel/laravel` left alone: changing it alters the `composer.lock` content hash. |
| AI `Co-Authored-By` trailers in Git history | Accurate record of AI-assisted commits; **kept** |

## 11. Files changed (uncommitted, in the working tree)

| File | Change |
|---|---|
| `README.md` | Project README replaces the Laravel skeleton README |
| `public/brand/1.mp4`, `hero-poster.jpg`, `hero-poster-1280.webp` | Deleted (orphaned) |
| `composer.lock` | Security updates (§12) |
| `app/Filament/Resources/Appointments/RelationManagers/IntakeRelationManager.php` | Denied-access audit logging restored under the new Filament (§12) |
| `app/Support/ClinicSchema.php`, `app/Support/PageSchema.php` | `JSON_HEX_TAG` added to JSON-LD encoding (§12) |
| `tests/Feature/ClinicSchemaTest.php` | New test guarding the JSON-LD fix |
| `tests/Feature/WorkingHoursAdminTest.php` | Called another file's helper (`staffWithRole`) instead of its own (`scheduleStaff`), so it failed when run alone |
| `docs/REHLET_SEHA_TECHNICAL_AUDIT.md` | This report |

## 12. Security findings

**Fixed locally (uncommitted, tested):**

1. **Dependency advisories: 10 across 7 packages → 0.** Updated within existing version constraints: `laravel/framework` 12.67.0 → 12.70.1, `filament/*` 5.7.6 → 5.10.3, `livewire/livewire` 4.4.1 → 4.4.8, `league/commonmark` 2.10.0 → 2.10.4, `league/flysystem` 3.35.2 → 3.36.0, `symfony/http-foundation`, `symfony/yaml`, and their transitive patches.
   Exposure before the fix: the *critical* Laravel TokenGuard bypass did **not** apply (the app uses only the `session` guard). The `symfony/http-foundation` ReDoS **did** apply, because it is reachable through a request header on any public request. The Livewire `#[Authorize]` and Filament MFA advisories affect the admin surface.
2. **Audit-log regression caught during the update.** Filament after 5.7.6 adds `CanAuthorizeAccess`, which refuses an unauthorised relation-manager mount *before* `mount()`. Access stayed blocked, but the app logged refusals inside `mount()`, so **denied attempts on patient intake records stopped being audited**. `ClinicalReadAuditTest` caught it. Fixed by logging the refusal in the boot hook that now does the refusing, while keeping the `mount()` check as a second line. This only ever existed in the updated local tree; production never ran it.
3. **JSON-LD markup injection.** Structured data is printed raw inside `<script type="application/ld+json">`, encoded with `JSON_UNESCAPED_SLASHES` and without `JSON_HEX_TAG`. A staff-entered title or label containing `</script>` would end the element and inject HTML into public pages. The nonce-based CSP would block injected scripts, but not the markup. Fixed with `JSON_HEX_TAG` (still valid JSON, identical when parsed); a test reproduces the injection and guards against it.

**Verified as already sound:** CSRF middleware on web and panel; throttling on token URLs and public POSTs; policies for every admin resource; `ArticleBody::render` escapes all text and link output; `.env.example` holds safe production defaults (guarded by `EnvironmentExampleTest`); `LOG_LEVEL=warning`, `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`; debug output absent in production.

**Open:**

4. **Server identifiers in a public repository.** `docs/deployment/hostinger.md` contains the origin IP, the hosting account username and the SSH port. Combined with password-only SSH, that gives a brute-force target. Recommended: switch the server to key-based SSH and disable password login, or make the repository private. Removing the lines now would not remove them from history.
5. **Front-end dev dependencies:** `npm audit` reports `shell-quote` (via `concurrently`) and `source-map-js`. These are build-time only and not shipped, but worth fixing with `npm audit fix` followed by a rebuild check.

This was a targeted review (automated gates, dependency audit, authorization paths, output escaping, production headers), not a line-by-line review of all 60 routes.

## 13. Tests executed and actual results

All runs used a **throwaway MySQL 8.4** instance on port 3317 with an empty data directory, selected only through environment variables. Neither the local development databases nor production were used. The usual local development MySQL **would not start**, because a package-manager upgrade removed a shared library it links against.

| Check | Result |
|---|---|
| PHP syntax (`php -l`, app/config/routes/database/lang/bootstrap) | No errors |
| Pint (`--test`) | Passed |
| PHPStan | No errors |
| Route list | 60 routes, resolves cleanly |
| `composer audit` | 10 advisories → **none** |
| Vite build (to a scratch directory) | Succeeds; hashed filenames identical to committed `public/build` |
| Full suite, **serial**, final tree | **939 passed, 0 failed** (10,377 assertions) |
| Full suite, parallel, final tree | 937–939 passed across runs; see flakes below |

**Pre-existing parallel flakes:** in parallel runs, one or two different tests fail each time (`HealthCheckTest`, `HeaderOverHeroTest`, `CredentialsAndReviewsTest` observed). Each passes repeatedly when run alone, and all pass serially. This also happened before any change. Cause not yet identified; the test cache store is `array`, so it is not a shared cache.

**Fresh-environment fragility:** some tests (e.g. `MotionTest`, `HealthCheckTest`) query the base `rehlet_sehha_test` database without `RefreshDatabase`, so they fail with "table doesn't exist" until that database has been migrated once.

**Not tested:** real-browser rendering of Arabic/English RTL layouts beyond the HTML checks above; the updated Filament assets on the production server (only possible after a push).

## 14. Pending tasks

1. ~~Apply the corrected authorship history~~ — done 2026-10-24 (§9).
2. Review and commit the working-tree changes; push when ready (this deploys). Then check `/up` → `deploy: true` and that the admin panel styling loads.
3. Repair the local development MySQL (see §13).
4. Move server SSH to key-based authentication; consider making the repository private.
5. `npm audit fix` for the two dev-only advisories, then confirm the build output.
6. Optional: set `composer.json` `name` for this project, followed by `composer update --lock`.
7. Investigate the parallel-only test flakes.
8. Further items from the follow-up review are listed in §17.

## 15. Safe GitHub publishing plan

**A — Ship the fixes without rewriting history (lowest risk).**
1. Commit the working-tree changes on `main` (they are grouped in §11).
2. `git push origin main` — a normal fast-forward push, which is a deploy.
3. Watch for about 2 minutes, then check `curl -s https://rehletsehha.com/up?format=json` → all true, and sign in to `/admin` to confirm the panel renders.

**B — Authorship correction.** Done on 2026-10-24 (§9). Any other working
copy made before then should be re-cloned, or reset with
`git fetch && git reset --hard origin/main` once it has no unpushed work. Old
commit IDs can stay reachable on GitHub for a while (cached views, forks); the
rewrite corrects attribution going forward but does not erase the past.

## 16. Production rollback plan

- **For a bad application push:** `git revert <commit>` and push. That is a normal deploy, and the watcher rebuilds within about 2 minutes. If the watcher itself is broken, run the manual sequence in `docs/deployment/hostinger.md` §"The manual sequence".
- **For the dependency update specifically:** reverting the commit restores the old `composer.lock`; the watcher's `composer install` downgrades on the next cycle.
- **For the history rewrite:** the original history is intact in an offline bundle (tip `6df7ddd`) and could be pushed back with `--force-with-lease`. The deployed tree is identical either way, so there is no operational reason to.
- **Database:** none of these changes include migrations. Backups are produced by `spatie/laravel-backup` (`/up` → `backup: true`).

## 17. Follow-up: project review and repository presentation (2026-10-24)

A second pass reviewed the application end to end to write an accurate README
and prepare the public repository. The feature inventory, including what is
implemented only in part, is in the [README](../README.md#features).

### Findings

| # | Finding | Action |
|---|---|---|
| 1 | **The automatic deploy does not run `clinic:verify-key`.** `clinic:watch-deployment` runs `down` → composer → `migrate` → caches → `up`; `APP_KEY.md` and `routes/console.php` both say the key check must come first on any deploy. On the automatic path a wrong key is caught only by the daily 03:00 run. | Documented in `APP_KEY.md`. **Recommended code change** (not made: it alters production deploy behaviour): call `clinic:verify-key` at the start of the watcher's rebuild and abort on failure. |
| 2 | **Real PHP minimum is 8.4.1**, not the `^8.2` in `composer.json`; the locked Symfony 8 components require it. | README and `hostinger.md` corrected. Raising the constraint in `composer.json` changes the lock hash; do it with `composer update --lock`. |
| 3 | **`composer.json` claimed `"license": "MIT"`** (inherited from the Laravel skeleton) with no licence file in the repository; description and keywords were the skeleton's. | Set to `"proprietary"` with a project description and keywords. These fields are outside the lock hash (`composer validate` clean). **The owner should confirm the licence choice**; it is one line to change. |
| 4 | **`composer setup` migrates before `.env` can be edited**, and `.env.example` is the production template. | README gives manual steps instead and explains why. |
| 5 | **Booking source is never set to `phone` or `walk_in`**; appointments created in the panel are recorded as `website`. | Documented as partial. Fix needs a product decision (a field on the staff form). |
| 6 | **WhatsApp and SMS** exist as notification channel values but nothing sends through them. | Documented as partial. |
| 7 | Testimonials, plate-builder foods and blocked slots have **no admin screen**. | Documented as partial. |
| 8 | `.gitignore` covered only three `.env` variants and no database dumps. | Added `.env.*` (except `.env.example`), `*.sql` and `*.sql.gz`. No tracked file is affected. |
| 9 | `tests/Unit/ExampleTest.php` was the skeleton's `assertTrue(true)`. | Removed. (`tests/Feature/ExampleTest.php` keeps the skeleton name but tests the `/` → `/ar` redirect; worth renaming.) |
| 10 | `APP_KEY.md` described deploys as a `git pull`. | Updated to the clone + watcher mechanism. |
| 11 | This report contained machine-specific paths and workstation details. | Removed. |
| 12 | The three media files deleted in the first pass were re-checked against all tracked files, built assets, the live pages and the web manifest. | Confirmed unused; deletion kept. |

### Public-repository content to review (no change made)

- **`docs/deployment/hostinger.md`** publishes the origin IP, the hosting
  account username and the SSH port (§12 item 4).
- **`docs/deployment/APP_KEY.md`, “Where they are”** names the files on the
  developer machine that hold the production key and backup password. The
  secrets are not in the repository, but the note tells a reader which machine
  to target. Consider replacing it with "in the password manager entry …" once
  they have been moved there, as that section itself asks.
- **`docs/content/`** holds generated editorial working documents for
  unpublished drafts (open clinical questions, citations to verify). They are
  accurate provenance and the publish gate depends on the process they
  describe; whether they should be public is the owner's decision.

### Proposed GitHub presentation

- **Description.** The proposed “A bilingual healthcare appointment and clinic
  management platform built with Laravel” overstates the scope: this is a
  single-practice application, not a multi-clinic platform. Suggested:
  *“Bilingual (Arabic/English) website, online booking and staff panel for a
  clinical nutrition practice — Laravel 12, Filament 5, Livewire 4.”*
- **Website:** `https://rehletsehha.com`
- **Topics:** `laravel`, `filament`, `livewire`, `tailwindcss`, `php`, `mysql`,
  `healthcare`, `appointment-booking`, `clinic`, `nutrition`, `arabic`, `rtl`,
  `bilingual`
- **Repository settings:** enable private vulnerability reporting (the README
  asks for private reports); consider branch protection on `main` that requires
  a pull request, since every push to `main` deploys.

### Validation (final tree)

| Check | Result |
|---|---|
| Full test suite, serial | **938 passed, 0 failed** (one boilerplate test removed) |
| Pint / PHPStan | Passed / no errors |
| `composer audit` / `composer validate` | No advisories / valid, lock in sync |
| `npm audit --omit=dev` | 0 vulnerabilities (dev-only advisories as in §12) |
| Vite build | Succeeds; output identical to committed `public/build` |
| Markdown links and anchors | All resolve |
| README commands | Every Artisan command and Composer/npm script named exists; `clinic:verify-copy` and `clinic:send-review-requests --dry-run` run cleanly |
