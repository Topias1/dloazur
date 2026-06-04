# Phase 9: espace-client-finitions-retours-pierre - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-06-04
**Phase:** 09-espace-client-finitions-retours-pierre
**Areas discussed:** Mes documents — copy, Test client-2 — portée, Nits perf client-4

---

## Mes documents — copy

**Q1 — L'UI doit montrer quoi sous chaque ligne de document ?**

| Option | Description | Selected |
|--------|-------------|----------|
| Rewrite les sous-titres | Remplacer les sous-titres actuels par « Disponible avec la mise en place de la facturation. » | ✓ |
| Note au niveau section | Garder les sous-titres actuels ; ajouter une ligne sous le titre de section | |
| Badge seul suffit | Le badge « Bientôt » parle de lui-même | |

**Q2 — Wording exact des sous-titres réécrits ?**

| Option | Description | Selected |
|--------|-------------|----------|
| Disponible avec la facturation (Phase 3) | Technique mais honnête, expose les numéros de phase | |
| Disponible avec la mise en place de la facturation. | Propre, client-friendly | ✓ |
| Tu décides | Claude choisit le wording | |

**Q3 — Dépendance Phase 3 tracée — où ?**

| Option | Description | Selected |
|--------|-------------|----------|
| Dans le ROADMAP.md seulement | Une note dans ROADMAP.md Phase 9 suffit | ✓ |
| ROADMAP + REQUIREMENTS.md | Lien explicite PORT-01/PORT-02 vers FACT-01..05 | |

---

## Test client-2 — portée

**Q1 — Quel comportement le test doit-il couvrir ?**

| Option | Description | Selected |
|--------|-------------|----------|
| Rendu HTML + accordéon Alpine | Feature test Livewire, vérifie attributs HTML (aria-expanded, aria-controls, id) | ✓ |
| Browser Pest (Playwright) | Test E2E réel : clic + vérification panneau visible | |

**Q2 — Contenu déplié à vérifier ?**

| Option | Description | Selected |
|--------|-------------|----------|
| Structure + quelques mesures | aria-controls/id + pH et chlore du passage seedé | |
| Structure seulement | aria-expanded, aria-controls, id panel — rien sur le contenu | |
| Tout le contenu | Actions, notes, mesures complètes, photos count | ✓ |

**Q3 — Fichier Pest ?**

| Option | Description | Selected |
|--------|-------------|----------|
| tests/Feature/PortailTimelineTest.php (nouveau) | Fichier dédié à la timeline | ✓ |
| Dans PortailAccessTest.php | Ajouter dans le fichier portail existant | |

---

## Nits perf client-4

**Q1 — Intégrer dans cette phase ou différer ?**

| Option | Description | Selected |
|--------|-------------|----------|
| Intégrer dans cette phase | Retirer loading="lazy" + swap <x-picture> — rapide | ✓ |
| Différer | Optionnel selon ROADMAP | |

**Q2 — Sources webp/avif pour <x-picture> ?**

| Option | Description | Selected |
|--------|-------------|----------|
| URL R2 directe uniquement | Pas de srcset — juste retirer loading="lazy" | ✓ |
| x-picture complet avec srcset | Conversions medialibrary — hors scope | |

---

## Claude's Discretion

Aucun « tu décides » explicite. Le wording exact identique pour les deux sous-titres (Contrat + Factures) est une décision Claude (cohérence).

## Deferred Ideas

- `<x-picture>` avec srcset webp/avif — différé backlog perf (URLs R2 signées incompatibles avec srcset statique).
- Slider avant/après (V7 vitrine) — différé jusqu'à 2 photos réelles de Pierre.
