# Phase 08: Vitrine — corrections retours Pierre - Pattern Map

**Mapped:** 2026-06-04
**Files analyzed:** 13 (1 new, 11 modified, 1 deleted)
**Analogs found:** 13 / 13

---

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|-------------------|------|-----------|----------------|---------------|
| `resources/views/vitrine/services/depannage.blade.php` | view (service page) | request-response | `resources/views/vitrine/services/spa.blade.php` | exact |
| `app/Http/Controllers/VitrineController.php` (add method) | controller | request-response | existing `spa()` method in same file | exact |
| `routes/vitrine.php` (add route) | route | request-response | existing `Route::get('/services/spa', ...)` in same file | exact |
| `resources/views/vitrine/partials/hero.blade.php` | partial (copy edit) | — | self (rewrite paragraph at line 22) | exact |
| `resources/views/vitrine/services/entretien-recurrent.blade.php` | view (copy edit) | — | self (rewrite line 105) | exact |
| `resources/views/vitrine/services/analyse-eau.blade.php` | view (copy edit) | — | self (rewrite line 117) | exact |
| `resources/views/vitrine/partials/philosophie.blade.php` | partial (fusion target) | — | self + `engagements.blade.php` | exact |
| `resources/views/vitrine/partials/engagements.blade.php` | partial (emptied/removed) | — | self | exact |
| `resources/views/vitrine/partials/pierre.blade.php` | partial (copy edit) | — | self (rewrite line 19) | exact |
| `resources/views/vitrine/partials/final-cta.blade.php` | partial (copy edit) | — | self (rewrite line 11) | exact |
| `resources/views/vitrine/partials/services-detail.blade.php` | partial (copy edit) | — | self (rewrite line 202) | exact |
| `resources/views/vitrine/partials/services-grid.blade.php` | partial (link update) | — | self (rewrite line 29 href) | exact |
| `resources/views/vitrine/home.blade.php` | view (includes update) | — | self (rewrite lines 16+31) | exact |
| `resources/views/vitrine/services.blade.php` | view (include removal) | — | self (remove line 10) | exact |
| `resources/views/vitrine/partials/urgence-eau-verte.blade.php` | DELETE | — | — | — |

---

## Pattern Assignments

### `resources/views/vitrine/services/depannage.blade.php` (NEW — view, service page)

**Analog:** `resources/views/vitrine/services/spa.blade.php`

**Extends/section pattern** (spa.blade.php lines 1–3):
```blade
@extends('layouts.app')

@section('content')
<div class="pt-32 pb-0">
```

**Hero band pattern** (spa.blade.php lines 7–34):
```blade
<section class="bg-navy-900 pt-16 pb-12 md:pt-24 md:pb-16">
    <div class="mx-auto max-w-content px-5 sm:px-8">
        <p class="text-xs font-bold uppercase tracking-[0.18em] text-lagon-400 mb-3">SPA ET JACUZZI</p>
        <h1 class="font-display font-bold text-[clamp(2.6rem,5vw,4rem)] leading-[1.05] tracking-[-0.005em] text-sand-50">
            Entretien de spa<br class="hidden sm:block"> en Martinique
        </h1>
        <p class="mt-5 text-lg text-sand-100/80 leading-relaxed max-w-2xl">
            [pitch text]
        </p>
        <div class="mt-8 flex flex-wrap gap-3">
            <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-[#25D366] text-white font-bold shadow-md hover:brightness-95 transition">
                <x-icon.whatsapp :size="20" />
                Nous contacter par WhatsApp
            </a>
        </div>
        {{-- Breadcrumb nav (see below) --}}
    </div>
</section>
```

**Breadcrumb pattern** (spa.blade.php lines 26–33 — place under CTAs inside hero band):
```blade
<nav aria-label="Fil d'Ariane" class="mt-6 flex items-center flex-wrap min-h-[44px] gap-2 text-sm text-sand-100/60">
    <a href="{{ route('home') }}" class="hover:text-sand-50 transition-colors">Accueil</a>
    <span aria-hidden="true" class="text-sand-100/40">›</span>
    <a href="{{ route('services') }}" class="hover:text-sand-50 transition-colors">Services</a>
    <span aria-hidden="true" class="text-sand-100/40">›</span>
    <span class="text-sand-50" aria-current="page">Dépannage</span>
</nav>
```

