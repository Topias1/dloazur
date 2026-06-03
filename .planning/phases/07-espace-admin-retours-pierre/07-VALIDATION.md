---
phase: 7
slug: 07-espace-admin-retours-pierre
status: draft
nyquist_compliant: true
wave_0_complete: false
created: 2026-06-03
---

# Phase 7 — Validation Strategy

> Généré sans RESEARCH.md (research skipped — CRUD bien connu, bug trivial).

---

## Test Infrastructure

| Property | Value |
|----------|-------|
| **Framework** | Pest PHP v4 |
| **Config file** | `phpunit.xml` |
| **Quick run command** | `php artisan test --filter=NotesPrivees\|PassageProduit\|Agenda` |
| **Full suite command** | `php artisan test` |
| **Estimated runtime** | ~60 secondes |

---

## Sampling Rate

- **After every task commit:** Run quick run command
- **After every plan wave:** Run full suite
- **Before `/gsd:verify-work`:** Full suite must be green
- **Max feedback latency:** 60 seconds

---

## Per-Task Verification Map

| Task ID | Plan | Wave | Requirement | Test Type | Automated Command | Status |
|---------|------|------|-------------|-----------|-------------------|--------|
| 07-01 T1 | 07-01 | 1 | admin-2 | migration | `php artisan migrate --path=database/migrations/..._add_notes_privees_to_passages_table.php` | pending |
| 07-01 T2 | 07-01 | 1 | admin-2 | feature test (Pest) | `php artisan test --filter=PassageNotesPriveesTest` | pending |
| 07-02 T1 | 07-02 | 2 | admin-1 | migration | `php artisan migrate --path=database/migrations/..._add_frequence_jour_to_piscines_table.php` | pending |
| 07-02 T2 | 07-02 | 2 | admin-1 | route + blade | `php artisan route:list \| grep agenda` | pending |
| 07-02 T3 | 07-02 | 2 | admin-1 | feature test (Pest) | `php artisan test --filter=AgendaTest` | pending |
| 07-03 T1 | 07-03 | 2 | admin-5 | migration | `php artisan migrate --path=database/migrations/..._create_passage_produit_table.php` | pending |
| 07-03 T2 | 07-03 | 2 | admin-5 | feature test (Pest) | `php artisan test --filter=PassageProduitSyncTest` | pending |
| 07-03 T3 | 07-03 | 2 | admin-5 | manual + build | `npx vite build` + procédure offline DevTools | pending |
| 07-04 T1 | 07-04 | 3 | admin-5 | route + blade | `php artisan route:list \| grep recap` | pending |
| 07-04 T2 | 07-04 | 3 | admin-5 | browser / grep | `grep -i tva resources/views/admin/recap-mensuel/index.blade.php` exits 1 | pending |

---

## Invariants de sécurité à vérifier

| Invariant | Vérification | Plan |
|-----------|-------------|------|
| `notes_privees` jamais dans le portail client | `assertDontSee` dans PassageNotesPriveesTest | 07-01 T2 |
| Aucun calcul TVA dans le récap | `grep -i tva` doit retourner 0 lignes | 07-04 T2 |
| Sélecteur produits = Alpine/IndexedDB, jamais Livewire | `grep Livewire resources/js/passage-form.js` doit retourner 0 lignes | 07-03 T3 |
