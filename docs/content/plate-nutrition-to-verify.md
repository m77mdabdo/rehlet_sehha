# Plate nutrition — values to verify

> **Generated file.** `php artisan clinic:export-review-docs`. Edit the values in
> `database/seeders/PlateFoodSeeder.php`, not this document.

33 foods. **33 awaiting sign-off, 0 signed off.**

## Why this document exists

The plate builder showed no numbers at all until 2026-10-12, by design and
with a test enforcing it. The practitioner decided an honest energy estimate,
kept secondary to the proportion feedback and free of any target, is more
useful to her patients than silence. This sheet is the condition attached to
that decision.

**Every figure below was drafted without internet access, from memory of what
USDA FoodData Central and the Egyptian food composition tables say.** That is
a legitimate way to draft and an indefensible way to publish. A plausible
calorie figure on a clinic's homepage, under a licensed practitioner's name,
borrows a nutritional authority's credibility for a number that authority may
never have published.

So nothing here reaches a patient until it is checked. A food awaiting
sign-off still appears on the plate and still counts towards the proportion
feedback — it simply carries no estimate and is left out of the total. With
every food pending, the feature behaves exactly as it did before the change.

## What verifying means here

Not "is this number plausible" — it is, that is why it was easy to draft.
The question is **does the named source actually say this, for this portion**.

Three outcomes, all useful:

1. **Confirmed.** Tick the box, record the edition or FDC ID in the source
   note, and set `needs_review => false` with your name against it.
2. **The source says something different.** Replace the span with what it
   actually says. The span may get wider; that is fine and usually better.
3. **The source does not cover this food.** Either name a different source
   or leave the food with no estimate. A food with no figure is a working
   part of this tool, not a gap.

## Why every value is a span and never a single figure

The unit is a household measure — a spoon, a cup, a baladi loaf. A spoon is
not a calibrated volume, so anything derived from one is a range before the
food itself varies at all. `PlateFood` refuses to save a row where the
minimum equals the maximum, so a single figure cannot enter the database even
by accident.

The four composite dishes — كشري، مسقعة، ملوخية، كرنب محشي — carry
deliberately wide spans. Nobody measures the oil in molokhia or the pasta in
koshari. Those are the rows where the span is the whole point: a
tight-looking number for a dish nobody measures is worse than an honest one.

## Two foods flagged for a decision, not just a check

- **بطاطس** spans 110–350 because boiled potatoes and chips are the same chip
  on this screen. You may want them as two foods.
- **مكسرات** is "nuts" generally; almonds and cashews are not interchangeable
  figures. You may want to name one.

---

## خضار  (vegetable)

### [ ] 🥗 سلطة خضراء — Green salad

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **25–60** |
| Protein (g) | 1–3 |
| Carbs (g) | 4–9 |
| Fat (g) | 0–3 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> Mixed leaf and tomato salad, undressed at the low end; the upper end allows a spoon of oil.

### [ ] 🌿 ملوخية — Molokhia

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **60–180** |
| Protein (g) | 3–6 |
| Carbs (g) | 6–12 |
| Fat (g) | 2–13 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> COMPOSITE DISH — span is wide on purpose. The whole variation is the ghee or oil, which is added by eye and differs by household more than the leaf does.

### [ ] 🥒 طرشي — Torshi (pickles)

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **10–30** |
| Protein (g) | 0–2 |
| Carbs (g) | 2–6 |
| Fat (g) | 0–1 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> Pickled turnip and carrot, drained. Energy is negligible; salt is the thing that matters clinically and this tool does not discuss it.

### [ ] 🍆 مسقعة — Mesaqaa (aubergine)

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **180–400** |
| Protein (g) | 3–7 |
| Carbs (g) | 12–25 |
| Fat (g) | 12–30 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> COMPOSITE DISH — span is wide on purpose. Aubergine absorbs frying oil in amounts that vary enormously with pan temperature and whether it was fried or oven-baked.

### [ ] 🥬 كرنب محشي — Stuffed cabbage

| | |
|---|---|
| Portion | one piece, default 3 |
| **Energy, per piece** | **45–90** |
| Protein (g) | 1–3 |
| Carbs (g) | 7–14 |
| Fat (g) | 1–3 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> COMPOSITE DISH — span is wide on purpose, and this is PER ROLL. Rice-to-cabbage ratio and the amount of samna differ by household.

### [ ] 🍅 طماطم وخيار — Tomato and cucumber

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **20–40** |
| Protein (g) | 1–2 |
| Carbs (g) | 4–8 |
| Fat (g) | 0–1 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> Plain, undressed.

## بروتين  (protein)

### [ ] 🫘 فول مدمس — Foul medames

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **120–260** |
| Protein (g) | 8–14 |
| Carbs (g) | 17–30 |
| Fat (g) | 2–12 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> The span is the oil. Foul served dry and foul served with a generous spoon of oil on top are the same dish to a patient and very different figures.

