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
- **D-08 (SUPERSEDED 2026-05-30):** The interim generic floculant plan is **replaced** by an expert-validated branching sub-tree. The `cloudy-1 → floculant` leaf is no longer a static plan — it becomes a sub-branch keyed on **filter type** (sable/verre → floculant choc, cartouche → clarifiant, DE → nettoyage + clarifiant) with a **blocking pH precondition** and a salt/electrolysis warning. The word "floculant" must never appear in the cartridge path. **Full spec (authoritative, planner MUST implement):** `05-FLOCULANT-BRANCH-SPEC.md`.
  - **Wizard implication:** the anonymous wizard must add a *filter-type* question node before any product recommendation; for logged-in clients, pre-fill from the pool's `filtration` field (Phase 2) with override allowed.
  - **⚠ Verified gap (2026-05-30):** `piscines.filtration` is a **free-text string** (`nullable|string|max:30` in `PiscineForm`, plain `string` column) — NOT a normalized enum. The floculant branch routing needs canonical values (sable/verre/cartouche/diatomées). Planner MUST either (a) map/normalize the free-text value to a canonical set (with a fallback to the question node when unmappable/empty), or (b) always present the constrained filter-type select in the wizard and treat the pool value as a non-authoritative hint. Do not assume clean enum values exist.
  - Pierre sign-off no longer needed for floculant — expert resolved it. The QUESTIONS-PIERRE Drive doc entry can be marked answered.

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

