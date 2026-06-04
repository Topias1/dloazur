# Phase 10: diagnostic-retours-pierre - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-06-04
**Phase:** 10-diagnostic-retours-pierre
**Areas discussed:** Analyser mon eau (chimie), Carnet (DIAG-07), Tests à adapter

---

## Analyser mon eau (chimie) — placement

| Option | Description | Selected |
|--------|-------------|----------|
| Sur le disclaimer (S4) | Juste sous le bouton « J'accepte », lien discret vers le wizard chimie | ✓ |
| Pied de wizard persistant | Lien fixe en bas du composant, visible à toutes les étapes | |
| Uniquement sur le résultat | Apparaît seulement après résultat de l'arbre symptôme | |

**User's choice:** Sur le disclaimer (S4)

---

## Analyser mon eau — forme du lien

| Option | Description | Selected |
|--------|-------------|----------|
| Lien texte sobre | Ligne après le bouton J'accepte, ton discret, ne rivalise pas avec le CTA | ✓ |
| Bouton secondaire outline | Bouton ghost sous le bouton principal, plus visible | |

**User's choice:** Lien texte sobre

---

## Carnet (DIAG-07) — placement

| Option | Description | Selected |
|--------|-------------|----------|
| Sur le disclaimer aussi | Sous le lien chimie, x-show conditionnel (carnetEntries.length > 0) | ✓ |
| Pied de page wizard persistant | Lien fixe en bas du composant, toutes étapes | |
| Hors wizard — page vitrine | Lien dans le wrapper Blade de la page /diagnostic | |

**User's choice:** Sur le disclaimer aussi

---

## Tests à adapter

### assertSee de remplacement

| Option | Description | Selected |
|--------|-------------|----------|
| `assertSee('J'accepte')` | CTA principal du disclaimer, premier contenu visible | ✓ |
| assertSee texte disclaimer | Fragment du disclaimer body | |

**User's choice:** `assertSee('J'accepte')`

### resumeFromCarnet() — nouvelle cible

| Option | Description | Selected |
|--------|-------------|----------|
| step:'tree', nodeId:'start' | Repart du disclaimer pour un nouveau diagnostic | ✓ |
| Garder showCarnet=true | Rester bloqué dans la vue carnet | |

**User's choice:** step:'tree', nodeId:'start'

---

## Claude's Discretion

- Libellé exact du lien chimie : « Vous avez vos mesures ? → Analyser mon eau » (vouvoiement, sobre)
- Positionnement exact sur le disclaimer (ordre : J'accepte → lien chimie → Carnet conditionnel)
- Gestion du bug potentiel `setMode('chemistry')` si la branche `if (this.step === 'mode')` ne matche plus — investiguer au plan

## Deferred Ideas

- Slider avant/après vitrine (V7) — attend 2 vraies photos de Pierre
- Monétisation diagnostic (DIAG-04) — V2
- Suivi multi-mesures (DIAG-05) — V2