**Content body pattern** (spa.blade.php lines 37–38 — keep max-w-3xl):
```blade
<section class="mx-auto max-w-3xl px-5 sm:px-8 py-16">
```

**Checklist bullet pattern** (spa.blade.php lines 59–64 — reuse for pannes):
```blade
<ul class="space-y-2 text-ink-700 leading-relaxed max-w-[65ch]">
    <li class="flex gap-2"><span class="text-azure-500 mt-0.5 shrink-0">✓</span><span>[panne description]</span></li>
</ul>
```

**CTA band pattern** (spa.blade.php lines 112–131):
```blade
<section class="relative bg-navy-800 text-white overflow-hidden">
    <div class="relative mx-auto max-w-content px-5 sm:px-8 py-16 sm:py-20 text-center">
        <h2 class="font-display font-bold text-3xl sm:text-4xl text-white max-w-2xl mx-auto">
            [headline]
        </h2>
        <p class="mt-4 text-lg text-navy-200 max-w-xl mx-auto">[sub]</p>
        <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
            <a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-[#25D366] text-white font-bold text-lg shadow-lg hover:brightness-95 transition">
                <x-icon.whatsapp :size="22" />
                0696 94 00 54
            </a>
            <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-azure-500 text-white font-bold text-lg hover:bg-azure-600 transition">
                Devis gratuit
            </a>
        </div>
    </div>
</section>
```

**JSON-LD breadcrumb injection:** Passed from controller via `$breadcrumbJsonLd` variable — rendered by the layout automatically (pattern confirmed on all service pages). The view does not output it directly.

---

### `app/Http/Controllers/VitrineController.php` (ADD method `depannage`)

**Analog:** existing `spa()` method, lines 133–146

**Method pattern to copy** (VitrineController.php lines 133–146):
```php
public function spa(BreadcrumbSchema $breadcrumb): View
{
    return view('vitrine.services.spa', [
        'title'            => 'Entretien de spa en Martinique · Dlo Azur Piscines',
        'description'      => 'Entretien et dépannage de spa et jacuzzi en Martinique. ...',
        'canonical'        => url('/services/spa'),
        'ogImage'          => asset('assets/brand/og-default.jpg'),
        'breadcrumbJsonLd' => $breadcrumb->toScript([
            ['name' => 'Accueil', 'url' => url('/')],
            ['name' => 'Services','url' => url('/services')],
            ['name' => 'Spa',     'url' => url('/services/spa')],
        ]),
    ]);
}
```

**Adapt for depannage:**
- Method name: `depannage`
- View: `vitrine.services.depannage`
- title: `'Dépannage piscine en Martinique · Dlo Azur Piscines'`
- description: `'Panne de pompe, filtration HS, eau trouble : dépannage rapide en Martinique. Contactez Dlo Azur sur WhatsApp pour une intervention le jour même.'`
- canonical: `url('/services/depannage')`
- breadcrumb 3rd item: `['name' => 'Dépannage', 'url' => url('/services/depannage')]`

---

### `routes/vitrine.php` (ADD route)

**Analog:** line 29 of `routes/vitrine.php`

**Pattern to copy** (vitrine.php line 29 — inside the `cache.headers:vitrine` group):
```php
Route::get('/services/spa', [VitrineController::class, 'spa'])->name('services.spa');
```

**Adapt for depannage — insert after line 29:**
```php
Route::get('/services/depannage', [VitrineController::class, 'depannage'])->name('services.depannage');
```

---

### `resources/views/vitrine/partials/hero.blade.php` (MODIFY — copy edit line 22)

**Current text** (hero.blade.php line 22):
```
Entretien régulier, dépannage et analyse de l'eau. Un service à taille humaine, du nord-atlantique au centre de la Martinique. Un appel suffit pour voir si votre piscine entre dans ma tournée.
```

**Target:** Replace with 3rd-person, honest zone, invitation to call. No "je/ma/mon". No named communes. Keep "Un appel suffit…" Per D-01/D-02/D-03.

