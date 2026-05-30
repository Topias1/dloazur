# 05 — DIAGNOSTIC CHEMISTRY SPEC (corrections expert)

> **STATUT : AUTORITAIRE.** Issu de l'audit expert pisciniste de `mockups/diagnostic-dloazur.html` (2026-05-30).
> Le planner implémente ces corrections verbatim. Même autorité que `05-FLOCULANT-BRANCH-SPEC.md`.
> Hors périmètre (NE PAS toucher) : branche floculant (figée), arbre électrolyseur `electro-2/3/4`, formules `3.2`/`3.5`/`3.9`/`3.10`/`3.11`, isolation serveur du calcul (DIAG-02), réflexe de redirection Dlo Azur sur les pannes.
> **P0 = bloquant** (sécurité ou faux diagnostic → surdosage). P1 = important. P2 = rédaction/confort.

## Triage

| # | Élément | Niveau | Nature |
|---|---|---|---|
| 1 | `3.8` chlore bas dosé comme un choc (15 g/m³) | **P0** | Surdosage |
| 2 | `electro-entartree` acide chlorhydrique par un particulier | **P0** | Sécurité |
| 3 | EPI / « ne jamais mélanger » non systématiques | **P0** | Sécurité |
| 4 | `green-1` : aucun test stabilisant → chlore-lock invisible | **P0** | Faux diagnostic |
| 5 | `eau-boueuse` + `pollution-organique` : floculant + filtration + égout | **P0** | Incohérence / colmatage |
| 6 | `3.3`/`3.4` pH− : dose fixe ignorant le TAC | **P1** | Surdosage possible |
| 7 | Pas de seuil stabilisant **bas** (tropical) | **P1** | Cause racine non détectée |
| 8 | Ordre de traitement multi-paramètres non imposé | **P1** | Efficacité |
| 9 | `odeur-forte` : choc standard au lieu du point de rupture | **P1** | Traitement insuffisant |
| 10 | Choc hypochlorite de calcium en eau dure | **P1** | Entartrage |
| 11 | Entrée électrolyseur cachée si `sel` non coché | **P1** | UX / accès perdu |
| 12 | Cas manquant « eau laiteuse calcaire » | **P1** | Diagnostic incomplet |
| 13 | Fausse précision des doses vs bandelettes | **P1** | Confiance trompeuse |
| 14 | `algues-installees` analyse contradictoire ; timing anti-algues | **P2** | Rédaction |

---

## 1. Intake — champs à ajouter

| Champ | Statut | Usage |
|---|---|---|
| `chlore_total` (mg/L) | recommandé | déduire le combiné `= total − libre` (chloramines) pour `odeur-forte`/`irritation-yeux` (point 9) |
| `th` / dureté calcique (mg/L) | recommandé | règle « choc calcium vs sodium » en eau dure (point 10) + risque entartrage |
| `temperature` (°C) | optionnel | défaut tropical **~28-30 °C** (ne pas imposer la saisie) |
| `phosphates` | hors intake principal | uniquement parcours avancé « algues récurrentes malgré chlore correct » |

Garder `volume` comme entrée première ; afficher le volume calculé (surface×profondeur) pour validation visuelle.

---

## 2. Seuils cibles — corrections

- **Stabilisant — seuil BAS manquant (P1) :** ajouter un déclencheur `< 20-30 mg/L` → proposer un apport de stabilisant. En tropical, c'est une **cause racine fréquente** d'eau verte (chlore détruit par les UV en quelques heures). Probablement le vrai diagnostic derrière beaucoup de `algues-installees`.
- **Stabilisant — seuil HAUT (P1) :** garder l'action vidange à `> 75`, mais **détecter le chlore-lock dès ~80-100** dans l'arbre eau-verte (point 4) : au-delà, l'eau peut verdir malgré un chlore « dans la cible ».
- **Chlore libre 1-3 (P2) :** borne basse de 1 trop faible en plein soleil. Idéal : indexer la cible sur le stabilisant (**~5-7,5 % du stabilisant**). À défaut d'indexation : viser **2-3 minimum** en pleine saison.
- **Sel cible 4000 (P2) :** dépend de la cellule. Formuler « viser la valeur préconisée par le fabricant, à défaut ~4000 ».
- **pH/TAC :** OK. Note (optionnel) : bassins au sel font monter le pH ; viser TAC 80-90 limite la dérive.

