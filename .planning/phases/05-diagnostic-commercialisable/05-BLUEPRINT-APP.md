# Blueprint app Dloazur — « je règle ma piscine moi-même, sinon j'appelle Dloazur »

> **STATUT : AUTORITAIRE (produit/UX).** Verrouille le persona principal et la boucle centrale. Pour le planner / l'agent d'implémentation. S'appuie sur `05-CDC-SAFE-DIAGNOSTIC.md` et les specs chimiques `05-FLOCULANT-BRANCH-SPEC.md` / `05-DIAGNOSTIC-EXPERT-AUDIT.md`.

> ## ⚠ RÉCONCILIATION DE SCOPE vs SPEC VERROUILLÉ (à lire avant de planifier)
> Ce blueprint décrit la **vision app complète**. Le SPEC verrouillé (`05-SPEC.md`) cadre **Phase 5** plus étroitement. Trois éléments du blueprint **dépassent ou contredisent** le SPEC Phase 5 et appartiennent à une phase ultérieure, SAUF décision explicite de re-scoper :
> - **Notifications push de re-test (§8)** — non prévues par le SPEC ; impliquent PWA push (limité/récent sur iOS, cf. memory `pierre-device-platform` : matrice iOS+Android anciens). → **phase ultérieure**.
> - **Persistance offline + carnet (§7.8, §8)** — le SPEC liste explicitement « Offline support — the diagnostic is online-only » **HORS SCOPE** Phase 5, et le carnet = DIAG-05 différé. → **phase ultérieure** (V2 du CDC).
> - **Caméra / photo (§8)** — additif léger, non dans le SPEC ; peut entrer en Phase 5 ou plus tard.
>
> **Ce qui EST compatible Phase 5 / V0 (web public `/diagnostic`, SPEC Req 9) :** la boucle minimale §10.1 — symptôme → « déjà tenté ? » → diagnostic conscient des actions (§6) → plan sûr (garde-fous P0) → **escalade en 1 geste avec contexte riche** (= WhatsApp hand-off SPEC Req 7, enrichi) → capture de lead → PDF. L'**indice de confiance** et le diagnostic **action-aware** (§6) sont des ajouts forts et compatibles.
>
> **La fermeture de boucle native (push + re-test + carnet offline) est précisément ce que le blueprint lui-même séquence en étape §10.3, APRÈS la V0.** Lecture retenue (à confirmer) : Phase 5 = boucle minimale sur le web ; push/offline/carnet/espace-Pierre = phase « mobile & rétention » dédiée.

---

## 1. Persona verrouillé

> « J'ai une piscine chez moi en Martinique. Je faisais l'entretien moi-même jusqu'ici, mais maintenant j'ai des soucis que je n'arrive pas à résoudre. Je télécharge l'app Dloazur pour régler mes soucis moi-même. Et si ce n'est pas gérable pour un particulier, ou si je n'y arrive pas, l'app me redirige vers un contact avec Dloazur. »

**Implications :**
1. **Utilisateur compétent mais bloqué.** Il a déjà tenté l'évident (choc, brossage). Ne pas le traiter en débutant ni re-proposer ce qu'il a fait. L'évident a échoué : c'est pour ça qu'il est là.
2. **La valeur est dans le diagnostic profond.** Causes non évidentes (chlore-lock, calcaire, métaux, équipement) — là où l'app bat un tuto générique.
3. **L'escalade est un filet, pas un piège à lead.** On l'aide à réussir seul d'abord. La redirection arrive (a) si hors-DIY diagnostiqué, ou (b) après échec. Cette honnêteté **est** ce qui convertit.
4. **App mobile** : usage au bord du bassin, plein soleil, téléphone en main. *(NB scope : cf. réconciliation — surface Phase 5 = web `/diagnostic` mobile-first / PWA, pas une app native store.)*

---

## 2. La boucle centrale

