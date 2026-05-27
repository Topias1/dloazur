# Assets Dlo Azur Piscines (extraction du site Zyro)

**Date d'extraction :** 2026-05-27
**Source :** dloazurpiscines.com
**Total :** 10 fichiers, pleine résolution

## Inventaire

| Fichier | Type | Dimensions | Usage suggéré (Claude Design) |
|---|---|---|---|
| `01-logo-dlo-azur.png` | PNG + transparence | 824×1000 | Logo officiel, header, footer, favicon |
| `02-hero-piscine-accueil.jpg` | JPEG | 2880×2160 | Piscine sombre/coque noire entourée de verdure. Convient pour section "réparation" ou ambiance. **À ne pas mettre en hero d'accueil** (eau pas turquoise) |
| `03-pierre-au-travail.jpg` | JPEG portrait | 1215×2160 | **Photo phare** : Pierre nettoyant une piscine, vue mer/Caraïbes en arrière-plan. Parfaite pour section "à propos" ou "pourquoi nous choisir" |
| `04-enfants-piscine-antilles.png` | PNG | 1024×1024 | Enfants jouant. Section "famille" ou témoignage |
| `05-avant-apres-nettoyage.jpeg` | JPEG portrait | 1440×1908 | **Image forte** : avant (eau verte) / après (eau bleue + flamand rose). Section transformations / "ma piscine est verte" (lien Lot 2) |
| `06-coup-epuisette-piscine.jpg` | JPEG | 1200×800 | Photo générique (probablement stock) d'épuisette dans l'eau. Section services |
| `07-piscine-hors-sol-sainte-anne.jpeg` | JPEG | 3840×2160 (4K) | Piscine hors-sol à Sainte-Anne. Section services / montage hors-sol |
| `08-nettoyage-piscine-sale.jpg` | JPEG portrait | 576×1024 | Nettoyage piscine en mauvais état. Section remise en état |
| `09-nettoyage-entretien-piscine.jpg` | JPEG portrait | 1620×2160 | Nettoyage piscine. Section entretien hebdo |
| `10-montage-piscine-hors-sol.jpg` | JPEG | 1062×720 | Montage piscine hors-sol. Section montage |

## Photo phare pour le hero accueil

Aucune des photos existantes n'est l'idéale "eau turquoise éclatante au soleil" pour un hero d'accueil. Les meilleures candidates sont :
- `03-pierre-au-travail.jpg` (authentique, montre le pro et l'environnement caribéen)
- `05-avant-apres-nettoyage.jpeg` (la moitié droite, recadrée)

Pour un vrai hero "eau turquoise éclatante", il faudra soit une nouvelle photo, soit en chercher en stock photo libre de droits (Unsplash, Pexels), soit demander à Claude Design d'en générer une.

## Point bleu logo vs palette turquoise

Le logo est en **bleu azur saturé** (≈ `#1E90FF`, cohérent avec le nom "Dlo AZUR"). La palette posée dans `2026-05-27-dloazur-design-system.md` part sur du **turquoise lagon** (`#14B8A6`).

Deux options :
1. **Aligner la palette sur le logo** : passer la couleur primary à un bleu azur (`#1E90FF` ou `#0EA5E9` sky-500 Tailwind), garder le turquoise comme accent secondaire.
2. **Recoloriser le logo** : demander à Claude Design de produire une variante du logo en turquoise pour aligner avec la palette.

Mon avis : **option 1** (aligner la palette sur le logo). Le logo dit "azur", le nom dit "Azur", autant respecter ça. Le turquoise reste un excellent accent.

## Workflow Claude Design

1. Uploader le logo (`01-logo-dlo-azur.png`) dans la conversation Claude Design dès le prompt initial : « voici notre logo officiel à intégrer ».
2. Uploader les photos pertinentes au moment de chaque écran (par exemple `03-pierre-au-travail.jpg` pour la section "à propos" de l'accueil).
3. Pour les sections où une vraie photo manque, demander à Claude Design d'utiliser un placeholder ou une photo de banque.
