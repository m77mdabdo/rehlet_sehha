# Article plan

> **This document is now the PLAN, not the state.** It records what each piece is
> for, who it is aimed at and — most importantly — what it must not say. That
> part has not changed and still governs.
>
> What has changed is that the articles are written. There are **fourteen** of
> them, in `database/seeders/Articles/`, one class per article, 1,200–1,800 words
> in each language. Read the code for what an article says; read this file for
> why it exists and where its boundary is.
>
> Three companion documents:
>
> - [`clinical-prompts.md`](clinical-prompts.md) — the 44 questions left open for
>   Dr. Rana, generated from the articles
> - [`citations-to-verify.md`](citations-to-verify.md) — the 63 references, with
>   the claim each one supports
> - `docs/media/photography.md` — what was rejected from the imagery and why

## What "written" does and does not mean

**Written, and cited:** everything attributable to a published source. What the
ADA recommends. How insulin resistance develops. What a systematic review found.
Why a myth spreads and what the evidence says instead. Reporting a named body's
recommendation is *evidence*, not advice, and it does not need a clinician to
write it — it needs a citation, and every one of them has one.

**Left open, and gated:** two things, neither of which anybody else may fill in.

`CLINICAL_INPUT` marks a directed recommendation — a quantity, a target, an
instruction aimed at the reader. `PRACTITIONER_VOICE` marks a first-person
clinical observation, «في تجربتي»، «اللي بشوفه مع مرضايا», which is a claim about
what one named clinician has personally seen and cannot honestly be written by
anybody else.

`Post::booted()` refuses to publish an article containing either, refuses to
publish one with no named reviewer, and refuses to publish one carrying a
citation nobody has verified. `scopePublished()` refuses to serve such a row
even if it reaches the table by some other route.

**Everything in the boundary table below is still absent from every body**, and
that is asserted rather than assumed.

---

## The boundary, in full

These articles publish under a licensed practitioner's name, on the site of the
clinic a reader is about to book with. She will not read them as a blog. She
will read them as advice from her prospective clinician, and act on them.

So none of the following appears in any body on this site:

| Not this | Because |
|---|---|
| Target ranges — "aim for X", "should be under Y" | A number a reader can measure herself against is a target she can fail, set by somebody who has not seen her |
| Medication interactions, or anything about a dose | Not this practice's scope, and getting it wrong is the one mistake with a body count |
| "Eat X to lower Y" | A treatment claim. It is what the consultation is for |
| Portion sizes, gram weights, calorie figures | The same rule the plate builder enforces, for the same reason |
| Reference intervals for any lab value | A reader comparing her own result to a printed range is self-diagnosing |
| Before/after anything | Already banned site-wide |
| "Studies show", with no study | Either cite it or do not claim it |

What is left is still worth writing, and is in fact the harder and more useful
thing: **what a question means, why it is asked, and what happens when you bring
it to somebody.** An article that ends with "this is the conversation to have,
and here is how to prepare for it" has done its job.

### The test each outline has to pass

> Could a reader act on this article *instead of* booking, and come to harm?

If yes, the article is out of scope. If the honest answer is "she would need to
speak to somebody", the article is doing what it should.

---

## The fourteen

Mapped to the specialties that already exist in `specialties`. The first twelve
are below in their original planning form; the two the practitioner added later
are at the end.

### Medical nutrition — `medical-nutrition`

**1. لما الدكتور يقولك «ظبطي أكلك» — يعني إيه بالظبط؟**
*What your doctor means when they say "fix your diet"*

The single most common referral sentence in Egyptian practice, and the one that
sends people to the internet. This is the piece that says: it is not one
instruction, it is a different instruction for every condition, and here is why
nobody can tell you which one over the phone.

- What the sentence usually means when it comes from each kind of clinic
- Why the same words point at different changes for different people
- What a dietitian actually does with it that a search engine cannot
- What to bring to the first appointment
- *Never:* which change applies to which condition

**2. الفرق بين أخصائي التغذية والدايت أونلاين**
*What a clinical dietitian does that a diet plan does not*

Positioning, honestly argued rather than asserted. The comparison is with the
downloadable plan, not with another clinic.