```
   Symptôme
      │
      ▼
   Ce que tu as DÉJÀ tenté  ──────────►  (signal de diagnostic, §6)
      │
      ▼
   Diagnostic approfondi  +  indice de confiance
      │
      ├──────────────► hors-DIY d'emblée ──────────────┐
      ▼                                                  │
   Plan d'action précis (gestes sûrs, doses si mesures)  │
      │                                                  │
      ▼                                                  │
   Exécution                                             │
      │                                                  │
      ▼   (rappel à 24-48 h — push = phase ultérieure)   │
   Re-test : ça a marché ?                               │
      ├─ Oui ─► Carnet + prévention (carnet = ultérieur) │
      └─ Non ─►─────────────────────────────────────────┤
                                                         ▼
                                          ESCALADE Dloazur
                               (contact en 1 geste, contexte complet)
```

---

## 3. Ce qui rend l'app la meilleure pour CE besoin
1. **Respecter la compétence.** Première vraie question après le symptôme : « qu'as-tu déjà essayé ? ». Retirer de l'arbre ce qui a été fait, utiliser l'échec comme indice (§6).
2. **Diagnostic conscient des actions tentées.** Un choc raté ne mène pas à « refais un choc » : il oriente vers chlore-lock, algues résistantes ou métaux.
3. **Escalade honnête = intégrité qui convertit.** Dire « ça dépasse le DIY, appelle Pierre » au bon moment > faux espoir. Lead qualifié, confiance gagnée.
4. **Fermer la boucle (valeur mobile native).** Rappel de re-test + « ça a marché ? ». *(Phase ultérieure — voir réconciliation.)*
5. **Escalade sans friction, contexte riche.** Un bouton « demander Dloazur » qui transmet tout (symptôme, mesures, tenté, diagnostic, photo). Pierre arrive en sachant tout.

---

## 4. Flux clés
- **4.1 DIY résolu (nominal)** : symptôme → tenté → diagnostic (confiance élevée) → plan → re-test OK → (carnet + prévention). Pas d'escalade.
- **4.2 DIY tenté puis échec → escalade (réactif)** : retour après re-test sans amélioration → l'app reconnaît l'échec, ne tourne pas en rond, propose l'escalade en transmettant tenté + résultat. Lead le plus rentable.
- **4.3 Hors-DIY d'emblée → escalade (préemptif)** : diagnostic = équipement HS / manip à risque / déséquilibre majeur → bascule franche sur le contact.
- **4.4 Données insuffisantes / ambigu → mesurer (safe by default)** : demander une mesure (ou photo) plutôt que deviner ; si ambiguïté persiste → escalade.

---

## 5. Moteur d'escalade
**Préemptifs (avant DIY) :** équipement (cellule entartrée/usée, coffret, pompe, fuite) ; manip à risque (acide chlorhydrique, électrique 230 V) ; déséquilibre majeur (gros volumes produit, vidange substantielle).
**Réactif (après DIY) :** plan exécuté + re-test sans amélioration → escalade ; 2 tentatives infructueuses → escalade forcée.
**Contexte transmis (lead riche) :** symptôme, mesures (date + fiabilité), filtre + volume, **historique des actions tentées + résultats**, diagnostic app, photo si fournie, coordonnées. Objectif : Pierre n'a aucune question à reposer.
**Action :** un seul geste (« demander une intervention Dloazur »), pré-rempli. Pas de formulaire long.

---

## 6. Diagnostic conscient des actions tentées (différenciant — entrée d'arbre)

| Déjà tenté, sans succès | Cause probable (non évidente) | Orientation |
|---|---|---|
| Choc chlore, eau toujours verte | Chlore-lock (stab haut), algues résistantes (moutarde/noires), ou métaux | Tester stabilisant ; haut → vidange partielle ; sinon choc renforcé + brossage ; teinte métallique → séquestrant |
| Ajout de chlore, lecture reste ~0 | Forte demande (bloom organique), chlore-lock, ou test faussé par excès de chlore | Vérifier stabilisant + matière organique, re-tester correctement |
| pH baissé, remonte sans cesse | TAC trop haut qui pousse le pH (aggravé au sel) | Traiter le TAC d'abord, jamais le pH seul |
| Eau trouble malgré filtration + floculant | Média filtrant épuisé/canalisé, ou trouble **calcaire** (TH/pH hauts) | Vérifier/régénérer le média ; si calcaire → rééquilibrer (pas floculer) |
| Électrolyseur ne produit plus | Sel bas, cellule entartrée/usée, ou coffret HS | Arbre électrolyseur ; entartrage/usure = limite DIY → escalade probable |

