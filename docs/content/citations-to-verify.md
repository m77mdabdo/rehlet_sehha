# Citations to verify

> **Generated file.** `php artisan clinic:export-review-docs`. Edit the articles in
> `database/seeders/Articles/`, not this document.

63 citations across 14 articles.

## Why this document exists

These articles were drafted **without internet access**, from memory, and they
report what the ADA, WHO, NICE, ESHRE, AAP and Cochrane say. That is a
legitimate way to draft and an indefensible way to publish.

A fabricated reference in a medical article, under a licensed practitioner's
name, is worse than no article at all — it borrows an institution's authority to
make a claim that institution never made. And a plausible-looking citation is
the most convincing thing on a page, which is precisely why the uncertainty is
recorded per reference rather than waved at in a note nobody reads.

**No article publishes until every one of its citations is marked verified.**
That is not a convention. `Post::assertCitationsArePublishable()` throws,
`Citation` refuses to attach an unverified reference to a published article,
and `scopePublished()` will not serve one even if a row reaches the table by
some other route. `CitationGateTest` covers all four orders of operations.

## What verifying means here

Not "does this document exist" — that is the easy half and the less important
one. The question is **does this document say what the article says it says**,
which is why every entry below names the claim it is holding up.

Three outcomes, and all three are useful:

1. **Confirmed.** Record the URL and the edition, and tick it.
2. **Exists, says something different.** Say what it actually says. The
   sentence in the article changes, or goes.
3. **Cannot be found.** Delete the citation *and the sentence it supports*.
   Softening the sentence and keeping the reference is the one outcome that
   makes things worse.

## What was deliberately left out

No DOIs, no volume numbers, no page numbers, and no URLs. Those are the fields
a draft written from memory invents most convincingly — a fabricated DOI looks
more like evidence of checking than anything else on the page. An organisation,
a title and a year identify a guideline unambiguously and can be checked with a
search engine. The URL column below is for you to fill in once you have
actually opened the document.

Several numbers were also left out of the articles themselves for the same
reason: protein grams per kilogram, the postpartum glucose screening interval,
weekly activity minutes, anaemia thresholds, waist-to-height ratios. Where an
entry below says so, the figure should be added **from the document**, not from
anybody's memory.

---

## Not publishable — none

No citation in the set is marked `low`. A source the draft could not be confident
exists was left out rather than recorded, and `CitationGateTest` fails if one appears.

---

## Check these first — 41 to check

The draft is confident these documents EXIST. What may be wrong is a
detail — the edition year, the exact title, which of two documents from the
same body carries the statement, or whether the body named is really the one
that issued it. Two of these name no issuing body at all, deliberately,
because inventing one is exactly the failure this list exists to prevent.

**Start here.** This is where the article set is most likely to be wrong.


### National Institute for Health and Care Excellence (NICE)

**Behaviour change: individual approaches** · 2014

- **Appears in:** [Why we quit in week three](../../database/seeders/Articles/) — `why-we-quit-in-week-three`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). تغيير السلوك: المداخل الفردية
- **What it is being cited for:** Supports the list of what makes a change hold — planning, defined goals, self-monitoring, sustained support — and the claim that willpower is not among the components these interventions target. Confident the guidance exists; confirm the year and that these four components are the ones it names.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Association for the Study of Obesity (EASO)

**European Guidelines for Obesity Management in Adults** · 2015

- **Appears in:** [Why we quit in week three](../../database/seeders/Articles/) — `why-we-quit-in-week-three`
- **Arabic rendering:** الاتحاد الأوروبي لدراسة السمنة (EASO). الإرشادات الأوروبية لإدارة السمنة عند البالغين
- **What it is being cited for:** Supports the sentence on realistic goals and long-term follow-up mattering more than initial intensity. Confident such a document exists under EASO; confirm the exact title and year, and that this emphasis is stated rather than inferred.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Waist Circumference and Waist–Hip Ratio: Report of a WHO Expert Consultation** · 2008

- **Appears in:** [What the scale says, and what it does not](../../database/seeders/Articles/) — `what-the-scale-does-not-say`
- **Arabic rendering:** منظمة الصحة العالمية. محيط الخصر ونسبة الخصر إلى الورك: تقرير مشاورة خبراء
- **What it is being cited for:** Supports the claim that fat distribution, not total mass alone, carries value in estimating weight-related risk. Confident the consultation and report exist; the year given is the consultation date and the report was published later — confirm which year should appear.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Eating disorders: recognition and treatment** · 2017

- **Appears in:** [What the scale says, and what it does not](../../database/seeders/Articles/) — `what-the-scale-does-not-say`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). اضطرابات الأكل: التعرّف والعلاج
- **What it is being cited for:** Supports the paragraph on frequent weighing being a clinically sensitive matter decided individually in people with disordered eating, rather than a general rule. Confident the guideline exists and covers weighing; confirm that it frames the decision as individual rather than prescribing a frequency.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Society for Clinical Nutrition and Metabolism (ESPEN)

**ESPEN guideline on definitions and terminology of clinical nutrition** · 2017

