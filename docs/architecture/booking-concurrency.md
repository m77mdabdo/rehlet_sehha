# Booking concurrency: what `slot_key` guarantees

## The problem

Two people load the booking page, both see Saturday 14:00 as free, and both
submit within the same second. The obvious defence — check whether the slot is
taken, then insert — does not work:

```
request A: SELECT ... WHERE starts_at = '14:00'   -> 0 rows, slot is free
request B: SELECT ... WHERE starts_at = '14:00'   -> 0 rows, slot is free
request A: INSERT                                  -> ok
request B: INSERT                                  -> ok
```

Both reads happen before either write. There is no amount of PHP that closes
that window, because the window is between two statements. The only arbiter
that sees both requests is the database.

## What we do

MySQL has no partial (filtered) unique indexes, so we cannot express:

```sql
UNIQUE (staff_id, starts_at) WHERE status <> 'cancelled'
```

What MySQL *does* give us is that a unique index permits an unlimited number of
NULLs. So the occupancy of a slot is encoded into one nullable column,
`appointments.slot_key`:

| state | `slot_key` |
|---|---|
| holding the slot | `"{staff_id}-{starts_at}"`, e.g. `3-2026-09-01 11:00:00` |
| released | `NULL` |

`slot_key` carries a `UNIQUE` index. A second attempt to book an occupied slot
fails with SQLSTATE 23000 at the database, which Laravel surfaces as
`Illuminate\Database\UniqueConstraintViolationException` (a `QueryException`).
The loser of the race gets an exception; nobody gets a double booking.

The key is derived — never user input. It is maintained by
`Appointment::syncSlotKey()`, wired into the model's `booted()` hook, and is
deliberately **absent from `$fillable`** so that a request body containing
`slot_key: null` cannot free an occupied slot.

### When the key is written and cleared

| event | effect |
|---|---|
| `saving` | key recomputed from `staff_id` + `starts_at` |
| status becomes `cancelled` | key set to `NULL` — slot immediately rebookable |
| status becomes `no_show` | key **retained** — see below |
| soft delete | key set to `NULL` |
| restore | key recomputed; fails if someone took the slot meanwhile |
| reschedule | key moves; the vacated hour becomes bookable |

Soft delete must clear the key. The unique index does not care that a row is
soft-deleted, so without that hook a deleted appointment would hold its hour
hostage forever with no way to reclaim it.

A no-show deliberately keeps its key. It records that the clinic's hour was
already consumed; freeing a past slot would let a second appointment be written
into an hour that is already accounted for.

`staff_id` is nullable, and an unassigned appointment collapses to key
`0-<time>`. With a single practitioner that is the correct reading: an
unassigned booking still consumes her hour.

## What this does NOT guarantee

**`slot_key` prevents two appointments from SHARING A START INSTANT. It does not
prevent them from OVERLAPPING.**

A 90-minute appointment at 10:00 and a 60-minute appointment at 11:00 produce
two different keys and both insert happily, even though one practitioner cannot
be in both.

Today no overlap is possible, but only by arithmetic accident: every service is
25 or 45 minutes, the working-hours grid is 60 minutes, and slots are generated
on the hour — so consecutive slots never collide. That is an invariant nothing
in the schema states.

It is therefore enforced in two places:

1. **`Service::guardAgainstOutgrowingTheSlotGrid()`** — a `saving` hook that
   refuses to store an active service longer than the shortest active
   `working_hours.slot_minutes`. This covers the application path, including any
   admin panel built later.
2. **`tests/Feature/SlotGridInvariantTest.php`** — asserts the same property
   across the seeded database, catching rows that arrive by migration, raw SQL,
   or a seeder that bypasses the model.

## What must change before you can relax it

Replace `slot_key` with a real range check the moment any of these becomes true:

- **a service longer than the slot grid** (a 90-minute intensive consultation);
- **half-hour or arbitrary start times**, so appointments no longer align to a
  common grid;
- **admin drag-and-drop rescheduling**, which can drop an appointment at any
  offset;
- **more than one appointment per practitioner per slot** (group sessions), for
  which a uniqueness constraint is the wrong shape entirely.

The replacement is a transactional overlap check on the half-open interval
`[starts_at, ends_at)`:

```php
DB::transaction(function () use ($staffId, $startsAt, $endsAt) {
    $conflict = Appointment::query()
        ->where('staff_id', $staffId)
        ->whereNotIn('status', ['cancelled'])
        // half-open: an appointment ending exactly at 11:00 does not
        // conflict with one starting at 11:00
        ->where('starts_at', '<', $endsAt)
        ->where('ends_at', '>', $startsAt)
        ->lockForUpdate()          // SELECT ... FOR UPDATE
        ->exists();

    if ($conflict) {
        throw new SlotUnavailableException;
    }

    return Appointment::create([...]);
});
```

Three things make that correct, and all three are required:

- **`lockForUpdate()`** takes next-key locks over the scanned range under
  InnoDB's default REPEATABLE READ, so a concurrent transaction inserting into
  that range blocks rather than races. A plain `SELECT` would not.
- **An index on `(staff_id, starts_at)`** keeps the locked range narrow. Without
  one the scan locks far more of the table than intended and turns booking into
  a serialisation bottleneck.
- **The half-open comparison** (`<` and `>`, not `<=`/`>=`) so back-to-back
  appointments are legal.

Keep the `slot_key` column and its unique index when you make that change. The
range check is the primary defence; the unique index remains a cheap backstop
against a code path that forgets to take the lock.

## Related

- `database/migrations/*_create_appointments_table.php` — the column and index
- `app/Models/Appointment.php` — `syncSlotKey()` and the `booted()` hooks
- `app/Models/Service.php` — the duration guard
- `tests/Feature/AppointmentSlotKeyTest.php` — the behavioural proof