**Surrounding markup to preserve** (hero.blade.php lines 21–23):
```blade
<p class="mt-5 text-lg sm:text-xl text-navy-100 max-w-xl leading-relaxed">
    [REWRITE THIS LINE ONLY]
</p>
```

---

### `resources/views/vitrine/services/entretien-recurrent.blade.php` (MODIFY — line 105)

**Current text** (entretien-recurrent.blade.php line 105):
```
Un service client réactif, professionnel et personnalisé. Interventions sur toute la Martinique : Fort-de-France, Le Lamentin, Schoelcher, Les Trois-Îlets et communes alentour.
```

**Target per D-04:** Replace "toute la Martinique" + commune listing with "dans notre zone d'intervention" (or "sur le corridor atlantique et caraïbe"). Keep the rest of the sentence or reformulate naturally.

**Surrounding markup** (lines 101–106):
```blade
<p class="text-ink-700 leading-relaxed max-w-[65ch]">
    [REWRITE THIS LINE ONLY]
</p>
```

---

### `resources/views/vitrine/services/analyse-eau.blade.php` (MODIFY — line 117)

**Current text** (analyse-eau.blade.php line 117):
```
Réservez une analyse professionnelle : intervention rapide sur toute la Martinique.
```

**Target per D-04:** Replace "toute la Martinique" with honest zone wording.

**Surrounding markup** (lines 116–118):
```blade
<p class="mt-4 text-lg text-azure-50 max-w-xl mx-auto">
    [REWRITE THIS LINE ONLY]
</p>
```

---

### `resources/views/vitrine/partials/philosophie.blade.php` (REWRITE — becomes "Notre approche" fusion)

**Current content summary** (philosophie.blade.php, full file, 41 lines):
- Section `bg-lagon-50/40 border-y border-navy-900/8`
- Two `h2` blocks: "Pourquoi choisir Dlo Azur Piscines ?" + "Votre piscine mérite une eau parfaite"
- Line 24 contains "Un seul interlocuteur qui connaît votre bassin, pas de sous-traitance" — REMOVE per D-10/D-11

**Engagements cards to absorb** (engagements.blade.php, full file, 49 lines):
- 4-card grid pattern: `grid sm:grid-cols-2 lg:grid-cols-4 gap-5`
- Card structure (lines 9–17):
```blade
<div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 flex flex-col gap-4">
    <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-azure-50 text-azure-600">
        <x-icon.shield :size="22" />
    </span>
    <div>
        <h3 class="font-display font-semibold text-xl text-ink-950">[title]</h3>
        <p class="mt-2 text-ink-700 leading-relaxed">[body]</p>
    </div>
</div>
```
- Card "Joignable sur WhatsApp" line 25: "Vous tombez sur Pierre, jamais sur un standard." → REFORMULATE to positive per D-10
- Card "Toujours le même interlocuteur" line 44: title uses "interlocuteur" → rename to avoid D-11 duplication

**Fusion approach (Option A):** Rewrite `philosophie.blade.php` to contain the merged "Notre approche" section combining both angles. The section title changes from "Pourquoi choisir Dlo Azur Piscines ?" to "Notre approche". Retain the `bg-lagon-50/40 border-y` background.

**Section header pattern** (from engagements.blade.php lines 4–7):
```blade
<div class="text-center mb-12">
    <h2 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">Notre approche</h2>
    <p class="mt-3 text-lg text-ink-700 max-w-xl mx-auto">[sub]</p>
</div>
```

---

### `resources/views/vitrine/partials/engagements.blade.php` (EMPTY after fusion)

Per Option A, after the content is merged into `philosophie.blade.php`, this file is emptied (or its `@include` in `home.blade.php` is removed). See home.blade.php section below for the include update.

---

### `resources/views/vitrine/partials/pierre.blade.php` (MODIFY — line 19)

**Current text** (pierre.blade.php line 19):
```
Pas de centre d'appel, pas de sous-traitance : vous échangez directement avec celui qui plonge l'épuisette. C'est ça, un service à taille humaine.
```

**Target per D-10 (CONSERVER — turn positive):** Remove "Pas de centre d'appel,". Keep "vous échangez directement avec celui qui plonge l'épuisette." Suggestion: "Vous échangez directement avec celui qui plonge l'épuisette. C'est ça, un service à taille humaine."