- **Appears in:** ["Sort your diet out" — what does that actually mean?](../../database/seeders/Articles/) — `what-fix-your-diet-actually-means`
- **Arabic rendering:** الجمعية الأوروبية للتغذية السريرية والأيض (ESPEN). دليل ESPEN لتعريفات ومصطلحات التغذية السريرية
- **What it is being cited for:** Supports "ESPEN produced a guideline devoted specifically to the definitions and terminology of clinical nutrition", used to establish that this is a field with a defined vocabulary. Confident the guideline exists; confirm the year and the exact title.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Type 2 diabetes in adults: management** · 2015

- **Appears in:** ["Sort your diet out" — what does that actually mean?](../../database/seeders/Articles/) — `what-fix-your-diet-actually-means`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). السكري من النوع الثاني عند البالغين: التعامل
- **What it is being cited for:** Supports "recommends that dietary advice be individual and delivered by somebody with competence in nutrition". Confident the guideline exists and covers dietary advice; the year given is the original publication and it has been updated repeatedly — confirm the current version and that the competence wording survives.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Obesity: identification, assessment and management** · 2014

- **Appears in:** [A downloadable plan and an individual one — where is the difference?](../../database/seeders/Articles/) — `dietitian-versus-downloadable-plan`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). السمنة: التعرّف والتقييم والتعامل
- **What it is being cited for:** Supports the section on very low calorie diets: limited place, not a general solution, and only within a broader programme under clinical supervision. Confident the guideline addresses very low calorie diets; confirm the supervision wording and that it survives in the current edition.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Type 2 diabetes in adults: management** · 2015

- **Appears in:** [A downloadable plan and an individual one — where is the difference?](../../database/seeders/Articles/) — `dietitian-versus-downloadable-plan`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). السكري من النوع الثاني عند البالغين: التعامل
- **What it is being cited for:** Supports "dietary advice should be individual, delivered by somebody with competence in nutrition". Original publication year given; the guideline has been updated repeatedly, so confirm the current version.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Association for the Study of Obesity (EASO)

**European Guidelines for Obesity Management in Adults** · 2015

- **Appears in:** [A downloadable plan and an individual one — where is the difference?](../../database/seeders/Articles/) — `dietitian-versus-downloadable-plan`
- **Arabic rendering:** الاتحاد الأوروبي لدراسة السمنة (EASO). الإرشادات الأوروبية لإدارة السمنة عند البالغين
- **What it is being cited for:** Supports "individual assessment, realistic goals, sustained follow-up". Confirm the exact title and year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**WHO guideline on use of ferritin concentrations to assess iron status** · 2020

- **Appears in:** [My results are normal and I am still exhausted](../../database/seeders/Articles/) — `normal-results-still-tired`
- **Arabic rendering:** منظمة الصحة العالمية. دليل منظمة الصحة العالمية بشأن استخدام تركيزات الفيريتين لتقييم حالة الحديد
- **What it is being cited for:** Supports "the WHO has issued guidance devoted specifically to using ferritin concentrations to assess iron status". Confident such a guideline exists; confirm the exact title and year, and whether it covers both individuals and populations.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Vitamin B12 deficiency in over 16s: diagnosis and management** · 2024

- **Appears in:** [My results are normal and I am still exhausted](../../database/seeders/Articles/) — `normal-results-still-tired`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). نقص فيتامين ب١٢ عند من هم فوق ١٦ سنة: التشخيص والتعامل
- **What it is being cited for:** Supports "NICE has produced guidance devoted to B12 deficiency in adults, and treats diagnosis as combining the result with the clinical picture". This is a recent guideline and the year is the detail most likely to be wrong — confirm both that it exists and its publication year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Vitamin D: supplement use in specific population groups** · 2014

- **Appears in:** [My results are normal and I am still exhausted](../../database/seeders/Articles/) — `normal-results-still-tired`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). فيتامين د: استخدام المكملات في فئات سكانية محددة
- **What it is being cited for:** Supports the existence of published guidance on groups more likely to be vitamin D deficient. No dose or threshold is quoted. Confirm title and year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Thyroid Association (ETA)

**Guidelines for the Management of Subclinical Hypothyroidism** · 2013

- **Appears in:** [My results are normal and I am still exhausted](../../database/seeders/Articles/) — `normal-results-still-tired`
- **Arabic rendering:** الجمعية الأوروبية للغدة الدرقية (ETA). إرشادات التعامل مع قصور الغدة الدرقية تحت الإكلينيكي
- **What it is being cited for:** Supports "the European Thyroid Association has guidance devoted to managing subclinical hypothyroidism". Confident such guidance exists; confirm the year and whether a newer edition supersedes it.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Coeliac disease: recognition, assessment and management** · 2015

- **Appears in:** [My results are normal and I am still exhausted](../../database/seeders/Articles/) — `normal-results-still-tired`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). الداء البطني: التعرّف والتقييم والتعامل
- **What it is being cited for:** Supports "NICE recommends considering coeliac disease in situations including unexplained anaemia and chronic fatigue". Confirm that both of those appear in the guideline's list of situations prompting testing.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### Egypt Demographic and Health Survey

**Egypt Demographic and Health Survey** · 2014

