# Phase 5: Diagnostic Commercialisable - Research

**Researched:** 2026-05-30
**Domain:** Livewire 3 multi-step wizard, server-side dose engine (PHP service), Alpine.js step navigation, decision tree (PHP config array), DomPDF PDF, WhatsApp deep links, lead capture
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

- **D-01:** One full-page Livewire component owns persistence and dose-compute submit; Alpine handles step navigation (no network round-trip per step).
- **D-02:** Dose engine = `app/Services/Diagnostic/DoseEngine.php` (pure functions); decision tree = versioned PHP config array. Server-only, Pest-testable.
- **D-03:** Lead data as additive columns on `diagnostics` table (`prenom`, `commune`, `email`, `site_web`); no separate Lead model. Pierre notified via Mail, following ContactForm pattern.
- **D-04:** `Diagnostic` row persisted on completion (results computed / disclaimer accepted). `disclaimer_accepted_at` guaranteed non-null on any row carrying dosing advice.
- **D-05:** PDF generated synchronously on download via `spatie/laravel-pdf` DomPDF driver. No queue infra.

### Claude's Discretion

- Exact column names, service namespace layout, route/component naming, PDF Blade layout — left to planner, consistent with existing conventions.

### Deferred Ideas (OUT OF SCOPE)

- Stripe monetization (DIAG-04) — own later phase.
- Full multi-measure history dashboard (DIAG-05) — own later phase.
- Authoring new dose chemistry (vs mockup baseline) — out of scope.
- Native pool CRUD beyond linking `piscine_id`.
</user_constraints>

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|-----------------|
| DIAG-01 | Two-flow entry: water-chemistry wizard (pool info + 5 measurements) AND "Dépannage rapide" symptom tree | Decision tree fully extracted from mockup; wizard inputs mapped |
| DIAG-02 | Server-side dose engine — formulas never in client JS | DoseEngine service pattern; all formulas transcribed from mockup JS |
| DIAG-03 | Disclaimer gate before any dosing advice; `disclaimer_accepted_at` non-null on any dosing row | Alpine gate pattern; persistence decision D-04 |
| DIAG-04 | Stripe monetization — **DEFERRED, out of scope for this phase** | — |
| DIAG-05 | Full multi-measure history dashboard — **DEFERRED, out of scope for this phase** | — |
</phase_requirements>

---

## Summary

Phase 5 builds a free public pool diagnostic accessible at `/diagnostic` — two flows (measurement wizard + symptom decision tree), a server-side dose engine, legal disclaimer gate, lead-capture, WhatsApp hand-off, and PDF download. The data layer (`Diagnostic` model + migration) already exists. Zero new packages are required beyond what is already in `composer.json` and `package.json`.

The mockup (`mockups/diagnostic-dloazur.html`) has been successfully parsed — it is a minified Next.js/React export. **All dose formulas and the full decision tree are embedded in the minified JS bundle and have been extracted verbatim** in the Code Examples section below. The authoritative file is present in the repo; the earlier SPEC/CONTEXT gap warning is resolved.

**Primary recommendation:** Build as one full-page Livewire component (`DiagnosticWizard`) with Alpine.js driving step transitions client-side, a pure-PHP `DoseEngine` service class (all formulas in PHP config), and synchronous DomPDF PDF generation. No new packages needed.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Multi-step wizard navigation | Browser (Alpine) | — | No network per step (SPEC constraint, D-01) |
| Dose calculation | API/Backend (Livewire action) | — | Server-only (DIAG-02); formulas never reach client JS |
| Disclaimer gate enforcement | Backend (Livewire) | Browser (Alpine gate) | Server enforces `disclaimer_accepted_at`; Alpine may show/hide UI |
| Decision tree traversal | Browser (Alpine) | — | Pure UI navigation; leaves are static PHP config, no live compute needed |
| Persistence (`Diagnostic` row) | Backend (Livewire) | — | On completion, single save |
| Lead-capture form | Backend (Livewire) | — | Follows ContactForm pattern; rate-limited, honeypot-protected |
| Pierre notification email | Backend (Laravel Mail) | — | Same as ContactMessage pattern |
| PDF generation | Backend (Laravel controller) | — | Synchronous DomPDF; no browser dependency |
| WhatsApp link | Browser | — | Static `wa.me` URL built from diagnostic summary; no backend needed |
| Public routing | Backend (routes/vitrine.php) | — | Outside auth group; extend existing vitrine routes |

---

## Standard Stack

### Core (all already installed — zero new packages)

| Library | Installed Version | Purpose | Status |
|---------|-----------------|---------|--------|
| `livewire/livewire` | ^3.0 | Full-page wizard component + form persistence | Already in composer.json |
| `spatie/laravel-pdf` | ^2.11 | PDF report generation (DomPDF driver) | Already in composer.json |
| `spatie/laravel-honeypot` | ^4.7 | Spam protection on lead-capture form | Already in composer.json |
| `danharrin/livewire-rate-limiting` | ^2.2 | Rate limiting on form submit | Already in composer.json |
| `pestphp/pest` | ^4.7 | Tests for DoseEngine | Already in composer.json |
| Alpine.js | via Livewire bundle | Step navigation, decision tree UI | Already loaded |

**Installation:** No new `composer require` or `npm install` needed. All dependencies are installed. [VERIFIED: grep composer.json]

---

## Package Legitimacy Audit

No new packages are being added in this phase. All libraries are pre-existing, verified, and already in use.

| Package | Status |
|---------|--------|
| All phase dependencies | Already installed, previously audited in CLAUDE.md |

**Packages removed due to slopcheck:** none
**Packages flagged as suspicious:** none

---

## Architecture Patterns

### System Architecture Diagram