**Pull-quote line 21** (pierre.blade.php):
```blade
<p class="font-display font-semibold text-2xl text-ink-950 leading-snug flex-1">Un seul interlocuteur,<br>qui connaît votre bassin.</p>
```
Per D-11: if "Notre approche" keeps an interlocuteur angle, reformulate this pull-quote to a different angle (e.g. "Pierre connaît votre bassin,\nil passe quand il le faut."). Claude's discretion.

---

### `resources/views/vitrine/partials/final-cta.blade.php` (MODIFY — line 11)

**Current text** (final-cta.blade.php line 11):
```
Devis gratuit et sans engagement. Vous parlez directement à Pierre, jamais à un standard.
```

**Target per D-10 (CONSERVER — turn positive):** Remove "jamais à un standard". Suggestion: "Devis gratuit et sans engagement. Vous parlez directement à Pierre, sans intermédiaire."

**Surrounding markup** (final-cta.blade.php lines 10–11):
```blade
<p class="mt-4 text-lg text-azure-50 max-w-md mx-auto lg:mx-0">
    [REWRITE THIS LINE ONLY]
</p>
```

---

### `resources/views/vitrine/partials/services-detail.blade.php` (MODIFY — line 202)

**Current text** (services-detail.blade.php lines 201–203):
```
À l'écoute de vos besoins, joignable directement par WhatsApp, sans standard téléphonique ni rotation d'interlocuteurs.
```

**Target per D-10 (SUPPRIMER la négation):** Replace with a positive argument orthogonal to "interlocuteur unique" — e.g., focus on reactivity or compte-rendu. Suggestion: "Joignable directement par WhatsApp, avec un compte-rendu après chaque intervention."

**Surrounding markup** (lines 199–208 context):
```blade
<div class="rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 text-center hover:shadow-md hover:-translate-y-0.5 transition duration-300">
    <span class="text-3xl block mb-3" aria-hidden="true">🤝</span>
    <h3 class="font-display font-semibold text-lg text-ink-950 mb-2">Service client réactif &amp; amical</h3>
    <p class="text-ink-700 leading-relaxed text-sm">
        [REWRITE THIS PARAGRAPH]
    </p>
</div>
```

---

### `resources/views/vitrine/partials/services-grid.blade.php` (MODIFY — line 29 href)

**Current** (services-grid.blade.php line 29):
```blade
<a href="{{ route('services') }}" class="group rounded-3xl bg-white ...">
```

**Target per D-08:** Change `route('services')` → `route('services.depannage')` on the "Dépannage rapide" card only.

**Full card context** (lines 29–36):
```blade
<a href="{{ route('services') }}" class="group rounded-3xl bg-white ring-1 ring-navy-900/8 shadow-sm p-7 hover:shadow-md hover:-translate-y-0.5 transition duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-azure-400">
    <span class="inline-grid h-11 w-11 place-items-center rounded-xl bg-azure-50 text-azure-600 mb-4">
        [wrench svg]
    </span>
    <h3 class="font-display font-semibold text-xl text-ink-950">Dépannage rapide</h3>
    ...
    <span class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-azure-600 group-hover:gap-2.5 transition-all">En savoir plus <x-icon.arrow-right :size="15" /></span>
</a>
```

---

### `resources/views/vitrine/home.blade.php` (MODIFY — includes update)

**Current includes** (home.blade.php lines 15–31):
```blade
{{-- 4b. Philosophie SEO --}}
@include('vitrine.partials.philosophie')

...

{{-- 10. Nos engagements --}}
@include('vitrine.partials.engagements')
```

**Target per D-09 (Option A):** After `philosophie.blade.php` absorbs the engagements content, remove the `engagements` include (line 31) and keep only the `philosophie` include (now "Notre approche"). Section order must be preserved — `philosophie` stays at position 4b, `engagements` slot (position 10) is dropped.

**Lines to touch:** line 31 `@include('vitrine.partials.engagements')` — remove this line. Line 15 `@include('vitrine.partials.philosophie')` — keep as-is (content changes inside the file).

---

