# Phase 5: Diagnostic Commercialisable - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-05-30
**Phase:** 05-diagnostic-commercialisable
**Areas discussed:** Wizard/tree architecture, Dose engine + tree data home, Lead capture model, Persistence timing & PDF

> Requirements locked by `05-SPEC.md` (9 requirements, ambiguity 0.18) — discussion covered implementation (HOW) only.

---

## Wizard & decision-tree architecture

| Option | Description | Selected |
|--------|-------------|----------|
| Livewire page + Alpine nav | Full-page Livewire owns persistence/dose-submit; Alpine handles step nav (no per-step round-trip). Matches ContactForm/PiscineForm. | ✓ |
| Pure Alpine + single POST | All state in Alpine; one POST to a controller computes + persists. | |

**User's choice:** Livewire page + Alpine nav (recommended)
**Notes:** Satisfies SPEC constraint that step navigation not round-trip while dose computation may be a server submit.

---

## Dose engine + decision-tree home

| Option | Description | Selected |
|--------|-------------|----------|
| Service class + PHP config arrays | DoseEngine service + tree as versioned PHP array. Server-only, Pest-testable, single-file sign-off for Pierre. | ✓ |
| Service class + JSON files | Tree/formulas in resources JSON. Editable but weaker type-safety. | |
| Database-backed rules | Tree + formulas in tables. Overkill, adds deferred CRUD scope. | |

**User's choice:** Service class + PHP config arrays (recommended)
**Notes:** Keeps dose formulas off the client (DIAG-02), easy baseline tests, simple review surface for Pierre's chemistry/legal sign-off.

---

## Lead capture model

| Option | Description | Selected |
|--------|-------------|----------|
| Columns on Diagnostic + email notify | Additive migration for lead fields; row IS the lead; Mail to Pierre like ContactForm. | ✓ |
| Separate Lead model | New leads table FK to diagnostic. | |
| Create/link Client record | Turn lead into a Client row. | |

**User's choice:** Columns on Diagnostic + email notify (recommended)
**Notes:** Preserves the 1:1 diagnostic↔lead tie the SPEC describes; avoids conflating marketing leads with real entretien clients.

---

## Persistence timing & PDF delivery

| Option | Description | Selected |
|--------|-------------|----------|
| Persist on completion, sync PDF | Row written when results computed/disclaimer accepted; PDF generated synchronously via DomPDF. | ✓ |
| Persist progressively, sync PDF | Write/update row at each step. | |
| Persist on completion, queued PDF | Queue PDF generation. | |

**User's choice:** Persist on completion, sync PDF (recommended)
**Notes:** Guarantees non-null `disclaimer_accepted_at`; no queue infra needed on scale-to-zero Cloud for a single-page DomPDF render.

---

## Claude's Discretion

- Exact lead column names, service namespace, route/component naming, PDF Blade layout — left to planner per existing conventions.

## Deferred Ideas

- Stripe monetization (DIAG-04) — own later phase.
- Full multi-measure history dashboard (DIAG-05) — own later phase.
- Authoring new dose chemistry (vs mockup baseline) — out of scope.
- Native pool CRUD beyond linking `piscine_id`.

## Open gap flagged

- ⚠ SPEC's authoritative UX reference `diagnostic-dloazur (2).html` is not present in the repo — must be obtained before implementing dose formulas / full decision tree.
