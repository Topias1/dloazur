# Phase 11: Audit Impeccable — UX / UI / Wording - Discussion Log

> **Audit trail only.** Do not use as input to planning, research, or execution agents.
> Decisions are captured in CONTEXT.md — this log preserves the alternatives considered.

**Date:** 2026-06-04
**Phase:** 11-audit-impeccable-ux-ui-wording
**Areas discussed:** Périmètre/sévérité, Correctif #fff, Registre tu/vous opérateur, Pierre en copie marketing, Dashboard admin (P2)

---

## Périmètre / sévérité

| Option | Description | Selected |
|--------|-------------|----------|
| P0+P1 (recommandé) | Bloquants + theming + tu/vous + états ; P2/P3 reportés | |
| P0 seul | Uniquement les 4 bloquants data/légal | |
| P0+P1+P2 | + nav/dashboard/empty-states/steppers | |
| Tout (P0→P3) | Audit clean d'un coup, incl. polish | ✓ |

**User's choice:** Tout (P0→P3)
**Notes:** Override du recommandé (P0+P1) — Antoine veut l'audit entièrement remédié en une phase, pas de passes successives.

---

## Correctif #fff systémique

| Option | Description | Selected |
|--------|-------------|----------|
| Override token global | `--color-white: var(--color-sand-50)` dans @theme, 1 ligne | ✓ |
| Sweep des classes | Remplacer ~90 `bg-white`/`text-white` | |

**User's choice:** Override token global
**Notes:** Exceptions WhatsApp `#25D366` + carte QR à préserver ; vérifier contraste `text-white` sur azure après l'override.

---

## Registre tu/vous opérateur

| Option | Description | Selected |
|--------|-------------|----------|
| tu | Intimité outil-solo ; aligner empty-states/offline/toasts JS | ✓ |
| vous | Une seule voix partout | |

**User's choice:** tu
**Notes:** Client-facing reste `vous` strict (verrouillé, hors scope).

---

## Pierre en copie marketing

| Option | Description | Selected |
|--------|-------------|----------|
| Hybride : CTA only (recommandé) | « Appeler Pierre » sur CTA, prose en « nous » | |
| Tout garder | Légaliser l'usage étendu dans DESIGN.md §6 | |
| Tout revenir à « nous » | Respect strict DESIGN.md §6 | ✓ |

**User's choice:** Tout revenir à « nous »/« Dlo Azur »
**Notes:** Override du recommandé (hybride). Pierre nommé reste uniquement dans pierre.blade + footer + légal. « Appeler Pierre » → reformuler.

---

## Dashboard admin (P2)

| Option | Description | Selected |
|--------|-------------|----------|
| Restructure complète (recommandé) | Remonter agenda du jour + cartes cliquables + démoter vanity + casser uniformité | ✓ |
| Cartes cliquables seules | Garder grille, cartes cliquables only | |
| Rediriger accueil → agenda | Le dashboard disparaît comme landing | |

**User's choice:** Restructure complète
**Notes:** Le bloc agenda du jour existe déjà sur agenda/index — à remonter sur le dashboard.

---

## Claude's Discretion

- Approche technique des P0 PWA offline (store partagé Alpine + récupération zombies `uploading`).
- Forme du câblage `@error('ml')` et du placeholder témoignages.
- Forme exacte de tous les P1/P2/P3 restants tant que l'intention du finding est respectée.
- Implémentation du garde-fou CI (grep tokens non déclarés).

## Deferred Ideas

Aucune — le périmètre « Tout » absorbe l'intégralité du FINDINGS. Seules les dépendances action-Pierre (vrais avis Google, vraie photo avant/après) restent hors-code, livrées en placeholder gaté.
