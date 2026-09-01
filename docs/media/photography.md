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

## Attribution

Pexels does not require attribution. It is recorded anyway, because **a licence
you cannot evidence is a licence you do not have** — and in three years nobody
will remember which of these came from where.

`php artisan clinic:fetch-pexels` writes `public/photos/inbox/candidates.json`
with the photographer, their profile URL, the source page, the Pexels id, the
download date and the library's own alt text for every candidate it downloads,
kept or rejected. That file is the record. Keep it with the originals.

### Article covers

One image per article, matched to that article's argument rather than to a
generic mood — the staples bowls belong to the piece that says a plan has to be
built from the food already in the house; the jug of water to the piece that
says most of a short-term scale reading is water; the cinnamon to the piece that
uses cinnamon as its worked example of a mechanism that is not an outcome.

| slug | article | photographer | Pexels id | downloaded |
| --- | --- | --- | --- | --- |
| `pantry-staples-overhead` | why-we-quit-in-week-three | Silviu Din | 17236198 | 2026-09-01 |
| `water-jug-morning-table` | what-the-scale-does-not-say | Diego Gonzalez | 14545871 | 2026-09-01 |
| `hands-slicing-tomato-board` | what-fix-your-diet-actually-means | Arina Krasnikova | 6653638 | 2026-09-01 |
| `phone-blank-screen-breakfast` | dietitian-versus-downloadable-plan | Marcus Aurelius | 9788841 | 2026-09-01 |
| `blood-tubes-rack` | normal-results-still-tired | Pavel Danilyuk | 8442021 | 2026-09-01 |
| `packing-papers-into-bag` | what-to-bring-to-a-first-appointment | Daniel & Hannah Snipes | 29359845 | 2026-09-01 |
| `cinnamon-bowl-dark` | pcos-and-food-judging-a-claim | Eva Bronzini | 5740404 | 2026-09-01 |
| `two-hands-blank-page` | questions-to-ask-about-hormones-and-food | Artem Podrez | 6787057 | 2026-09-01 |
| `eggs-cheese-bread-plate` | pregnancy-eating-myths | Nataliya Vaitkevich | 5605634 | 2026-09-01 |
| `vegetable-puree-small-bowls` | feeding-and-eating-recurring-questions | Katrin Bolovtsova | 5662126 | 2026-09-01 |
| `child-hands-untouched-plate` | the-child-who-will-not-eat | cottonbro studio | 6969724 | 2026-09-01 |
| `tuna-eggs-chickpea-bowl` | eating-around-training | Alesia Kozik | 6632285 | 2026-09-01 |
| `postpartum-kitchen-simple-meal` | postpartum-nutrition | Klaus Nielsen | 6287482 | 2026-08-31 |
| `diabetes-home-glucose-kitchen` | diabetes-in-women | Towfiqu barbhuiya | 12326657 | 2026-08-31 |

## Rejections, and why

### The article covers

Seventy-seven candidates were downloaded across fourteen searches and every one
was opened. What follows is what they were rejected for, because the reasons
repeat and the next person searching for the same thing will meet the same
pictures.

