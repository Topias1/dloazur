# 05 — FLOCULANT BRANCH SPEC

> **STATUT : AUTORITAIRE**
> Cette spec fait foi pour la branche « eau trouble à particules fines + filtration OK ».
> Elle supersède D-08 (ancien plan générique « filtration 24 h + aspiration », qui mélangeait deux méthodes incompatibles).
> Le planner implémente cette logique verbatim. Toute divergence nécessaire se remonte, ne se décide pas unilatéralement.

## 0. Portée

Branche déclenchée quand le diagnostic conclut : eau trouble, particules fines en suspension, filtration fonctionnelle. Couvre le choix produit, la séquence d'application, les préconditions et les contre-indications. Le type de filtre est une entrée obligatoire de cette branche.

## 1. Principe directeur

Deux méthodes mutuellement exclusives. Ne jamais les combiner.

| Méthode | Filtration | Devenir des particules | Filtres compatibles |
|---|---|---|---|
| **Floculant choc** (décantation) | À l'ARRÊT pendant la décantation | Tombent au fond → aspiration à l'égout | Sable / verre uniquement |
| **Clarifiant** (filtration) | EN MARCHE en continu | Captées par le filtre → nettoyage filtre | Tous (obligatoire cartouche/DE) |

Erreur superseded (D-08) : filtration 24 h **et** aspiration des dépôts au fond. Si la filtration tourne, les fines restent en suspension, rien ne décante.

## 2. Branchement (ordre impératif)

```
1. TYPE DE FILTRE (entrée obligatoire, AVANT toute reco produit)
   ├─ Sable / Verre   → méthode A (floculant choc) ; clarifiant si trouble léger
   ├─ Cartouche       → méthode B (clarifiant). Floculant PROSCRIT.
   └─ Diatomées (DE)  → méthode C (nettoyage filtre + clarifiant). Floculant choc déconseillé.

2. pH (précondition bloquante)
   └─ Si pH ∉ [7,0 ; 7,4] → étape « ajuster le pH » insérée AVANT le traitement.

3. Dérouler la séquence de la méthode retenue.
```

Source du type de filtre :
- Client connecté : pré-rempli depuis la fiche piscine (champ filtration, Phase 2), **override possible**.
- Visiteur anonyme : nœud-question dédié.

Le mot « floculant » ne doit jamais apparaitre dans le parcours cartouche.

## 3. Séquences

### Méthode A — Floculant choc (filtre sable / verre)

1. Équilibrer le pH entre 7,0 et 7,4.
2. Verser le floculant liquide (dosage § 5), filtration en marche 30 min à 1 h pour disperser.
3. **Arrêter la filtration.** Laisser décanter 8 à 24 h (une nuit complète recommandée).
4. Aspirer le dépôt lentement, vanne en position **égout**, vitesse basse, sans remuer le fond.
5. Appoint d'eau, rééquilibrage (pH, désinfectant, sel le cas échéant).
6. Lavage à contre-courant (backwash) + rinçage du filtre.

### Méthode B — Clarifiant (filtre cartouche ; ou trouble léger sable/verre)

1. Équilibrer le pH entre 7,0 et 7,4.
2. Verser le clarifiant.
3. Filtration **en continu** 24 à 72 h.
4. Nettoyer le média filtrant quand la pression monte (§ 7).
5. Répéter si nécessaire.

### Méthode C — Diatomées

1. Nettoyer / recharger le filtre DE d'abord (capte déjà la majorité des fines).
2. Si insuffisant : clarifiant + filtration continue. **Pas de floculant choc.**

## 4. Préconditions (bloquantes)

- **pH** : 7,0 à 7,4 idéal (tolérance jusqu'à 7,6 selon produit). Hors plage = efficacité fortement réduite. Étape imposée avant traitement.
- **Arrêt filtration** (méthode A uniquement) : décantation 8 à 24 h, une nuit complète recommandée. Toute remise en marche prématurée annule la décantation.

## 5. Dosages et formes

| Forme | Usage | Dosage indicatif | Remarque |
|---|---|---|---|
| Liquide | Méthode choc (A) | 1 à 2,5 L / 100 m³ | Le plus efficace pour trouble marqué. Suivre la notice (concentrations très variables). |
| Chaussettes / cartouches (skimmer) | Clarification douce | selon notice | Libération lente sur plusieurs jours, le filtre capte. Trouble modéré uniquement. |
| Longue durée / galets | Préventif | selon notice | Entretien, pas curatif. |

## 6. Contre-indications (à signaler à l'utilisateur)

- **Filtre cartouche → floculant proscrit.** Les amas colmatent les plis, rinçage très difficile, risque d'endommager la cartouche. Basculer sur clarifiant ou nettoyage manuel.
- **Diatomées → floculant choc déconseillé.** Risque de colmatage rapide. Nettoyage filtre + clarifiant.
- **Électrolyse au sel** : pas de contre-indication stricte. Couper la cellule pendant décantation et aspiration. L'aspiration à l'égout fait perdre eau et sel → prévoir appoint + réajustement de la salinité.

## 7. Étape « nettoyage filtre » — formulation générique

Conditionner au type de filtre. Ne jamais écrire « backwash » seul :

- **Sable / verre** : lavage à contre-courant (backwash) puis rinçage.
- **Cartouche** : retirer et rincer la cartouche au jet, ou la remplacer.
- **Diatomées** : backwash puis recharge de diatomées, ou nettoyage manuel selon le modèle.

## 8. Critères de validation (Pest)

Spécification d'acceptation. Le planner implémente ces tests ; le corps de chaque `it` valide le comportement décrit.

```php
describe('Branche eau trouble / floculant', function () {

    // Branchement
    it('demande le type de filtre avant toute recommandation de produit');
    it('recommande la méthode floculant choc pour un filtre sable');
    it('recommande la méthode floculant choc pour un filtre verre');
    it('recommande un clarifiant et proscrit le floculant pour un filtre cartouche');
    it('ne fait jamais apparaitre le mot "floculant" dans le parcours cartouche');
    it('recommande nettoyage filtre puis clarifiant pour un filtre diatomées');

    // Source du type de filtre
    it('pre-remplit le type de filtre depuis la fiche piscine pour un client connecte');
    it('autorise l override du type de filtre pre-rempli');
    it('presente un noeud-question type de filtre au visiteur anonyme');

    // Préconditions
    it('insere une etape ajuster le pH quand le pH est hors 7.0-7.4');
    it('n insere pas l etape pH quand le pH est dans 7.0-7.4');
    it('en methode choc, marque la filtration a l arret pendant la decantation');
    it('en methode choc, prevoit une decantation entre 8h et 24h');
    it('en methode choc, indique l aspiration en position egout');

    // Cohérence anti-régression (D-08)
    it('ne propose jamais filtration continue 24h ET aspiration au fond dans la meme sequence');

    // Cas spéciaux
    it('signale la perte eau et sel a rebalancer en electrolyse apres aspiration egout');
    it('adapte l etape nettoyage filtre au type de filtre (3 variantes)');
});
```

## 9. Définition de « terminé »

- [ ] Type de filtre demandé avant toute reco produit.
- [ ] Branchement A/B/C correct selon le type de filtre.
- [ ] Précondition pH appliquée.
- [ ] Méthode A : filtration explicitement à l'arrêt pendant la décantation.
- [ ] Étape filtre générique (3 variantes).
- [ ] Avertissement électrolyse (eau + sel).
- [ ] Aucune occurrence de « floculant » dans le parcours cartouche.
- [ ] Suite Pest du § 8 verte.
