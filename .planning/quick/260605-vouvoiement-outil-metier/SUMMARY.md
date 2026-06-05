---
slug: vouvoiement-outil-metier
date: 2026-06-05
status: complete
---

# Résumé — Vouvoiement de l'outil métier

Outil métier (admin + PWA offline) repassé du tutoiement au vouvoiement. Voix unique
sur toute l'app. **Renverse D-07** (Phase 11). Client-facing inchangé.

## Modifié
- 6 fichiers UI/JS : `dashboard`, `passages/create`, `client-index`, `post-index`,
  `offline.blade.php`, `passage-form.js` (8 chaînes visibles + impératifs vous).
- 2 tests alignés : `PwaConfigTest` (« Vous êtes hors ligne »), `AdminShellTest` (« Voici votre agenda du jour. »).
- `11-CONTEXT.md` : D-07 annoté « renversé 2026-06-05 ».

## Vérif
- Re-grep `\b(tu|ton|ta|tes|toi)\b` sur admin + livewire + offline + js → **CLEAN** (hors commentaires/attributs/diagnostic-wizard déjà propre).
- `pest AdminShellTest PwaConfigTest` → 14 passed, 38 assertions.

## Non couvert (intentionnel)
- `diagnostic-wizard.blade.php` : déjà en vous (corrigé via feedback Pierre 2026-06-03), seul résidu = un commentaire.