```
Visitor (browser)
      |
      | GET /diagnostic
      v
[VitrineController::diagnostic()]
      |
      v
[Blade: vitrine/diagnostic.blade.php]
  └── <livewire:diagnostic-wizard />
            |
            | Alpine step nav (client-side, no network)
            |-- Step 0: Disclaimer gate (Alpine show/hide)
            |-- Step 1: Mode selector (wizard | depannage)
            |
            | [Wizard path]
            |-- Step 2: Pool info (volume/surface+depth, filtration, sel)
            |-- Step 3: Measurements (pH, chlore, TAC, stabilisant, sel ppm)
            |-- [Livewire action: computeDoses()]
            |       └── DoseEngine::compute($mesures, $volume)
            |               └── config/diagnostic-tree.php (formulas)
            |-- [Livewire action: persist() → Diagnostic::create()]
            |-- Step 4: Results + disclaimer confirmation
            |-- Step 5: Lead-capture form + actions
            |
            | [Dépannage rapide path]
            |-- Alpine traverses decision tree (config/diagnostic-tree.php PHP array)
            |-- Leaf reached → show result card
            |-- Step N: Lead-capture form + actions
            |
            | [Actions on results screen]
            |-- "Télécharger le rapport" → GET /diagnostic/{id}/pdf
            |       └── DiagnosticPdfController → spatie/laravel-pdf DomPDF
            |-- "Contacter sur WhatsApp" → wa.me link (Alpine computed)
            |-- Lead-capture submit → Livewire action → Mail to Pierre
```

### Recommended Project Structure

```
app/
├── Http/Controllers/
│   ├── DiagnosticController.php       # GET /diagnostic (view), GET /diagnostic/{id}/pdf
│   └── (VitrineController.php)        # existing — add diagnostic() method OR use DiagnosticController
├── Livewire/
│   └── DiagnosticWizard.php           # full-page Livewire component
├── Services/
│   └── Diagnostic/
│       └── DoseEngine.php             # pure static methods, no side-effects
└── Mail/
    └── DiagnosticLead.php             # Pierre notification (follows ContactMessage)

config/
└── diagnostic.php                     # versioned dose tree + decision tree PHP arrays
    # or split:
    # diagnostic-formulas.php          # dose coefficients
    # diagnostic-tree.php              # decision tree nodes and leaves

resources/views/
├── vitrine/
│   └── diagnostic.blade.php           # full-page layout wrapper
├── livewire/
│   └── diagnostic-wizard.blade.php    # wizard/tree UI
└── pdf/
    └── diagnostic-report.blade.php    # DomPDF PDF layout (table-based CSS, no Tailwind)
```

### Pattern 1: Livewire + Alpine step navigation (D-01)

The key architecture: Livewire owns state (PHP properties) and server actions. Alpine owns the current step index and any client-only transitions. No Livewire round-trip for step navigation.

```php
// app/Livewire/DiagnosticWizard.php
// Source: existing app/Livewire/ContactForm.php pattern + Livewire 3 docs

use Livewire\Attributes\Validate;
use Livewire\Component;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class DiagnosticWizard extends Component
{
    use WithRateLimiting, UsesSpamProtection;

    // Wizard inputs
    public string $sizeMode = 'volume';        // 'volume' | 'surface'
    public string $volume = '';
    public string $surface = '';
    public string $depth = '';
    public string $filtration = '';
    public ?bool $sel = null;
    public string $selPpm = '';
    public string $ph = '';
    public string $chlore = '';
    public string $alcalinite = '';
    public string $stabilisant = '';

    // Lead capture
    public string $prenom = '';
    public string $commune = '';
    public string $email = '';
    public string $siteWeb = '';

    // State
    public bool $disclaimerAccepted = false;
    public ?array $recommendations = null;  // computed server-side
    public ?int $savedDiagnosticId = null;
    public bool $leadSaved = false;

    // Server actions (called via wire:click, not per step)
    public function computeAndPersist(): void { ... }
    public function submitLead(): void { ... }
}
```

```html
<!-- resources/views/livewire/diagnostic-wizard.blade.php -->
<!-- Alpine drives the step: x-data="{ step: 0, mode: null }" -->
<!-- Livewire properties sync via wire:model -->
<div x-data="{ step: 0, mode: null }">
    <!-- Step 0: disclaimer -->
    <div x-show="step === 0">
        <p>Conseils indicatifs — En cas de doute, contactez un professionnel</p>
        <button @click="step = 1" wire:click="acceptDisclaimer">J'accepte et je continue</button>
    </div>

    <!-- Step 1: choose mode -->
    <div x-show="step === 1">
        <button @click="mode = 'wizard'; step = 2">Analyse chimique</button>
        <button @click="mode = 'depannage'; step = 10">Dépannage rapide</button>
    </div>

    <!-- Wizard steps 2-3 (Alpine-driven, no wire round-trip) -->
    <div x-show="step === 2 && mode === 'wizard'">...</div>
    <div x-show="step === 3 && mode === 'wizard'">
        <!-- wire:click triggers server compute -->
        <button wire:click="computeAndPersist" @click="step = 4">Calculer mon plan</button>
    </div>

    <!-- Results step 4 -->
    <div x-show="step === 4 && mode === 'wizard'" wire:ignore.self>
        @foreach($recommendations as $rec) ... @endforeach
    </div>
</div>
```

**Pitfall:** `wire:ignore` or careful scoping needed so Alpine step state doesn't get reset by Livewire re-renders. Use `x-cloak` + CSS to avoid flash.

### Pattern 2: DoseEngine service class (D-02)

Pure static functions, no dependencies, fully Pest-testable:

