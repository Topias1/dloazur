# Phase 05: Diagnostic Commercialisable - Pattern Map

**Mapped:** 2026-05-30
**Files analyzed:** 13 new/modified
**Analogs found:** 10 / 13 (3 are greenfield — closest structural convention cited)

## File Classification

| New/Modified File | Role | Data Flow | Closest Analog | Match Quality |
|-------------------|------|-----------|----------------|---------------|
| `app/Livewire/DiagnosticWizard.php` | component (Livewire, full-page) | request-response (form + compute submit + persist) | `app/Livewire/ContactForm.php` | exact (form) + structural (multi-step) |
| `resources/views/livewire/diagnostic-wizard.blade.php` | component view | request-response + client-state (Alpine) | `resources/views/livewire/contact-form.blade.php` + `piscine-form.blade.php` | exact (form fields) + structural (steps) |
| `app/Services/Diagnostic/DoseEngine.php` | service (pure functions) | transform (measurements → cards) | `app/Services/GoogleReviewsService.php` | role-match (namespace/structure only) |
| `config/diagnostic.php` | config (versioned PHP array) | static data (decision tree + formulas) | `config/contact.php`, `config/pricing.php` | role-match (config-array idiom) |
| `app/Http/Controllers/DiagnosticController.php` | controller | request-response (view) + file-I/O (PDF download) | `app/Http/Controllers/VitrineController.php` | exact (view method) + greenfield (pdf method) |
| `resources/views/vitrine/diagnostic.blade.php` | view (full-page wrapper) | request-response | `resources/views/vitrine/contact.blade.php` | exact |
| `resources/views/pdf/diagnostic-report.blade.php` | view (DomPDF, table-CSS) | file-I/O (PDF render) | `resources/views/emails/contact-message.blade.php` | structural (inline-CSS HTML doc) — NO Pdf analog |
| `app/Mail/DiagnosticLead.php` | mailer | event-driven (notify Pierre) | `app/Mail/ContactMessage.php` | exact |
| `resources/views/emails/diagnostic-lead.blade.php` | email view | — | `resources/views/emails/contact-message.blade.php` | exact |
| `database/migrations/2026_05_30_xxxxxx_add_lead_columns_to_diagnostics_table.php` | migration (additive) | schema | `2026_05_28_000009_create_diagnostics_table.php` (Schema::table additive) | role-match |
| `tests/Unit/DoseEngineTest.php` | test (unit) | — | `tests/Feature/GoogleReviewsServiceTest.php` | role-match (Pest unit-of-service) |
| `tests/Unit/DecisionTreeTest.php` | test (unit) | — | `tests/Feature/GoogleReviewsServiceTest.php` | role-match (asserts on config/data) |
| `tests/Feature/DiagnosticWizardTest.php` | test (feature, Livewire) | — | `tests/Feature/ContactFormTest.php` | exact |
| `tests/Feature/DiagnosticPdfTest.php` | test (feature, route) | — | `tests/Feature/ContactFormTest.php` (route-render assertions) | role-match |

> **Routes** (`routes/vitrine.php`) and the **`Diagnostic` model** (`app/Models/Diagnostic.php`) are *modified*, not created. The model is reused as-is per CONTEXT D-03 — only `$fillable` gains the 4 lead columns. See Pattern Assignments below.

---

## Pattern Assignments

### `app/Livewire/DiagnosticWizard.php` (Livewire component, request-response)

**Analog:** `app/Livewire/ContactForm.php` — this is the canonical lead-form pattern AND the host for the dose-compute submit + persistence (CONTEXT D-01). Reuse its traits, validation idiom, rate-limit/honeypot/mail-try-catch verbatim. Borrow `mount()`/edit-prefill shape from `PiscineForm.php` for the logged-in `client_id`/`piscine_id` + filtration prefill.

**Traits + imports** (`ContactForm.php:5-17`):
```php
use App\Mail\ContactMessage;                                            // → App\Mail\DiagnosticLead
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use DanHarrin\LivewireRateLimiting\WithRateLimiting;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Spatie\Honeypot\Http\Livewire\Concerns\HoneypotData;
use Spatie\Honeypot\Http\Livewire\Concerns\UsesSpamProtection;

class ContactForm extends Component
{
    use WithRateLimiting, UsesSpamProtection;
```

