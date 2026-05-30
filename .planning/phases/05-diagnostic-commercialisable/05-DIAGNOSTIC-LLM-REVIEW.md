# Relecture experte (LLM) — Diagnostic piscine Dlo Azur : TOUT valider

> **À l'attention du relecteur LLM.** Tu es un expert en traitement de l'eau de piscine *et* en conception d'outils d'aide à la décision grand public. Ce document contient **l'intégralité** de la logique d'un outil de diagnostic en ligne (piscine résidentielle, Martinique — chlore ou électrolyse au sel), extraite **verbatim** de la maquette de référence. Ta mission : **auditer absolument tout** — exactitude chimique, sécurité, cohérence de l'arbre, qualité des plans d'action, lacunes, et formulation grand public. Sois exhaustif. Pour chaque problème : cite l'élément précis (ID de nœud/leaf, n° de formule), explique le risque, propose la correction. Termine en répondant aux **questions ouvertes** du §7.
>
> Contexte produit : auto-entrepreneur seul (Pierre, pisciniste), clientèle particuliers + quelques locations B2B. L'outil sert à (a) générer un lead, (b) donner un premier conseil fiable, (c) ne pas créer de danger ni de responsabilité juridique. Public **non expert** qui va manipuler des produits chimiques. Climat tropical : forte chaleur, UV intense, évaporation, eau de réseau parfois dure.

---

## 0. Comment l'outil fonctionne

1. **Intake** : le visiteur saisit le **volume du bassin** (ou surface + profondeur) et, optionnellement, des **mesures** : pH, chlore libre, alcalinité (TAC), stabilisant, sel (ppm). Il déclare aussi s'il a un **électrolyseur au sel** et son **type de filtre**.
2. **Arbre de questions** : il choisit son symptôme (eau verte / trouble / marron / claire / électrolyseur) et répond à 1–3 questions de tri.
3. **Sortie** : un **plan d'action** textuel (leaf de l'arbre) et/ou des **doses chiffrées** calculées **côté serveur** à partir des mesures (jamais exposées en JS — contrainte produit DIAG-02).

> La branche **« eau trouble / floculant »** a déjà été revue et figée dans `05-FLOCULANT-BRANCH-SPEC.md` (filtre→méthode, pH bloquant, choc-vs-clarifiant). Audite la **cohérence du reste** avec cette spec (notamment les leaves `eau-boueuse` et `pollution-organique` qui réutilisent floculant + filtration).

---

## 1. Modèle de données d'intake (état collecté)

```
{
  volume: m³ (ou sizeMode = surface+depth → volume calculé),
  filtration: "sable" | "verre" | "cartouche" | "diatomées",
  sel: bool (piscine au sel ?),
  selPpm: ppm,
  chlore: mg/L (chlore libre),
  ph: —,
  alcalinite: mg/L (TAC),
  stabilisant: mg/L (acide cyanurique)
}
```

L'option « ⚡ Problème d'électrolyseur » n'apparaît dans le menu de départ **que si `sel = true`**.