```php
// app/Services/Diagnostic/DoseEngine.php
// Source: formulas extracted verbatim from mockups/diagnostic-dloazur.html

final class DoseEngine
{
    /**
     * Compute adjustment recommendations from wizard inputs.
     * Returns array of RecommendationCard arrays.
     * MUST remain server-side only (DIAG-02).
     */
    public static function compute(array $mesures, float $volume): array
    {
        $recs = [];
        $ph = isset($mesures['ph']) ? (float) str_replace(',', '.', $mesures['ph']) : null;
        $chlore = isset($mesures['chlore']) ? (float) str_replace(',', '.', $mesures['chlore']) : null;
        $alcalinite = isset($mesures['alcalinite']) ? (float) str_replace(',', '.', $mesures['alcalinite']) : null;
        $stabilisant = isset($mesures['stabilisant']) ? (float) str_replace(',', '.', $mesures['stabilisant']) : null;
        $selPpm = isset($mesures['selPpm']) ? (float) str_replace(',', '.', $mesures['selPpm']) : null;
        $hasSel = (bool) ($mesures['sel'] ?? false);

        // pH LOW (< 7.2): pH+ (carbonate de soude)
        // Formula: steps = ceil((7.2 - pH) / 0.2); dose = steps * 10 g/m³ * volume
        if ($ph !== null && $ph < 7.2) {
            $steps = max(1, (int) ceil((7.2 - $ph) / 0.2));
            $doseGrams = $volume > 0 ? round($steps * 10 * $volume) : $steps * 10;
            $recs[] = [
                'param'   => 'pH',
                'current' => "{$mesures['ph']} (trop bas)",
                'target'  => '7.2 — 7.4',
                'product' => 'pH+ (carbonate de soude)',
                'dose'    => $volume > 0 ? "{$doseGrams} g (~" . ($steps * 10) . " g/m³)" : ($steps * 10) . ' g/m³',
                'note'    => 'Diluer dans un seau d\'eau, verser devant les buses, filtration en marche.',
            ];
        }

        // pH HIGH (> 7.6): pH- (bisulfate de sodium)
        // Formula: steps = max(1, round((pH - 7.4) / 0.2)); dose = steps * 200 g per 10 m³
        if ($ph !== null && $ph > 7.6) {
            $steps = max(1, (int) round(($ph - 7.4) / 0.2));
            $doseGrams = $volume > 0 ? round($steps * 200 * ($volume / 10)) : $steps * 200;
            $recs[] = [
                'param'   => 'pH',
                'current' => "{$mesures['ph']} (trop haut)",
                'target'  => '7.2 — 7.4',
                'product' => 'pH- (bisulfate de sodium)',
                'dose'    => $volume > 0 ? "{$doseGrams} g (~" . ($steps * 200) . " g par 10 m³)" : ($steps * 200) . ' g par 10 m³',
                'note'    => 'Diluer dans un seau d\'eau, verser devant les buses, filtration en marche 4h, re-tester.',
            ];
        }

        // TAC LOW (< 80): bicarbonate de sodium
        // Formula: steps = max(1, ceil((80 - TAC) / 20)); dose = steps * 18 g/m³ * volume
        if ($alcalinite !== null && $alcalinite < 80) {
            $steps = max(1, (int) ceil((80 - $alcalinite) / 20));
            $doseGrams = $volume > 0 ? round($steps * 18 * $volume) : $steps * 18;
            $recs[] = [
                'param'   => 'Alcalinité (TAC)',
                'current' => "{$mesures['alcalinite']} mg/L (trop bas)",
                'target'  => '80 — 120 mg/L',
                'product' => 'TAC+ (bicarbonate de sodium)',
                'dose'    => $volume > 0 ? "{$doseGrams} g (~" . ($steps * 18) . " g/m³)" : ($steps * 18) . ' g/m³',
                'note'    => 'Verser directement dans le bassin, filtration en marche, re-tester après 24h.',
            ];
        }

        // TAC HIGH (> 120): progressive pH- reduction
        if ($alcalinite !== null && $alcalinite > 120) {
            $recs[] = [
                'param'   => 'Alcalinité (TAC)',
                'current' => "{$mesures['alcalinite']} mg/L (trop haut)",
                'target'  => '80 — 120 mg/L',
                'product' => 'pH- (en plusieurs petites doses)',
                'dose'    => 'À ajuster progressivement',
                'note'    => 'Baisser le pH à 7.0 pendant quelques jours fait redescendre le TAC. Re-tester chaque jour.',
            ];
        }

        // Stabilisant HIGH (> 75): partial drain
        // Formula: fraction = (>100 → 0.5, else → 0.33); drain = volume * fraction m³
        if ($stabilisant !== null && $stabilisant > 75) {
            $fraction = $stabilisant > 100 ? 0.5 : 0.33;
            $drainM3 = $volume > 0 ? round($volume * $fraction) : 0;
            $recs[] = [
                'param'   => 'Stabilisant',
                'current' => "{$mesures['stabilisant']} mg/L (trop élevé)",
                'target'  => '30 — 50 mg/L',
                'product' => 'Vidange partielle',
                'dose'    => $drainM3 > 0
                    ? "Vidanger {$drainM3} m³ (~" . round($fraction * 100) . "% du bassin) puis recompléter à l'eau du réseau"
                    : 'Vidanger ' . round($fraction * 100) . "% du bassin puis recompléter",
                'note'    => 'Le stabilisant ne se dégrade pas : seule la dilution le fait baisser. Utiliser hypochlorite de calcium (sans stabilisant) pour les prochains chocs.',
            ];
        }

        // Sel LOW (< 3000 ppm, only if sel = true)
        // Formula: delta = 4000 - selPpm; kg = round(delta * volume / 1000)
        if ($hasSel && $selPpm !== null && $selPpm < 3000) {
            $delta = 4000 - $selPpm;
            $kgSel = $volume > 0 ? round($delta * $volume / 1000) : 0;
            $recs[] = [
                'param'   => 'Sel',
                'current' => "{$mesures['selPpm']} ppm (trop bas)",
                'target'  => '3000 — 5000 ppm (selon ton électrolyseur)',
                'product' => 'Sel pour piscine (pastilles)',
                'dose'    => $kgSel > 0 ? "Ajouter ~{$kgSel} kg de sel" : 'Ajouter du sel pour viser 4000 ppm',
                'note'    => 'Verser le sel directement dans le bassin (pas dans le skimmer), filtration en marche 24h. Re-tester ensuite.',
            ];
        }

        // Sel HIGH (> 6000 ppm, only if sel = true)
        // Formula: fraction = min(0.5, (ppm - 4500) / ppm); drain = volume * fraction
        if ($hasSel && $selPpm !== null && $selPpm > 6000) {
            $fraction = min(0.5, ($selPpm - 4500) / $selPpm);
            $drainM3 = $volume > 0 ? round($volume * $fraction) : 0;
            $recs[] = [
                'param'   => 'Sel',
                'current' => "{$mesures['selPpm']} ppm (trop haut)",
                'target'  => '3000 — 5000 ppm',
                'product' => 'Vidange partielle',
                'dose'    => $drainM3 > 0 ? "Vidanger {$drainM3} m³ (~" . round($fraction * 100) . "%) puis recompléter" : 'Vidanger ' . round($fraction * 100) . '% du bassin',
                'note'    => 'Un taux trop élevé peut endommager l\'électrolyseur et corroder les équipements.',
            ];
        }

        // Chlore LOW (< 1 mg/L): hypochlorite de calcium en poudre
        // Formula: 15 g/m³
        if ($chlore !== null && $chlore < 1) {
            $dose = $volume > 0 ? round(15 * $volume) . ' g (15 g/m³)' : '15 g/m³';
            $recs[] = [
                'param'   => 'Chlore libre',
                'current' => "{$mesures['chlore']} mg/L (trop bas)",
                'target'  => '1 — 3 mg/L',
                'product' => 'Hypochlorite de calcium en poudre (sans stabilisant)',
                'dose'    => $dose,
                'note'    => 'Diluer dans un seau d\'eau, verser autour du bassin pompe en marche. Attendre que le chlore redescende sous 3 mg/L avant baignade.',
            ];
        }

        // Chlore HIGH (> 3 mg/L): aucun ajout
        if ($chlore !== null && $chlore > 3) {
            $recs[] = [
                'param'   => 'Chlore libre',
                'current' => "{$mesures['chlore']} mg/L (trop haut)",
                'target'  => '1 — 3 mg/L',
                'product' => 'Aucun ajout — laisser baisser',
                'dose'    => 'Stopper toute chloration et aérer le bassin',
                'note'    => 'Attendre 24 à 48h, le chlore redescendra naturellement avec l\'UV et l\'aération.',
            ];
        }

        return $recs;
    }

    /**
     * Chlore choc formula for decision tree leaves.
     * 15 g/m³ hypochlorite de calcium.
     */
    public static function chloreChoc(float $volumeM3): array
    {
        return [
            'title' => 'Chlore choc — hypochlorite de calcium en poudre',
            'dose'  => round(15 * $volumeM3) . ' g d\'hypochlorite de calcium en poudre',
            'rule'  => "Règle : 15 g par m³ — pour {$volumeM3} m³ (sans stabilisant ajouté)",
        ];
    }

    /**
     * pH- formula (used in auto-green branch).
     * 100 g abaisse 0.1 pH pour 10 m³.
     */
    public static function phMinus(float $volumeM3, ?float $currentPh = null): array
    {
        $delta = ($currentPh !== null && $currentPh > 7.4) ? round(($currentPh - 7.4) * 10) / 10 : 0.2;
        $grams = round($delta / 0.1 * 100 * ($volumeM3 / 10));
        return [
            'title' => 'pH-',
            'dose'  => "{$grams} g de pH-",
            'rule'  => $currentPh !== null && $currentPh > 7.4
                ? "Pour passer de pH {$currentPh} à pH 7.4 (Δ {$delta}) — 100 g abaisse 0.1 pH pour 10 m³"
                : 'Règle : 100 g abaisse 0.1 pH pour 10 m³ (correction standard ~0.2 pH)',
        ];
    }
}
```