- **Appears in:** [My results are normal and I am still exhausted](../../database/seeders/Articles/) — `normal-results-still-tired`
- **Arabic rendering:** المسح الصحي الديموغرافي لمصر. المسح الصحي الديموغرافي لمصر
- **What it is being cited for:** Supports only the weak claim that iron deficiency and anaemia among Egyptian women are tracked in national health surveys. NO PREVALENCE FIGURE IS QUOTED, deliberately — the figure is what a draft written from memory would get wrong. If a number is wanted here, take it from the survey itself and add it with the edition year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Society for Clinical Nutrition and Metabolism (ESPEN)

**ESPEN guideline on definitions and terminology of clinical nutrition** · 2017

- **Appears in:** [What to bring to a first appointment](../../database/seeders/Articles/) — `what-to-bring-to-a-first-appointment`
- **Arabic rendering:** الجمعية الأوروبية للتغذية السريرية والأيض (ESPEN). دليل ESPEN لتعريفات ومصطلحات التغذية السريرية
- **What it is being cited for:** Supports "assessment is a distinct step preceding intervention". Confirm the year and that the guideline defines assessment as a separate stage.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Medicines optimisation** · 2015

- **Appears in:** [What to bring to a first appointment](../../database/seeders/Articles/) — `what-to-bring-to-a-first-appointment`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). الاستخدام الأمثل للأدوية
- **What it is being cited for:** Supports "recommends reconciling the medication list at any transition of care". Confident NICE guidance on medicines optimisation exists and covers reconciliation; confirm the title and year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### United States Food and Drug Administration (FDA)

**Safety communication: biotin interference with laboratory tests** · 2017

- **Appears in:** [What to bring to a first appointment](../../database/seeders/Articles/) — `what-to-bring-to-a-first-appointment`
- **Arabic rendering:** إدارة الغذاء والدواء الأمريكية (FDA). تنبيه سلامة: تداخل البيوتين مع تحاليل المختبر
- **What it is being cited for:** Supports the whole biotin section, which is the most specific claim in this article and therefore the one most worth checking. Confident the FDA issued a communication on biotin interference and that thyroid immunoassays are among those affected; confirm the year, the exact title, and whether it has been updated. If the detail cannot be confirmed, the section should be cut rather than softened — a half-remembered warning about test interference is worse than none.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### Cochrane

**Inositol for subfertile women with polycystic ovary syndrome** · *no year given*

- **Appears in:** [PCOS and food — how to judge a claim](../../database/seeders/Articles/) — `pcos-and-food-judging-a-claim`
- **Arabic rendering:** مكتبة كوكرين. الإينوزيتول للنساء المصابات بمتلازمة تكيس المبايض وضعف الخصوبة
- **What it is being cited for:** Supports the paragraph describing the inositol evidence as real but limited. Confident a Cochrane review on inositol in PCOS exists; NO YEAR IS GIVEN because Cochrane reviews are updated and citing a superseded version would be worse than citing none. Confirm the current version, its year, and that its conclusion is genuinely one of limited or uncertain evidence rather than a positive finding — if the review is more favourable than the article implies, the paragraph must change.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Coeliac disease: recognition, assessment and management** · 2015

- **Appears in:** [PCOS and food — how to judge a claim](../../database/seeders/Articles/) — `pcos-and-food-judging-a-claim`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). الداء البطني: التعرّف والتقييم والتعامل
- **What it is being cited for:** Supports the sentence that a gluten-free diet has no bearing on PCOS unless coeliac disease has been diagnosed — cited for what a gluten-free diet is actually indicated for. Confirm the guideline year and current status.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Society of Human Reproduction and Embryology (ESHRE) and partner bodies

**International evidence-based guideline for the assessment and management of polycystic ovary syndrome** · 2023

- **Appears in:** [Questions to ask about hormones and food](../../database/seeders/Articles/) — `questions-to-ask-about-hormones-and-food`
- **Arabic rendering:** الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE) والجهات المشاركة. الدليل الدولي المبني على الأدلة لتقييم وإدارة متلازمة تكيس المبايض
- **What it is being cited for:** Carries two specific claims: (1) the recommendation AGAINST using ultrasound for diagnosis in the years shortly after menarche, and (2) AMH as a possible alternative to ultrasound in adults. Both are genuinely useful and both are specific enough to be got wrong — in particular, the article deliberately does not state the number of years after menarche, and if the guideline gives one it should be added at verification. Marked medium rather than high for that reason, despite the guideline itself being certain.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Society of Human Reproduction and Embryology (ESHRE) and partner bodies

**International PCOS guideline — metabolic and psychological dimensions** · 2023

- **Appears in:** [Questions to ask about hormones and food](../../database/seeders/Articles/) — `questions-to-ask-about-hormones-and-food`
- **Arabic rendering:** الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE) والجهات المشاركة. الدليل الدولي لتكيس المبايض — الأبعاد الأيضية والنفسية
- **What it is being cited for:** Supports "recommends attention to markers such as blood glucose, lipids and blood pressure, and asking about mood and anxiety". Confident the guideline covers both cardiometabolic risk and psychological screening; confirm which specific markers it names and whether screening for depression and anxiety is a formal recommendation rather than a discussion point. No screening interval is stated in the article — add one only if the guideline gives it.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Folic acid supplementation for the prevention of neural tube defects** · *no year given*

