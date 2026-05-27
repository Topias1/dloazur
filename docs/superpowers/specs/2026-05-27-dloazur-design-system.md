# Design system & maquettes — Dlo Azur Piscines

**Version :** v1
**Date :** 2026-05-27
**Statut :** prêt à coller dans Claude Design (claude.ai/design)
**Document parent :** `2026-05-27-dloazur-refonte-design.md`

---

## 1. Principes

- **Mobile-first systématique** (iPhone 375px de référence).
- **Eau / Caraïbes** comme inspiration, sans cliché tropical (pas de palmiers, pas de cocotiers).
- **Pro et rassurant**, pas glacial corporate.
- **Photos réelles** de piscines, pas d'illustrations vectorielles.
- **Sobre**, généreux en espace blanc, ombres discrètes, coins arrondis.
- **Implémentable en Tailwind** sans framework JS lourd (cohérent avec Laravel + Livewire).

---

## 2. Palette

| Rôle | Hex | Tailwind | Usage |
|---|---|---|---|
| Primary (turquoise lagon) | `#14B8A6` | `teal-500` | Boutons principaux, liens, accents |
| Secondary (bleu profond) | `#0E4D64` | proche `teal-900` | Titres, texte fort, header |
| Accent (vert d'eau clair) | `#5EEAD4` | `teal-300` | États actifs, highlights |
| Background (sable très clair) | `#FEFCF7` | proche `stone-50` | Fond général |
| CTA chaud (corail doux) | `#FB923C` | `orange-400` | CTA secondaires, alertes douces |
| Texte courant (gris ardoise) | `#475569` | `slate-600` | Paragraphes |
| Bordures / séparateurs | `#E2E8F0` | `slate-200` | Lignes discrètes |
| Succès | `#10B981` | `emerald-500` | Validations, synchro OK |
| Alerte douce | `#F59E0B` | `amber-500` | Hors ligne, info à vérifier |
| Erreur | `#EF4444` | `red-500` | Erreurs critiques uniquement |

---

## 3. Typographie

- **Titres** : Plus Jakarta Sans, 600-700, hiérarchie nette
- **Corps** : Inter, 400-500
- **Pas de serif**, pas de fonte décorative
- **Tailles mobile** :
  - H1 : 28-32px
  - H2 : 22-24px
  - H3 : 18-20px
  - Body : 16px
  - Small : 14px
- **Line-height** : 1.5 pour le corps, 1.2 pour les titres

---

## 4. Style général

- **Coins arrondis** : `rounded-xl` (12px) par défaut, `rounded-2xl` pour les cards d'accent
- **Ombres** : très subtiles, `shadow-sm` à `shadow-md` max
- **Pas de glassmorphism**, pas de gradient agressif
- **Espacement** : généreux, padding minimum 16px sur mobile
- **Boutons** : minimum 44px de hauteur (touch target iOS)
- **Inputs** : grands, lisibles, focus ring visible en `teal-500`

---

## 5. Workflow Claude Design

1. Coller **le prompt initial (section 6)** comme premier message dans une nouvelle conversation Claude Design.
2. Une fois le design system validé visuellement, enchaîner avec **le prompt écran 1 (section 7)**.
3. Itérer sur cet écran jusqu'à validation (mesures, photos, ergonomie tactile).
4. Lancer en parallèle **le prompt écran 2 (section 8)** dans une autre conversation (ou la même si Claude Design le permet).
5. Suite des écrans (section 9) une fois ces deux références fixées.

---

## 6. Prompt initial Claude Design

```
Je crée une application web pour Dlo Azur Piscines, une entreprise martiniquaise
d'entretien de piscines. "Dlo" signifie "eau" en créole martiniquais.

Identité visuelle souhaitée :
- Inspiration : eau de lagon, Caraïbes, fraîcheur, lumière. Évite les clichés
  (palmiers, cocotiers, drapeaux). On vise pro/moderne, pas tourisme.
- Palette principale :
  • Turquoise lagon (primary) : #14B8A6 (teal-500)
  • Bleu profond (secondary/texte fort) : #0E4D64
  • Vert d'eau clair (accent) : #5EEAD4
  • Sable très clair (background) : #FEFCF7
  • Corail doux (CTA chaud) : #FB923C
  • Gris ardoise (texte courant) : #475569
- Typographie : Plus Jakarta Sans pour les titres, Inter pour le corps.
  Pas de serif. Hiérarchie nette.
- Style : moderne, généreux en espace blanc, coins arrondis (rounded-xl),
  ombres très subtiles. Pas de gradient agressif. Pas de glassmorphism.
- Photos : vraies photos de piscines (eau bleue claire, plein soleil),
  pas d'illustrations vectorielles.
- Mobile-first systématique (iPhone 375px de largeur).

L'app comporte deux univers à designer cohéremment :
1. Une vitrine publique (SEO local Martinique)
2. Une app métier (espace pro + espace client en lecture seule)

Stack technique cible : Laravel + Livewire + Tailwind CSS. Les maquettes
doivent rester implémentables sans framework JS lourd côté front.
```

---

## 7. Prompt écran 1 : saisie d'un passage (mobile, critique)

```
Crée la maquette mobile (iPhone 375x812) de l'écran le plus critique de l'app :
la saisie d'un passage par le professionnel chez un client, sur le terrain.

Contexte d'usage : il est dehors, au soleil, smartphone à une main, parfois
hors réseau. Doit pouvoir saisir un passage complet en moins de 2 minutes.

Structure de l'écran (de haut en bas) :
1. Header compact : nom du client + nom de la piscine + date du jour,
   bouton retour discret.
2. Banner contextuel : visible UNIQUEMENT si hors ligne, dans le style
   "Hors ligne, ta saisie est sauvegardée et sera envoyée au retour du réseau".
   Ton rassurant, pas alarmant. Couleur ambre douce, pas rouge.
3. Section "Mesures de l'eau" : 4 champs numériques en grille 2x2
   (pH, chlore libre, TAC, sel ppm). Steppers +/- de chaque côté du champ
   pour saisie au pouce. Unité affichée discrètement.
4. Section "Actions menées" : liste de checkboxes prédéfinies
   (Nettoyage skimmer, Brossage parois, Aspiration fond, Contrôle filtration,
   Ajustement chimique, Vidange partielle), plus un champ texte libre
   "Autre action".
5. Section "Photos" : zone de capture, grand bouton "+ Ajouter une photo"
   qui ouvre l'appareil photo, vignettes des photos déjà ajoutées avec
   possibilité de supprimer. Indique si la photo est uploadée ou en attente.
6. Section "Notes" : textarea pour le client + textarea pour notes internes.
7. CTA sticky bottom : bouton "Enregistrer le passage" pleine largeur,
   couleur primary (turquoise). Indique discrètement "Sauvegarde automatique"
   si du contenu est en train d'être tapé.

Tout doit être atteignable au pouce. Taille des touches minimum 44px.
Pas de menu burger, pas de tabs. Un seul scroll vertical fluide.
```

---

## 8. Prompt écran 2 : accueil vitrine

```
Crée la maquette de la page d'accueil du site vitrine de Dlo Azur Piscines,
en mobile-first (375px) puis desktop (1280px).

L'objectif : un visiteur (propriétaire de piscine en Martinique) doit
comprendre l'activité en 5 secondes et avoir envie de contacter via WhatsApp.

Structure (mobile, du haut en bas) :
1. Header : logo Dlo Azur + bouton WhatsApp visible (icône + numéro).
2. Hero : grande photo d'une belle piscine martiniquaise (eau turquoise,
   plein jour), titre fort "Votre piscine, claire toute l'année",
   sous-titre court "Entretien, dépannage, conseils. Martinique.",
   CTA principal "Demander un devis" + CTA secondaire "WhatsApp direct".
3. Section services : 4 cards (Entretien régulier, Dépannage rapide,
   Analyse et conseils, Formation autonomie), chacune avec une icône
   ligne fine, un titre, 2 lignes de description.
4. Section réalisations : grille de 3-6 photos de piscines (avant/après ou
   juste après), titre "Quelques piscines récentes".
5. Section confiance : 2-3 témoignages courts en cards, ou note Google
   avec étoiles, ou les deux.
6. Section "Comment ça marche" : 3 étapes simples (1. Vous contactez,
   2. On vient évaluer, 3. Eau claire), avec petits numéros stylisés.
7. CTA final : bandeau plein largeur turquoise, "Une piscine verte ?
   Une question ? Écrivez-nous", gros bouton WhatsApp.
8. Footer : coordonnées, mentions, réseaux, plan du site minimal.

Le ton doit être pro et rassurant, sans superlatif marketing.
```

---

## 9. Écrans suivants à maquetter

Une fois les deux références ci-dessus validées, à enchaîner :

### App métier
- Dashboard pro (mobile + desktop) : derniers passages, clients à recontacter, accès rapide à "Nouveau passage"
- Liste clients (recherche, filtres)
- Fiche client (infos, piscine, historique des passages)
- Historique des passages (vue chronologique avec filtres)
- Espace client portail (lecture seule, mobile-first)
- Détail d'un passage côté client (mesures, photos, notes du pro)
- Auth : login pro + écran magic link client

### Vitrine
- Page services (détail des 4 lignes)
- Page réalisations (galerie complète)
- Blog (liste + article)
- Page contact

### Phase 1a (plus tard, après validation Phase 0)
- Catalogue produits / services
- Vue contrat client
- Génération facture depuis un passage
- Espace client : section factures
- Écran signature électronique

### Phase 2 (plus tard)
- Wizard diagnostic public ("ma piscine est verte")
- Tunnel paiement Stripe (abonnement)
- Espace utilisateur diagnostic premium

---

## 10. Notes pratiques

- Claude Design peut **lire un codebase** pour appliquer un design system. Quand le code Laravel/Tailwind sera initialisé (Phase V), revenir dans Claude Design et lui pointer le repo pour qu'il extraie automatiquement les tokens.
- **Export possible** : PDF, URL partageable, PPTX, ou handoff direct vers Claude Code.
- **Itération** : préférer plusieurs petites itérations sur un même écran plutôt qu'un gros prompt initial. Claude Design répond bien aux ajustements ciblés ("rends le CTA plus discret", "double l'espacement entre les sections").
- **Photos** : Claude Design utilise des photos stock ou générées. Pour les vraies photos de Dlo Azur, les uploader directement dans la conversation et lui demander de les intégrer.
