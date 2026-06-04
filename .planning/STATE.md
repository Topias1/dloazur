---
gsd_state_version: 1.0
milestone: v1.0
milestone_name: milestone
status: executing
stopped_at: Phase 11 UI-SPEC approved
last_updated: "2026-06-04T07:54:31.376Z"
last_activity: 2026-06-04 -- Phase 11 execution started
progress:
  total_phases: 12
  completed_phases: 9
  total_plans: 47
  completed_plans: 43
  percent: 75
---

# Project State

## Project Reference

See: .planning/PROJECT.md (updated 2026-05-27)

**Core value:** L'opérateur enregistre chaque passage d'entretien sur le terrain de façon fiable (même sans réseau) et le client consulte l'historique de ses interventions.
**Current focus:** Phase 11 — audit-impeccable-ux-ui-wording

## Current Position

Phase: 11 (audit-impeccable-ux-ui-wording) — EXECUTED, 3 stale tests pending
Plan: 7 of 7 (all plans executed + merged to staging)
Status: Phase 11 code complete; 3 stale-assertion tests need alignment before "green"
Note: Both waves merged (W1: 11-01..05, W2: 11-06/07). Build + token guard green. Post-merge gate fixed 3 integration bugs (PwaConfigTest D-07, post-form curly-quote crash, warn-200 token). Verifier + code-review chain NOT yet run.
Last activity: 2026-06-04 -- Phase 11 plans executed & merged; paused on cost

Progress: [██████████] 100%

## Performance Metrics

**Velocity:**

- Total plans completed: 14
- Average duration: -
- Total execution time: 0 hours

**By Phase:**

| Phase | Plans | Total | Avg/Plan |
|-------|-------|-------|----------|
| 999.1 | 6 | - | - |
| 06 | 4 | - | - |
| 7 | 4 | - | - |

**Recent Trend:**

- Last 5 plans: -
- Trend: -

*Updated after each plan completion*
| Phase 999.1 P03 | 20 | 3 tasks | 10 files |
| Phase 999.1 P04 | 10m | 2 tasks | 7 files |
| Phase 999.1 P06 | 25 | 4 tasks | 9 files |
| Phase 05 P02 | 35 | 2 tasks | 3 files |
| Phase 05 P03 | 70 | 2 tasks | 8 files |
| Phase 05 P04 | 60 | 2 tasks | 4 files |
| Phase 09 P01 | 2 | 2 tasks | 3 files |
| Phase 10 P01 | 8m | 2 tasks | 3 files |
| Phase 10 P02 | 12m | 3 tasks | 2 files |

## Accumulated Context

### Decisions

Decisions are logged in PROJECT.md Key Decisions table.
Recent decisions affecting current work:

- Roadmap: Migrations complètes déployées dès Phase 1 (inclut `client_uuid`, `odoo_id`, `signature_path`)
- Roadmap: Phase 2 est la core value — doit être validée sur iPhone réel en Martinique avant de continuer
- Roadmap: Phase 3 démarre obligatoirement par le POC Odoo (gate technique — détermine API vs CSV)
- Roadmap: Phase 5 dépend de Phase 2 seulement, peut démarrer en parallèle de 3-4 si besoin
- Phase 5 (2026-05-30): scope **hybride** verrouillé (arbitrage expert) — diagnostic gratuit symptôme + « déjà tenté ? » + action-aware + doses serveur + escalade WhatsApp contextualisée → leads. **Stripe (DIAG-04) + multi-mesures (DIAG-05) différés V2** ; push/carnet offline/espace Pierre/natif différés V2. Nouvelle exigence DIAG-06 (escalade). Specs autoritaires : 05-CDC, 05-BLUEPRINT-APP, 05-EXPERT-ARBITRATION, 05-DIAGNOSTIC-EXPERT-AUDIT, 05-FLOCULANT-BRANCH-SPEC.
- [Phase ?]: 2-level breadcrumb trail for city hubs (Accueil > Commune) — pages not under /services
- [Phase ?]: datePublished emitted unconditionally in buildArticleSchema — show_date:false controls display only, not structured data validity
- [Phase ?]: Espace client demoted visually in nav (text-ink-500 quiet link) but retained in both desktop and mobile
- [Phase ?]: Case study cards fact-gated with CHANTIER RÉEL REQUIS placeholders — before/after measures never fabricated per D-13
- [Phase ?]: DoseEngine formule pH+ : steps*3 g/m³ par 0.1 pH (300 g pour pH=7.0/50m³, validé 05-VALIDATION)
- [Phase ?]: Gate chloration : pH < 7.0 ou TAC < 60 → card Chlore omise (audit P1 section 7)
- [Phase ?]: config/diagnostic-formulas.php version=1 = surface revue unique chimie Pierre pré-lancement DIAG-02
- [Phase ?]: validateOnly(array) invalide en Livewire 3 — validate(rules array) pour cibler un sous-ensemble de champs
- [Phase ?]: Livewire::actingAs() est statique void — appeler avant test(), pas chaînable
- [Phase ?]: whatsappSummary() enrichi : richContextPayload() symptôme/mesures/actions/confiance (DIAG-06 full)
- [Phase ?]: Alpine setSymptomResult() hook : synchronise la feuille arbre → escalade préemptive serveur-side
- [Phase ?]: Portail a11y accordéon : aria-controls/id reliés par passage id unique