- **Appears in:** [Pregnancy eating myths — and the advice that has a source](../../database/seeders/Articles/) — `pregnancy-eating-myths`
- **Arabic rendering:** منظمة الصحة العالمية. مكملات حمض الفوليك للوقاية من عيوب الأنبوب العصبي
- **What it is being cited for:** Supports the section on folic acid timing — that the neural tube closes very early and guidance therefore addresses periconceptional supplementation. Confident the physiological claim and the general recommendation; confirm the exact WHO document that carries it and add its year. NO DOSE is stated in the article.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Antenatal care** · 2021

- **Appears in:** [Pregnancy eating myths — and the advice that has a source](../../database/seeders/Articles/) — `pregnancy-eating-myths`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). الرعاية السابقة للولادة
- **What it is being cited for:** Supports the food-safety material: pasteurised dairy, thorough cooking, and avoiding liver because of preformed vitamin A. The liver recommendation is the most consequential single statement in this article — it contradicts advice given routinely in Egyptian households — so confirm which document carries it and cite that document specifically rather than this one if it sits elsewhere.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Listeriosis** · *no year given*

- **Appears in:** [Pregnancy eating myths — and the advice that has a source](../../database/seeders/Articles/) — `pregnancy-eating-myths`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: داء الليستريات
- **What it is being cited for:** Supports "listeria infection in pregnancy has recognised risks to the fetus even when the mother's own symptoms are mild". Confident of the clinical claim; confirm which WHO document states it and record its revision date.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Academy of Pediatrics (AAP)

**Breastfeeding and the Use of Human Milk — policy statement** · 2022

- **Appears in:** [Questions that keep coming up about feeding](../../database/seeders/Articles/) — `feeding-and-eating-recurring-questions`
- **Arabic rendering:** الأكاديمية الأمريكية لطب الأطفال (AAP). الرضاعة الطبيعية واستخدام لبن الأم — بيان سياسة
- **What it is being cited for:** Supports "the AAP recommends in the same direction and supports continued breastfeeding alongside complementary foods". Confident the policy statement exists and was updated around this year; confirm the year and how far its continued-breastfeeding recommendation extends, since the 2022 revision changed that specifically.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Guideline for complementary feeding of infants and young children** · 2023

- **Appears in:** [Questions that keep coming up about feeding](../../database/seeders/Articles/) — `feeding-and-eating-recurring-questions`
- **Arabic rendering:** منظمة الصحة العالمية. إرشادات بشأن التغذية التكميلية للرضع وصغار الأطفال
- **What it is being cited for:** Supports "complementary foods must be rich in the nutrients whose requirement rises at this stage, iron above all". Confident WHO guidance on complementary feeding exists; confirm the title and year, and that iron is named as a priority nutrient rather than being an inference.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Academy of Pediatrics (AAP)

**Guidance on the timing of introducing allergenic foods** · *no year given*

- **Appears in:** [Questions that keep coming up about feeding](../../database/seeders/Articles/) — `feeding-and-eating-recurring-questions`
- **Arabic rendering:** الأكاديمية الأمريكية لطب الأطفال (AAP). إرشادات حول توقيت إدخال الأطعمة المرتبطة بالحساسية
- **What it is being cited for:** Supports "current guidance discusses introducing these foods in their time rather than postponing them, because postponing does not reduce allergy and may do the opposite". Confident the direction of guidance changed and that the AAP reflects it; NO YEAR is given because this has been revised more than once. Confirm the current document — and note that the strongest evidence concerns peanut specifically, so if the guidance is narrower than the article implies, the paragraph must narrow with it.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Faltering growth: recognition and management** · 2017

- **Appears in:** [The child who will not eat](../../database/seeders/Articles/) — `the-child-who-will-not-eat`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). تعثّر النمو: التعرّف والتعامل
- **What it is being cited for:** Supports "approaches the question through the curve and change over time rather than the amount eaten at a meal", and underpins the red-flag list. Confirm the year, and check the guideline's own list of concerning features against the article's — the red-flag section is the part where an omission would matter most.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Academy of Pediatrics (AAP)

**Child feeding guidance — the division of responsibility in feeding** · *no year given*

- **Appears in:** [The child who will not eat](../../database/seeders/Articles/) — `the-child-who-will-not-eat`
- **Arabic rendering:** الأكاديمية الأمريكية لطب الأطفال (AAP). إرشادات تغذية الأطفال — تقسيم المسؤولية في الإطعام
- **What it is being cited for:** Supports "parents decide what, when and where; the child decides whether and how much". The principle is widely attributed to Ellyn Satter rather than originating with the AAP — the article says only that it appears in guidance including AAP material, which should be confirmed. If the AAP does not state it, attribute it to its actual source rather than to a body that merely echoes it.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Academy of Pediatrics (AAP)

**Iron deficiency in young children and cow's milk intake** · *no year given*

- **Appears in:** [The child who will not eat](../../database/seeders/Articles/) — `the-child-who-will-not-eat`
- **Arabic rendering:** الأكاديمية الأمريكية لطب الأطفال (AAP). نقص الحديد عند صغار الأطفال واستهلاك اللبن البقري
- **What it is being cited for:** Supports the milk section — the most immediately actionable claim in the article. Confident the relationship between high cow's milk intake and iron deficiency in toddlers is established and that the AAP addresses it; confirm which document, and add a volume limit only if the source states one. The article deliberately gives no quantity.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### Academy of Nutrition and Dietetics, Dietitians of Canada, and American College of Sports Medicine