### [ ] 🧆 طعمية — Taameya

| | |
|---|---|
| Portion | one piece, default 3 |
| **Energy, per piece** | **55–95** |
| Protein (g) | 2–4 |
| Carbs (g) | 5–9 |
| Fat (g) | 3–7 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER PIECE. Deep fried, so the span follows oil uptake and how thick the piece was pressed.

### [ ] 🍗 فراخ مشوية — Grilled chicken

| | |
|---|---|
| Portion | one piece, default 1 |
| **Energy, per piece** | **140–260** |
| Protein (g) | 22–32 |
| Carbs (g) | 0–2 |
| Fat (g) | 4–15 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> One grilled quarter. The span covers breast against thigh and whether the skin is eaten.

### [ ] 🐟 سمك بلطي — Tilapia

| | |
|---|---|
| Portion | one piece, default 1 |
| **Energy, per piece** | **110–200** |
| Protein (g) | 20–28 |
| Carbs (g) | 0–1 |
| Fat (g) | 2–9 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> One medium grilled fish. Fried rather than grilled would be higher than this span allows; flagged for the practitioner to decide whether to split the two.

### [ ] 🥚 بيض مسلوق — Boiled eggs

| | |
|---|---|
| Portion | one whole, default 2 |
| **Energy, per whole** | **70–80** |
| Protein (g) | 6–7 |
| Carbs (g) | 0–1 |
| Fat (g) | 5–6 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER EGG, boiled, so no cooking fat. The tightest span in the file, because a medium egg genuinely is a standard measure — but still a span, since egg sizes differ.

### [ ] 🍖 لحمة بتلو — Veal

| | |
|---|---|
| Portion | one piece, default 1 |
| **Energy, per piece** | **150–280** |
| Protein (g) | 20–30 |
| Carbs (g) | 0–2 |
| Fat (g) | 6–18 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> One cut, trimmed at the low end and untrimmed at the high end.

## نشويات  (starch)

### [ ] 🥖 عيش بلدي — Baladi bread

| | |
|---|---|
| Portion | one loaf, default 1 |
| **Energy, per loaf** | **180–260** |
| Protein (g) | 6–9 |
| Carbs (g) | 36–52 |
| Fat (g) | 1–3 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER LOAF. Baladi loaves are not a standard weight; the span covers the sizes actually sold rather than a nominal figure.

### [ ] 🍚 رز أبيض — White rice

| | |
|---|---|
| Portion | one cup, default 1 |
| **Energy, per cup** | **190–250** |
| Protein (g) | 4–6 |
| Carbs (g) | 40–53 |
| Fat (g) | 0–4 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> Cooked, per cup. The upper end allows the samna or oil rice is usually cooked with here.

### [ ] 🍝 مكرونة — Pasta

| | |
|---|---|
| Portion | one cup, default 1 |
| **Energy, per cup** | **190–260** |
| Protein (g) | 6–9 |
| Carbs (g) | 38–50 |
| Fat (g) | 1–5 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> Cooked, per cup, sauce not included.

### [ ] 🥔 بطاطس — Potatoes

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **110–350** |
| Protein (g) | 2–5 |
| Carbs (g) | 25–45 |
| Fat (g) | 0–18 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> ONE CHIP ON PURPOSE — practitioner decision, 2026-10-12. The widest non-composite span in the file and the point of it: boiled potatoes and chips are the same food to a patient choosing a chip on this screen, and the span is honest about the difference rather than making her classify her own cooking. Do not split this into two foods.

### [ ] 🍲 كشري — Koshari

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **300–650** |
| Protein (g) | 10–20 |
| Carbs (g) | 55–110 |
| Fat (g) | 5–20 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> COMPOSITE DISH — the widest span in the file and the clearest case for having spans at all. Rice, pasta, lentils, chickpeas, fried onion and oil, in proportions nobody measures. A single figure here would be the most precise-looking and least true number in the feature.

## دهون  (fat)

### [ ] 🫒 زيت زيتون — Olive oil

| | |
|---|---|
| Portion | one spoon, default 1 |
| **Energy, per spoon** | **110–135** |
| Protein (g) | 0–0 |
| Carbs (g) | 0–0 |
| Fat (g) | 12–15 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER SPOON. The span is how full the spoon is, which is the only variable.

### [ ] 🫙 طحينة — Tahina

| | |
|---|---|
| Portion | one spoon, default 1 |
| **Energy, per spoon** | **85–110** |
| Protein (g) | 2–4 |
| Carbs (g) | 2–4 |
| Fat (g) | 7–10 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER SPOON, plain tahina paste rather than the thinned salad.

### [ ] 🥑 أفوكادو — Avocado

| | |
|---|---|
| Portion | one whole, default 0.5 |
| **Energy, per whole** | **110–180** |
| Protein (g) | 1–3 |
| Carbs (g) | 5–10 |
| Fat (g) | 10–17 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER HALF, which is why the default portion is a half rather than one.