### Authoritative specs (planner MUST implement)
- `.planning/phases/05-diagnostic-commercialisable/05-EXPERT-ARBITRATION.md` — **scope/priority verdict (A-vs-B)**: locks the HYBRID scope + the 6→7-brick build order + the V2 frontier. North-star = qualified leads. READ FIRST for what to build and in what order.
- `.planning/phases/05-diagnostic-commercialisable/05-BLUEPRINT-APP.md` — **product/UX blueprint** locking the persona ("compétent mais bloqué") and the central loop. Adds two strong, SPEC-compatible features: the **"qu'as-tu déjà essayé ?"** question and **action-aware diagnosis** (§6 table — a failed shock routes to chlore-lock/metals/resistant-algae, not "re-shock"), plus **escalation-in-one-gesture with rich context** (enriches WhatsApp hand-off, SPEC Req 7). ⚠ Its push/offline/carnet features exceed Phase 5 SPEC — see scope decision below.
- `.planning/phases/05-diagnostic-commercialisable/05-CDC-SAFE-DIAGNOSTIC.md` — **product CDC** (the WHAT/WHY at app level): 4 safe-diagnostic guardrails, personas, progressive disclosure (symptôme d'abord), confidence index, lead capture+qualification, content gabarit. V0 (MVP) == this SPEC's scope; V1/V2 are evolutions.
- `.planning/phases/05-diagnostic-commercialisable/05-FLOCULANT-BRANCH-SPEC.md` — eau-trouble/floculant branch (filter-type routing, pH gate). Supersedes CONTEXT D-08.
- `.planning/phases/05-diagnostic-commercialisable/05-DIAGNOSTIC-EXPERT-AUDIT.md` — expert audit of the whole diagnostic (P0/P1/P2 + Pest suite). Key P0s: chlore-bas rattrapage ≠ choc (~3-4 g/m³); systematic safety block (EPI / ne jamais mélanger / délai baignade); détartrage acide opt-in/redirect (vinaigre first); `green-1` must test stabilisant (chlore-lock + manque stab); `eau-boueuse`/`pollution-organique` route clarification via the floculant sub-tree. Plus intake additions (chlore total, TH), treatment order TAC→pH→chlore→stab→sel, break-point for odeur-forte, calcium-vs-sodium by TH, eau-calcaire bifurcation.
- `05-DIAGNOSTIC-LLM-REVIEW.md` — review brief sent to the expert (resolved; corrections captured in the audit above). Kept for traceability.

### Scope decision (OPEN — confirm before planning)
- **Stripe: OUT** (confirmed 2026-05-30 "oublie Stripe"). Already out per SPEC (DIAG-04 deferred). No paywall/premium/Stripe scaffold in Phase 5.
- **V0 vs V0+V1 — RESOLVED:** the hybrid includes the **action-aware diagnosis (brique 3)**, which structurally depends on the P1 cases (chlore-lock, eau-calcaire, treatment order) — so Phase 5 ships **P0 + the P1 depth those bricks require**. The action-aware diagnosis IS the conversion differentiator for the locked persona; it can't be deferred without gutting the value prop.
- **Delivery surface:** Phase 5 is a **public web route `/diagnostic`** on the vitrine (per SPEC Req 9) + PWA — NOT a separate native app-store download, despite "application mobile" framing in user/persona language.
- **⭐ Contrainte directrice:** **infra la moins chère possible × effet whaou maximal** (Pierre = auto-entrepreneur). "Whaou" via le front (PWA installable, design OKLCH, animations, fluidité) + logique action-aware (règles, 0 infra). "Infra chère" = complexité/maintenance solo durable (push backend, sync offline multi-device, dashboard pro, natif), PAS la facture serveur (~4-7 €/mois scale-to-zero). Détaillé dans `05-CDC-SAFE-DIAGNOSTIC.md` §1.
- **SCOPE LOCKED 2026-05-30 = HYBRIDE** (per `05-EXPERT-ARBITRATION.md`, supersedes the earlier "App complète" lean). North-star = **qualified leads / conversion for Pierre**. Phase 5 builds, in this order:
  1. Route `/diagnostic` publique indexée, mobile-first + parcours symptôme sans mesures + disclaimer avant tout chiffre [A] — FORT/FAIBLE-MOYEN
  2. Hand-off WhatsApp pré-rempli + PDF [A] — FORT/FAIBLE
  3. « Qu'as-tu déjà essayé ? » + diagnostic action-aware + wizard chimie doses serveur [B+A] — FORT/MOYEN
  4. Moteur d'escalade contextualisé → CTA WhatsApp à contexte riche [B] — FORT/MOYEN
  5. Indice de confiance + photo optionnelle jointe au lead [B] — MOYEN/FAIBLE
  6. Boucle re-test légère (relance e-mail/in-session, **PAS de push**) [B allégé] — MOYEN/FAIBLE-MOYEN
  7. **Carnet LOCAL-ONLY** (ajout 2026-05-30) — historique des diagnostics/mesures **sur l'appareil** (IndexedDB/localStorage), vue liste « mes diagnostics passés », alimente la continuité du re-test. **0 serveur, 0 sync, 0 compte requis, pas de courbes lourdes.** Pur front = whaou + rétention sans infra. Pour un client connecté, la persistance serveur existe déjà (SPEC Req 5) ; le carnet local complète surtout l'anonyme. Lecture de l'historique possible hors-ligne ; le calcul d'un NOUVEAU diagnostic reste online (doses serveur). Cet ajout assouplit la ligne SPEC "online-only" **pour le stockage local du carnet uniquement**.
- **DIFFÉRÉ V2 (hors Phase 5):** push notifications, **carnet synchronisé multi-appareils + courbes/évolution**, multi-bassins B2B, espace Pierre dashboard, app native/store. Raison : infra de rétention chère qui ne génère pas le 1er lead. Espace Pierre remplacé au départ par WhatsApp. *(Le carnet LOCAL-ONLY est en V0 ci-dessus ; seul le carnet synchronisé/courbes/dashboard est différé — distinction explicite vs `05-EXPERT-ARBITRATION.md` qui groupait tout le carnet en V2.)*
- **Delivery surface:** web public `/diagnostic`, mobile-first / **PWA web-first** (pas de natif/store — l'install avant valeur tue le funnel). The SPEC's online-only boundary holds for Phase 5 (offline carnet = V2).
- **New requirement DIAG-06** (escalade contextualisée WhatsApp) added to REQUIREMENTS.md; DIAG-04 (Stripe) + DIAG-05 (multi-mesures) marked Deferred V2. Recommendation: Phase 5 = the **minimal loop on web** (symptôme → "déjà tenté ?" → action-aware diagnosis → safe plan → 1-gesture rich escalation → lead → PDF); push/offline/carnet/espace-Pierre go to a dedicated **"mobile & rétention" phase** (matches BLUEPRINT §10.3-4 sequencing). The "déjà tenté ?" + action-aware diagnosis ARE recommended for Phase 5 (additive, web-compatible). **Confirm before planning.**
- **"Qu'as-tu déjà essayé ?" — NEW requirement** (not in SPEC's 9): capture tried-actions, prune them from the tree, and use failure as a diagnostic signal (BLUEPRINT §6). Recommended in Phase 5. Also feeds the rich escalation context.

### UX/feature reference
- ✅ **`mockups/diagnostic-dloazur.html`** is present (the earlier "GAP" note was stale — confirmed by RESEARCH re-extraction 2026-05-30). All dose formulas + the full decision tree (10 question nodes, 15+ result leaves, electrolyser 5-fault sub-tree) were extracted from its JS bundle into `05-RESEARCH.md`. Only the `floculant` leaf was empty in the mockup — now resolved by `05-FLOCULANT-BRANCH-SPEC.md`.

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
