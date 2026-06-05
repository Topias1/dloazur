---
slug: vouvoiement-outil-metier
date: 2026-06-05
type: quick
---

# Quick : Vouvoiement de l'outil métier

Convertir toute la copie de l'outil métier (admin/opérateur + PWA offline) du tutoiement
au vouvoiement, pour une voix unique et irréprochable sur toute l'app
(« deux publics, une seule voix », PRODUCT.md §5).

**Renverse D-07** (Phase 11 : « registre opérateur = tu »). Client-facing déjà en `vous`
(D-08) — inchangé : vitrine, portail client, UI diagnostic.

## Cibles
- `resources/views/admin/dashboard.blade.php` — « Voici votre agenda du jour. »
- `resources/views/admin/passages/create.blade.php` — « Choisissez un client/une piscine… », « votre saisie est sauvegardée »
- `resources/views/livewire/client-index.blade.php` — « Ajoutez votre premier client »
- `resources/views/livewire/post-index.blade.php` — « Écrivez votre premier article »
- `resources/views/offline.blade.php` — « Vous êtes hors ligne », « à votre retour sur le réseau »
- `resources/js/passage-form.js` — « vérifiez votre lecture », « Choisissez un client avant… »
- Tests : `PwaConfigTest.php`, `AdminShellTest.php` (assertions alignées)
- Doc : `11-CONTEXT.md` D-07 annoté « renversé »