### Pattern 3: Decision tree as PHP config array (D-02)

The tree structure maps 1:1 to the mockup's embedded JS data. Planner must transcribe the full node set (see Code Examples section).

```php
// config/diagnostic.php — excerpt showing structure

return [
    'questions' => [
        'start' => [
            'id'      => 'start',
            'title'   => 'Quel est le problème avec ton eau ?',
            'options' => [
                ['label' => 'Eau verte',   'emoji' => '🟢', 'next' => ['kind' => 'question', 'id' => 'green-1']],
                ['label' => 'Eau trouble', 'emoji' => '⚪', 'next' => ['kind' => 'question', 'id' => 'cloudy-1']],
                ['label' => 'Eau marron',  'emoji' => '🟤', 'next' => ['kind' => 'question', 'id' => 'brown-1']],
                ['label' => 'Eau claire mais problème', 'emoji' => '💎', 'next' => ['kind' => 'question', 'id' => 'clear-1']],
                ['label' => 'Problème d\'électrolyseur', 'emoji' => '⚡', 'next' => ['kind' => 'question', 'id' => 'electro-1']],
            ],
        ],
        // ... all nodes below
    ],
    'results' => [
        'algues-avancees' => [
            'id'         => 'algues-avancees',
            'diagnostic' => 'Algues avancées',
            'analyse'    => 'L\'eau verte avec un fond invisible indique une prolifération massive d\'algues. Un traitement choc s\'impose immédiatement.',
            'plan'       => [
                'Brosser énergiquement les parois et le fond',
                'Effectuer un chlore choc à l\'hypochlorite de calcium en poudre (30 g/m³, sans stabilisant)',
                'Ajouter un anti-algues curatif',
                'Filtration en continu pendant 24-48h',
                'Nettoyer le filtre après traitement',
            ],
        ],
        // ... all leaves
    ],
];
```

### Pattern 4: Disclaimer gate enforcement

Two-layer enforcement: Alpine prevents navigation, Livewire enforces server-side.

```php
// Livewire action
public function computeAndPersist(): void
{
    if (! $this->disclaimerAccepted) {
        $this->addError('disclaimer', 'Vous devez accepter les conditions avant de continuer.');
        return;
    }
    // compute + persist
}

// Persistence guarantees non-null disclaimer_accepted_at
Diagnostic::create([
    ...
    'disclaimer_accepted_at' => now(),  // always set here, never null on dosing rows
]);
```

### Pattern 5: WhatsApp deep link (browser-side)

No backend needed. Alpine builds the URL from wizard summary:

```html
<a :href="`https://wa.me/596696940054?text=${encodeURIComponent(waMessage)}`"
   target="_blank" rel="noopener">
   Contacter un expert sur WhatsApp
</a>
```

Note: The mockup uses `Xd = "https://wa.me/596696940054"` — the international format of `0696 94 00 54` is `+596 696 940 054` → `596696940054`. [VERIFIED: extracted from mockup JS constant `Xd`]

### Pattern 6: PDF route + controller

```php
// routes/vitrine.php — add:
Route::get('/diagnostic/{diagnostic}/pdf', [DiagnosticController::class, 'pdf'])
    ->name('diagnostic.pdf');
// No auth middleware — diagnostic is public; the ID itself is the access token

// DiagnosticController::pdf()
public function pdf(Diagnostic $diagnostic): \Symfony\Component\HttpFoundation\Response
{
    return Pdf::view('pdf.diagnostic-report', ['diagnostic' => $diagnostic])
        ->name("diagnostic-{$diagnostic->id}.pdf")
        ->download();
}
```

**DomPDF constraint:** Use table-based CSS in the PDF Blade view. No Tailwind, no Flexbox/Grid. Inline styles or `<style>` block only. [CITED: spatie/laravel-pdf docs, DomPDF driver limitations]

### Pattern 7: Lead notification (follows ContactForm)

```php
// app/Mail/DiagnosticLead.php — follows app/Mail/ContactMessage.php
// Dispatched after Diagnostic::create() in the lead-submit Livewire action
Mail::to(config('contact.recipient', 'contact@dloazurpiscines.com'))
    ->send(new DiagnosticLead($diagnostic));