**À valider §1 :** manque-t-il des entrées indispensables à un bon diagnostic (température de l'eau, chlore **total** vs libre pour calculer le combiné, dureté/TH, présence de phosphates, volume réellement requis vs surface) ?

---

## 2. Seuils cibles affichés

| Paramètre | Cible | « trop bas » déclenché si | « trop haut » déclenché si | Unité |
|---|---|---|---|---|
| pH | 7,2 – 7,4 | < 7,0 | > 7,6 | — |
| Chlore libre | 1 – 3 | < 1 | > 3 | mg/L |
| Alcalinité (TAC) | 80 – 120 | < 80 | > 120 | mg/L |
| Stabilisant | 30 – 50 | (jamais « bas ») | action si > 75 | mg/L |
| Sel | 3000 – 5000 (cible 4000) | < 3000 | > 6000 | ppm |

**À valider §2 :** fourchettes correctes pour piscine résidentielle chlore/sel en climat tropical ? Le stabilisant qui ne déclenche qu'au-delà de **75** mg/L (alors que la cible est 30–50) est-il prudent, sachant le risque de « chlore-lock » ? Faut-il un seuil bas de chlore plus élevé en plein soleil tropical (consommation rapide) ?

---

## 3. Formules de dosage (verbatim, côté serveur)

> `V` = volume m³. Toutes les bornes ci-dessous sont celles réellement codées.

### 3.1 Chlore choc — `Pp(V)`
```
produit : hypochlorite de calcium en poudre, SANS stabilisant
dose    : 15 g/m³  →  15 × V grammes
règle   : "15 g par m³ (sans stabilisant ajouté)"
```

### 3.2 pH bas (< 7,0) — pré-ajustement `Cd`
```
produit : pH+ (carbonate de soude)
paliers : b = max(1, round((7,2 − pH) / 0,1))
dose    : b × 10 g/m³  →  b × 10 × V g
note    : diluer dans un seau, verser devant les buses, filtration en marche
cible   : 7,2 – 7,4
```

### 3.3 pH haut (> 7,6) — pré-ajustement `Cd`
```
produit : pH− (bisulfate de sodium)
paliers : b = max(1, round((pH − 7,4) / 0,2))
dose    : b × 200 g / 10 m³  →  b × 200 × (V/10) g
note    : diluer, verser devant les buses, filtration 4 h, re-tester
```

### 3.4 pH haut — calculateur dédié `Ip(V, pH)`
```
delta o : (pH − 7,4) si pH > 7,4, sinon 0,2 par défaut
dose    : (o/0,1 × 100 × (V/10)) g de pH−
règle    : "100 g abaisse 0,1 pH pour 10 m³"
```
> ⚠️ **Deux formules pH− coexistent** (3.3 et 3.4). Ratio implicite identique (1000 g pour 0,1 pH / 100 m³) mais formulations différentes. Vérifier la cohérence ET l'exactitude (un abaissement de 0,1 pH pour 100 m³ avec 1 kg de bisulfate — est-ce réaliste ?).

### 3.5 TAC bas (< 80 mg/L)
```
produit : TAC+ (bicarbonate de sodium)
paliers : b = max(1, round((100 − TAC) / 10))
dose    : b × 18 g/m³  →  b × 18 × V g
note    : verser dans le bassin, filtration en marche, re-tester après 24 h
cible   : 80 – 120 mg/L
```

### 3.6 TAC haut (> 120 mg/L)
```
produit : pH− en petites doses
dose    : "à ajuster progressivement"
note    : baisser le pH à 7,0 quelques jours fait redescendre le TAC, re-tester chaque jour
```

### 3.7 Stabilisant élevé (> 75 mg/L)
```
action  : vidange partielle (le stabilisant ne se dégrade pas)
fraction: b = 0,5 si stab > 100 ; sinon b = 0,33
dose    : vidanger b × V m³ (~b×100 %) puis recompléter au réseau
note    : privilégier ensuite l'hypochlorite de calcium poudre (sans stab) pour les chocs
cible   : 30 – 50 mg/L
```

### 3.8 Chlore bas (< 1 mg/L)
```
produit : hypochlorite de calcium poudre (sans stabilisant)
dose    : 15 g/m³  →  15 × V g
note    : diluer, verser pompe en marche, attendre < 3 mg/L avant baignade
cible   : 1 – 3 mg/L
```

### 3.9 Chlore haut (> 3 mg/L)
```
action  : aucun ajout — stopper la chloration, aérer
note    : attendre 24–48 h, le chlore redescend avec UV + aération
```

### 3.10 Sel bas (< 3000 ppm)
```
produit : sel pour piscine (pastilles)
dose    : (4000 − ppm) × V / 1000 kg
note    : verser dans le bassin (pas le skimmer), filtration 24 h, re-tester
```

### 3.11 Sel haut (> 6000 ppm)
```
action  : vidange partielle, fraction T = min(0,5 ; (ppm − 4500)/ppm)
dose    : vidanger T × V m³ puis recompléter
note    : taux trop élevé endommage l'électrolyseur et corrode
```

**À valider §3 — POINT CRITIQUE (sécurité) :**
- Chaque **produit** est-il le bon et le plus sûr ? (pH+ = carbonate de soude ; pH− = bisulfate de sodium ; TAC+ = bicarbonate ; choc = hypochlorite de calcium poudre.)
- Chaque **ratio g/m³** est-il exact et **sans risque de surdosage** ?
- Manque-t-il des **consignes de sécurité obligatoires** : EPI (gants/lunettes), « ne jamais mélanger chlore et acide », dissoudre avant versement, délai de baignade, ne pas surdoser le choc au calcium (risque de dureté/dépôts) ?
- Le choc à l'**hypochlorite de calcium** augmente le TH (calcium) — problème en eau déjà dure ? Faut-il proposer l'hypochlorite de sodium (Javel) en alternative ?

---

## 4. Arbre de décision complet

```
START « Quel est ton problème ? »
├─ 🟢 Eau verte ─────────────► green-1 « Vois-tu le fond ? »
│                               ├─ Oui ─► [analyse auto des mesures]
│                               │          • si chlore < 1      → CALC chlore choc (3.1/3.8)
│                               │          • sinon si pH > 7,6  → CALC pH (3.4)
│                               │          • sinon              → leaf algues-installees
│                               └─ Non (fond invisible) ─► leaf algues-avancees
│
├─ ⚪ Eau trouble ──────────────► cloudy-1 « Filtration OK ? »
│                               ├─ Oui ─► leaf floculant  →  voir 05-FLOCULANT-BRANCH-SPEC.md
│                               └─ Non ─► leaf filtration-insuffisante
│
├─ 🟤 Eau marron ───────────────► brown-1 « Après une pluie ? »
│                               ├─ Oui ─► leaf eau-boueuse
│                               └─ Non ─► brown-2 « Métaux ? »
│                                          ├─ Oui ─► leaf metaux
│                                          └─ Non ─► leaf pollution-organique
│
├─ 💎 Eau claire mais problème ─► clear-1
│                               ├─ Algues parois     ─► leaf algues-parois
│                               ├─ Odeur forte chlore ─► leaf odeur-forte
│                               └─ Irritation yeux    ─► leaf irritation-yeux
│
└─ ⚡ Électrolyseur (si sel) ───► electro-1 « Que se passe-t-il ? »
                                ├─ Aucun chlore / production faible ─► electro-2 « Sel ≥ 3000 ? »
                                │                                       ├─ Oui ─► electro-4
                                │                                       └─ Non ─► leaf electro-sel-bas
                                ├─ Voyant alarme ─► electro-3 « Type d'alarme ? »
                                │                    ├─ Manque sel  ─► leaf electro-sel-bas
                                │                    ├─ Débit/flow  ─► leaf electro-debit
                                │                    ├─ Cellule     ─► electro-4
                                │                    └─ Autre       ─► electro-4
                                └─ (via electro-2/3) electro-4 « Inspecte la cellule »
                                     ├─ Calcaire blanc        ─► leaf electro-entartree
                                     ├─ Plaques noircies/rongées ─► leaf electro-usee
                                     └─ Plaques propres        ─► leaf electro-panne
```

**À valider §4 :**
- Les **questions de tri** mènent-elles au bon diagnostic dans la majorité des cas réels ?
- **Branche eau verte / fond visible :** la logique auto ne teste que chlore puis pH ; si les deux sont OK elle conclut « algues qui s'installent ». Est-ce sûr (et si le stabilisant bloque le chlore = chlore-lock, non testé) ?
- **Cas fréquents manquants ?** chlore-lock (stabilisant saturé), eau laiteuse au calcaire/TH élevé, phosphates nourrissant les algues, point de rupture (break-point chloration), eau verte malgré chlore élevé (algues moutarde/résistantes).
- L'entrée électrolyseur **cachée si pas de sel déclaré** : un utilisateur au sel qui n'a pas coché « sel » perd l'accès — problème d'UX ?

---

## 5. Plans d'action — les 16 conclusions (verbatim)

> Format : **id** — *diagnostic* / analyse / étapes. **Audite chaque plan** : exactitude, ordre, sécurité, complétude.

### Eau verte
- **algues-avancees** — *Algues avancées.* « Prolifération massive, traitement choc immédiat. »
  1. Brosser énergiquement parois et fond
  2. Chlore choc hypochlorite de calcium poudre **30 g/m³** (sans stabilisant)
  3. Anti-algues curatif
  4. Filtration continue 24-48 h
  5. Nettoyer le filtre après traitement
- **algues-installees** — *Algues en cours d'installation.* « Le chlore est normal mais le pH est correct, les algues s'installent. »
  1. Brosser parois et fond  2. Anti-algues préventif  3. Vérifier le TAC  4. Filtration prolongée 12 h
  > ❓ Analyse incohérente : « chlore normal **mais** pH correct » — à reformuler.

### Eau trouble
- **floculant** — voir spec dédiée.
- **filtration-insuffisante** — *Filtration insuffisante.*
  1. Vérifier le filtre (sable/cartouche)  2. Nettoyer/remplacer le média  3. Vérifier pompe + paniers  4. Augmenter le temps de filtration  5. Contrôler le débit

### Eau marron
- **eau-boueuse** — *Eau boueuse après pluie.*
  1. Aspirer les dépôts au fond **à l'égout**  2. Chlore choc  3. Floculant pour clarifier  4. Filtration continue 24 h  5. Rincer le filtre
  > ❓ Même incompatibilité que floculant : « aspirer à l'égout » (filtration coupée) + « floculant + filtration 24 h ». À harmoniser avec la spec floculant.
- **metaux** — *Présence de métaux.*
  1. Séquestrant de métaux  2. pH 7,2-7,4  3. Filtration 24 h  4. Nettoyer le filtre  5. Analyser l'eau de remplissage
- **pollution-organique** — *Pollution organique.*
  1. Nettoyage mécanique complet  2. Chlore choc  3. Floculant pour clarifier  4. Filtration continue 24 h
  > ❓ Idem : floculant + filtration continue (cohérence filtre à valider).

### Eau claire mais problème
- **algues-parois** — *Algues fixées sur les parois.*
  1. Brosser toutes les parois  2. Anti-algues préventif  3. Chlore libre à 1,5 mg/L  4. pH 7,0-7,4
- **odeur-forte** — *Chloramines (chlore combiné).* « Odeur forte = il manque du chlore actif. »
  1. Chlore choc (détruire les chloramines)  2. Aérer  3. Filtration continue 12 h  4. Renouveler partiellement l'eau
  > À valider : faut-il une chloration **au point de rupture** (break-point, ~10× le chlore combiné) plutôt qu'un choc standard ?
- **irritation-yeux** — *Déséquilibre pH ou chloramines.*
  1. Mesurer + ajuster pH 7,2-7,4  2. Chlore choc  3. Vérifier TAC 80-120  4. Aérer

### Électrolyseur
- **electro-sel-bas** — *Taux de sel insuffisant.*
  1. Mesurer le sel  2. Ajouter pour viser ~4000 ppm  3. Verser dans le bassin (pas skimmer)  4. Filtration 24 h  5. Re-tester + relancer  6. Chloration manuelle en attendant
- **electro-debit** — *Défaut de débit (alarme flow).*
  1. Pompe + vannes  2. Nettoyer paniers  3. Nettoyer/backwasher le filtre  4. Contrôler le flow switch  5. Vérifier l'air dans le circuit  6. Sinon capteur HS
- **electro-entartree** — *Cellule entartrée.*
  1. Couper alim électrolyseur ET pompe
  2. Fermer vannes, démonter la cellule
  3. **Mélange : 1 volume d'acide chlorhydrique pour 9 volumes d'eau (acide dans l'eau, jamais l'inverse)**
  4. Plonger la cellule, **bouillonner 5-10 min max**
  5. Rincer abondamment, remonter, relancer
  6. Prévention : pH 7,2-7,4, vérifier l'inversion de polarité
  > ⚠️ **Sécurité : manipulation d'acide chlorhydrique par un particulier.** Ratio, durée, EPI, ventilation : valider et renforcer les avertissements, ou rediriger vers un pro.
- **electro-usee** — *Électrodes usées (remplacement).*
  1. Relever la référence  2. Commander une cellule compatible  3. Chloration manuelle en attendant  4. Remplacer (couper alim, vannes, joint)  5. Recalibrer  6. Sinon contacter Dlo Azur
- **electro-panne** — *Panne probable du boîtier.*
  1. Fusibles + câblage  2. Tension affichée  3. Tester une autre cellule  4. Diagnostic pro du coffret  5. Chloration manuelle  6. Contacter Dlo Azur

---

## 6. TOUT à valider — checklist exhaustive

**Chimie & dosage**
- [ ] Fourchettes cibles §2 (climat tropical inclus)
- [ ] Produits nommés §3 (corrects, sûrs, alternatives ?)
- [ ] Ratios g/m³ et kg exacts, sans surdosage dangereux
- [ ] Cohérence des deux formules pH− (3.3 vs 3.4)
- [ ] Niveaux de choc 15 g/m³ (standard) vs 30 g/m³ (algues avancées)
- [ ] Sel : viser 4000 ppm et seuils 3000/6000 ppm
- [ ] Stabilisant : seuil d'action 75 mg/L + logique de vidange 0,33/0,5

**Sécurité & responsabilité**
- [ ] Consignes EPI + « ne jamais mélanger » systématiques
- [ ] Délais avant baignade
- [ ] Manipulations électrolyseur (acide) : OK pour un particulier ou redirection pro ?
- [ ] Le disclaimer légal (obligatoire avant tout conseil) couvre-t-il ces risques ?

**Arbre & logique**
- [ ] Questions de tri non ambiguës
- [ ] Cas fréquents manquants (chlore-lock, phosphates, TH, break-point, algues résistantes)
- [ ] Logique auto eau-verte/fond-visible suffisante
- [ ] Cohérence floculant des leaves eau-boueuse / pollution-organique

**Plans d'action**
- [ ] Exactitude et ordre de chaque plan
- [ ] Reformuler analyses ambiguës (algues-installees)
- [ ] Ordre de priorité quand plusieurs paramètres déréglés (équilibrer avant chlorer ?)

**Rédaction grand public**
- [ ] Vocabulaire accessible, pas de jargon non expliqué
- [ ] Aucune instruction interprétable de façon dangereuse

---

## 7. Points en suspens — questions à éclaircir

1. **Ordre de traitement multi-paramètres.** Quand pH, TAC, chlore et sel sont tous déréglés, dans quel **ordre** agir, et faut-il imposer une séquence dans l'outil (ex. TAC → pH → chlore → sel) plutôt qu'une liste plate ?
2. **Stabilisant.** Le seuil d'action à 75 mg/L est-il sûr ? À partir de quelle valeur le « chlore-lock » devient-il un risque réel qu'il faut détecter dans l'arbre eau-verte ?
3. **Choc au calcium et eau dure.** L'hypochlorite de calcium ajoute du TH. En Martinique (eau parfois dure), faut-il basculer sur hypochlorite de sodium pour les chocs ? Quelle règle de décision ?
4. **Formule pH−.** Laquelle des deux (3.3 / 3.4) garder ? Le ratio « 1 kg pour 0,1 pH / 100 m³ » est-il correct, ou surévalué/sous-évalué ?
5. **Chloramines / odeur-forte.** Choc standard suffisant, ou chloration au point de rupture nécessaire (et comment la calculer simplement pour un particulier) ?
6. **Électrolyseur — limite particulier/pro.** Quelles manipulations laisser au client (détartrage acide ? remplacement cellule ?) et lesquelles rediriger systématiquement vers un pro, avec quel avertissement ?
7. **Cas manquants prioritaires.** Parmi (chlore-lock, phosphates, eau laiteuse calcaire, algues moutarde/noires, break-point), lesquels ajouter en priorité à l'arbre pour qu'il soit crédible ?
8. **Données d'intake.** Faut-il demander le **chlore total** (pour déduire le combiné), la **température**, le **TH/dureté** ? Lesquels sont indispensables vs superflus pour un premier diagnostic grand public ?
9. **Sécurité juridique.** Quelles formulations/avertissements ajouter pour qu'un conseil de dosage donné à un non-expert n'engage pas la responsabilité de l'entreprise (au-delà du disclaimer d'acceptation) ?
10. **Unités & mesures grand public.** Beaucoup de particuliers n'ont que des **bandelettes** (précision faible). Les seuils et doses doivent-ils être tolérants à cette imprécision (fourchettes, arrondis prudents) ?

---

*Source : `mockups/diagnostic-dloazur.html` — extraction verbatim 2026-05-30. Les corrections validées deviennent référence d'implémentation, au même titre que `05-FLOCULANT-BRANCH-SPEC.md`. La branche floculant est déjà figée — ne la re-spécifie pas, signale seulement les incohérences du reste avec elle.*