| candidate | rejected because |
| --- | --- |
| `egyptian-bread-lentils-table-2960581` | **legible text** — a newspaper and a printed brand name under the bowl |
| `child-plate-vegetables-*` (five of six) | identifiable children's faces. The face rule is at its strictest here: a stock licence does not carry a model release, and a recognisable child beside an article about a child who will not eat is a claim about that child |
| `baby-bowl-spoon-mashed-food-*` (four of six) | identifiable infants AND adults. The whole first search for the feeding article was unusable; it was re-run for food rather than for people |
| `tote-bag-notebook-table-18291381` | a legible Spanish book title. **Foreign-language material** — the rule that killed `2.mp4` |
| `hands-notebook-desk-conversation-5124868`, `-5124874` | an open book of legible printed paragraphs |
| `hands-notebook-desk-conversation-7731352`, `-8348763` | printed forms with legible text; the second also has identifiable people |
| `boiled-eggs-cheese-plate-breakfast-19409031` | **prosciutto**. Cured meat is on the caution list in the article this would have illustrated — an image contradicting our own text |
| `smartphone-…-36697522` | a chroma-key green screen, which reads as a mistake rather than as a phone |
| `smartphone-…-8440093` | a newspaper in frame, legible |
| `smartphone-…-7788433` | identifiable face |
| `laboratory-test-tubes-rack-8442376` | **legible printed labels on the sample tubes** |
| `laboratory-test-tubes-rack-8533087` | garish coloured liquids under purple light — reads as a school chemistry set, not a pathology lab |
| `cinnamon-sticks-…-1717771`, `-19665864`, `-9502233` | dried orange slices and pastry stars. Christmas styling, wrong register for a clinic |
| `lentils-chickpeas-eggs-protein-bowls-6187572`, `-6187593` | pink background. Same objection as the diabetes card below: "pink it" is not how this practice talks to women |
| `egyptian-bread-lentils-table-28010164` | a bakery interior, dim and industrial; the person is turned away but the frame is a workplace, not a kitchen |
| `boiled-eggs-cheese-plate-breakfast-5836619` | **chosen first, then dropped.** The strongest image of the set — grilled halloumi, halved boiled eggs, tomato and olives, every item on the safe side of the pregnancy article's own list. The patterned ceramic and two neighbouring plates put so much detail in frame that WebP missed the small variant's byte budget at the quality floor, and two successive crops did not recover it. A cover that cannot be served inside the performance budget is not a cover |

### The two staged earlier

Every candidate downloaded for the two new articles was opened and looked at.
The rejections are recorded because the reasons are reusable — the next person
searching a stock library for "glucometer" will meet the same six pictures.

| candidate | rejected because |
| --- | --- |
| `glucose-meter-home-table-17071581` | a **glucose value and a date on the screen**, and a legible label on the strip vial. A numeric readout is the one thing this clinic refuses to put in front of a reader anywhere else on the site |
| `glucose-meter-home-table-33200683` | legible brand name on the meter casing. Naming a device on a clinic page is an endorsement nobody agreed to |
| `glucose-meter-home-table-6823479`, `-6823491`, `-6823495`, `-6823496` | an identifiable man. Wrong on the face rule, and wrong for an article specifically about women |
| `glucose-meter-home-table-17043393` | **the best-composed candidate of all of them**, and rejected anyway: the photographer is a glucose-meter manufacturer. No brand was legible, but an article that spends two sections asking who is selling the claim should not be illustrated with a device maker's marketing photograph |
| `glucometer-lancet-…-6303708` | the words **WORLD DIABETES DAY** on a letter board. Legible English text — the rule that killed `2.mp4` |
| `glucometer-lancet-…-6940859`, `-6823670` | pink background with awareness ribbons. "Pink it" is not how this practice talks to women, and the second also had a legible vial label |
| `glucometer-lancet-…-5342563`, `-5342565`, `-5342566` | saturated green and orange product shots from a device retailer's account; one carries a legible brand |
| `home-kitchen-…-10432406` | an identifiable face |
| `home-kitchen-…-398259` | smoked salmon. Imported, aspirational, and **cold-smoked fish is on the list the pregnancy article tells readers to be careful with**. An image that contradicts our own text |
| `home-kitchen-…-3850924` | styled marble-and-candle flatlay. Reads as a food magazine, not as a kitchen somebody stands in at 2am |
| `home-kitchen-…-37923421` | dark, reads as a restaurant pass |
| `home-kitchen-…-936659` | chosen first, then dropped: the patterned tablecloths defeated WebP and **all three variants missed their byte budget** at the quality floor |
| `home-kitchen-…-89238` | usable, but arrived as a **PNG with a `.jpg` extension** — see the note below |

### Two things the fetch command got wrong

Both are small and both are worth fixing before the next fetch.

1. It saves whatever Pexels returns as `original` under a `.jpg` name without
   checking. One candidate was a 37 MB PNG called `.jpg`, which made
   `imagecreatefromjpeg()` fail on it. Sniff the type, or use the extension
   Pexels gives.
2. It downloads the `original` size unconditionally. That 37 MB was spent to
   look at one picture and throw it away.

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