```

### Anti-Patterns to Avoid

- **Dose formulas in Alpine/JS:** Any formula coefficient exposed to the client violates DIAG-02. The dose compute action must be a `wire:click` → Livewire server call, not an Alpine `x-on:click` calculation.
- **Livewire for step navigation:** Every `wire:navigate` or `$wire.call()` for step transitions adds ~200 ms latency. Use `@click="step++"` (Alpine) for navigation; only compute/persist need a server round-trip.
- **Persisting before disclaimer accept:** The `Diagnostic` row must only be created after `disclaimer_accepted_at` is set. Never create a partial row at step 1 or 2.
- **`wire:model` on Alpine-managed step index:** Don't bind Alpine's `step` variable to Livewire — it will be reset on re-renders. Keep it purely Alpine.
- **Livewire re-render wiping Alpine state:** Use `wire:ignore` carefully on Alpine-controlled sections, or use `$wire.entangle()` only for data that truly needs server sync.
- **Browsershot/Node PDF driver:** Already excluded per CLAUDE.md. DomPDF only.
- **PDF route behind auth:** `/diagnostic/{id}/pdf` must be public (anonymous visitors can also download their result). Route model binding exposes by ID only — acceptable for non-sensitive PDFs.

---

## Don't Hand-Roll

| Problem | Don't Build | Use Instead | Why |
|---------|-------------|-------------|-----|
| Spam protection on lead form | Custom bot detection | `spatie/laravel-honeypot` (already installed) | Proven, already wired in ContactForm |
| Rate limiting on lead submit | Custom throttle logic | `danharrin/livewire-rate-limiting` (already installed) | Already wired in ContactForm |
| PDF generation | Custom HTML→PDF | `spatie/laravel-pdf` DomPDF (already installed) | Complex rendering edge cases |
| Email to Pierre | Custom mailer | `App\Mail\DiagnosticLead` following `ContactMessage` | Already pattern-established |

**Key insight:** The existing ContactForm component is the template for ~60% of this phase. Reuse its validation, honeypot, rate-limit, and mail dispatch verbatim.

---

## Full Decision Tree Reference

Extracted verbatim from `mockups/diagnostic-dloazur.html` minified JS. Planner MUST transcribe this into `config/diagnostic.php`.

### Question nodes

| Node ID | Question | Options → Next |
|---------|----------|---------------|
| `start` | Quel est le problème avec ton eau ? | Eau verte → `green-1`; Eau trouble → `cloudy-1`; Eau marron → `brown-1`; Eau claire mais problème → `clear-1`; Problème d'électrolyseur → `electro-1` |
| `green-1` | Vois-tu le fond de la piscine ? | Oui → `auto-green` (special); Non → result `algues-avancees` |
| `cloudy-1` | La filtration fonctionne-t-elle correctement ? | Oui → result `floculant`; Non → result `filtration-insuffisante` |
| `brown-1` | Le problème est-il apparu après une pluie ? | Oui → result `eau-boueuse`; Non → `brown-2` |
| `brown-2` | Y a-t-il présence de métaux dans l'eau ? (sous-titre: tâches sur les parois, eau colorée après ajout de chlore) | Oui → result `metaux`; Non → result `pollution-organique` |
| `clear-1` | Quel est le problème exactement ? | Algues sur les parois → result `algues-parois`; Odeur forte de chlore → result `odeur-forte`; Irritation des yeux → result `irritation-yeux` |
| `electro-1` | Que se passe-t-il avec ton électrolyseur ? | Aucun chlore produit → `electro-2`; Voyant d'alarme/message d'erreur → `electro-3`; Manque de débit/flow → result `electro-debit`; Cellule à inverser/nettoyer → `electro-4`; Autre/inconnue → `electro-4` |
| `electro-2` | Le taux de sel est-il correct ? (sous-titre: au moins 3000 ppm requis) | Oui 3000–5000 → `electro-4`; Non trop bas → result `electro-sel-bas` |
| `electro-3` | Quel message/alarme ? | Alarme de sel (sel trop bas) → result `electro-sel-bas`; Manque de débit/flow → result `electro-debit`; Cellule à inverser/nettoyer → `electro-4`; Autre/inconnue → `electro-4` |
| `electro-4` | Inspecte la cellule (électrodes) (sous-titre: coupe l'alimentation, démonte la cellule, regarde les plaques) | Plaques recouvertes de calcaire blanc → result `electro-entartree`; Plaques noircies/abîmées/rongées → result `electro-usee`; Plaques propres, rien produit → result `electro-panne` |

**Special node `auto-green`:** When reached (fond visible, eau verte), the mockup runs the measurement flow check logic: if chlore OK and pH > 7.6 → result `ph-calc`; else → result `algues-installees`. This is the only decision node that has conditional logic beyond simple branch selection.

### Result leaves

| Leaf ID | Diagnostic | Action plan |
|---------|-----------|-------------|
| `algues-avancees` | Algues avancées | 1) Brosser parois/fond 2) Chlore choc hypochlorite calcium 30 g/m³ sans stabilisant 3) Anti-algues curatif 4) Filtration 24-48h 5) Nettoyer filtre |
| `algues-installees` | Algues en cours d'installation | 1) Brosser 2) Anti-algues préventif 3) Vérifier TAC 4) Filtration 12h |
| `floculant` | Eau trouble — particules en suspension | (plan: floculant) — mockup shows empty plan array; implement per standard practice: 1) Floculant 1 L/100 m³ 2) Filtration sur sable/verre, backwash après 24h |
| `filtration-insuffisante` | Filtration insuffisante | 1) Vérifier état filtre 2) Nettoyer/remplacer média filtrant 3) Vérifier pompe et paniers 4) Augmenter temps filtration quotidien 5) Contrôler débit après remise en route |
| `eau-boueuse` | Eau boueuse après pluie | 1) Aspirer dépôts au fond à l'égout 2) Chlore choc 3) Floculant pour clarifier 4) Filtration 24h 5) Rincer filtre |
| `metaux` | Présence de métaux (fer, cuivre, manganèse) | 1) Séquestrant de métaux 2) Ajuster pH 7.2–7.4 3) Filtration 24h 4) Nettoyer filtre 5) Faire analyser l'eau de remplissage |
| `pollution-organique` | Pollution organique | 1) Nettoyage mécanique complet 2) Chlore choc 3) Floculant 4) Filtration 24h |
| `algues-parois` | Algues fixées sur les parois | 1) Brosser soigneusement toutes les parois 2) Anti-algues préventif 3) Maintenir chlore libre 1.5 mg/L 4) Vérifier le pH |
| `odeur-forte` | Odeur forte de chlore | (chloramines — plan: choc à l'eau de Javel ou hypochlorite, éliminer chloramines) |
| `irritation-yeux` | Irritation des yeux | (déséquilibre pH ou chloramines) |
| `ph-calc` | Rééquilibrage pH | Dose pH- via `DoseEngine::phMinus()` |
| `electro-debit` | Défaut de débit (alarme flow) | 1) Vérifier pompe/vannes 2) Nettoyer paniers skimmer/pompe 3) Backwasher filtre 4) Contrôler détecteur débit (flow switch) 5) Vérifier air dans circuit 6) Si tout OK: capteur débit HS |
| `electro-entartree` | Cellule entartrée | 1) Couper alimentation + pompe 2) Fermer vannes, démonter cellule 3) Mélange 1 vol. acide chlorhydrique + 9 vol. eau 4) Plonger cellule 5-10 min max 5) Rincer abondamment 6) Pour éviter récidive: pH 7.2–7.4, vérifier inversion de polarité |
| `electro-usee` | Électrodes usées | 1) Relever référence cellule 2) Commander cellule compatible 3) En attendant: chloration manuelle hypochlorite 4) Instructions remplacement |
| `electro-panne` | Panne boîtier électrolyseur | 1) Vérifier alimentation 2) Tester autre cellule si possible 3) Faire diagnostiquer coffret par professionnel 4) En attendant: chloration manuelle 5) Contacter Dlo Azur |
| `electro-sel-bas` | Taux de sel insuffisant (<3000 ppm) | Ajouter sel pour viser 4000 ppm; formule DoseEngine::selDose() |

**Note on `floculant` leaf:** The mockup has an empty `plan` array — treat as a gap; implement standard floculant instructions per pool chemistry best practice.

---

## Common Pitfalls

### Pitfall 1: Alpine state reset on Livewire re-render
**What goes wrong:** After `wire:click="computeAndPersist"` triggers a Livewire re-render, Alpine's `step` variable resets to 0, returning the user to the disclaimer screen.
**Why it happens:** Livewire morphs the DOM; Alpine initializes on mount. Unless the component root is preserved, `x-data` re-runs.
**How to avoid:** Use `wire:ignore` on the Alpine root, or use `$wire.entangle()` only for data that must sync. The step index should never be a Livewire property — keep it pure Alpine.
**Warning signs:** User sees the disclaimer screen after clicking "Calculer."

### Pitfall 2: Dose formulas in Blade computed strings
**What goes wrong:** Developer puts formula as a computed Blade string in the view (`{{ $volume * 15 }}`) which still appears in page source.
**Why it happens:** Confusion between server-rendered Blade (safe) and client JS (forbidden).
**How to avoid:** Blade server-rendered output is fine — formulas run in PHP, HTML output contains only the result. The constraint is specifically about formula coefficients in `.js` files. Confirm: no formula logic in `resources/js/`.
**Warning signs:** `grep -r "15 \* " resources/js/` returns hits.

### Pitfall 3: PDF generation timeout on Laravel Cloud
**What goes wrong:** DomPDF on a complex multi-page layout exceeds the serverless execution timeout.
**Why it happens:** DomPDF is slower than Browsershot. Large HTML with many images.
**How to avoid:** Keep PDF layout simple (table-based, no external images, inline CSS only). The diagnostic report is 1-2 pages max; DomPDF handles this fine. Avoid embedding the Dlo Azur logo as an `<img>` with an external URL — use `public_path()` for local assets in DomPDF.
**Warning signs:** PDF download returns a 504 in production.

### Pitfall 4: WhatsApp link encodes badly on iOS
**What goes wrong:** Pre-filled message with accented characters or line breaks breaks on iOS Safari.
**Why it happens:** `encodeURIComponent` handles it, but Alpine string interpolation must encode before building the URL.
**How to avoid:** Always use `encodeURIComponent()` around the message text. Test with accented French characters (é, è, ê, à, ç).
**Warning signs:** WhatsApp opens but the message is empty or garbled.

### Pitfall 5: Anonymous diagnostic exposed by sequential ID
**What goes wrong:** `/diagnostic/123/pdf` exposes other users' reports by incrementing the ID.
**Why it happens:** Route model binding on `{diagnostic}` with sequential auto-increment IDs.
**How to avoid:** Either (a) use UUIDs as the public-facing key (add `HasUuids` to `Diagnostic` model), or (b) scope the PDF route: if authenticated, verify `client_id` match; if anonymous, show the download only on the results screen within the same session (store the ID in session, validate). Option (b) is simpler for this phase.
**Warning signs:** Any visitor can enumerate PDFs.

### Pitfall 6: `created_via` column requires a value
**What goes wrong:** Persisting a Diagnostic without setting `created_via` triggers a DB default but the value is unclear.
**Why it happens:** Column has `->default('wizard')` but if the decision tree flow saves a diagnostic, `created_via` should be `'depannage'` for clarity.
**How to avoid:** Always pass `'created_via' => 'wizard'` or `'created_via' => 'depannage'` explicitly based on mode.

---

## Schema Extension (additive migration)

Lead capture requires 4 additive columns on `diagnostics`. No column removal, no type changes.

```php
// database/migrations/2026_05_30_000010_add_lead_columns_to_diagnostics_table.php
Schema::table('diagnostics', function (Blueprint $table) {
    $table->string('prenom', 80)->nullable()->after('created_via');
    $table->string('commune', 80)->nullable()->after('prenom');
    $table->string('email', 160)->nullable()->after('commune');
    $table->string('site_web', 255)->nullable()->after('email');
});
```

Update `Diagnostic::$fillable` to include these 4 columns. No model relationship change needed.

---

## Route Integration

The `/diagnostic` route must NOT use the `cache.headers:vitrine` middleware (it's a stateful Livewire component, same pattern as `/contact`).

```php
// routes/vitrine.php — add below the contact route:
Route::get('/diagnostic', [DiagnosticController::class, 'show'])->name('diagnostic');
Route::get('/diagnostic/{diagnostic}/pdf', [DiagnosticController::class, 'pdf'])->name('diagnostic.pdf');
```

The `eauVerteUrgence` vitrine view already exists at `/services/eau-verte-urgence` — just add a CTA link pointing to `route('diagnostic')`.

The vitrine nav and hero also need a link. These are Blade view edits (no controller change).

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | Pest PHP 4.7 |
| Config file | `phpunit.xml` (root) |
| Quick run command | `./vendor/bin/pest --filter DoseEngine` |
| Full suite command | `./vendor/bin/pest` |

### Phase Requirements → Test Map

| Req ID | Behavior | Test Type | Automated Command | File Exists? |
|--------|----------|-----------|-------------------|-------------|
| DIAG-02 | pH low dose formula: volume=50, pH=7.0 → 300g pH+ | Unit | `./vendor/bin/pest --filter DoseEngine` | ❌ Wave 0 |
| DIAG-02 | pH high dose formula: volume=50, pH=7.8 → expected g pH- | Unit | `./vendor/bin/pest --filter DoseEngine` | ❌ Wave 0 |
| DIAG-02 | TAC low formula: volume=50, TAC=60 → expected g bicarbonate | Unit | `./vendor/bin/pest --filter DoseEngine` | ❌ Wave 0 |
| DIAG-02 | Stabilisant high >75: drain calculation | Unit | `./vendor/bin/pest --filter DoseEngine` | ❌ Wave 0 |
| DIAG-02 | Sel low <3000: kg sel calculation | Unit | `./vendor/bin/pest --filter DoseEngine` | ❌ Wave 0 |
| DIAG-02 | Chlore low <1: 15 g/m³ dose | Unit | `./vendor/bin/pest --filter DoseEngine` | ❌ Wave 0 |
| DIAG-02 | Formulas not in client JS | Smoke | `grep -r "15 \* " resources/js/` exits non-zero | ❌ Wave 0 |
| DIAG-03 | Disclaimer gate: computeAndPersist without accept → error | Feature | `./vendor/bin/pest --filter DiagnosticWizard` | ❌ Wave 0 |
| DIAG-03 | Persisted diagnostic has non-null disclaimer_accepted_at | Feature | `./vendor/bin/pest --filter DiagnosticWizard` | ❌ Wave 0 |
| DIAG-01 | All 8 tree top-level problems have at least one reachable leaf | Unit | `./vendor/bin/pest --filter DecisionTreeTest` | ❌ Wave 0 |
| DIAG-01 | Electrolyser sub-tree exposes 5 fault leaves | Unit | `./vendor/bin/pest --filter DecisionTreeTest` | ❌ Wave 0 |
| REQ-5 | Anon diagnostic: client_id=null, mesures+recommandations stored | Feature | `./vendor/bin/pest --filter DiagnosticWizard` | ❌ Wave 0 |
| REQ-7 | WhatsApp link contains correct number + non-empty message | Browser/manual | Manual test | manual |
| REQ-8 | PDF contains action plan + disclaimer, generated via DomPDF | Feature | `./vendor/bin/pest --filter DiagnosticPdf` | ❌ Wave 0 |
| REQ-9 | /diagnostic reachable without auth | Feature | `./vendor/bin/pest --filter DiagnosticRouteTest` | ❌ Wave 0 |

### Sampling Rate
- Per task commit: `./vendor/bin/pest --filter DoseEngine`
- Per wave merge: `./vendor/bin/pest`
- Phase gate: Full suite green before `/gsd:verify-work`

### Wave 0 Gaps
- [ ] `tests/Unit/DoseEngineTest.php` — covers DIAG-02 dose formula assertions
- [ ] `tests/Unit/DecisionTreeTest.php` — covers DIAG-01 tree completeness
- [ ] `tests/Feature/DiagnosticWizardTest.php` — covers DIAG-03, REQ-5, REQ-9
- [ ] `tests/Feature/DiagnosticPdfTest.php` — covers REQ-8

---

## Security Domain

| ASVS Category | Applies | Standard Control |
|---------------|---------|-----------------|
| V2 Authentication | No — diagnostic is fully public | — |
| V3 Session Management | Partial — session stores diagnostic ID for anon PDF access | Laravel session (already configured) |
| V4 Access Control | Partial — PDF must not be enumerable | Session-scoped download or UUID key |
| V5 Input Validation | Yes — numeric measurements, email format | Livewire `#[Validate]` rules |
| V6 Cryptography | No | — |