### `resources/views/vitrine/services.blade.php` (MODIFY — remove @include line 10)

**Current** (services.blade.php line 10):
```blade
@include('vitrine.partials.urgence-eau-verte')
```

**Target per D-12 / Pitfall 1:** Remove this line. The `services-grid.blade.php` already has its own "Eau verte urgence" card — no functional gap.

**Full file context** (services.blade.php lines 1–14):
```blade
@extends('layouts.app')

@section('content')
    <h1 class="sr-only">Services de pisciniste · Dlo Azur Piscines</h1>
    <div class="pt-24">
        @include('vitrine.partials.services-grid')
        @include('vitrine.partials.services-detail')
        @include('vitrine.partials.urgence-eau-verte')   ← REMOVE THIS
        @include('vitrine.partials.how-it-works')
        @include('vitrine.partials.final-cta')
    </div>
@endsection
```

---

### DELETE: `resources/views/vitrine/partials/urgence-eau-verte.blade.php`

No pattern to extract. File is deleted after its `@include` is removed from `services.blade.php`. Confirm deletion with `rm` — Blade will throw `InvalidArgumentException` if the file is deleted while an `@include` still references it.

---

## Shared Patterns

### WhatsApp CTA (apply to depannage.blade.php)
**Source:** `resources/views/vitrine/services/spa.blade.php` lines 20–24 and 121–125

Primary CTA in hero band:
```blade
<a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
   class="inline-flex items-center gap-2 min-h-[44px] h-13 px-6 rounded-xl bg-[#25D366] text-white font-bold shadow-md hover:brightness-95 transition">
    <x-icon.whatsapp :size="20" />
    Nous contacter par WhatsApp
</a>
```

Prominent CTA in bottom band:
```blade
<a href="https://wa.me/596696940054" target="_blank" rel="noopener noreferrer"
   class="inline-flex items-center gap-2 min-h-[44px] h-14 px-7 rounded-xl bg-[#25D366] text-white font-bold text-lg shadow-lg hover:brightness-95 transition">
    <x-icon.whatsapp :size="22" />
    0696 94 00 54
</a>
```

### BreadcrumbSchema injection (apply to depannage controller method)
**Source:** `app/Http/Controllers/VitrineController.php` lines 88–101

```php
// Inject via BreadcrumbSchema DI — 3-level path for service pages
'breadcrumbJsonLd' => $breadcrumb->toScript([
    ['name' => 'Accueil',   'url' => url('/')],
    ['name' => 'Services',  'url' => url('/services')],
    ['name' => 'Dépannage', 'url' => url('/services/depannage')],
]),
```

### Voix de marque — règle de reformulation (apply to all copy edits)
**Source:** D-10 audit in RESEARCH.md

| File | Line | Old (remove) | New (keep) |
|------|------|-------------|------------|
| pierre.blade.php | 19 | "Pas de centre d'appel, pas de sous-traitance :" | remove prefix, keep "vous échangez directement…" |
| final-cta.blade.php | 11 | "jamais à un standard" | "sans intermédiaire" |
| engagements.blade.php | 25 | "jamais sur un standard" | "Vous parlez directement à Pierre." |
| philosophie.blade.php | 24 | "pas de sous-traitance" | reformulate without negation |
| services-detail.blade.php | 202 | "sans standard téléphonique ni rotation d'interlocuteurs" | "avec un compte-rendu après chaque intervention" |

### Supprimer "interlocuteur unique" dans "Notre approche" (D-11)
**Source:** RESEARCH.md §Mentions "interlocuteur unique"

After fusion, "Notre approche" must NOT use "interlocuteur unique" as an angle. Acceptable angles: compte-rendu après chaque passage, WhatsApp direct, suivi en ligne. The pull-quote in `pierre.blade.php` line 21 ("Un seul interlocuteur, qui connaît votre bassin.") may remain but should be reviewed if "Notre approche" already uses a proximity angle — Claude's discretion.

---

## No Analog Found

All files have close analogs. No entries.

---

## Metadata

**Analog search scope:** `resources/views/vitrine/`, `app/Http/Controllers/VitrineController.php`, `routes/vitrine.php`
**Files scanned:** 15
**Pattern extraction date:** 2026-06-04
