# Cahier des charges — App « Safe-Diagnostic » piscine (Dlo Azur)

> **STATUT : AUTORITAIRE (produit).** Définit le **quoi** et le **pourquoi** au niveau app. Le détail chimique/technique vit dans `05-FLOCULANT-BRANCH-SPEC.md` et `05-DIAGNOSTIC-EXPERT-AUDIT.md`. Les arbitrages produit de ce CDC deviennent référence d'implémentation au même titre que ces specs.
> **Cohérence SPEC :** le V0 (MVP) de ce CDC correspond au périmètre de `05-SPEC.md` (Stripe DIAG-04 et dashboard DIAG-05 déjà différés). Voir §9 phasage.

---

## 1. Contexte et objectifs

**Commanditaire.** Pierre, pisciniste auto-entrepreneur seul, Martinique. Clientèle : particuliers + quelques locations B2B (gîtes, villas). Climat tropical (chaleur, UV intenses, évaporation, eau de réseau parfois dure).

**Objectifs de l'app, par ordre de priorité :**
1. **Générer des leads qualifiés** pour Pierre (intervention, vente de produits, contrat de suivi).
2. **Donner un premier conseil fiable et gratuit** qui installe la confiance.
3. **Ne créer ni danger ni responsabilité juridique**, alors que le public manipule des produits chimiques.