**`#[Validate]` property idiom** (`ContactForm.php:19-29` + `PiscineForm.php:13-32`) — note numeric measurements must follow PiscineForm's `numeric` rule, and lead fields mirror ContactForm:
```php
// Lead capture — mirror ContactForm exactly (SPEC Req 6: Prénom + Commune required, email optional)
#[Validate('required|string|max:80')]  public string $prenom  = '';
#[Validate('required|string|max:80')]  public string $commune = '';
#[Validate('nullable|email|max:160')]  public string $email   = '';
#[Validate('nullable|url|max:255')]    public string $siteWeb = '';   // "Site web" — url rule

// Measurements — follow PiscineForm numeric idiom (PiscineForm.php:19 volume_m3)
#[Validate('nullable|numeric|min:1|max:1000')] public string $volume = '';
// pH, chlore, alcalinité, stabilisant, selPpm → 'nullable|numeric' each (SPEC Req 2: numeric validation)
```

**Honeypot mount** (`ContactForm.php:33-38`) — copy verbatim:
```php
public HoneypotData $extraFields;

public function mount(): void
{
    $this->extraFields = new HoneypotData();
}
```
> For the logged-in path, extend `mount()` with the `PiscineForm.php:38-53` prefill shape (load the client's pool, prefill `filtration` as a *hint* — see Shared Patterns → Filter-type normalization).

**Submit guard chain** (`ContactForm.php:40-85`) — this exact 5-step order is the house pattern; the lead-submit action copies it 1:1, and `computeAndPersist()` reuses steps 1-3 then swaps mail for `Diagnostic::create()`:
```php
public function submit(): void
{
    // 1. Rate limit
    try { $this->rateLimit(5, 60); }
    catch (TooManyRequestsException) {
        $this->addError('throttle', "Trop d'essais. Attendez quelques minutes puis réessayez.");
        return;
    }
    // 2. Honeypot — silently swallow bots
    try { $this->protectAgainstSpam(); }
    catch (\Throwable) { return; }
    // 3. Validate
    $this->validate();
    // 4. Side effect (mail) wrapped so server errors don't leak
    try {
        Mail::to(config('contact.recipient', 'contact@dloazurpiscines.com'))
            ->send(new ContactMessage(/* ... */));
    } catch (\Throwable $e) {
        Log::error('Contact form mail send failed', ['exception' => $e->getMessage()]);
        $this->addError('send', "L'envoi a échoué. Vérifiez votre connexion ou contactez-nous sur WhatsApp.");
        return;
    }
    // 5. Success
    $this->sent = true;
    $this->reset([/* ... */]);
}
```

**Disclaimer-gate server enforcement** (RESEARCH Pattern 4 — no analog, follow this) — add before any compute/persist; guarantees DIAG-03:
```php
public function computeAndPersist(): void
{
    if (! $this->disclaimerAccepted) {
        $this->addError('disclaimer', 'Vous devez accepter les conditions avant de continuer.');
        return;
    }
    $recs = \App\Services\Diagnostic\DoseEngine::compute($this->mesures(), (float) $this->volume);
    $diagnostic = Diagnostic::create([
        'client_id'              => auth('clients')->id(),   // null when anonymous (SPEC Req 5)
        'piscine_id'             => $this->piscineId,
        'volume_m3'              => $this->volume ?: null,
        'mesures'                => $this->mesures(),
        'recommandations'        => $recs,
        'disclaimer_accepted_at' => now(),                   // never null on a dosing row (D-04)
        'created_via'            => 'wizard',                // or 'depannage' (RESEARCH Pitfall 6)
    ]);
    $this->savedDiagnosticId = $diagnostic->id;
    session()->put('diagnostic_ids', array_merge(session('diagnostic_ids', []), [$diagnostic->id])); // D-06 PDF gate
}
```
> Persist guard: `Persist only after disclaimer accept` (RESEARCH anti-pattern). `created_via` MUST be set explicitly (RESEARCH Pitfall 6). Auth guard is `clients` (verify: `app/Http/Controllers/Portail/*`, `config/auth.php` defines a `clients` guard).

**`render()`** (`ContactForm.php:87-90` / `PiscineForm.php:94-97`):
```php
public function render(): View { return view('livewire.diagnostic-wizard'); }
```

---

### `resources/views/livewire/diagnostic-wizard.blade.php` (component view, Alpine-driven steps)

**Analogs:** `contact-form.blade.php` (form fields, honeypot, success state, error display, submit-loading, WhatsApp link), `piscine-form.blade.php` (numeric input + select field idiom for the wizard steps).

**Honeypot directive** (`contact-form.blade.php:1-5`) — copy verbatim into the lead step:
```blade
<div aria-hidden="true" tabindex="-1" style="display:none">
    <x-honeypot livewire-model="extraFields" />
</div>
```

**Numeric measurement input** (`piscine-form.blade.php:17-32`) — the template for every measurement field (pH, chlore, TAC, stabilisant, sel ppm):
```blade
<input wire:model="ph" type="number" inputmode="decimal" step="0.1" min="0"
    class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none"
    placeholder="ex: 7.4">
@error('ph') <p class="mt-1 text-sm text-danger">{{ $message }}</p> @enderror
```

**Filter-type `<select>`** (`piscine-form.blade.php:51-65`) — this is the canonical filtration select; the wizard's filter-type question node reuses it BUT must extend the option set to the floculant-branch canonical values (`sable`, `verre`, `cartouche`, `diatomées`) per `05-FLOCULANT-BRANCH-SPEC.md` (analog only offers `sable/cartouche/diatomée`):
```blade
<select wire:model="filtration" class="w-full h-12 px-4 rounded-xl bg-sand-50 ring-1 ring-sand-200 focus:ring-2 focus:ring-azure-500 outline-none">
    <option value="">— Sélectionner —</option>
    <option value="sable">Sable</option>
    <option value="cartouche">Cartouche</option>
    <option value="diatomée">Diatomée</option>
</select>
```

**Submit button with `wire:loading`** (`contact-form.blade.php:106-115`) — reuse for "Calculer mon plan" / "Envoyer mes coordonnées":
```blade
<button type="submit" wire:loading.attr="disabled" wire:loading.class="opacity-60 cursor-not-allowed"
    class="w-full h-12 px-6 rounded-xl bg-azure-500 text-white font-semibold hover:bg-azure-600 active:bg-azure-700 transition-colors flex items-center justify-center gap-2">
    <span wire:loading.remove>Calculer mon plan</span>
    <span wire:loading>Calcul en cours…</span>
</button>
```

**Success state + WhatsApp deep link** (`contact-form.blade.php:7-21, 119-130`) — the WhatsApp hand-off (SPEC Req 7) reuses this exact `wa.me/596696940054` link; for the diagnostic, build the pre-filled `?text=` via Alpine `:href` + `encodeURIComponent` (RESEARCH Pattern 5, Pitfall 4):
```blade
<a href="https://wa.me/596696940054" rel="noopener noreferrer" target="_blank"
   class="inline-flex items-center gap-2 h-12 px-6 rounded-xl bg-[#25D366] text-white font-semibold hover:bg-[#20c05a] transition-colors">
   Ou directement sur WhatsApp
</a>
{{-- Diagnostic variant (RESEARCH Pattern 5): --}}
<a :href="`https://wa.me/596696940054?text=${encodeURIComponent(waMessage)}`" target="_blank" rel="noopener">…</a>
```

**Alpine step navigation** (RESEARCH Pattern 1 — NO codebase analog; this is new). Step index lives ONLY in Alpine (`x-data="{ step: 0, mode: null }"`), never a Livewire property (RESEARCH Pitfall 1: Livewire re-render resets Alpine state). Use `@click="step++"` for nav, `wire:click` only for compute/persist/lead submit. Guard Alpine-controlled regions with `wire:ignore.self` / `x-cloak`.

> The vitrine uses semantic Tailwind tokens (`azure-500`, `ink-700`, `sand-50`, `danger`, `font-display`) defined in `resources/css/app.css` `@theme` — match them; do not introduce raw hex except the brand WhatsApp green `#25D366`.

---

### `app/Services/Diagnostic/DoseEngine.php` (service, transform) — NO BEHAVIORAL ANALOG

**Closest structural convention:** `app/Services/GoogleReviewsService.php` — the only existing `app/Services/*` class. It establishes: `namespace App\Services;` (DoseEngine extends to `App\Services\Diagnostic;`), plain class, docblock-per-method describing contract + return shape, resolved via `app(...)`. **It does NOT match behaviorally** — GoogleReviewsService does HTTP/DB I/O with instance methods; DoseEngine is **pure static functions, zero side-effects, zero dependencies** (CONTEXT D-02). Follow GoogleReviewsService's *file/namespace/docblock* shape only.

GoogleReviewsService namespace + docblock convention (`GoogleReviewsService.php:1-19`):
```php
namespace App\Services;
// ...
class GoogleReviewsService
{
    /**
     * Fetch the Google Places Details API and upsert reviews into the local cache.
     * Returns the number of reviews processed (0 on failure or when disabled).
     */
    public function fetchAndUpsert(): int
```

**The actual DoseEngine signatures + all formulas are fully transcribed in `05-RESEARCH.md` Pattern 2** (lines 259-442) — `compute(array $mesures, float $volume): array`, `chloreChoc()`, `phMinus()`, etc. Planner copies those verbatim. Decimal parsing helper to keep: `(float) str_replace(',', '.', $value)` (handles French decimal comma).

> **Critical constraint (DIAG-02):** every formula stays in this PHP class. NEVER in `resources/js/`. The view triggers `wire:click="computeAndPersist"` (server round-trip), never an Alpine `x-on:click` arithmetic. Smoke test: `grep -r "15 \*" resources/js/` must return nothing (RESEARCH Pitfall 2).
> **Chemistry source of truth:** doses are expert-audited, NOT raw mockup baseline — apply the P0/P1 corrections in `05-DIAGNOSTIC-EXPERT-AUDIT.md` (e.g. chlore-bas rattrapage ≈3-4 g/m³ ≠ choc; treatment order TAC→pH→chlore→stab→sel; calcium-vs-sodium by TH) over the RESEARCH-extracted values where they conflict.

---

### `config/diagnostic.php` (config, versioned PHP array) — convention-match only

**Analogs:** `config/contact.php`, `config/pricing.php` — both are flat `return [ ... ]` arrays with banner comments tying each key to a decision ID. Laravel auto-loads everything in `config/` (verified: no `mergeConfigFrom` in `app/Providers/*`), so `config('diagnostic.questions.start')` works with zero registration.

`config/contact.php:1-32` idiom (banner comment + `return []`):
```php
<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Contact Form Configuration (SITE-05 — D-13/D-14/D-15/D-16)
    |--------------------------------------------------------------------------
    */
    'recipient'       => env('CONTACT_RECIPIENT', 'contact@dloazurpiscines.com'),
    'whatsapp_number' => env('WHATSAPP_NUMBER', '596696940054'),
    'rate_limit'      => ['attempts' => 5, 'decay_seconds' => 60],
];
```

**The decision-tree array shape is fully specified in `05-RESEARCH.md` Pattern 3** (lines 449-483: `questions` + `results` keys) and the **full node/leaf content in the "Full Decision Tree Reference" table** (RESEARCH lines 573-615). The eau-trouble/floculant sub-branch is overridden by `05-FLOCULANT-BRANCH-SPEC.md` (filter-type routing + pH gate — supersedes the empty `floculant` leaf and CONTEXT D-08). Add a `'version'` key (CONTEXT D-02: versioned, single-file review surface for Pierre's sign-off). Consider env-overridable values (`whatsapp_number`, disclaimer copy) per `contact.php`/`pricing.php` precedent.

> Splitting into `config/diagnostic-tree.php` + `config/diagnostic-formulas.php` is acceptable (RESEARCH Recommended Structure) — either is consistent with the one-file-per-domain config convention.

---

### `app/Http/Controllers/DiagnosticController.php` (controller, request-response + file-I/O)

**Analog:** `app/Http/Controllers/VitrineController.php` — the `show()` (GET `/diagnostic`) method copies its view-with-SEO-vars idiom exactly. The `pdf()` method is **greenfield** (no `Pdf::` usage exists anywhere in the repo — verified; `spatie/laravel-pdf ^2.11` is installed but never wired).

**`show()` — SEO view method** (`VitrineController.php:48-56, 88-101`) — mirror the `eauVerteUrgence` shape (it's an indexable page per SPEC Req 9, so include `canonical`; breadcrumb optional):
```php
public function contact(): View
{
    return view('vitrine.contact', [
        'title'       => 'Nous contacter · Dlo Azur Piscines',
        'description' => '…',
        'canonical'   => url('/contact'),
        'ogImage'     => asset('assets/brand/og-default.jpg'),
    ]);
}
```

**`pdf()` — RESEARCH Pattern 6** (no analog; follow this + D-06 session gate):
```php
use Spatie\LaravelPdf\Facades\Pdf;

public function pdf(Diagnostic $diagnostic): \Symfony\Component\HttpFoundation\Response
{
    // D-06: anonymous access is session-gated (mitigates sequential-ID enumeration, RESEARCH Pitfall 5)
    abort_unless(
        in_array($diagnostic->id, session('diagnostic_ids', []), true)
            || $diagnostic->client_id === auth('clients')->id(),
        403
    );
    return Pdf::view('pdf.diagnostic-report', ['diagnostic' => $diagnostic])
        ->name("diagnostic-{$diagnostic->id}.pdf")
        ->download();
}
```
> Do NOT add `HasUuids` to `Diagnostic` (CONTEXT D-06 — no shareable permalink this phase). Final import is `Spatie\LaravelPdf\Facades\Pdf` (verify exact FQN against vendor on first use).

---

### `resources/views/vitrine/diagnostic.blade.php` (full-page view wrapper)

**Analog:** `resources/views/vitrine/contact.blade.php` — exact pattern for hosting a full-page Livewire component inside the vitrine layout, including the `class_exists` guard + WhatsApp fallback.

`contact.blade.php:1-29` (copy structure, swap component + copy):
```blade
@extends('layouts.app')
@section('content')
    <div class="pt-32 pb-20 mx-auto max-w-content px-5 sm:px-8">
        <div class="max-w-lg mx-auto">
            <h1 class="font-display font-bold text-3xl sm:text-4xl text-ink-950">…</h1>
            <div class="mt-8">
                @if(class_exists(\App\Livewire\DiagnosticWizard::class))
                    <livewire:diagnostic-wizard />
                @else
                    {{-- WhatsApp fallback --}}
                @endif
            </div>
        </div>
    </div>
@endsection
```
> The wizard is wider than a contact form — the planner may relax `max-w-lg`. Tokens (`pt-32`, `max-w-content`, `ink-950`, `font-display`) come from the established `@theme`.

---

### `resources/views/pdf/diagnostic-report.blade.php` (DomPDF view) — NO Pdf analog

**Closest structural analog:** `resources/views/emails/contact-message.blade.php` — the only existing self-contained HTML doc with an inline `<style>` block and table-free, simple-CSS layout. This is exactly the DomPDF constraint surface (CSS 2.1, no Tailwind/Flexbox/Grid — CLAUDE.md + RESEARCH Pattern 6). Copy its `<!DOCTYPE html>` + `<style>`-in-`<head>` + `.field`/`.label`/`.value` block idiom; brand color `#0080ff` already used there.

`contact-message.blade.php:1-19` (the inline-CSS doc skeleton to mirror):
```blade
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>…</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 14px; color: #333; line-height: 1.6; }
        .header { background: #0080ff; color: #fff; padding: 24px 32px; }
        .field { margin-bottom: 20px; }
        .label { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #888; }
        .message-box { background: #f9f9f9; border-left: 3px solid #0080ff; padding: 12px 16px; }
    </style>
</head>
```
> DomPDF specifics (RESEARCH Pitfall 3): use `public_path()` for the logo `<img>` (no external URL), table-based layout for the Problème/Étapes/Dosage/Produit cards, keep it 1-2 pages. Blade auto-escaping `{{ }}` only — never `{!! !!}` on user input (RESEARCH Security V5/XSS). Must render the disclaimer text (SPEC Req 8 acceptance).

---

### `app/Mail/DiagnosticLead.php` (mailer, event-driven)

**Analog:** `app/Mail/ContactMessage.php` — exact pattern: `Mailable` + `Queueable, SerializesModels`, promoted constructor props, `envelope()` with `from`/`replyTo`, `content()` → Blade view. CONTEXT D-03 ties Pierre notification to this pattern.

`ContactMessage.php:12-43` (copy 1:1, adapt props to the diagnostic/lead):
```php
class ContactMessage extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $name,
        public readonly string $email,
        public readonly string $phone,
        public readonly string $message,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Nouveau message — Dlo Azur Piscines',
            from: new Address(config('mail.from.address', 'contact@dloazurpiscines.com'), config('mail.from.name', 'Dlo Azur Piscines')),
            replyTo: [new Address($this->email, $this->name)],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.contact-message');
    }
}
```
> For the lead, pass the `Diagnostic` (or its lead fields + summary) into the constructor; `replyTo` only when `$diagnostic->email` is present (email is optional per SPEC Req 6). Dispatch via `Mail::to(config('contact.recipient', ...))->send(new DiagnosticLead(...))` (RESEARCH Pattern 7) inside the lead-submit action.

---

### `resources/views/emails/diagnostic-lead.blade.php` (email view)

**Analog:** `resources/views/emails/contact-message.blade.php` — exact. Same inline-CSS doc, `.field`/`.label`/`.value` blocks, `#0080ff` header, `@if` around optional fields (`contact-message.blade.php:36-41` shows the optional-phone pattern → reuse for optional email/site web). Render Prénom, Commune, Email?, Site web?, and the diagnostic summary.

---

### `database/migrations/2026_05_30_xxxxxx_add_lead_columns_to_diagnostics_table.php` (additive migration)

**Analog:** `2026_05_28_000009_create_diagnostics_table.php` — same `return new class extends Migration` anonymous-class idiom; but use `Schema::table()` (additive) not `Schema::create()` (CONTEXT D-03 + SPEC constraint: additive only). Exact column spec is in `05-RESEARCH.md` Schema Extension (lines 662-672):
```php
Schema::table('diagnostics', function (Blueprint $table) {
    $table->string('prenom', 80)->nullable()->after('created_via');
    $table->string('commune', 80)->nullable()->after('prenom');
    $table->string('email', 160)->nullable()->after('commune');
    $table->string('site_web', 255)->nullable()->after('email');
});
```
> `down()` must `dropColumn([...])` those 4. Then add the 4 names to `Diagnostic::$fillable` (`app/Models/Diagnostic.php:10-19`) — model is otherwise reused as-is (CONTEXT D-03).

---

### `tests/Unit/DoseEngineTest.php` (unit test) — convention match

**Analog:** `tests/Feature/GoogleReviewsServiceTest.php` — the existing service-test convention: Pest functional style (`it('...', function () { ... })`), `expect(...)->toBe(...)`. DoseEngine is pure → **no `RefreshDatabase`, no `beforeEach` config** (drop those — GoogleReviewsServiceTest uses them only for its DB/HTTP needs). Put it in `tests/Unit/` (matches `tests/Unit/ExampleTest.php` location) and call `DoseEngine::compute(...)` directly. Assertions map to RESEARCH "Phase Requirements → Test Map" (lines 705-721) — e.g. volume=50, pH=7.0 → expected pH+ grams.

Pest `it()` + `expect()` idiom (`GoogleReviewsServiceTest.php:19-36`):
```php
it('fetchAndUpsert with valid API response upserts rows', function () {
    $count = app(GoogleReviewsService::class)->fetchAndUpsert();
    expect($count)->toBe(5);
});
```

---

### `tests/Unit/DecisionTreeTest.php` (unit test) — convention match

**Analog:** `tests/Feature/GoogleReviewsServiceTest.php` (Pest `it()`/`expect()` style). Asserts on `config('diagnostic.*')` data: every one of the 8 top-level problems reaches ≥1 leaf; electrolyser sub-tree exposes its 5 fault leaves (RESEARCH Test Map). Pure config assertions → no DB.

---

### `tests/Feature/DiagnosticWizardTest.php` (feature test, Livewire) — exact analog

**Analog:** `tests/Feature/ContactFormTest.php` — the canonical Livewire-component test. Reuse verbatim: `Livewire::test(...)->set(...)->call(...)`, `Mail::fake()` + `Mail::assertSent/assertNothingSent`, `->assertHasErrors([...])`, honeypot trip via `->set('extraFields.my_name', ...)`, rate-limit key `'livewire-rate-limiter:' . sha1(Class.'|submit|'.request()->ip())`, and route-render `$this->get('/diagnostic')->assertStatus(200)`.

Livewire test idioms to copy (`ContactFormTest.php:20-25, 43-53, 69-83, 89`):
```php
Livewire::test(ContactForm::class)
    ->set('name', 'Jean Dupont')->set('email', 'jean@example.com')
    ->call('submit')
    ->assertHasErrors(['name' => 'required']);

// honeypot:
->set('extraFields.my_name', 'I am a bot')->call('submit'); Mail::assertNothingSent();
// rate-limit key:
$key = 'livewire-rate-limiter:' . sha1(ContactForm::class . '|submit|' . request()->ip());
```
**Diagnostic-specific assertions** (RESEARCH Test Map): disclaimer gate (call `computeAndPersist` without accept → `assertHasErrors(['disclaimer'])`); persisted row has non-null `disclaimer_accepted_at`; anonymous → `client_id = null` with `mesures`+`recommandations` stored; `/diagnostic` reachable without auth. This test WILL need `uses(RefreshDatabase::class)` (it persists `Diagnostic` rows — see `GoogleReviewsServiceTest.php:9`).

---

### `tests/Feature/DiagnosticPdfTest.php` (feature test, route) — convention match

**Analog:** `tests/Feature/ContactFormTest.php` route-assertion blocks (`$this->get('/contact')->assertStatus(200)->assertSee(...)`). Asserts: completed diagnostic downloads a PDF containing the action plan + disclaimer (RESEARCH Test Map REQ-8); session-gated access returns 403 for a non-session, non-owner ID (CONTEXT D-06). Needs `RefreshDatabase`.

---

## Shared Patterns

### Spam protection (honeypot)
**Source:** `app/Livewire/ContactForm.php:12-13,17,33-38,53-58` + `resources/views/livewire/contact-form.blade.php:1-5`
**Apply to:** `DiagnosticWizard` lead-capture step
```php
use Spatie\Honeypot\Http\Livewire\Concerns\{HoneypotData, UsesSpamProtection};
class X extends Component {
    use UsesSpamProtection;
    public HoneypotData $extraFields;
    public function mount(): void { $this->extraFields = new HoneypotData(); }
    // in submit(): try { $this->protectAgainstSpam(); } catch (\Throwable) { return; }
}
```
```blade
<div aria-hidden="true" tabindex="-1" style="display:none"><x-honeypot livewire-model="extraFields" /></div>
```

### Rate limiting
**Source:** `app/Livewire/ContactForm.php:6-7,17,42-48`; tuning in `config/contact.php:27-30` (`5 / 60s`)
**Apply to:** `DiagnosticWizard` lead submit (and optionally the compute action)
```php
use DanHarrin\LivewireRateLimiting\{WithRateLimiting, Exceptions\TooManyRequestsException};
try { $this->rateLimit(5, 60); }
catch (TooManyRequestsException) { $this->addError('throttle', "Trop d'essais…"); return; }
```

### Pierre notification mail
**Source:** `app/Mail/ContactMessage.php` (whole) + `resources/views/emails/contact-message.blade.php`; recipient `config('contact.recipient')`
**Apply to:** all lead capture → `App\Mail\DiagnosticLead`
```php
Mail::to(config('contact.recipient', 'contact@dloazurpiscines.com'))->send(new DiagnosticLead($diagnostic));
```

### Error handling (side-effects)
**Source:** `app/Livewire/ContactForm.php:64-80` + `app/Livewire/PiscineForm.php:84-88` + `app/Services/GoogleReviewsService.php:75-81`
**Apply to:** every persist / mail / PDF action — wrap in `try { } catch (\Throwable $e) { Log::error(...); $this->addError(...); return; }`. Services never throw — they log and return a safe default (GoogleReviewsService idiom).

### WhatsApp deep link
**Source:** `resources/views/livewire/contact-form.blade.php:13-20,122-129` (`https://wa.me/596696940054`, `target="_blank" rel="noopener noreferrer"`, brand green `#25D366`); number also in `config/contact.php:20-21`
**Apply to:** SPEC Req 7 hand-off — same link, add `?text=${encodeURIComponent(...)}` via Alpine `:href` (RESEARCH Pattern 5; encode for iOS — Pitfall 4). International format of `0696 94 00 54` = `596696940054`.

### Validation idiom
**Source:** `app/Livewire/ContactForm.php:19-29` (text/email), `app/Livewire/PiscineForm.php:13-32` (numeric/exists)
**Apply to:** all `DiagnosticWizard` inputs — `#[Validate('...')]` attributes; numeric measurements use `'nullable|numeric'`, required lead fields `'required|string|max:80'`, email `'nullable|email|max:160'`.

### Public-route placement (no auth)
**Source:** `routes/vitrine.php:42` (`/contact` outside the cache group, stateful Livewire) vs `routes/admin.php` header (admin = `middleware(['web','auth'])->prefix('admin')` in `bootstrap/app.php`)
**Apply to:** `/diagnostic` + `/diagnostic/{diagnostic}/pdf` go in `routes/vitrine.php`, NOT in any auth group (SPEC Req 9). Like `/contact`, they are stateful Livewire → exclude from `cache.headers:vitrine` (RESEARCH Route Integration, lines 678-686):
```php
Route::get('/diagnostic', [DiagnosticController::class, 'show'])->name('diagnostic');
Route::get('/diagnostic/{diagnostic}/pdf', [DiagnosticController::class, 'pdf'])->name('diagnostic.pdf');
```

### Filter-type normalization (D-08 verified gap — NO analog, planner must build)
**Source of the gap:** `database/migrations/2026_05_28_000002_create_piscines_table.php:17` → `$table->string('filtration')->nullable()` (bare string, no enum, no length); `app/Livewire/PiscineForm.php:27-28` validates only `nullable|string|max:30`; `piscine-form.blade.php:51-65` offers `sable/cartouche/diatomée` but the column accepts any free text.
**Apply to:** the floculant sub-branch (`05-FLOCULANT-BRANCH-SPEC.md`) which routes on canonical filter types (`sable`/`verre` → floculant choc, `cartouche` → clarifiant, `diatomées` → nettoyage+clarifiant; the word "floculant" must NEVER appear in the cartridge path). Because `piscines.filtration` is free-text, the planner MUST NOT assume clean enum values. Per CONTEXT D-08, either (a) map/normalize the stored value to the canonical set with a fallback to the in-wizard question node when unmappable/empty, or (b) always show the constrained filter-type `<select>` in the wizard and treat the pool's `filtration` as a non-authoritative prefill hint. The wizard adds a filter-type question node before any product recommendation.

---

## No Analog Found

Files with no behavioral match in the codebase (use the cited spec/RESEARCH patterns, not a copy target):

| File | Role | Data Flow | Reason / where the pattern lives |
|------|------|-----------|----------------------------------|
| `app/Services/Diagnostic/DoseEngine.php` | service (pure fns) | transform | Only `GoogleReviewsService` exists and it's I/O-bound, not pure. Structure (namespace, docblock) from it; full formulas in `05-RESEARCH.md` Pattern 2 + `05-DIAGNOSTIC-EXPERT-AUDIT.md` corrections. |
| `config/diagnostic.php` (decision tree) | config | static data | `contact.php`/`pricing.php` give the config-array idiom only; tree content from `05-RESEARCH.md` Pattern 3 + Full Decision Tree Reference + `05-FLOCULANT-BRANCH-SPEC.md`. |
| `resources/views/pdf/diagnostic-report.blade.php` + `DiagnosticController::pdf()` | view + controller | file-I/O | `spatie/laravel-pdf ^2.11` installed but NEVER used (`grep` confirms zero `Pdf::` usage). Inline-CSS doc shape from `emails/contact-message.blade.php`; wiring from `05-RESEARCH.md` Pattern 6. |
| Alpine multi-step navigation (inside `diagnostic-wizard.blade.php`) | client-state | — | No multi-step Alpine wizard exists. Pattern from `05-RESEARCH.md` Pattern 1 + Pitfall 1 (keep `step` in Alpine only). |
| Disclaimer-gate enforcement | server guard | — | New (DIAG-03). Pattern from `05-RESEARCH.md` Pattern 4. |
| Filter-type normalization (floculant routing) | utility | transform | New. `piscines.filtration` is free-text (verified); see Shared Patterns above + `05-FLOCULANT-BRANCH-SPEC.md`. |

## Metadata

**Analog search scope:** `app/Livewire`, `app/Mail`, `app/Models`, `app/Http/Controllers`, `app/Services`, `routes/`, `config/`, `resources/views/{livewire,vitrine,emails,pdf}`, `tests/{Feature,Unit}`, `database/migrations/`
**Files scanned:** ~35 (read in full: ContactForm, PiscineForm, Diagnostic model, diagnostics migration, piscines migration, ContactMessage, VitrineController, vitrine routes, admin routes header, GoogleReviewsService, config/contact, config/pricing, contact-form view, contact-message email view, piscine-form view, contact vitrine view, ContactFormTest, GoogleReviewsServiceTest, ExampleTest)
**Key verifications:** `spatie/laravel-pdf ^2.11` installed + zero existing `Pdf::` usage (greenfield PDF); `piscines.filtration` = bare nullable string (D-08 gap confirmed); admin auth group is in `bootstrap/app.php` (so `/diagnostic` belongs in `routes/vitrine.php`, no auth); config auto-loaded (no `mergeConfigFrom` — `config/diagnostic.php` needs no registration).
**Pattern extraction date:** 2026-05-30