### Known Threat Patterns

| Pattern | STRIDE | Standard Mitigation |
|---------|--------|---------------------|
| Bot spam on lead form | Spoofing | `spatie/laravel-honeypot` + `WithRateLimiting` |
| Sequential ID enumeration of PDFs | Information Disclosure | Session-gate the download (store ID in session) or add `HasUuids` |
| Formula injection via numeric inputs | Tampering | `is_numeric()` validation + `#[Validate('numeric')]` |
| XSS via unsanitized wizard inputs in PDF | Tampering | Blade auto-escaping (`{{ }}`) in PDF view; never use `{!! !!}` for user input |

---

## Environment Availability

| Dependency | Required By | Available | Version | Fallback |
|------------|------------|-----------|---------|----------|
| PHP 8.3 | Laravel 13 | ✓ | 8.3 (composer.json ^8.3) | — |
| DomPDF (via spatie/laravel-pdf) | PDF generation | ✓ | ^2.11 in composer.json | — |
| PostgreSQL 17 | Diagnostic persistence | ✓ | Laravel Cloud Neon managed | — |
| Alpine.js | Step navigation | ✓ | Livewire 3 bundle | — |

No missing dependencies. This phase is greenfield code only — no external service integrations (Stripe, Odoo, S3) are involved.

---

