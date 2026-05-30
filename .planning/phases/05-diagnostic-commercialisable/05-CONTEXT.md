# Phase 5: Diagnostic Commercialisable - Context

**Gathered:** 2026-05-30
**Status:** Ready for planning

<domain>
## Phase Boundary

A visitor (anonymous or logged-in) runs a **free** pool diagnostic from a public `/diagnostic` route — either a water-chemistry wizard (measurements → server-computed dosing plan) or a "Dépannage rapide" symptom decision tree — accepts a legal disclaimer before any dosing advice, and converts via lead-capture, WhatsApp hand-off to Pierre, and a downloadable PDF. Stripe monetization (DIAG-04) and the full multi-measure history dashboard (DIAG-05) are out of scope.

</domain>

<spec_lock>
## Requirements (locked via SPEC.md)

**9 requirements are locked.** See `05-SPEC.md` for full requirements, boundaries, and acceptance criteria.

Downstream agents MUST read `05-SPEC.md` before planning or implementing. Requirements are not duplicated here.

**In scope (from SPEC.md):** Public `/diagnostic` route + vitrine entry points (nav/hero CTA, eau-verte-urgence page, city pages); disclaimer acceptance gate (DIAG-03); water-chemistry wizard pool-info + measurements steps (DIAG-01); server-side dose engine producing quantified action-plan cards (DIAG-02, formulas = mockup baseline); "Dépannage rapide" symptom decision tree incl. electrolyser sub-tree (DIAG-01); anonymous diagnostics + linking to client account when logged in; lead-capture form (Prénom, Commune, Email, Site web); WhatsApp hand-off with pre-filled summary to 0696 94 00 54; downloadable PDF report; persisting `Diagnostic` rows.

**Out of scope (from SPEC.md):** Stripe monetization (DIAG-04); full multi-measure history dashboard (DIAG-05); authoring/validating the dose chemistry (Pierre signs off the mockup baseline pre-launch); native pool/piscine CRUD beyond linking; offline support (diagnostic is online-only).

</spec_lock>

<decisions>
## Implementation Decisions

### Wizard & decision-tree architecture
- **D-01:** One full-page **Livewire component** owns persistence and the dose-compute submit; **Alpine** handles step navigation in-browser (no network round-trip per step, satisfies SPEC constraint). Mirrors existing `ContactForm`/`PiscineForm` Livewire conventions.

### Dose engine & decision-tree home
- **D-02:** Dose engine lives in a **service class** (e.g. `app/Services/Diagnostic/DoseEngine.php`) of pure functions; the decision tree is a **versioned PHP/config array**. Rationale: server-only (formulas never reach client JS — DIAG-02 constraint), Pest-testable against the mockup baseline, single-file review surface for Pierre's pre-launch chemistry sign-off.

### Lead-capture storage
- **D-03:** Lead data stored as **additive columns on the `diagnostics` table** (`prenom`, `commune`, `email`, `site_web` — names TBD by planner) — the diagnostic row IS the lead (matches the 1:1 tie the SPEC describes). **No separate Lead model.** Pierre is notified via **Mail**, following the `ContactForm` mailer pattern.
- Schema change MUST be an additive migration (per SPEC constraint).

### Persistence timing & PDF delivery
- **D-04:** The `Diagnostic` row is persisted **on completion** (when results are computed / disclaimer accepted), guaranteeing a non-null `disclaimer_accepted_at` on any row carrying dosing advice (DIAG-03).
- **D-05:** PDF generated **synchronously on download** via `spatie/laravel-pdf` DomPDF driver — no queue infra (fits scale-to-zero Laravel Cloud, single-page render).
- **D-06:** Anonymous PDF access is **session-gated** (decision 2026-05-30). Store the diagnostic ID in the session at persist time and validate it on the `/diagnostic/{id}/pdf` request; authenticated requests verify `client_id` match. **No shareable permalink** for this phase — do NOT add `HasUuids` to `Diagnostic` now (can be added later if Pierre wants shareable links). Mitigates sequential-ID enumeration (RESEARCH Pitfall 5 / threat-model V4).