**⭐ Contrainte directrice (commanditaire, 2026-05-30) : INFRA LA MOINS CHÈRE POSSIBLE × EFFET WHAOU MAXIMAL.** Pierre est auto-entrepreneur (ami du dev) — zéro appétit pour des coûts récurrents ou de la maintenance lourde, MAIS le produit doit « en jeter ». Conséquence de conception : on met le « whaou » là où il est **gratuit** — le **front-end** (PWA installable, design soigné OKLCH, animations/micro-interactions, fluidité, plan d'action premium, indice de confiance) et la **logique « intelligente »** (diagnostic action-aware = de simples règles, 0 infra). On évite ce qui crée du **coût récurrent / de la dette de maintenance solo** : push backend + scheduler, sync offline multi-appareils, dashboard pro, app native/stores. « Infra chère » ici = **complexité & maintenance durable** (pas la facture serveur, déjà ~4-7 €/mois scale-to-zero). Voir `05-EXPERT-ARBITRATION.md`.

**« Safe-diagnostic » (colonne vertébrale, 4 garde-fous).** Un diagnostic gratuit qui :
- ne **dégrade jamais** la situation (dosage prudent, ordre de traitement correct),
- ne met en danger ni l'utilisateur ni les baigneurs (EPI, délais, jamais de mélange dangereux),
- est **honnête sur sa propre incertitude** (mesures vs bandelettes vs visuel),
- **escalade vers Pierre** quand le cas est risqué, ambigu ou hors DIY.

---

## 2. Cibles (personas)

| Persona | Situation | Attente | L'app doit |
|---|---|---|---|
| **Novice inquiet** | Eau verte soudaine, aucune mesure | « Qu'est-ce que je fais ? » | Diagnostic visuel rapide, 2-3 gestes sûrs, rassurer, proposer Pierre |
| **Débrouillard** | Entretient lui-même, a des bandelettes | Doses précises, validation | Parcours mesures, doses prudentes + re-test, plan complet |
| **Gestionnaire B2B** | Plusieurs bassins de location | Triage rapide, traçabilité | Diagnostic express, carnet multi-bassins, contrat de suivi |

Servir les trois sans front-loader le formulaire : le novice ne doit jamais être bloqué par une saisie de mesures qu'il n'a pas.

---

## 3. Principes directeurs

1. **Symptôme d'abord, mesures à la demande** (divulgation progressive). Les mesures ne sont demandées que lorsque la branche en a besoin.
2. **Équilibrer avant désinfecter** : séquence **TAC → pH → (TH) → chlore → stabilisant → sel**, action par action.
3. **Safe by default** : données insuffisantes / cas ambigu ou risqué → ne pas deviner ; proposer une mesure ou rediriger vers Pierre.
4. **Honnêteté sur l'incertitude** : bandelettes peu précises → doses arrondies prudemment (à la baisse sur acide/chlore), dosage incrémental, **re-test obligatoire**, jamais de dose finale à 3 chiffres significatifs.
5. **Escalade pro pour le risqué** : acide chlorhydrique, électricité 230 V, gros équipement, échec répété → opt-in encadré ou redirection Pierre (= le lead le plus rentable).

---

## 4. Parcours utilisateur

```
Accueil
 └─ Choix du symptôme (eau verte / trouble / marron / claire mais problème / électrolyseur)
    └─ Tri progressif (1 à 3 questions)
       └─ Le moteur demande UNIQUEMENT les entrées utiles à la branche
          (type de filtre avant toute reco produit ; mesures si elles affinent)
          └─ Diagnostic + indice de confiance (élevé / moyen / indicatif)
             └─ Plan d'action sécurisé (gestes sûrs d'abord, doses si mesures fournies)
                └─ Boucle re-test si applicable
                └─ Capture du lead au bon moment (valeur déjà délivrée)
                   └─ [connecté] Carnet de suivi du bassin
```

**Règles :**
- **Mobile-first** (diagnostic au bord du bassin, plein soleil → contraste).
- **Deux voies toujours dispo :** qualitative (visuelle, sans mesure) pour le novice ; quantitative (mesures) qui enrichit la confiance et débloque les doses.
- **Connecté vs anonyme :** connecté = filtre/volume/historique pré-remplis (override possible) ; anonyme = nœuds-questions à la demande.
- **Disclaimer au bon moment :** acceptation **avant toute sortie chiffrée**, pas à l'arrivée (pas de friction pour un simple diagnostic visuel).
- **Photo de l'eau (optionnelle) :** enrichit le lead, **pas** de diagnostic automatique par photo — l'humain valide.
- **Reprise de session** (lien carnet de suivi).

---

## 5. Fonctionnalités

- **5.1 Intake intelligent** (divulgation progressive) : symptôme d'abord ; puis volume (ou surface+profondeur → volume affiché), type de filtre, sel (oui/non + ppm), pH, chlore libre, TAC, stabilisant. Recommandés option : chlore **total**, **TH/dureté**. Température : défaut ~28-30 °C.
- **5.2 Arbre de diagnostic** : par symptôme, questions non ambiguës ; couvre chlore-lock/surstabilisation, eau laiteuse calcaire, manque de stabilisant ; branche électrolyseur visible si `sel=true` **OU** `selPpm>1000`.
- **5.3 Moteur de doses** (serveur, isolé du JS — DIAG-02) : doses prudentes, plafonnées par application, indexées sur les mesures (pH− pondéré par le TAC) ; toute sortie chiffrée finit par « re-tester avant la dose suivante ».
- **5.4 Plans d'action sécurisés** : gabarit fixe (§7) ; gestes sûrs d'abord, doses si mesures fournies.
- **5.5 Indice de confiance** : élevé / moyen / indicatif ; « indicatif » → invite à mesurer ou contacter Pierre. *(Ajout CDC vs SPEC — V0.)*
- **5.6 Garde-fous sécurité** (transversaux, non contournables) : bloc EPI + « ne jamais mélanger », délais de baignade, hard stops/opt-in/redirection (acide, électricité, gros équipement), doses « indicatives, vérifier la notice ».
- **5.7 Capture & qualification de lead** : diagnostic + 2-3 gestes offerts immédiatement, contact ensuite (plan PDF / dosage perso / RDV) ; qualification auto : (a) DIY, (b) achat produit, (c) intervention/équipement, (d) suivi récurrent ; cas risqués/hors-DIY → « contacter Pierre ».
- **5.8 Carnet de suivi** : **(a) carnet LOCAL-ONLY en V0** (DIAG-07) — historique des diagnostics/mesures sur l'appareil (IndexedDB, 0 serveur/0 sync/0 compte), vue « mes diagnostics passés », continuité du re-test ; pur front = whaou + rétention sans infra. **(b) carnet synchronisé multi-appareils + courbes/évolution = V2** (DIAG-05 différé).
- **5.9 Espace Pierre** (back-office léger : leads taggés, photo/contexte, rappel/planif) — **Phase ultérieure**.

---

## 6. Contraintes

- **Sécurité chimique** (§5.6) : aucune sortie interprétable dangereusement ; risques majeurs = surdosage (acide/chlore) + mélange de produits.
- **Juridique** : disclaimer avant dose ; « ne remplace pas un professionnel » par plan ; doses indicatives + renvoi notice ; **exposition n°1 = acide chlorhydrique par un particulier** (vinaigre d'abord, acide opt-in EPI, ou redirection pro par défaut).
- **Climat tropical** : stabilisant central (bas → chlore évaporé UV ; haut → chlore-lock) ; demande chlore élevée ; eau dure → choc calcium vs sodium ; évaporation concentre sel/stabilisant ; eau ~28-30 °C par défaut.
- **Technique** : doses serveur jamais en JS (DIAG-02) ; mobile-first, rapide, contraste extérieur ; reprise de session ; tolérer une reprise différée (pas de dépendance à une connexion parfaite).

---

## 7. Contenu et ton

**Ton** grand public, rassurant, sans jargon non expliqué (chloramines, TAC, stabilisant définis en une phrase à la 1ʳᵉ occurrence).

**Gabarit d'un plan d'action :**
1. *Diagnostic* (une phrase) + indice de confiance.
2. *Pourquoi* (une phrase pédagogique).
3. *Sécurité* (EPI, ne jamais mélanger, délai baignade) — avant les gestes chimiques.
4. *Étapes* dans l'ordre correct, gestes sûrs d'abord.
5. *Doses* (si mesures) + « re-tester avant la dose suivante ».
6. *Quand appeler Pierre* (seuil d'escalade explicite).

**Bibliothèque de disclaimers réutilisable** (sécurité chimique, délai baignade, dose indicative, escalade pro, acceptation légale).

---

## 8. KPIs

| Objectif | Indicateur |
|---|---|
| Lead gen | Taux de capture après diagnostic ; leads qualifiés/mois |
| Qualité lead | Répartition DIY/produit/intervention/suivi ; conversion en RDV |
| Confiance | Taux d'achèvement ; retours « diagnostic utile » |
| Sécurité | Zéro incident ; aucune sortie signalée dangereuse |
| Rétention (ultérieur) | Bassins suivis ; re-tests effectués ; passage en contrat |

---

## 9. Périmètre et phasage

> Mapping avec le SPEC verrouillé : **V0 = `05-SPEC.md` (Phase 5 actuelle)**. V1/V2 = évolutions (certaines = DIAG-04/05 déjà différés). **Décision de scope Phase 5 (V0 seul vs V0+V1) à confirmer — voir note CONTEXT.**

- **MVP (V0)** — arbre par symptôme + voies qualitative/quantitative, doses serveur prudentes, garde-fous sécurité, indice de confiance, capture de lead simple, disclaimer. Branche floculant figée. **Corrections P0** de l'audit appliquées.
- **V1** — corrections **P1** (ordre de traitement imposé, chlore-lock, eau laiteuse calcaire, seuil stabilisant bas, break-point chloramines, intake TH/chlore total), qualification fine du lead, photo optionnelle.
- **V2** — carnet de suivi (rétention, multi-bassins B2B), espace Pierre, rappels de re-test, contrat récurrent. *(= DIAG-05 + au-delà.)*

**Hors-scope (pour l'instant) :** diagnostic auto par photo, e-commerce intégré, capteurs connectés, géoloc des interventions. *(Stripe/DIAG-04 reste différé à sa propre phase, cf. SPEC.)*

---

## 10. Références
- `05-SPEC.md` — périmètre verrouillé Phase 5 (= V0).
- `05-FLOCULANT-BRANCH-SPEC.md` — branche eau trouble/floculant (figée, autoritaire).
- `05-DIAGNOSTIC-EXPERT-AUDIT.md` — audit complet, corrections P0/P1/P2 + suite Pest + réponses aux questions ouvertes.