- What a plan built from a template can and cannot account for
- What a history and a set of labs change about the answer
- Why follow-up is the part that does the work
- When a template is genuinely fine — say this plainly, it costs nothing
- *Never:* a claim about outcomes or a rate of success

### Lab review — `lab-review`

**3. تحاليلك «طبيعية» — طب ليه لسه تعبانة؟**
*Your results came back "normal" — so why do you still feel tired?*

The strongest piece on the list and the most dangerous to write. It is about
what a reference range *is*, not about any value.

- What a reference range is derived from, and what it therefore is not
- Why a trend over three tests says more than one result
- Why "normal" is a statistical statement, not a clinical one
- Why this is a conversation, not a calculation
- *Never:* a single lab name paired with a number, a range, or a symptom

**4. إيه اللي تجيبيه معاكي أول جلسة**
*What to bring to a first appointment*

Practical, entirely safe, and probably the most-read piece on the site.

- Which papers matter and roughly how far back
- What to write down in the week before
- Why an incomplete file is still worth bringing
- What happens in the session itself
- *Never:* which tests to go and get. Ordering tests is not this clinic's call

### Weight management — `weight-management`

**5. ليه الرجيم بينجح شهر وبعدين يقف؟**
*Why a diet works for a month and then stops*

Mechanism and expectation, no prescription.

- What the first weeks change that later weeks do not
- Why an early result is not a forecast
- What "the plan stopped working" usually turns out to mean
- Why the answer is a review rather than a stricter version of the same plan
- *Never:* a rate of loss, a timeline, or a number of any kind

**6. الميزان بيقول إيه — والحاجات اللي مش بيقولها**
*What the scale tells you, and the things it does not*

Directly downstream of the rule the plate builder already enforces.

- What a single reading includes besides the thing being measured
- Why the clinic follows energy, sleep and adherence alongside it
- Why weighing more often makes the picture worse, not clearer
- What is worth tracking instead
- *Never:* a target weight, a BMI figure, or a healthy-range claim

### PCOS and hormones — `pcos-hormonal`

**7. تكيس المبايض والأكل: إيه اللي الأدلة بتقوله وإيه اللي لأ**
*PCOS and food: what the evidence supports, and what it does not*

The highest-risk piece on the list. It is written to *dismantle* claims, not to
make one — the internet is already full of PCOS diets and the useful
contribution is a way to judge them.

- Why PCOS presents differently between two people with the same diagnosis
- What kinds of claims to be sceptical of, and how to test one
- Why a plan for this is built with a physician rather than instead of one
- What the clinic's role is alongside the treating doctor
- *Never:* a named diet, a supplement, a food to add or remove, an insulin claim
- **Requires an explicit second read before publishing, even by Dr. Rana's own standard**

**8. أسئلة تسأليها لدكتورتك عن التغذية والهرمونات**
*Questions worth asking your doctor about nutrition and hormones*

The safest possible treatment of this specialty: it hands the reader better
questions rather than answers. Genuinely useful and structurally incapable of
prescribing.

- How to ask what a diagnosis changes about eating
- How to ask whether a supplement is doing anything
- How to ask what to expect and by when
- Why writing the answers down matters
- *Never:* a suggested answer to any of the questions

### Pregnancy and lactation — `pregnancy-nutrition`

**9. الحمل والأكل: الخرافات اللي بتتقال في كل بيت**
*Eating in pregnancy: the things everyone tells you*

Cultural, specific, and correctable without prescribing — "you are eating for
two", "crave it and you need it", the foods relatives ban.

- Where each belief comes from
- Which are harmless, which are worth a conversation
- Why the answer is different in each trimester
- Who to ask, and when to ask them urgently
- *Never:* a food to eat or avoid, a supplement, a weight-gain figure
- **Obstetric referral line required in the body, not only the disclaimer**

**10. الرضاعة والأكل: الأسئلة اللي بتتكرر**
*Feeding and eating: the questions that keep coming up*

- What changes about appetite and thirst, and why
- Why "my milk is not enough" is a question for a clinician, not a diet
- What is worth raising at the postnatal visit
- Where the dietitian fits alongside the paediatrician
- *Never:* anything about supply, supplements, or infant intake

### Child nutrition — `child-nutrition`