**Joint Position Statement: Nutrition and Athletic Performance** · 2016

- **Appears in:** [Eating around training — what has evidence behind it](../../database/seeders/Articles/) — `eating-around-training`
- **Arabic rendering:** الأكاديمية الأمريكية للتغذية وأخصائيو التغذية في كندا والكلية الأمريكية للطب الرياضي. بيان موقف مشترك: التغذية والأداء الرياضي
- **What it is being cited for:** Supports "treats athletes' protein requirement as higher than the general requirement for inactive adults". Confident such a joint statement exists between these three bodies; confirm the year and the exact list of issuing organisations. NO FIGURE is quoted in the article — if a range is added later it must come from this document rather than from memory.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### International Society of Sports Nutrition (ISSN)

**Position stand: Protein and exercise** · *no year given*

- **Appears in:** [Eating around training — what has evidence behind it](../../database/seeders/Articles/) — `eating-around-training`
- **Arabic rendering:** الجمعية الدولية للتغذية الرياضية (ISSN). بيان موقف: البروتين والتمرين
- **What it is being cited for:** Supports the same claim from a second body, and underpins the anabolic-window section — the ISSN position stand addresses timing directly. No year given because the position stands are periodically updated. Confirm the current version and check specifically that it supports "total daily protein matters more than precise post-exercise timing", which is the article's claim.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### International Society of Sports Nutrition (ISSN)

**Position stand: Creatine supplementation in exercise and sport** · *no year given*

- **Appears in:** [Eating around training — what has evidence behind it](../../database/seeders/Articles/) — `eating-around-training`
- **Arabic rendering:** الجمعية الدولية للتغذية الرياضية (ISSN). بيان موقف: الكرياتين في التمرين والرياضة
- **What it is being cited for:** Supports "creatine is among the most studied supplements in sport and has a position stand devoted to it". Confirm the current version and year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### Published research on undeclared substances in sports supplements

**Analytical studies of the contents of commercially available sports supplements** · *no year given*

- **Appears in:** [Eating around training — what has evidence behind it](../../database/seeders/Articles/) — `eating-around-training`
- **Arabic rendering:** أبحاث منشورة عن تلوث المكملات الرياضية بمواد غير معلنة. دراسات تحليل محتوى المكملات الرياضية المتاحة تجاريًا
- **What it is being cited for:** THE WEAKEST CITATION IN THE ARTICLE AND THE STRONGEST CLAIM — this needs a specific named source before publication, not a description of a literature. The finding that a proportion of sports supplements contain undeclared stimulants and steroid derivatives is well established and has been documented repeatedly, but this entry names no single document. Replace it with one identifiable study or agency report (an anti-doping body publication would be ideal), or cut the section to what a named source will carry.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**WHO recommendations on maternal and newborn care for a positive postnatal experience** · 2022

- **Appears in:** [Postpartum nutrition — the mother in the first six months](../../database/seeders/Articles/) — `postpartum-nutrition`
- **Arabic rendering:** منظمة الصحة العالمية. توصيات منظمة الصحة العالمية بشأن رعاية الأم والمولود لتجربة إيجابية بعد الولادة
- **What it is being cited for:** Supports "treats the postnatal period as a period of care with its own requirements", and the sentence that postnatal guidance recommends asking about mental health as part of follow-up. Confident such a WHO document exists and was issued around this year; confirm the exact title and year, and that mental-health enquiry is a stated recommendation rather than background text.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Guideline: Iron supplementation in postpartum women** · 2016

- **Appears in:** [Postpartum nutrition — the mother in the first six months](../../database/seeders/Articles/) — `postpartum-nutrition`
- **Arabic rendering:** منظمة الصحة العالمية. إرشادات: مكملات الحديد للنساء بعد الولادة
- **What it is being cited for:** Supports "the WHO has guidance on iron supplementation in postpartum women". Cited only for the existence of dedicated guidance, not for any dose or duration — the article gives neither. Confirm the title and year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### Specialist guidance on thyroid disease in pregnancy and the postpartum period

**Guidelines for the diagnosis and management of thyroid disease during pregnancy and the postpartum** · *no year given*

- **Appears in:** [Postpartum nutrition — the mother in the first six months](../../database/seeders/Articles/) — `postpartum-nutrition`
- **Arabic rendering:** إرشادات متخصصة في أمراض الغدة الدرقية في الحمل وبعد الولادة. إرشادات تشخيص وإدارة أمراض الغدة الدرقية في الحمل وما بعد الولادة
- **What it is being cited for:** Supports the postpartum thyroiditis paragraph. NO ISSUING BODY IS NAMED because the draft was not confident which — the American Thyroid Association and the European Thyroid Association have both published in this area. Name the actual body and year at verification, or cut the paragraph: a clinical condition described without a traceable source is the weakest thing in this article.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Diabetes in pregnancy: management from preconception to the postnatal period** · 2015

