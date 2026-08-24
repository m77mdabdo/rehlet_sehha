# Photography

## The rule

**No identifiable face appears beside condition-specific content.**

Every photograph on this site is licensed stock. A stock licence permits
commercial use; it does **not** guarantee a model release. Placing a
recognisable person next to diabetes or hypertension content is a statement
that this person has that condition — which is exactly the "sensitive use" a
release covers, and we can evidence none for any of these images.

The cost of the rule is close to zero. Hands, devices, cropped torsos and food
carry the clinical meaning perfectly well, and the strongest images in the
library never showed a face. The cost of breaking it is a real person,
findable by reverse image search, implied to have a disease.

It is enforced, not remembered: `config/photos.php` marks `faces` on every
entry and `clinic:process-photos` refuses to run if one is ever `true`.
`PhotoLibraryTest` fails too.

If a photograph of the practitioner, or of a real consenting patient, is ever
added — that is not stock, does not belong in this library, and needs its
release stored with it.

## The off-message rule

The same rule that trimmed the weighing scale and the measuring tape out of
the hero video applies to stills. **No weighing scales, tape measures, calorie
displays, gym equipment, or before/after body comparisons.**

Ten of the twenty-eight images supplied were rejected. Seven of those ten were
the same photograph taken different ways: a tape measure round a waist, a
scale with a number on it, a body being appraised. That is what stock
libraries return for "nutrition" and "obesity", and it is precisely what this
clinic positions against.

Every rejection is listed with its reason in the `rejected` block of
`config/photos.php`, so nobody re-adds one in six months not knowing it was
already considered. The command refuses to process anything named there.

**If you are searching for more images**, search the conditions and the
*devices* — "glucometer", "blood pressure cuff", "pharmacy", "vegetable
market", "kitchen". Never "weight loss" or "diet"; those queries return the
rejected set.

## Where the originals live

`public/photos/` is **gitignored**. It holds the full-resolution stock
originals (~48 MB) and is not served.

> **Originals are held outside this repository.** Ask the clinic owner for the
> current location — they were supplied directly and are not in any cloud
> bucket this project controls.
>
> `TODO_COPY — record the agreed storage location for the photography
> originals here, plus the licence receipts.`

You do **not** need the originals to build, deploy or run the site. The
processed set under `public/media/` is committed and is what the site serves.
You only need them to add a new image or change a crop.

## Adding or changing an image

1. Put the original in `public/photos/`.
2. Add an entry to `library` in `config/photos.php` — slug, source filename,
   topic, `faces => false`, an optional `crop`, and a `describes` note of what
   is factually in the frame.
3. Run `php artisan clinic:process-photos`.
4. Commit the new files under `public/media/`.

`describes` is **not** the alt text. Alt text is bilingual copy and lives in
the translation files beside the section the image appears in, because a good
alt depends on what the surrounding text already says.

## Crops are rules, not taste

Four images are cropped. Three of the crops exist to enforce something:

| image | why |
|---|---|
| `pregnancy-bump` | the chin and mouth were in frame — the face rule |
| `infant-feeding-hands` | the infant's head was in frame — the face rule |
| `consultation-desk-wide` | two legible **Turkish** textbooks sat on the desk. A visitor who reads another country's clinic off our page has learned the photograph is not us, on a page whose job is trust |
| `consultation-meal-plan` | composition only — the lower third was empty desk |

The rectangles live in the manifest rather than being baked into a hand-edited
file, so the reason is written next to the decision and re-running the command
reproduces it exactly.

## Sizing

Variants are budgeted by **megapixel**, not by width.

The first version budgeted by width and it was wrong in the obvious way:
"1400 wide, at most 120 KB" charges a 1400×933 landscape (1.3 Mpx) and a
1400×2489 portrait (3.5 Mpx) the same. Two thirds of this library is portrait,
so four food images could not fit at any quality — not because they were badly
compressed, but because they were being asked to hold three times the pixels
for the same bytes.

| variant | pixels | bytes |
|---|---|---|
| `sm` | ≤ 0.35 Mpx | ≤ 45 KB |
| `md` | ≤ 0.8 Mpx | ≤ 85 KB |
| `lg` | ≤ 1.6 Mpx | ≤ 150 KB |

Quality is **searched**, not set: the command compresses down from q=80 until
the file fits its budget, and reports rather than silently shipping something
ugly if it cannot get there by q=50. A busy vegetable flat-lay and a plain
studio shot do not compress alike — at one fixed quality they were 237 KB and
32 KB.

Widths for the `srcset` are derived per image from its own aspect ratio, so a
tall portrait comes out narrower than a landscape at the same variant. That is
correct: it is the same amount of picture.