**11. الطفل اللي «مش بياكل»**
*The child who "will not eat"*

- What this usually looks like when described in clinic
- Why mealtime pressure tends to make it worse
- What the paediatrician checks first, and why that comes first
- What a dietitian adds once that is done
- *Never:* portions, growth centiles, weight, or a feeding schedule
- **Paediatric referral line required in the body**

**12. الأكل في اللانش بوكس من غير حرب**
*School lunches without the argument*

The lightest piece on the list, and deliberately so — an all-clinical index is
its own kind of intimidating.

- Why the box comes home full
- Building it around what the child already accepts
- Why involving them in the choice changes the outcome
- What is worth mentioning to the paediatrician
- *Never:* a menu, a portion, or a nutrient target

### Sports nutrition — `sports-nutrition`

**13. الأكل حوالين التمرين: إيه اللي بيتقال وإيه اللي بيتطبق**
*Eating around training: what gets said and what actually applies*

- Why most of what circulates is written for competitive athletes
- What changes when training is three times a week rather than daily
- Why supplements are the most-asked and least-useful question
- When this is worth an appointment at all — including "it usually is not"
- *Never:* protein figures, timing windows, a named supplement

---

## The two the practitioner added

These were her own picks, and both fill gaps the original twelve left.

**13. التغذية بعد الولادة — للأم في أول ستة شهور**
*Postpartum nutrition — the mother in the first six months* — `postpartum-nutrition`,
filed under `pregnancy-nutrition`.

Everything written for this period is written about the baby. The gap is the
mother: a requirement that rises above pregnancy at the point when preparing
food is hardest, an iron store drawn on twice — through nine months and again at
delivery — and exhaustion that gets attributed to having a newborn and never
investigated. Carries the postpartum glucose screening after gestational
diabetes, which the ADA recommends and which is almost never offered here.

- *Never:* a dose, a supplement recommendation, or a weight target in this period

**14. السكري عند الستات — إيه اللي بيختلف**
*Diabetes in women — what is different* — `diabetes-in-women`, filed under
`medical-nutrition` and linking to `pcos-hormonal` as well.

Everything written about diabetes is written for a generic patient, and that
patient has no menstrual cycle, no pregnancy, no postpartum period and no
menopause. Genuinely under-covered in Arabic. Maps onto two specialties at once,
which is why it links to both.

- *Never:* a glucose target, an HbA1c figure, or advice about fasting for an
  individual — the Ramadan section reports that specialist guidance exists and
  says the decision is made with the treating doctor

## Two more, if sixteen is wanted

**ليه العيادة أونلاين، وإيه اللي بيحصل في الجلسة**
*Why the practice is online, and what a session actually is* — service
explanation rather than clinical content. Zero risk, and it answers the question
the contact page gets asked most.

**إزاي تقري ادعاء عن التغذية على السوشيال ميديا**
*How to read a nutrition claim on social media* — media literacy. Partly
absorbed already: the PCOS article now carries a six-question method for judging
a claim, and the sports article applies it to supplements. A standalone piece
would still work.

---

## The specialty with no article

`corporate-wellness` is a service, not a condition, and an article about it
would be a sales page wearing an article's clothes.

The remaining coverage is deliberately shallow rather than accidentally so — the
safest article in each specialty has been taken first. Adding a second piece to
a specialty already covered is a better next step than reaching for a topic that
cannot be written without prescribing.

---

## Before any of these publishes

1. **Answer the prompts.** [`clinical-prompts.md`](clinical-prompts.md) — 44
   questions, each answerable in a sentence or two. Nobody else may write these.
2. **Verify the citations.** [`citations-to-verify.md`](citations-to-verify.md) —
   63 references, 41 of them marked as needing a detail confirmed. Start there.
3. Somebody re-reads the body against the boundary table at the top of this file.
4. `reviewed_by` and `reviewed_at` are set in the admin — doctor or admin only.
5. `published_at` gets a date.

Steps 1, 2 and 4 are enforced by the model rather than by this list, so they
cannot be done out of order and cannot be skipped by anybody in a hurry.

The piece marked **requires a second read** and the two marked **referral line
required** carry that requirement because of what a reader might do with a wrong
sentence, not because of how hard they are to write.