---

## 3. Formules de dosage — corrections

### P0 — `3.8` chlore bas : SÉPARER choc et rattrapage
- **Bug :** 15 g/m³ d'hypochlorite de calcium ≈ **10 ppm** = une dose de **choc**, pas un rattrapage vers 1-3 mg/L.
- **Correction :** créer **deux formules distinctes** :
  - **Choc** (`3.1`) = 15 g/m³ (léger) / 30 g/m³ (algues) — inchangé.
  - **Rattrapage chlore libre** (NOUVELLE) = **~3-4 g/m³** pour remonter ~2 ppm vers la cible, **suivi d'un re-test** (jamais une dose « finale »).

### P1 — `3.3`/`3.4` pH− : une seule formule, plafonnée
1. **Supprimer `3.3`**, garder uniquement `3.4` (calculateur explicite).
2. **Plafonner la dose par application** (viser au max ~0,2 pH de baisse d'un coup) puis « re-tester avant toute nouvelle dose ».
3. La demande en acide est gouvernée par le **TAC** (le tampon), pas par l'écart de pH seul : idéalement pondérer par le TAC mesuré ; à défaut, dosage **incrémental prudent**. Ne jamais afficher une dose finale unique.

### P2 — `3.1` choc : dépendance au titre du produit
Le tiérage 15/30 g/m³ suppose un hypochlorite de calcium **~65 %**. Indiquer dans la consigne que la dose dépend du titre du produit.

### P2 — `3.6` TAC haut
Méthode acide + aération correcte mais lente : prévenir du délai (plusieurs jours) + re-test quotidien.

### Inchangées (P0 de ne PAS toucher)
`3.2` pH+ (carbonate de soude), `3.5` TAC+ (bicarbonate ~18 g/m³/10 ppm, exact), `3.9` chlore haut, `3.10`/`3.11` sel. Produits bien nommés.

---

## 4. Arbre de décision — corrections

### P0 — `green-1` analyse auto : tester le stabilisant
La logique actuelle teste chlore puis pH ; si OK → `algues-installees`. Elle **ignore le stabilisant**. Ajouter :
- chlore présent + pH OK **mais** `stabilisant > ~80` → **nouvelle leaf « surstabilisation / chlore-lock »** (vidange partielle).
- `stabilisant < ~20-30` → **nouvelle leaf « manque de stabilisant »** (cause racine fréquente, apport de stabilisant).

### P0 — `eau-boueuse` / `pollution-organique` : router vers le sous-arbre floculant
Ne plus ré-écrire la clarification en ligne (incompatibilité égout + filtration 24 h, pas de gate filtre). **Router l'étape de clarification vers `05-FLOCULANT-BRANCH-SPEC.md`.** Ordre imposé (point §5).

### P1 — `cloudy-1` : bifurcation « eau laiteuse calcaire »
Sous `cloudy-1`, ajouter : si trouble **+ TH/pH/TAC élevés** → **nouvelle leaf « eau calcaire »** (baisser pH/TAC, séquestrant calcaire — **PAS de floculant**) ; sinon → sous-arbre floculant.

### P1 — entrée électrolyseur (#11)
Afficher l'option si `sel = true` **OU** `selPpm > 1000`, et permettre une auto-sélection « j'ai un électrolyseur » dans le menu symptôme.

### Inchangé
`electro-2/3/4` : logique saine.

---

## 5. Plans d'action — corrections

- **`algues-installees` (P2) :** reformuler l'analyse contradictoire → « chlore et pH corrects, pourtant les algues s'installent », puis orienter vers la vraie cause (stabilisant trop bas/haut, phosphates). Lier au test stabilisant ajouté en `green-1`.
- **`eau-boueuse` (P0) :** ré-ordonner sans mélanger les méthodes (filtre sable/verre) : brosser/épuiser gros débris → **choc chlore** → rééquilibrer → **puis** clarification via sous-arbre floculant (floc choc : filtration à l'arrêt, décantation, aspiration égout). **Floculant en dernier**, une fois le chlore redescendu (un chlore élevé gêne la floculation). Gate filtre obligatoire.
- **`pollution-organique` (P0) :** idem — nettoyage mécanique → choc → circulation/équilibre → clarification routée vers le sous-arbre floculant.
- **`odeur-forte` (P1) :** point de rupture (~10× le combiné). (a) avec `chlore_total` : `combiné = total − libre` → dose 10× ; (b) à défaut : choc généreux (viser ~10-15 ppm libre) + aération + re-test. Garder le message pédagogique « odeur forte = chloramines, signal de choc, pas un excès de chlore ».
- **`algues-avancees` (P2) :** ajouter « équilibrer le pH à ~7,2 avant le choc » ; décaler l'anti-algues **après** la redescente du chlore (préférer un polyquaternaire compatible chlore) ; vérifier le stabilisant.
- **`metaux` (P1, ajout) :** « éviter le choc tant que les métaux ne sont pas séquestrés » (sinon taches).
- **`algues-parois` (P2, ajout) :** indexer le chlore cible sur le stabilisant plutôt que 1,5 mg/L fixe.
- **`electro-entartree` (P0 — SÉCURITÉ) :** voir §6. Recommandation forte : **opt-in derrière avertissement, ou redirection pro par défaut**.
- **Inchangés :** `filtration-insuffisante`, `irritation-yeux`, `electro-sel-bas`, `electro-debit`, `electro-usee`, `electro-panne`.

---

## 6. Sécurité & responsabilité (transversal — P0)

**Bloc sécurité systématique sur CHAQUE étape chimique :**
- EPI : gants + lunettes.
- **Ne jamais mélanger** les produits, surtout chlore + acide (chlore gazeux toxique).
- Diluer/dissoudre **avant** de verser ; ajouter le produit **à l'eau**, jamais l'eau au produit.
- Délai de baignade : chlore libre < 3 mg/L et pH 7,0-7,6 avant retour à l'eau.
- Ne pas surdoser le choc calcium (apport de dureté).
- Stockage : hors de portée des enfants, à l'ombre, contenants d'origine.

**Cadrage par plan :** doses « **indicatives, à vérifier sur la notice du produit** » ; « ces conseils ne remplacent pas l'analyse d'un professionnel ; en cas de doute, contactez Dlo Azur ».

**Disclaimer :** confirmer l'acceptation **avant** toute sortie chiffrée (DIAG-02 déjà bien isolé).

**`electro-entartree` — renforcements minimaux si l'étape est conservée :**
1. **Vinaigre blanc d'abord** (entartrage léger) ; acide chlorhydrique seulement si le calcaire résiste.
2. EPI affichés AVANT la manip : gants chimiques, lunettes, **en extérieur ventilé, jamais en intérieur**.
3. « Acide dans l'eau, jamais l'inverse » en avertissement proéminent.
4. **Trempage** plutôt que bullage prolongé ; durée strictement limitée (l'acide érode le revêtement, raccourcit la vie de la cellule) ; ne nettoyer que si entartrage visible.
5. Rinçage abondant ; jamais d'outil métallique sur les plaques.
6. Sortie « en cas de doute, arrête et contacte Dlo Azur ».
   → **Pour un auto-entrepreneur seul, le risque juridique penche vers la redirection par défaut.**

**Limite particulier/pro (électrolyseur) :** au client = mesure/ajout sel, nettoyage paniers/filtre, contrôle flow, remplacement simple de cellule (alim/vannes coupées). À encadrer/rediriger = détartrage acide, diagnostic coffret/électronique, tout le câblage 230 V.

---

## 7. Ordre de traitement multi-paramètres (P1)

Imposer une **séquence**, pas une liste plate :

```
TAC → pH → (TH) → désinfection (chlore) → stabilisant → sel
```

Le TAC tamponne le pH (l'ajuster d'abord). Pas de chloration efficace sur une eau non équilibrée. L'outil affiche les actions dans cet ordre et, si possible, **bloque la chloration tant que pH/TAC sont hors plage**.

## 8. Imprécision bandelettes (P1)

- Raisonner en **fourchettes** ; **arrondir prudemment** (à la baisse sur acide et chlore pour éviter l'overshoot).
- Privilégier **dosage incrémental + re-test** plutôt qu'une dose finale précise.
- **Jamais 3 chiffres significatifs** (fausse précision face à une bandelette ±0,5 pH).
- Toute sortie chiffrée se termine par « **re-tester avant la dose suivante** ».

---

## 9. Critères d'acceptation (Pest)

```php
describe('Diagnostic — corrections expert', function () {
    // P0 dosage
    it('dose le rattrapage chlore bas a ~3-4 g/m3, pas 15 (separe du choc)');
    it('conserve le choc a 15 g/m3 (leger) et 30 g/m3 (algues)');
    it('termine toute sortie chiffree par une consigne de re-test');

    // P0 securite
    it('affiche le bloc EPI + ne jamais melanger sur chaque etape chimique');
    it('affiche un delai de baignade sur toute sortie chlore/pH');
    it('met le detartrage acide en opt-in ou redirige vers un pro par defaut');
    it('recommande le vinaigre blanc avant l acide chlorhydrique');

    // P0 chlore-lock / stabilisant
    it('en eau verte chlore+pH OK mais stabilisant > 80, conclut surstabilisation (pas algues)');
    it('detecte un stabilisant bas (< 20-30) comme cause racine et propose un apport');

    // P0 coherence floculant
    it('route la clarification de eau-boueuse vers le sous-arbre floculant');
    it('route la clarification de pollution-organique vers le sous-arbre floculant');
    it('place le floculant en dernier, apres redescente du chlore');

    // P1 pH-
    it('n expose qu une seule formule pH- (3.4) plafonnee par application');

    // P1 odeur-forte
    it('calcule le point de rupture (10x le combine) si chlore total fourni');
    it('a defaut de combine, propose un choc genereux + aeration + re-test');

    // P1 eau dure
    it('bascule le choc sur hypochlorite de sodium si TH > 250-300');

    // P1 arbre
    it('bifurque cloudy-1 vers eau-calcaire si trouble + TH/pH/TAC eleves (pas floculant)');
    it('affiche l entree electrolyseur si sel=true OU selPpm > 1000');

    // P1 ordre
    it('affiche les actions dans l ordre TAC -> pH -> TH -> chlore -> stabilisant -> sel');
    it('bloque la chloration tant que pH ou TAC sont hors plage');
});
```

## 10. Définition de « terminé »

- [ ] Rattrapage chlore bas séparé du choc (~3-4 g/m³) + re-test.
- [ ] Bloc sécurité (EPI, ne jamais mélanger, délai baignade) sur chaque étape chimique.
- [ ] Détartrage acide opt-in/redirigé + vinaigre d'abord.
- [ ] `green-1` teste le stabilisant (haut → chlore-lock, bas → apport).
- [ ] `eau-boueuse` + `pollution-organique` routés vers le sous-arbre floculant, floculant en dernier.
- [ ] Formule pH− unique, plafonnée, re-test.
- [ ] `odeur-forte` au point de rupture (ou fallback documenté).
- [ ] Règle choc calcium/sodium selon TH.
- [ ] Bifurcation eau-calcaire sous `cloudy-1`.
- [ ] Entrée électrolyseur sur `sel=true OU selPpm>1000`.
- [ ] Ordre de traitement imposé + blocage chloration si pH/TAC hors plage.
- [ ] Doses en fourchettes, arrondi prudent, pas de fausse précision.
- [ ] `algues-installees` reformulé.
- [ ] Suite Pest §9 verte.
