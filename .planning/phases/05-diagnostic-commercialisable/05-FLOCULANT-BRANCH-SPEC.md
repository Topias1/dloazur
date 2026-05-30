# Spec — Branche diagnostic « eau trouble / floculant »

**Source:** expert pisciniste (handoff 2026-05-30), via Antoine. Supersedes the generic default plan in CONTEXT D-08.
**Status:** Authoritative — planner MUST implement this branch per below.
**Scope:** decision-tree leaf reached when water is *trouble à particules fines + filtration OK* (mockup node `cloudy-1` → `floculant`).

## Principe directeur

Deux approches **mutuellement exclusives** :

| Méthode | Filtration | Devenir des particules | Filtres compatibles |
|---|---|---|---|
| **Floculant choc** (décantation) | À l'ARRÊT pendant la décantation | Tombent au fond → aspiration à l'égout | Sable / verre uniquement |
| **Clarifiant** (filtration) | EN MARCHE en continu | Captées par le filtre → nettoyage filtre | Tous (recommandé cartouche/DE) |

Erreur du plan initial (D-08, à abandonner) : filtration 24 h **et** aspiration du fond. Incompatible — si la filtration tourne, les fines restent en suspension, rien à décanter.

## Logique de branchement (ordre impératif)

```
1. Demander le TYPE DE FILTRE  ← AVANT toute reco de produit
   ├─ Sable / Verre      → méthode FLOCULANT CHOC (ou clarifiant si trouble léger)
   ├─ Cartouche          → méthode CLARIFIANT (floculant PROSCRIT)
   └─ Diatomées (DE)     → nettoyage filtre + CLARIFIANT (floculant choc déconseillé)

2. Vérifier le pH (précondition bloquante)
   └─ Si pH hors 7,0–7,4 → étape « ajuster le pH » AVANT traitement

3. Dérouler la séquence correspondant à la méthode retenue
```

Le mot « floculant » ne doit **jamais** s'afficher pour un client en filtre cartouche.

> **Implication wizard (anonyme):** le visiteur n'a pas de fiche piscine → le type de filtre doit être un nœud-question du wizard. Pour un client connecté avec piscine renseignée (Phase 2 : `filtration` ∈ sable/cartouche/diatomée), pré-remplir et permettre override.

## Séquences par méthode

### A. Floculant choc (filtre sable / verre)
1. Équilibrer le pH entre 7,0 et 7,4
2. Verser le floculant liquide (dosage : voir tableau), filtration en marche 30 min à 1 h pour disperser
3. **Arrêter la filtration**, laisser décanter 8 à 24 h (une nuit complète recommandée)
4. Aspirer le dépôt lentement, vanne en position **égout**, vitesse basse, sans remuer le fond
5. Faire l'appoint d'eau, rééquilibrer (pH, désinfectant, sel le cas échéant)
6. Lavage à contre-courant (backwash) + rinçage du filtre

### B. Clarifiant (filtre cartouche, ou trouble léger sable/verre)
1. Équilibrer le pH entre 7,0 et 7,4
2. Verser le clarifiant
3. Filtration **en continu** 24 à 72 h
4. Nettoyer le média filtrant quand la pression monte (voir formulation générique ci-dessous)
5. Répéter si nécessaire

### C. Diatomées
1. Nettoyer/recharger le filtre DE d'abord (capte déjà la majorité des fines)
2. Si insuffisant : clarifiant + filtration continue (pas de floculant choc)

## Étape « nettoyage filtre » — formulation générique (conditionnée au type)

Ne jamais écrire « backwash » seul :
- **Sable / verre** : lavage à contre-courant (backwash) puis rinçage
- **Cartouche** : retirer et rincer la cartouche au jet, ou la remplacer
- **Diatomées** : backwash puis recharge de diatomées, ou nettoyage manuel selon modèle

## Dosages et formes de produit

| Forme | Usage | Dosage indicatif | Remarque |
|---|---|---|---|
| Liquide | Méthode choc | 1 à 2,5 L / 100 m³ | Le plus efficace pour trouble marqué. Toujours suivre la notice (concentrations variables) |
| Chaussettes / cartouches (skimmer) | Clarification douce | selon notice | Libération lente sur plusieurs jours, le filtre capte. Trouble modéré uniquement |
| Longue durée / galets | Préventif | selon notice | Entretien, pas curatif |

## Préconditions (bloquantes)
- **pH** : 7,0–7,4 idéal (tolérance jusqu'à 7,6 selon produit). Hors plage = efficacité fortement réduite. Imposer avant traitement.
- **Arrêt filtration** (méthode choc uniquement) : 8 à 24 h de décantation, une nuit recommandée. Remise en marche prématurée = décantation annulée.

## Contre-indications / cas spéciaux à signaler
- **Filtre cartouche → floculant proscrit.** Amas colmatent les plis, rinçage difficile, risque d'endommager la cartouche. → clarifiant ou nettoyage manuel.
- **Diatomées → floculant choc déconseillé.** Risque de colmatage rapide. → nettoyage filtre + clarifiant.
- **Électrolyse au sel** : pas de contre-indication stricte. Couper la cellule pendant décantation/aspiration. L'aspiration à l'égout fait perdre eau + sel → prévoir appoint + réajustement salinité après traitement.

## Critères de validation (acceptance — pour le planner / Pest)
- [ ] Type de filtre demandé avant toute recommandation de produit
- [ ] Branchement floculant/clarifiant correct selon le type de filtre
- [ ] Précondition pH affichée avant l'étape traitement
- [ ] Méthode choc : filtration explicitement à l'arrêt pendant la décantation (jamais 24 h en marche + aspiration)
- [ ] Étape filtre formulée génériquement (3 variantes)
- [ ] Avertissement sel/électrolyse (perte eau + sel) si applicable
- [ ] Aucune occurrence du mot « floculant » dans le parcours cartouche