### Roadmap Evolution

- Phase 6 added (2026-05-30): Blog admin CRUD — autonomie de publication pour Pierre (fichiers Markdown → modèle Post DB + /admin/blog). Déclenché par sa question « Pierre pourra-t-il ajouter des articles sans coder ? » — réponse : non aujourd'hui, aucune interface admin blog.

### Pending Todos

- **Phase 11 — 3 stale-assertion tests to align with intended changes (then run full suite + verifier + code-review):**
  1. `tests/Feature/AdminShellTest.php` (~l.40-43) — D-10 dashboard restructure removed "Bonjour Pierre,", "Ta semaine en un coup d'œil.", "Clients actifs", "Passages cette semaine". Update assertions to the new agenda-led dashboard (still shows "À synchroniser", "Eau à surveiller"). Verify intended content in `resources/views/admin/dashboard.blade.php`.
  2. `tests/Feature/DashboardStatsTest.php` (~l.121-133) — "À synchroniser" no longer rendered as a `state="offline"` `<x-admin.stat-card>`, so `text-[oklch(0.5_0.11_72)]` is absent. Either restore the offline stat-card or update/remove the assertion per the new dashboard.
  3. `tests/Feature/ContactFormTest.php` (l.147) — assert `'Nous vous répondrons rapidement.'` (was "Pierre vous répondra rapidement.", changed per D-09 in `resources/views/livewire/contact-form.blade.php:14`).
- Run full suite with `php -d memory_limit=1024M ./vendor/bin/pest` (128M default OOMs the full suite — ceiling artifact, not a bug; CI likely sets higher).

### Blockers/Concerns

- **Phase 5 (LAUNCH GATE bloquant)**: sign-off chimie + disclaimer/légal de Pierre (Task 3 du 05-06) non obtenu. Doses `config/diagnostic-formulas.php` vs P0/P1 `05-DIAGNOSTIC-EXPERT-AUDIT.md`, arbre `config/diagnostic-tree.php`, bloc sécurité, disclaimer, WhatsApp 0696 94 00 54. Tant que `05-VALIDATION.md` lit « Approval: pending », le diagnostic ne peut pas être lancé publiquement.
- **Phase 2**: Validation terrain iOS Safari + réseau mobile réel Martinique requise avant Phase 3
- **Phase 3**: Plan Odoo de l'opérateur non confirmé → POC obligatoire en 1er ticket
- **Phase 3**: TVA 8,5 % à valider par comptable local avant premières factures
- **Phase 4**: Templates WhatsApp Business : approval 1-5 j → ne pas bloquer sur l'approval

### Quick Tasks Completed

| # | Description | Date | Commit | Directory |
|---|-------------|------|--------|-----------|
| 260529-o92 | fix liens Espace client vers portail magic-link au lieu du contact | 2026-05-29 | 7ac4404 | [260529-o92-fix-liens-espace-client-vers-portail-mag](./quick/260529-o92-fix-liens-espace-client-vers-portail-mag/) |
| 260529-qac | login démo dev-only sur /auth/magic (Démo Client + Démo Admin, gated config app.demo_login) | 2026-05-29 | 57a2182 | [260529-qac-demo-login-auth-magic](./quick/260529-qac-demo-login-auth-magic/) |
| 260531-nys | demo data enrichi : 10 clients, 14 piscines, 90 passages (draft/signed/synced), 62 signatures, 6 diagnostics, 10 contrats, 8 factures | 2026-05-31 | e1a7174 | [260531-nys-more-demo-data](./quick/260531-nys-more-demo-data/) |

## Deferred Items

| Category | Item | Status | Deferred At |
|----------|------|--------|-------------|
| v2 | STAT-01 — Tableau de bord stats/reporting | Deferred | Init |
| v2 | PORT-03 — Paiement en ligne par le client | Deferred | Init |
| v2 | CLI-04 — Multi-piscines par client en UI | Deferred | Init |

## Session Continuity

Last session: 2026-06-04T06:24:43.455Z
Stopped at: Phase 11 UI-SPEC approved
Resume file: None

## Decision Coverage Override (Phase 1)

Date: 2026-05-28
Phase: 1 (vitrine-fondations)
Decision: User selected "Proceed anyway" on the decision-coverage gate.
Reason: 14 of 31 D-IDs lack literal citations in plan `must_haves`/`truths` but the plan-checker (semantic) verified all decisions are implemented. Gap is citation hygiene only, not substance.
Uncovered D-IDs (substantively covered, citation missing): D-02, D-03, D-04, D-06, D-11, D-13, D-15, D-20, D-21, D-27, D-28, D-29, D-30, D-31.
Action: verify-phase will re-surface this gap for re-evaluation against the executed code.