### Decision-tree leaf content (resolved from mockup)
- **D-07:** Re-extraction of `mockups/diagnostic-dloazur.html` confirms only the `floculant` leaf has an empty `plan: []`. `odeur-forte` and `irritation-yeux` already carry complete, chemically sound plans in the mockup — transcribe them verbatim, no Pierre input needed.
- **D-08:** `floculant` leaf gets a **generic default plan** (decision 2026-05-30 — "remplir pour que le site ait l'air complet"), pending Pierre's chemistry sign-off (logged in QUESTIONS-PIERRE Drive doc for the Sunday call):
  1. Verser un floculant clarifiant (~1 L pour 100 m³, selon notice produit)
  2. Laisser la filtration tourner en continu 24 h
  3. Lavage du filtre (backwash) puis rinçage après traitement
  4. Aspirer les dépôts au fond, doucement, à l'égout si nécessaire

### Claude's Discretion
- Exact column names, service namespace layout, route/component naming, and PDF Blade layout left to the planner, consistent with existing conventions.

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Phase spec & requirements
- `.planning/phases/05-diagnostic-commercialisable/05-SPEC.md` — Locked requirements (9), boundaries, constraints, acceptance criteria. MUST read before planning.
- `.planning/REQUIREMENTS.md` — DIAG-01..05 source requirements.
- `.planning/ROADMAP.md` — Phase 5 goal + success criteria.

### Data layer (exists today)
- `app/Models/Diagnostic.php` — Eloquent model; fillable + casts (`mesures`/`recommandations` → array, `disclaimer_accepted_at` → datetime); `client()`/`piscine()` relations. Reuse as-is.
- `database/migrations/2026_05_28_000009_create_diagnostics_table.php` — existing table shape (nullable `client_id`/`piscine_id`, `volume_m3`, `type_probleme`, JSON `mesures`/`recommandations`, `disclaimer_accepted_at`, `created_via`).

### UX/feature reference — GAP
- ⚠ **`diagnostic-dloazur (2).html`** (the SPEC's authoritative UX + dose-formula + decision-tree reference) is **NOT present in the repo** — not at root, `mockups/`, or elsewhere (only the Diagnostic model + migration exist). The dose baseline formulas and tree leaves are partially transcribed in `05-SPEC.md` Req 2–4, but the planner/researcher MUST obtain the actual mockup file before implementing dose formulas and the full tree. Flag to user if still missing at plan time.

### Existing patterns to follow
- `app/Livewire/ContactForm.php` — Livewire form with `#[Validate]`, honeypot spam protection, rate limiting, Mail notification. Pattern for lead-capture + Pierre notification.

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `Diagnostic` model + `diagnostics` migration: complete data layer; reuse as-is, extend via additive migration only for lead columns.
- `spatie/laravel-pdf` ^2.11: already installed (composer.json) — DomPDF driver for the PDF report.
- `ContactForm` Livewire component: template for the lead-capture form (validation + honeypot + rate-limit + Mail to Pierre).
- Existing Livewire components (`PiscineForm`, `ClientForm`, etc.) establish the component + view conventions.

### Established Patterns
- Forms = Livewire 3 components with `#[Validate]` attributes, `spatie/honeypot` spam protection, and `WithRateLimiting`.
- Pierre notifications = `App\Mail\*` mailers dispatched via `Mail::` (see `ContactMessage`).

### Integration Points
- New **public** route `/diagnostic` — outside the `['web','auth']->prefix('admin')` group (no auth). Links from vitrine nav/hero, `/services/eau-verte-urgence`, and city/commune pages (SEO-indexable, extends Phase 999.1 work).
- Logged-in clients: diagnostic links to their account (`client_id`); espace-client dashboard "Démarrer le diagnostic" CTA also points to `/diagnostic` (NOT the primary entry).

</code_context>

<specifics>
## Specific Ideas

- WhatsApp number: `0696 94 00 54` (Pierre) — verify before launch.
- Disclaimer copy baseline: "Conseils indicatifs — En cas de doute, contactez un professionnel" (final wording pending Pierre sign-off).
- Dose formulas are the **mockup baseline pending Pierre's pre-launch validation** (liability: chemical dosing advice). Launch gate = Pierre sign-off on chemistry + legal wording.
- Results render as **Problème / Étapes / Dosage / Produit** cards.

</specifics>

<deferred>
## Deferred Ideas

- **Stripe monetization (DIAG-04)** — abonnement particulier + premium upsell — own later phase.
- **Full multi-measure history dashboard (DIAG-05)** — "Historique de tes visites", measurement-evolution charts, entretien reminders — own later phase (Phase 5 persists diagnostics but builds no history/evolution UI).
- Authoring new dose chemistry (vs implementing the mockup baseline) — out of scope.
- Native pool CRUD integration beyond linking `piscine_id`.

</deferred>

---

*Phase: 05-diagnostic-commercialisable*
*Context gathered: 2026-05-30*
