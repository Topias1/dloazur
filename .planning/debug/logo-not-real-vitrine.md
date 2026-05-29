---
slug: logo-not-real-vitrine
status: resolved
trigger: "le logo sur https://topias1.github.io/dloazur/mockups/v1/vitrine.html n'est pas repris correctement"
created: 2026-05-29
updated: 2026-05-29
---

# Debug: logo de la vitrine pas le bon

## Symptoms
- Le logo affiché sur la vitrine publiée (topias1.github.io GH-Pages) n'est **pas** le vrai logo Dlo Azur.
- Visuellement : une goutte stylisée avec une vague `~` à l'intérieur (et selon le contexte, une goutte avec reflet). Le client : « c'est pas le bon logo ».

## Root cause
La vitrine (et toute l'app) utilisait un **SVG dessiné à la main** (approximation générée par Claude) au lieu du **vrai asset logo** présent dans `assets/brand/logo/` (`logo.svg`, `logo.png`, …).

- Faux logo = `<svg viewBox="0 0 28 34">` : path goutte + path vague stroke. Inliné dans `mockups/v1/*` et dans le composant app `resources/views/components/icon/drop.blade.php`.
- Vrai logo = lockup azur détaillé : soleil · goutte-à-swirl · éclaboussures · vagues · mot-symbole **DLO AZUR PISCINES** intégré.

Le live n'était pas périmé (HTML identique au repo) — l'asset réel n'avait simplement jamais été repris ; un stand-in stylisé avait été dessiné puis propagé partout (~16 vues app + 7 fichiers mockups, 13 occurrences).

## Fix
Décision client : utiliser le **vrai logo détaillé**, traitement « icône réelle + texte Fredoka » (l'icône sans le mot-symbole, à côté du texte existant).

1. Extraction de l'icône depuis `assets/brand/logo/logo.svg` :
   - Suppression des `<text>` (mot-symbole, polices Watermelon/Caramel Milk) + du `<path id="text111">` (le « DLO » converti en path).
   - Recadrage du `viewBox` à `0 0 816.89 705` pour couper les tirets décoratifs sous l'icône.
   - Réduction à un seul path (les 4 étaient des copies quasi-identiques) + arrondi des coords → ~15 KB.
2. Nouveaux assets : `assets/brand/logo/logo-mark.svg` (azur) + `logo-mark-white.svg`, copies dans `public/assets/brand/`.
3. `components/icon/drop.blade.php` réécrit avec l'icône réelle en `fill="currentColor"`, viewBox `0 0 816.89 705` → les ~16 vues app héritent automatiquement de leur couleur (azur sur clair, blanc/azur sur navy).
4. Les 13 SVG inline des `mockups/v1/*.html` remplacés par l'icône réelle.

## Verification
- 24 tests de rendu de vues passent (`AuthLoginTest`, `PassageCreateViewTest`, `DashboardStatsTest`, `PassageIndexTest`).
- Rendu navigateur vérifié : topbar vitrine (icône azur + texte Fredoka), footer navy (icône azur + mot blanc — conforme à la carte de visite : fond navy + logo azur).

## Files changed
- resources/views/components/icon/drop.blade.php
- mockups/v1/{vitrine,index,auth,dashboard,passage,portail,styleguide}.html
- assets/brand/logo/logo-mark.svg, logo-mark-white.svg
- public/assets/brand/logo-mark.svg, logo-mark-white.svg

## Note
Le site live topias1.github.io ne reflètera le correctif qu'après déploiement/push de ce repo.