### [ ] 🌰 لوز — Almonds

| | |
|---|---|
| Portion | one spoon, default 1 |
| **Energy, per spoon** | **55–75** |
| Protein (g) | 2–3 |
| Carbs (g) | 2–3 |
| Fat (g) | 5–7 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER SPOON, whole and unsalted.

### [ ] 🌰 عين جمل — Walnuts

| | |
|---|---|
| Portion | one spoon, default 1 |
| **Energy, per spoon** | **50–75** |
| Protein (g) | 1–2 |
| Carbs (g) | 1–2 |
| Fat (g) | 5–8 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER SPOON, halves. The lightest of the five by volume, which is why the span sits low despite walnuts being the fattiest here by weight.

### [ ] 🌰 كاجو — Cashews

| | |
|---|---|
| Portion | one spoon, default 1 |
| **Energy, per spoon** | **55–80** |
| Protein (g) | 2–3 |
| Carbs (g) | 3–4 |
| Fat (g) | 4–6 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER SPOON, unsalted. The highest carbohydrate of the five, which is the reason a patient might be asked to tell them apart at all.

### [ ] 🥜 فول سوداني — Peanuts

| | |
|---|---|
| Portion | one spoon, default 1 |
| **Energy, per spoon** | **65–85** |
| Protein (g) | 3–4 |
| Carbs (g) | 2–3 |
| Fat (g) | 5–7 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER SPOON, unsalted. The one most actually eaten here, and the densest per spoon because it packs tighter than the others.

### [ ] 🌰 بندق — Hazelnuts

| | |
|---|---|
| Portion | one spoon, default 1 |
| **Energy, per spoon** | **55–80** |
| Protein (g) | 1–2 |
| Carbs (g) | 1–2 |
| Fat (g) | 5–8 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER SPOON, whole and unsalted.

## فاكهة  (fruit)

### [ ] 🍊 برتقال — Orange

| | |
|---|---|
| Portion | one whole, default 1 |
| **Energy, per whole** | **50–85** |
| Protein (g) | 1–2 |
| Carbs (g) | 12–21 |
| Fat (g) | 0–1 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> One medium fruit; the span is fruit size.

### [ ] 🍉 بطيخ — Watermelon

| | |
|---|---|
| Portion | one small_plate, default 1 |
| **Energy, per small_plate** | **40–90** |
| Protein (g) | 1–2 |
| Carbs (g) | 10–22 |
| Fat (g) | 0–1 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> A plate of cut watermelon, which is how it is eaten here rather than by the slice.

### [ ] 🍌 موز — Banana

| | |
|---|---|
| Portion | one whole, default 1 |
| **Energy, per whole** | **90–130** |
| Protein (g) | 1–2 |
| Carbs (g) | 23–33 |
| Fat (g) | 0–1 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> One medium banana; the span is fruit size, which for bananas is large.

### [ ] 🌴 بلح — Dates

| | |
|---|---|
| Portion | one whole, default 3 |
| **Energy, per whole** | **20–30** |
| Protein (g) | 0–1 |
| Carbs (g) | 5–8 |
| Fat (g) | 0–1 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER DATE, dried. Fresh dates are lower and the practitioner may want them separate.

### [ ] 🥭 مانجو — Mango

| | |
|---|---|
| Portion | one whole, default 1 |
| **Energy, per whole** | **130–200** |
| Protein (g) | 1–3 |
| Carbs (g) | 32–50 |
| Fat (g) | 0–1 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> One fruit. Egyptian varieties differ in size considerably, hence the span.

## ألبان  (dairy)

### [ ] 🧀 جبنة قريش — Gebna qareesh

| | |
|---|---|
| Portion | one spoon, default 2 |
| **Energy, per spoon** | **25–40** |
| Protein (g) | 4–6 |
| Carbs (g) | 1–2 |
| Fat (g) | 0–2 |
| Source | Egyptian food composition tables (National Nutrition Institute) |
| Status | awaiting sign-off — **no estimate shown to patients** |

> PER SPOON. Low fat by nature, so the span is mostly how heaped the spoon is.

### [ ] 🥛 زبادي — Yoghurt

| | |
|---|---|
| Portion | one cup, default 1 |
| **Energy, per cup** | **100–160** |
| Protein (g) | 5–9 |
| Carbs (g) | 8–14 |
| Fat (g) | 2–8 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> Plain, per cup. The span covers full fat against low fat.

### [ ] 🍶 لبن — Milk

| | |
|---|---|
| Portion | one cup, default 1 |
| **Energy, per cup** | **100–150** |
| Protein (g) | 7–9 |
| Carbs (g) | 11–13 |
| Fat (g) | 2–8 |
| Source | USDA FoodData Central |
| Status | awaiting sign-off — **no estimate shown to patients** |

> Per cup. The span covers full fat against low fat.
