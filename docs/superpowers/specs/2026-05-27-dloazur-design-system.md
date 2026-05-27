# Design system — Dlo Azur Piscines

**Version :** v3 — **dérivé de la marque réelle** (logo vectoriel + supports imprimés fournis par le client). Remplace la v1/v2 générées sans source de marque.
**Date :** 2026-05-27
**Statut :** design system + maquettes implémentés dans le dépôt (`mockups/`), prêts pour le portage Blade/Livewire.
**Source de vérité :** les fichiers de marque dans `assets/brand/` (logo `logo/`, photos `photos/`, imprimés `print/`, `qr`).

> **Changement majeur vs v1/v2 :** la palette, la typographie et le ton ne sont plus inventés. Ils sont **extraits des vrais supports** : le logo vectoriel (`#0080ff`), la **carte de visite** (fond bleu marine + texte turquoise), le **flyer** et la **plaquette hospitalité**. La carte de visite est la pierre de Rosette de la marque.

---

## 1. Principes

- **Mobile-first systématique** (iPhone 390px de référence).
- **Caraïbes authentiques, pas le cliché.** Mer, soleil et palmiers sont le décor **réel** de Pierre : on les assume. On évite le stock tropical générique (palmiers vectoriels, fausses piscines turquoise de banque d'images).
- **Chaleureux ET premium.** Deux publics : les particuliers **et** le B2B hospitalité (agences, conciergeries, villas de location). Le ton doit rassurer un propriétaire comme une conciergerie de standing.
- **Photos réelles** : Pierre au travail, vraies piscines, vraies villas martiniquaises.
- **Engagé en couleur** : l'azur et le marine portent les grandes surfaces, jamais des bleus délavés.
- **Implémentable en Tailwind** sans framework JS lourd (cohérent Laravel + Livewire + Alpine).

---

## 2. Palette (OKLCH, extraite des supports)

Tous les tokens sont en OKLCH, neutres teintés vers le marine, **jamais `#000` ni `#fff`**. Voir l'implémentation exacte dans `mockups/theme.js` (config Tailwind) et `mockups/app.css` (variables CSS).

| Rôle | Réf. marque | Hex | OKLCH (500/clé) | Usage |
|---|---|---|---|---|
| **Azur** — primary | logo | `#0080ff` | `oklch(0.615 0.211 256)` | Boutons, liens, marque, actions |
| **Marine** — surfaces sombres & encre | fond carte de visite | `#154c79` | `oklch(0.405 0.094 248)` (600) | Héros, bandeaux, footer, texte fort |
| **Lagon** — accent turquoise | texte carte de visite | `#2fb8c8` | `oklch(0.720 0.113 207)` | « Eau vivante », états actifs/synchro |
| **Soleil** — accent chaud | soleil du logo / lumière | — | `oklch(0.760 0.150 72)` | CTA chaud rare, badges avant/après |
| **Sable** — surfaces claires | papier flyer | — | `oklch(0.987 0.005 85)` (50) | Fond général (blanc tiède) |
| **Encre** — texte sur clair | — | — | `oklch(0.255 0.045 250)` (950) | Titres / corps (teinté marine) |
| Succès | — | — | `oklch(0.700 0.150 155)` | Eau saine, synchro OK |
| Alerte (hors-ligne) | — | — | `oklch(0.800 0.130 80)` | Hors-ligne, à vérifier (ambre, **jamais rouge**) |
| Erreur | — | — | `oklch(0.620 0.210 25)` | Erreurs critiques uniquement |

Chaque famille a une rampe complète (50→950 pour azur/marine ; 300→700 pour lagon ; 300→500 soleil). Voir le styleguide rendu : `mockups/styleguide.html`.

**Stratégie couleur (impeccable) : engagée.** Azur + marine occupent 30–60 % de certaines surfaces (héros, section hospitalité, footer, CTA). Lagon et soleil restent des accents délibérés (< 10 %).

---

## 3. Typographie

- **Titres : Fredoka** (500–700). Son arrondi répond directement au mot-logo « AZUR » : chaleur, accessibilité, signature de marque.
- **Corps & app : Inter** (400–700). Lisibilité maximale en plein soleil et à petite taille (saisie terrain).
- **Pas de serif.** La personnalité vient de Fredoka + couleur + photo + motif.
- Échelle (mobile → desktop) : Display 2.6–3.5rem · H1 1.9–2.5rem · H2 1.5rem · H3 1.125–1.25rem · Body 1rem · Small .875rem.
- Contraste d'échelle ≥ 1.25 ; corps plafonné à 68 caractères ; interlignage 1.5 (corps) / 1.05–1.2 (titres).

---

## 4. Style & composants

- **Coins** : `rounded-xl` (14px) par défaut, `rounded-2xl`/`rounded-3xl` pour cards et sections.
- **Ombres** : teintées vers le marine (`--shadow-xs→lg`), jamais grises. Discrètes.
- **Motif vague & goutte** : extrait du logo, en SVG. Séparateurs de section (transition « eau »), puces, badges, bandeaux (`.ripple`).
- **Cibles tactiles** ≥ 44px ; steppers de part et d'autre des champs numériques pour la saisie au pouce ; focus visible en azur.
- **Hors-ligne** : bandeau ambre rassurant (« ta saisie est sauvegardée »), jamais alarmant.
- **Bans** (impeccable) : pas de glassmorphism décoratif, pas de dégradé de texte, pas de bord-rayé latéral, pas de grilles de cartes identiques, pas d'emoji-icône.

---

## 5. Maquettes livrées (`mockups/`)

Ouvrir via un serveur local (les chemins `../assets` exigent HTTP) : `python3 -m http.server` à la racine, puis `http://localhost:8000/mockups/`.

| Fichier | Univers (registre) | Contenu |
|---|---|---|
| `index.html` | — | Galerie de toutes les maquettes |
| `styleguide.html` | fondations | Tokens, palette, typo, motif, composants |
| `vitrine.html` | **brand** (public) | Accueil : héros, services (layout asymétrique), « comment ça marche », **offre hospitalité B2B**, réalisations, Pierre, teaser espace client, témoignages, CTA, footer + QR |
| `passage.html` | **product** (terrain) | Saisie d'un passage offline-first : header, bandeau hors-ligne, mesures 2×2 avec steppers, actions, photos (envoyée/en attente), notes, barre d'enregistrement collante |
| `dashboard.html` | **product** (pro) | Tableau de bord : tournée du jour, à recontacter, stats, derniers comptes-rendus. Sidebar desktop / bottom-nav mobile |
| `portail.html` | **product** (client) | Espace client lecture seule : piscine, dernier passage (mesures, mot de Pierre, photos), historique, lien sécurisé sans mot de passe |

Implémentation : Tailwind via CDN + `theme.js` (config) + `app.css` (tokens/motif/base). Aucune étape de build ; transposable tel quel en Blade.

---

## 6. Photographie & assets

- **Logo** : `assets/brand/logo/logo.svg` (vectoriel, source de vérité) ; `logo.png`, `logo-on-navy.png`, `logo-icon.png`. Mono-couleur azur — visible sur clair comme sur marine.
- **QR** : `assets/brand/qr.svg` / `qr.png` (vers les coordonnées).
- **Photos** (`assets/brand/photos/`, optimisées) : `hero-pierre-piscine` (héros), `pierre-portrait` (à propos), `entretien-dos-logo`, `villa-hospitality` (B2B), `piscine-hors-sol`, `montage-hors-sol`, `balai-detail`, `avant-apres`, `piscine-propre`, `test-bandelette`.
- **Imprimés de référence** (`assets/brand/print/`) : `flyer.png`, `carte-visite.pdf`, `plaquette-hospitality.pdf`.
- Traitement photo unifié léger (`.photo-grade`) pour homogénéiser des sources variées. Héros : superposition dégradé marine pour la lisibilité du texte.

---

## 7. Positionnement (rappel produit)

- **Opérateur** : Pierre ADAM, pisciniste solo. Tél `0696 94 00 54` · `contact@dloazurpiscines.com` · WhatsApp `wa.me/596696940054`.
- **Deux publics** : particuliers (entretien, dépannage, « ma piscine est verte ») **et** B2B hospitalité (conciergeries, agences de location saisonnière — villas de standing). La section hospitalité de la vitrine reprend l'angle réel de la plaquette.
- **Promesse de l'app** dans la vitrine : chaque passage gardé en mémoire (mesures + photos), consultable côté client → justifie la Phase 0.

---

## 8. Portage Laravel (à venir)

- Reprendre les tokens de `mockups/theme.js` dans `tailwind.config.js` du projet (mêmes valeurs OKLCH).
- `app.css` → fichier CSS d'app (fonts, variables, `.ripple`, motif). Les fonts via `@fontsource` ou Google Fonts.
- Les écrans `vitrine`/`dashboard`/`portail` → Blade + Livewire. **`passage` reste hors Livewire** (Alpine + IndexedDB + Service Worker), conformément à la contrainte offline-first.
- Icônes : jeux SVG inline (style Lucide) déjà présents dans les maquettes ; WhatsApp = glyphe officiel.

---

*Maquettes et tokens produits avec le skill `impeccable`, à partir des supports de marque réels. Itérer écran par écran dans le navigateur plutôt qu'en un gros prompt.*