- **Appears in:** [Diabetes in women — what is different](../../database/seeders/Articles/) — `diabetes-in-women`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). السكري في الحمل: التعامل من مرحلة ما قبل الحمل إلى ما بعد الولادة
- **What it is being cited for:** Supports "NICE has guidance devoted to diabetes in pregnancy from preconception through to the postnatal period" — cited essentially for its own scope, which the title states. Confirm the year and current status; it has been updated since original publication.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### Systematic reviews and meta-analyses of diabetes and cardiovascular risk by sex

**Sex differences in diabetes-associated cardiovascular risk** · *no year given*

- **Appears in:** [Diabetes in women — what is different](../../database/seeders/Articles/) — `diabetes-in-women`
- **Arabic rendering:** مراجعات منهجية وتحليلات تجميعية عن السكري وخطر أمراض القلب والأوعية الدموية حسب الجنس. الفروق بين الجنسين في الخطر القلبي الوعائي المرتبط بالسكري
- **What it is being cited for:** THE CLAIM MOST WORTH CHECKING IN THIS ARTICLE AND THE ONE WITH THE VAGUEST SOURCE. The finding — that diabetes raises cardiovascular risk proportionally more in women than in men — is well established and has been reported in large meta-analyses, but this entry names no single one, because the draft was not confident of authors or year and would not invent them. Before publication this must be replaced with one named meta-analysis, or with a guideline that states the finding. If neither can be produced, cut the section: it is the kind of striking claim a reader will repeat, and it must be traceable when she does.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### Specialist practical guidance on diabetes and Ramadan

**Practical guidelines for the management of diabetes during Ramadan fasting** · *no year given*

- **Appears in:** [Diabetes in women — what is different](../../database/seeders/Articles/) — `diabetes-in-women`
- **Arabic rendering:** إرشادات عملية متخصصة في السكري ورمضان. إرشادات عملية لإدارة السكري أثناء صيام رمضان
- **What it is being cited for:** Supports "there is specialist practical guidance covering risk stratification, treatment adjustment and monitoring during fasting". Confident such guidance exists and is produced in collaboration between the International Diabetes Federation and a Ramadan-focused alliance; the issuing body is not named here because the draft was not certain of the exact name. Name it and give the edition year at verification — this is the most locally relevant citation in the entire set and deserves a precise reference.
- **Verified:** ☐ &nbsp; **URL:** ______________________

---

## Confirm and record the edition — 22 to check

Major guidelines and position statements. The draft is confident both that
these exist and that they say what the article says they say.

They still need checking, for one specific reason: **several are reissued
annually**. Citing the 2025 edition of a document whose 2027 edition is current
is the most likely way these articles date, and it is invisible from the page.


### National Institute for Health and Care Excellence (NICE)

**Obesity: identification, assessment and management** · 2014

- **Appears in:** [Why we quit in week three](../../database/seeders/Articles/) — `why-we-quit-in-week-three`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). السمنة: التعرّف والتقييم والتعامل
- **What it is being cited for:** Supports "What published guidance says about this moment": that weight management programmes should run over time and include follow-up rather than being a one-off intervention. The general shape of the recommendation is cited, not a specific programme duration — the duration figure is the detail most likely to be misremembered. Confirm the guideline is still current (it has been updated since 2014) and that the follow-up recommendation survives in the current edition.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Obesity and overweight** · *no year given*

- **Appears in:** [Why we quit in week three](../../database/seeders/Articles/) — `why-we-quit-in-week-three`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: السمنة وزيادة الوزن
- **What it is being cited for:** Supports "the WHO classifies obesity as a chronic condition", used to justify the article's framing that it is managed rather than resolved. The fact sheet is revised periodically and carries no fixed year, so none is given — record the revision date current at verification.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### National Institute for Health and Care Excellence (NICE)

**Obesity: identification, assessment and management** · 2014

- **Appears in:** [What the scale says, and what it does not](../../database/seeders/Articles/) — `what-the-scale-does-not-say`
- **Arabic rendering:** المعهد الوطني للصحة وجودة الرعاية (NICE). السمنة: التعرّف والتقييم والتعامل
- **What it is being cited for:** Supports "recommends using waist measurement alongside body mass index in assessment". No threshold value is quoted in the article, deliberately — the specific waist-to-height figure is the detail most likely to be misremembered and has changed between editions. Confirm the current edition still recommends waist measurement alongside BMI.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Diabetes Association (ADA)

**Nutrition Therapy for Adults With Diabetes or Prediabetes: A Consensus Report** · 2019

- **Appears in:** ["Sort your diet out" — what does that actually mean?](../../database/seeders/Articles/) — `what-fix-your-diet-actually-means`
- **Arabic rendering:** الجمعية الأمريكية للسكري (ADA). العلاج الغذائي للبالغين المصابين بالسكري أو ما قبل السكري: تقرير توافقي
- **What it is being cited for:** Supports the description of nutrition therapy as a therapeutic intervention with assessment, an individual plan, follow-up and adjustment. Widely cited consensus report; confirm the year and that this framing is explicit rather than inferred from the structure.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Diabetes Association (ADA)

**Standards of Care in Diabetes** · 2025

