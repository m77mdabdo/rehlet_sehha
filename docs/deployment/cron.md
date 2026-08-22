# Cron and the queue on Hostinger shared hosting

Everything the clinic sends is queued, and the queue is drained by cron. If the
cron entry below is missing, **no email is ever delivered** — bookings still
work, the confirmation screen still appears, and every row in
`notification_logs` sits at `queued` forever. That is the symptom to look for.

## The single cron entry

Add exactly one cron job in hPanel (Advanced → Cron Jobs), set to run **every
minute**:

```
* * * * * cd /home/USER/domains/rehletsehha.com && php artisan schedule:run >> /dev/null 2>&1
```

Replace `USER` with the hosting account username. Confirm the path against
hPanel's file manager before saving — `domains/rehletsehha.com` is where
Hostinger puts the site, but a subdomain or an addon domain sits elsewhere, and
a cron entry pointing at a directory that does not exist fails silently.

`cd` to the **project root**, not `public_html`. `artisan` lives in the root.

Do not add a second cron entry for anything else. Laravel's scheduler is the
one process that decides what runs when; adding a separate cron for, say,
reminders would give you two schedulers disagreeing about whether a job has
already run.

## What that entry drives

`routes/console.php` holds the schedule. As of this task:

| Entry | Frequency | Purpose |
| --- | --- | --- |
| `queue:work --stop-when-empty --max-time=50 --tries=3` | every minute | Delivers queued mail |
| `clinic:send-reminders` | every minute | 24-hour and 1-hour reminders |
| `clinic:send-daily-schedule` | 07:00 Cairo | The day's appointments, to the clinic |
| `clinic:verify-key` | 03:00 Cairo | APP_KEY canary |
| `model:prune` | 03:30 Cairo | Drops expired log rows |

Check it on the server with:

```
php artisan schedule:list
```

## Why `--stop-when-empty` and not a daemon

The usual Laravel advice is to run `queue:work` as a supervised daemon that
never exits. **Do not do that here.**

A persistent worker is a long-running background process. Hostinger's shared
hosting terms prohibit those, and the platform enforces it: the process reaper
kills long-lived PHP processes, and an account that keeps respawning them gets
flagged for resource abuse and can be **suspended**. Losing the clinic's
hosting to keep a mail queue warm is not a trade worth making.

So the worker is started by cron, works until the queue is empty, and exits:

- `--stop-when-empty` — exit as soon as there is nothing left, rather than
  sitting idle waiting for work.
- `--max-time=50` — exit after fifty seconds no matter what. The next cron tick
  is at sixty, and a worker still alive then would mean two workers competing
  for the same jobs. The ten seconds of headroom covers a slow SMTP handshake
  on the final job.
- `--tries=3` — matches `$tries` on the notifications. Set in both places
  because the flag governs the worker and the property governs the job, and a
  mismatch means the retry policy in the code is not the one in effect.

`->withoutOverlapping()` in the schedule is a second guard for the case where a
job hangs past `--max-time`.

The cost of this design is latency: a message is delivered within about a
minute of being queued rather than instantly. For a booking confirmation that
is invisible to the patient, who is still reading the confirmation screen.

## Verifying delivery after a deploy

```
php artisan schedule:list                 # the entries are registered
php artisan queue:work --stop-when-empty  # drain by hand once, watch for errors
php artisan tinker --execute="echo App\Models\NotificationLog::latest()->first()?->status;"
```

A healthy row goes `queued` → `sent` within a minute. A row stuck at `queued`
means the worker is not running. A row at `failed` carries the reason in the
`error` column.

## If the queue table gets stuck

Failed jobs land in `failed_jobs`. They are not retried automatically.

```
php artisan queue:failed        # list them
php artisan queue:retry all     # requeue
php artisan queue:flush         # discard
```

A patient-facing message that exhausts its retries also mails the clinic (see
`App\Notifications\PatientMailFailedAlert`), so a failed confirmation surfaces
to a human rather than only to this table.
