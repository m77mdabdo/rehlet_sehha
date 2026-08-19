# حزمة اللوجو — رحلة صحة

## الأيقونات (جاهزة، حجم مضبوط)
| الملف | الاستخدام |
|---|---|
| `favicon.svg` | أيقونة التاب — الأفضل، بيتحجّم لأي مقاس |
| `favicon-16/32/48.png` | احتياطي للمتصفحات القديمة |
| `apple-touch-icon.png` | أيقونة الآيفون لما حد يضيف الموقع للشاشة (١٨٠×١٨٠، من غير حواف دايرية — iOS بيعملها لوحده) |
| `icon-192.png` · `icon-512.png` | أيقونة أندرويد و PWA |
| `icon-maskable-512.png` | نسخة أندرويد اللي بيقصّها في أشكال مختلفة |
| `avatar-navy-1000.png` | صورة البروفايل — انستجرام، فيسبوك، واتساب بيزنس |
| `avatar-light-1000.png` | نفسها على خلفية فاتحة |
| `watermark-white-800.png` | علامة مائية للصور والفيديوهات |

## العلامة لوحدها (SVG — استخدمها في الموقع)
`mark-navy.svg` · `mark-white.svg` · `mark-icon-navy.svg` · `mark-icon-white.svg`
`mark-mono-navy.svg` · `mark-mono-black.svg` · `mark-mono-white.svg` (للفاكس والختم والطباعة بلون واحد)

**قاعدة:** استخدم `mark-icon-*` تحت ٤٨ بكسل (النبضة مشيلة فيها عن قصد)، و`mark-navy/white` فوق كده.
النبضة مشيلة في المقاس الصغير عشان بتبقى مبهمة، مش عشان اختيارية — فوق ٤٨ بكسل العلامة من غير
نبضة بتبقى لوجو تاني خالص.

في الموقع القاعدة دي متطبّقة في كومبوننتين:
`<x-logo.mark-full>` (٤٨ فما فوق) و `<x-logo.mark>` (تحت ٤٨). و`<x-logo.lockup>` بيختار
الاتنين لوحده حسب الـ size، فالأفضل تستخدمه بدل ما تختار بإيدك. وفيه تست
(`tests/Unit/LogoGeometryTest.php`) بيتأكد إن الرسم الجوّه الكود مطابق للملفات اللي هنا،
فلو غيّرت واحد لازم تغيّر التاني.

## التركيبات مع الاسم
`lockup-h-ar-light.svg` · `lockup-h-ar-dark.svg` · `lockup-h-en-*.svg` · `lockup-v-ar-*.svg`

⚠️ **مهم:** الملفات دي فيها كلام كنص حي، فمحتاجة خطوط Tajawal و Readex Pro تكون محمّلة. لو هتبعتها لمطبعة أو مصمم، افتحها في Illustrator أو Inkscape واعمل **Convert text to outlines** الأول، وإلا الاسم هيطلع بخط تاني.

في الموقع نفسه مش محتاج الملفات دي — الاسم مكتوب HTML عادي جنب العلامة، وده الصح لأنه بيفضل نص يقراه جوجل.

## صورة المشاركة (واتساب وفيسبوك)
`docs/og-image.html` — افتحها في كروم واعمل export للمستطيل كـ PNG بمقاس ١٢٠٠×٦٣٠، وسمّيها `og-image.png`.
معملتهاش PNG جاهزة لأن الخطوط العربية مش متاحة في بيئة التوليد، والكلام كان هيطلع حروفه مفكوكة.

## الكود اللي تحطه في `<head>`
> **ملحوظة:** الجزء ده متطبّق بالفعل في `resources/views/components/layouts/app.blade.php`.
> الـ manifest بقى **route** (`/{locale}/site.webmanifest`) مش ملف ثابت، عشان ألوانه تيجي من
> `config/clinic.php` بدل ما تتكتب تالت مرة. متضفش `site.webmanifest` تاني في `public/`.

```html
<link rel="icon" href="/brand/favicon.svg" type="image/svg+xml">
<link rel="icon" href="/brand/favicon-32.png" sizes="32x32" type="image/png">
<link rel="apple-touch-icon" href="/brand/apple-touch-icon.png">
<link rel="manifest" href="/ar/site.webmanifest">
<meta name="theme-color" content="#0E2E4D">

<meta property="og:title" content="رحلة صحة — عيادة تغذية علاجية">
<meta property="og:description" content="خطة تقدر تكمّل عليها. احجزي أونلاين في أقل من دقيقة.">
<meta property="og:image" content="https://rehletsehha.com/brand/og-image.png">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:type" content="website">
<meta property="og:locale" content="ar_EG">
<meta name="twitter:card" content="summary_large_image">
```

## الألوان
| | HEX | استخدام |
|---|---|---|
| كحلي | `#0E2E4D` | النصوص والأقسام الغامقة |
| أزرق | `#1E82C4` | الأزرار واللينكات |
| دهبي | `#E8A94A` | النبضة والنقطة واللمسات |
| خلفية | `#EEF3F8` | خلفية الصفحة |

## قواعد سريعة
- المقاس الأدنى: ٢٤ بكسل.
- مساحة فاضية حوالين اللوجو = ربع عرضه على الأقل.
- متغيّرش الألوان، متمططوش، متحطش ظل، متشيلش النقطة الدهبي.