- **Appears in:** ["Sort your diet out" — what does that actually mean?](../../database/seeders/Articles/) — `what-fix-your-diet-actually-means`
- **Arabic rendering:** الجمعية الأمريكية للسكري (ADA). معايير الرعاية في السكري
- **What it is being cited for:** Supports "recommends that nutrition therapy be delivered by a qualified dietitian". The Standards are reissued annually — confirm the current edition at verification and update the year here, since citing a superseded edition of an annual document is the most likely way this article dates.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Healthy diet** · *no year given*

- **Appears in:** ["Sort your diet out" — what does that actually mean?](../../database/seeders/Articles/) — `what-fix-your-diet-actually-means`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: النظام الغذائي الصحي
- **What it is being cited for:** Supports the summary of WHO healthy-eating guidance (vegetables and fruit, less free sugar, less salt, less saturated fat) and the article's framing of it as population-level guidance. Revised periodically, so no year is given — record the revision date current at verification.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Diabetes Association (ADA)

**Nutrition Therapy for Adults With Diabetes or Prediabetes: A Consensus Report** · 2019

- **Appears in:** [A downloadable plan and an individual one — where is the difference?](../../database/seeders/Articles/) — `dietitian-versus-downloadable-plan`
- **Arabic rendering:** الجمعية الأمريكية للسكري (ADA). العلاج الغذائي للبالغين المصابين بالسكري أو ما قبل السكري: تقرير توافقي
- **What it is being cited for:** Carries the load of this whole article: "there is no single ideal eating pattern for everybody with diabetes, and the plan must be individualised". This is a well-known statement in the report and the article leans on it hard, so it is the first one to check. Confirm the wording covers eating PATTERN rather than only macronutrient percentages.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Anaemia** · *no year given*

- **Appears in:** [My results are normal and I am still exhausted](../../database/seeders/Articles/) — `normal-results-still-tired`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: فقر الدم
- **What it is being cited for:** Supports "the WHO defines anaemia by haemoglobin thresholds that vary with age, sex and pregnancy". No specific threshold is quoted in the article — the thresholds were revised recently and quoting one from memory is exactly the error this article warns about. Record the revision date current at verification.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Diabetes Association (ADA)

**Standards of Care in Diabetes** · 2025

- **Appears in:** [What to bring to a first appointment](../../database/seeders/Articles/) — `what-to-bring-to-a-first-appointment`
- **Arabic rendering:** الجمعية الأمريكية للسكري (ADA). معايير الرعاية في السكري
- **What it is being cited for:** Supports "describes a comprehensive medical evaluation as the foundation of any plan, with medication review inside it". Reissued annually — confirm the current edition and update the year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Society of Human Reproduction and Embryology (ESHRE) and partner bodies

**International evidence-based guideline for the assessment and management of polycystic ovary syndrome** · 2023

- **Appears in:** [PCOS and food — how to judge a claim](../../database/seeders/Articles/) — `pcos-and-food-judging-a-claim`
- **Arabic rendering:** الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE) والجهات المشاركة. الدليل الدولي المبني على الأدلة لتقييم وإدارة متلازمة تكيس المبايض
- **What it is being cited for:** Carries three separate claims in this article and is the first thing to verify: (1) lifestyle modification recommended as core management; (2) NO SINGLE DIETARY PATTERN shown superior; (3) supplements with limited evidence treated cautiously, and inositol specifically discussed with evidence described as limited. Claim (2) is the load-bearing one — confirm the wording covers dietary PATTERN and not only macronutrient composition. Confirm also which bodies are named as developers and endorsers, since the article names ESHRE specifically.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Society of Human Reproduction and Embryology (ESHRE) and partner bodies

**International PCOS guideline — diagnostic criteria and exclusion of mimicking conditions** · 2023

- **Appears in:** [Questions to ask about hormones and food](../../database/seeders/Articles/) — `questions-to-ask-about-hormones-and-food`
- **Arabic rendering:** الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE) والجهات المشاركة. الدليل الدولي لتكيس المبايض — معايير التشخيص واستبعاد الحالات المشابهة
- **What it is being cited for:** Supports the description of diagnosis as a combination of criteria rather than a single finding, and the requirement to exclude other conditions with overlapping presentations including thyroid disease and raised prolactin. Listed separately from the entry above because it is a different claim at a different confidence — this one is core, long-standing guidance; the ultrasound timing recommendation is newer and more specific.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Anaemia** · *no year given*

- **Appears in:** [Questions to ask about hormones and food](../../database/seeders/Articles/) — `questions-to-ask-about-hormones-and-food`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: فقر الدم
- **What it is being cited for:** Supports the paragraph linking heavy or irregular periods to iron stores. Cited for the general relationship only; no threshold is quoted. Record the revision date current at verification.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**WHO recommendations on antenatal care for a positive pregnancy experience** · 2016

- **Appears in:** [Pregnancy eating myths — and the advice that has a source](../../database/seeders/Articles/) — `pregnancy-eating-myths`
- **Arabic rendering:** منظمة الصحة العالمية. توصيات منظمة الصحة العالمية بشأن الرعاية السابقة للولادة من أجل تجربة حمل إيجابية
- **What it is being cited for:** Supports several claims: daily iron and folic acid supplementation in pregnancy; that complete salt restriction is not a recommendation; and that caffeine reduction is discussed. Confirm each separately — the salt point is a claim about ABSENCE from the guidance, which is harder to verify than a positive recommendation and should be checked carefully or softened.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Infant and young child feeding** · *no year given*

