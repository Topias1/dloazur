---
status: partial
phase: 07-espace-admin-retours-pierre
source: [07-VERIFICATION.md]
started: 2026-06-03T00:00:00Z
updated: 2026-06-03T00:00:00Z
---

## Current Test

[awaiting human testing]

## Tests

### 1. Sélecteur produits offline — flux complet
expected: Cocher un produit dans la saisie offline, aller hors ligne, valider le passage, repasser en ligne → flux déclenché → `select * from passage_produit` montre une ligne avec le bon `prix_snapshot` (= `prix_ht` du produit au moment de la saisie).
result: [pending]

### 2. Chemin dégradé produits_pending
expected: Bloquer `/api/passages/produits` après que le passage soit synced → l'item IDB porte `produits_pending: true` (visible dans DevTools → Application → IndexedDB) → repasser en ligne → flush retente → ligne `passage_produit` apparaît en base.
result: [pending]

### 3. Nav mobile grid-cols-5
expected: Sur un viewport mobile réel (ou DevTools responsive), les 5 onglets de la bottom nav (Accueil, Agenda, Clients, Passages, Factures) s'affichent sans débordement ni onglet orphelin.
result: [pending]

## Summary

total: 3
passed: 0
issues: 0
pending: 3
skipped: 0
blocked: 0

## Gaps