Ces cas recoupent les manques de l'audit (chlore-lock, eau laiteuse calcaire, break-point) — précisément les murs sur lesquels bute un bon bricoleur.

---

## 7. Écrans et états (mobile)
1. **Accueil** : « Quel est ton souci ? » (gros boutons symptômes).
2. **Symptôme → tri** : 1-3 questions + « qu'as-tu déjà essayé ? » (multi-choix).
3. **Mesures (à la demande)** : seulement si la branche en a besoin ; saisie tolérante bandelettes.
4. **Diagnostic** : énoncé clair + indice de confiance + « pourquoi ».
5. **Plan d'action** : bloc sécurité, étapes ordonnées, doses si mesures, « re-tester avant la dose suivante ».
6. **Suivi / re-test** : « as-tu re-testé ? ça a marché ? » *(déclenché par notification — phase ultérieure ; en V0, accessible manuellement / via le PDF / retour sur le site).*
7. **Escalade Dloazur** : récap contexte + bouton unique de contact.
8. **Carnet** : **local-only en V0** (DIAG-07) — historique sur l'appareil (IndexedDB, 0 serveur/0 sync), « mes diagnostics passés », continuité du re-test. *(Synchronisé multi-appareils + courbes + multi-bassins = V2, DIAG-05.)*

États transverses : sécurité avant chaque geste chimique ; escalade accessible depuis n'importe quel écran de plan.

---

## 8. Exigences mobiles *(majoritairement phase ultérieure — voir réconciliation)*
- **Push** : rappels de re-test. PWA push OK Android + iOS récent ; sinon wrapper natif. **→ phase mobile/rétention.**
- **Persistance locale** : carnet local-only sur l'appareil (IndexedDB) + reprise de session. **← Phase 5 V0** (DIAG-07). La **synchro multi-appareils** reste V2 ; le calcul d'un nouveau diagnostic reste online (doses serveur).
- **Caméra** : photo de l'eau optionnelle, en aide au lead (pas de diagnostic auto). Peut entrer en Phase 5 (upload simple) ou plus tard.
- **Plein soleil** : fort contraste, gros boutons, lisibilité extérieure. **← Phase 5 (CSS/UX).**
- **Contact en un geste** : appeler / demander Dloazur sans ressaisie. **← Phase 5.**
- **Architecture** : PWA installable recommandée pour démarrer (un codebase, cohérent stack web) ; wrapper natif si stores + push riches deviennent prioritaires. **Choix à trancher.**

---

## 9. Garde-fous (non négociables, hérités du CDC + audit)
Dosage prudent et plafonné ; ordre TAC → pH → TH → chlore → stabilisant → sel ; EPI + « ne jamais mélanger » avant chaque geste chimique ; délais de baignade ; doses côté serveur (DIAG-02) ; opt-in fort ou redirection sur l'acide électrolyseur ; honnêteté sur l'incertitude des bandelettes.

---

## 10. Séquence de construction
1. **Boucle minimale (V0 — Phase 5)** : symptôme → « déjà tenté ? » → diagnostic → plan (garde-fous P0) → escalade en un geste.
2. **Profondeur de diagnostic** : table §6, chlore-lock, eau laiteuse calcaire, seuil stabilisant bas *(= P1 audit ; V1)*.
3. **Fermeture de boucle** : carnet local, notifications de re-test, écran « ça a marché ? » → escalade réactive *(phase mobile/rétention)*.
4. **Lead riche + Pierre** : caméra/photo, contexte complet, espace Pierre de qualification *(phase ultérieure)*.

---

## 11. Références
- `05-CDC-SAFE-DIAGNOSTIC.md` — CDC produit.
- `05-SPEC.md` — périmètre verrouillé Phase 5 (= V0).
- `05-FLOCULANT-BRANCH-SPEC.md` — branche floculant (figée).
- `05-DIAGNOSTIC-EXPERT-AUDIT.md` — audit, corrections P0/P1/P2.