- **Appears in:** [Questions that keep coming up about feeding](../../database/seeders/Articles/) — `feeding-and-eating-recurring-questions`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: تغذية الرضع وصغار الأطفال
- **What it is being cited for:** Supports "exclusive breastfeeding for the first six months, then complementary foods alongside continued breastfeeding to two years or beyond". Long-standing WHO position. Record the revision date current at verification.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Botulism** · *no year given*

- **Appears in:** [Questions that keep coming up about feeding](../../database/seeders/Articles/) — `feeding-and-eating-recurring-questions`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: التسمم الوشيقي
- **What it is being cited for:** Supports the honey section — the single most consequential statement in this article, since acting on it could prevent harm and ignoring it could cause some. Confirm that the WHO source states the under-twelve-months line for honey specifically; if it does not, cite whichever national guidance does rather than leaving the strongest claim on the weakest reference.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Child Growth Standards** · *no year given*

- **Appears in:** [The child who will not eat](../../database/seeders/Articles/) — `the-child-who-will-not-eat`
- **Arabic rendering:** منظمة الصحة العالمية. معايير نمو الطفل
- **What it is being cited for:** Supports "a child follows a curve, not a single point" as the basis for assessing growth. No year given — the standards are a maintained resource. Confirm the current reference at verification.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**WHO guidelines on physical activity and sedentary behaviour** · 2020

- **Appears in:** [Eating around training — what has evidence behind it](../../database/seeders/Articles/) — `eating-around-training`
- **Arabic rendering:** منظمة الصحة العالمية. إرشادات منظمة الصحة العالمية بشأن النشاط البدني والسلوك الخامل
- **What it is being cited for:** Supports "sets out what adults should be doing weekly, including aerobic activity and muscle-strengthening exercise". The article deliberately gives no weekly minutes — add them from the guideline if wanted.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Diabetes Association (ADA)

**Standards of Care in Diabetes** · 2025

- **Appears in:** [Postpartum nutrition — the mother in the first six months](../../database/seeders/Articles/) — `postpartum-nutrition`
- **Arabic rendering:** الجمعية الأمريكية للسكري (ADA). معايير الرعاية في السكري
- **What it is being cited for:** Supports the gestational diabetes section: glucose testing at a defined interval after delivery and periodic lifelong screening thereafter. THE ARTICLE DELIBERATELY GIVES NO INTERVAL — the ADA states one, and it should be added here from the current edition rather than from memory. Reissued annually; update the year. This is the most actionable recommendation in the article.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Infant and young child feeding** · *no year given*

- **Appears in:** [Postpartum nutrition — the mother in the first six months](../../database/seeders/Articles/) — `postpartum-nutrition`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: تغذية الرضع وصغار الأطفال
- **What it is being cited for:** Supports the framing of full breastfeeding and, with it, the claim that lactation carries an energy cost above pregnancy. If this fact sheet does not state the energy comparison explicitly, find the source that does — the comparison opens the article and carries its argument.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### American Diabetes Association (ADA)

**Standards of Care in Diabetes** · 2025

- **Appears in:** [Diabetes in women — what is different](../../database/seeders/Articles/) — `diabetes-in-women`
- **Arabic rendering:** الجمعية الأمريكية للسكري (ADA). معايير الرعاية في السكري
- **What it is being cited for:** Carries four separate claims and needs checking against each: (1) PCOS listed among conditions warranting attention to diabetes risk; (2) preconception care for women with diabetes; (3) postpartum glucose testing at a defined interval after gestational diabetes; (4) periodic lifelong screening thereafter. THE ARTICLE GIVES NO INTERVAL for (3) — the ADA states one and it should be added from the current edition. Reissued annually; update the year.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### European Society of Human Reproduction and Embryology (ESHRE) and partner bodies

**International evidence-based guideline for the assessment and management of polycystic ovary syndrome** · 2023

- **Appears in:** [Diabetes in women — what is different](../../database/seeders/Articles/) — `diabetes-in-women`
- **Arabic rendering:** الجمعية الأوروبية للتكاثر البشري وعلم الأجنة (ESHRE) والجهات المشاركة. الدليل الدولي المبني على الأدلة لتقييم وإدارة متلازمة تكيس المبايض
- **What it is being cited for:** Supports "treats the metabolic dimension as part of managing the condition". Same guideline used in the two PCOS articles — verify once, apply to all three.
- **Verified:** ☐ &nbsp; **URL:** ______________________

### World Health Organization

**Fact sheet: Diabetes** · *no year given*

- **Appears in:** [Diabetes in women — what is different](../../database/seeders/Articles/) — `diabetes-in-women`
- **Arabic rendering:** منظمة الصحة العالمية. صحيفة وقائع: السكري
- **What it is being cited for:** General background for the description of type 2 diabetes developing from insulin resistance with genetic and lifestyle contributors, used in the myths section against "diabetes comes from eating sweets". Record the revision date current at verification.
- **Verified:** ☐ &nbsp; **URL:** ______________________