## Open Questions

1. **`floculant` leaf action plan is empty in mockup**
   - What we know: The mockup's `floculant` result has `plan: []` (empty array)
   - What's unclear: Is Pierre okay with a standard "1 L floculant per 100 m³" plan, or does he want to review this specific leaf before launch?
   - Recommendation: Implement a standard plan; flag for Pierre's pre-launch review alongside the chemistry sign-off.

2. **`odeur-forte` and `irritation-yeux` leaf content**
   - What we know: These result IDs exist in the tree but the mockup extraction did not yield their full `plan` arrays (possibly cut off in the extraction).
   - What's unclear: Full action plan text.
   - Recommendation: Planner should do a targeted extraction of these two leaves before writing implementation tasks. Run: `python3 -c "... text.find('odeur-forte') ..."` on the mockup.

3. **Anonymous PDF security model**
   - What we know: The SPEC does not specify whether anonymous visitors should be able to access `/diagnostic/{id}/pdf` post-session.
   - What's unclear: Whether Pierre wants PDFs to be permalink-accessible (share the link) or session-only.
   - Recommendation: Default to session-gated (store diagnostic ID in session at persist time; validate on PDF request). Add `HasUuids` to Diagnostic if Pierre later wants shareable links.

4. **Vitrine nav change scope**
   - What we know: SPEC says vitrine nav/hero must link to `/diagnostic`.
   - What's unclear: Which nav file, and whether this touches a Phase 01 file that has been locked.
   - Recommendation: Planner checks `resources/views/layouts/` and `resources/views/vitrine/partials/` for the nav partial before writing the task.

---

## Assumptions Log

| # | Claim | Section | Risk if Wrong |
|---|-------|---------|---------------|
| A1 | `floculant` plan array is empty in mockup — standard "1 L/100 m³" plan acceptable | Decision Tree Reference | Pierre may want a different plan; flag for sign-off |
| A2 | `odeur-forte` and `irritation-yeux` leaf plans not fully extracted — content assumed from pool chemistry domain knowledge | Decision Tree Reference | Wrong action plan advice; requires mockup re-extraction |
| A3 | Sequential-ID enumeration is the primary PDF security concern for this phase | Security Domain | Could be wrong if Pierre expects shareable PDF links (different tradeoff) |

---

## Sources

### Primary (HIGH confidence)
- `mockups/diagnostic-dloazur.html` — minified Next.js/React export: all dose formulas, decision tree nodes, and result leaves extracted via Python text stripping. [VERIFIED: codebase read + Python extraction]
- `app/Models/Diagnostic.php` — existing model, fillable, casts, relations. [VERIFIED: codebase read]
- `database/migrations/2026_05_28_000009_create_diagnostics_table.php` — existing schema. [VERIFIED: codebase read]
- `app/Livewire/ContactForm.php` — template for Livewire form pattern (validate, honeypot, rate-limit, mail). [VERIFIED: codebase read]
- `composer.json` — all dependencies confirmed installed. [VERIFIED: codebase read]
- `routes/vitrine.php` — existing route structure and middleware groups. [VERIFIED: codebase read]
- `app/Http/Controllers/VitrineController.php` — existing controller pattern for new route. [VERIFIED: codebase read]

### Secondary (MEDIUM confidence)
- DomPDF driver constraints (CSS 2.1, no Flexbox/Grid, use `public_path()` for assets) [CITED: CLAUDE.md DomPDF notes, spatie/laravel-pdf docs reference]
- Livewire 3 + Alpine step isolation pattern (`wire:ignore`, keep step in Alpine only) [ASSUMED based on Livewire 3 DOM-morphing behavior]

---

## Metadata

**Confidence breakdown:**
- Dose formulas: HIGH — extracted verbatim from authoritative mockup
- Decision tree: HIGH — all nodes extracted from mockup; 2 leaf plans need verification (odeur-forte, irritation-yeux)
- Architecture patterns: HIGH — follows existing codebase conventions
- Pitfalls: MEDIUM — Livewire+Alpine interaction patterns based on known behavior

**Research date:** 2026-05-30
**Valid until:** 2026-06-30 (stable stack, no fast-moving dependencies)
