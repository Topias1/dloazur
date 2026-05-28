---
name: Dlo Azur Piscines
description: Plateforme métier + vitrine du pisciniste de Martinique, ancrée sur la marque réelle (offline-first).
colors:
  azure-50:  "oklch(0.965 0.022 256)"
  azure-100: "oklch(0.930 0.045 256)"
  azure-200: "oklch(0.872 0.085 256)"
  azure-300: "oklch(0.788 0.130 256)"
  azure-400: "oklch(0.702 0.176 256)"
  azure-500: "oklch(0.615 0.211 256)"
  azure-600: "oklch(0.545 0.205 257)"
  azure-700: "oklch(0.470 0.176 256)"
  azure-800: "oklch(0.400 0.140 252)"
  azure-900: "oklch(0.340 0.105 250)"
  azure-950: "oklch(0.262 0.078 252)"
  navy-50:   "oklch(0.955 0.013 246)"
  navy-100:  "oklch(0.908 0.025 246)"
  navy-200:  "oklch(0.820 0.044 247)"
  navy-300:  "oklch(0.708 0.060 247)"
  navy-500:  "oklch(0.470 0.092 248)"
  navy-600:  "oklch(0.405 0.094 248)"
  navy-700:  "oklch(0.345 0.082 249)"
  navy-800:  "oklch(0.288 0.066 250)"
  navy-900:  "oklch(0.232 0.052 251)"
  navy-950:  "oklch(0.182 0.040 252)"
  lagon-300: "oklch(0.852 0.090 202)"
  lagon-500: "oklch(0.720 0.113 207)"
  lagon-600: "oklch(0.620 0.100 209)"
  lagon-700: "oklch(0.520 0.085 211)"
  sun-400:   "oklch(0.825 0.130 80)"
  sun-500:   "oklch(0.760 0.150 72)"
  sand-50:   "oklch(0.987 0.005 85)"
  sand-100:  "oklch(0.967 0.008 84)"
  sand-200:  "oklch(0.928 0.011 80)"
  ink-950:   "oklch(0.255 0.045 250)"
  ink-900:   "oklch(0.310 0.045 250)"
  ink-700:   "oklch(0.445 0.030 250)"
  ink-500:   "oklch(0.585 0.024 250)"
  ink-400:   "oklch(0.690 0.018 250)"
  success:   "oklch(0.700 0.150 155)"
  warn:      "oklch(0.800 0.130 80)"
  warn-bg:   "oklch(0.965 0.045 85)"
  danger:    "oklch(0.620 0.210 25)"
  whatsapp:  "#25D366"
typography:
  display:
    fontFamily: "Fredoka, system-ui, sans-serif"
    fontSize: "clamp(2.6rem, 5vw, 4rem)"
    fontWeight: 700
    lineHeight: 1.05
    letterSpacing: "-0.005em"
  headline:
    fontFamily: "Fredoka, system-ui, sans-serif"
    fontSize: "clamp(1.875rem, 3vw, 2.5rem)"
    fontWeight: 700
    lineHeight: 1.1
    letterSpacing: "-0.005em"
  title:
    fontFamily: "Fredoka, system-ui, sans-serif"
    fontSize: "1.25rem"
    fontWeight: 600
    lineHeight: 1.3
  body:
    fontFamily: "Inter, system-ui, sans-serif"
    fontSize: "1rem"
    fontWeight: 400
    lineHeight: 1.6
  label:
    fontFamily: "Inter, system-ui, sans-serif"
    fontSize: "0.75rem"
    fontWeight: 700
    lineHeight: 1.2
    letterSpacing: "0.18em"
rounded:
  sm: "0.5rem"
  md: "0.875rem"
  lg: "1.25rem"
  xl: "1.75rem"
  full: "9999px"
spacing:
  "1": "0.25rem"
  "2": "0.5rem"
  "3": "0.75rem"
  "4": "1rem"
  "6": "1.5rem"
  "8": "2rem"
  "12": "3rem"
  "13": "3.25rem"
  "16": "4rem"
components:
  button-primary:
    backgroundColor: "{colors.azure-500}"
    textColor: "{colors.sand-50}"
    typography: "{typography.body}"
    rounded: "{rounded.md}"
    padding: "0 1.5rem"
    height: "3.25rem"
  button-primary-hover:
    backgroundColor: "{colors.azure-600}"
  button-dark:
    backgroundColor: "{colors.navy-900}"
    textColor: "{colors.sand-50}"
    rounded: "{rounded.md}"
    padding: "0 1.5rem"
    height: "3.25rem"
  button-ghost:
    backgroundColor: "{colors.sand-50}"
    textColor: "{colors.azure-700}"
    rounded: "{rounded.md}"
    padding: "0 1.5rem"
    height: "3rem"
  button-whatsapp:
    backgroundColor: "{colors.whatsapp}"
    textColor: "{colors.sand-50}"
    rounded: "{rounded.md}"
    padding: "0 1.5rem"
    height: "3.25rem"
  chip-azure:
    backgroundColor: "{colors.azure-50}"
    textColor: "{colors.azure-700}"
    rounded: "{rounded.full}"
    padding: "0.25rem 0.625rem"
  card:
    backgroundColor: "{colors.sand-50}"
    rounded: "{rounded.lg}"
    padding: "1.75rem"
  card-feature-dark:
    backgroundColor: "{colors.navy-800}"
    textColor: "{colors.sand-50}"
    rounded: "{rounded.xl}"
    padding: "1.75rem"
  input:
    backgroundColor: "{colors.sand-50}"
    textColor: "{colors.ink-900}"
    rounded: "{rounded.md}"
    padding: "0 1rem"
    height: "3rem"
  stepper-plus:
    backgroundColor: "{colors.azure-500}"
    textColor: "{colors.sand-50}"
    rounded: "{rounded.md}"
    height: "3rem"
    width: "3.5rem"
  stepper-minus:
    backgroundColor: "{colors.sand-100}"
    textColor: "{colors.ink-700}"
    rounded: "{rounded.md}"
    height: "3rem"
    width: "3.5rem"
  offline-banner:
    backgroundColor: "{colors.warn-bg}"
    textColor: "{colors.ink-900}"
    rounded: "{rounded.lg}"
    padding: "0.875rem"
---

# Design System: Dlo Azur Piscines

## 1. Overview : L'artisan du lagon

**Creative North Star: "L'artisan du lagon"**

Le système porte deux choses à la fois : la main d'un artisan unique (Pierre ADAM, pisciniste solo en Martinique) et la clarté de l'eau qu'il rend possible. Ce n'est pas un décor de carte postale ; c'est un savoir-faire qu'on voit à l'œuvre. La marque parle comme lui : simple, directe, à taille humaine, jamais corporate, jamais clinquante.

Le toucher général est **franc et rassurant** : boutons pleins, cibles généreuses, contrastes nets, beaucoup d'air autour des éléments forts. La page assume des grands aplats colorés (azur, bleu marine) plutôt que des dégradés génériques, et porte des photos réelles plutôt que des illustrations vectorielles. La hiérarchie typographique est portée par une paire délibérée : Fredoka (rondeur qui répond au mot-logo « AZUR ») pour les titres, Inter pour le corps et toute l'UI métier.

Le système rejette explicitement quatre univers, repris de PRODUCT.md : le **stock tropical générique** (palmiers vectoriels, fausses piscines turquoise de banque d'images), le **corporate glacial / bleu SaaS impersonnel**, le **rendu builder bas de gamme** du site Zyro qu'on remplace, et le **surchargé / clinquant** (glassmorphism décoratif, animations gadget).

**Key Characteristics:**
- Engagé en couleur : l'azur du logo (`#0080ff`) et le marine de carte de visite portent les grandes surfaces.
- Typographie arrondie qui répond au logo (Fredoka), corps clinique (Inter).
- Photos réelles de la marque, jamais de stock générique ; mer, soleil et palmiers assumés *parce que c'est le décor réel*.
- Cibles tactiles ≥ 44px partout, lisibilité plein soleil ; thème clair par défaut.
- Hors-ligne dit calmement en **ambre**, jamais en rouge.
- Tout en OKLCH ; aucun `#000` ni `#fff` purs.

## 2. Colors : la palette du lagon martiniquais

Stratégie **engagée** : l'azur du logo et le marine de carte de visite portent 30 à 60 % de certaines surfaces fortes (héros, bandeau hospitalité, footer, CTA). Turquoise et soleil restent des accents rares, délibérés. Tous les tokens sont extraits des supports réels (logo vectoriel, carte de visite imprimée, flyer) et exprimés en OKLCH ; les hex sont indicatifs.

### Primary
- **Azur Logo** (`oklch(0.615 0.211 256)` ≈ `#0080ff`) : la couleur exacte du logo. Boutons d'action principaux, liens, présence de marque. C'est la teinte qu'on retrouve sur la carte de visite, le flyer et la plaquette hospitalité.

### Secondary
- **Marine de Quai** (`oklch(0.405 0.094 248)` ≈ `#154c79`) : le fond de la carte de visite. Surfaces sombres (héros, footer, bandeau hospitalité), encre forte, sidebar de l'app pro. Variantes plus profondes (`navy-800`/`900`/`950`) pour le drenched.

### Tertiary
- **Turquoise du Lagon** (`oklch(0.720 0.113 207)`) : l'« eau vivante ». Accents : états actifs, mot de Pierre dans le portail client, badges « synchronisé », étapes lagon dans le « comment ça marche ».
- **Or de Midi** (`oklch(0.760 0.150 72)`) : le soleil. CTA chauds rares (« Ma piscine est verte ? »), badges avant/après, étoiles d'avis.

### Neutral
- **Sable Tiède** (`oklch(0.987 0.005 85)`) : fond général. Blanc tiède teinté vers le soleil (hue 85), jamais pur. C'est le « papier ».
- **Sable Mat** (`oklch(0.967 0.008 84)` / `oklch(0.928 0.011 80)`) : surfaces secondaires (puits de stepper, fond de pré-input).
- **Encre Marine** (`oklch(0.255 0.045 250)`) : titres et texte fort. Teintée vers le marine de la marque (hue 250), pas neutre.
- **Encre Corps** (`oklch(0.445 0.030 250)`) : corps de texte ; contraste suffisant en plein soleil.
- **Ambre Hors-ligne** (`oklch(0.800 0.130 80)` sur fond `oklch(0.965 0.045 85)`) : bandeau hors-ligne et états d'attente. Calme, jamais alarmant.
- **Vert Eau Saine** (`oklch(0.700 0.150 155)`) : succès, synchro OK, « eau saine ».
- **Rouge Critique** (`oklch(0.620 0.210 25)`) : erreurs critiques uniquement.

### Named Rules

**La règle « rare et délibéré ».** Le turquoise et le soleil ne sont jamais décoratifs : ils signalent quelque chose de vivant (eau active, synchro, lever du jour, avant/après). Si on en met partout, ils ne signifient plus rien.

**La règle de l'engagement.** L'azur et le marine portent les grandes surfaces ; on ne dilue jamais le bleu en pastel pour « adoucir ». Si une section a besoin d'être moins forte, on retombe sur le sable, pas sur un azur délavé.

**La règle anti-rouge pour le hors-ligne.** Le hors-ligne se dit en **ambre** (« ta saisie est sauvegardée »). Le rouge est réservé aux erreurs critiques, et seulement à elles.

## 3. Typography

**Display Font :** Fredoka (Google Fonts, weights 500-700), fallback `system-ui, sans-serif`.
**Body Font :** Inter (Google Fonts, weights 400-700), fallback `system-ui, sans-serif`.
**Label/Mono :** pas de mono dédiée ; les labels et kickers sont Inter 700 + tracking 0.18em uppercase.

**Caractère :** Fredoka apporte la chaleur arrondie qui répond directement au mot-logo « AZUR » (lettres dessinées à la main, terminaisons rondes). Inter porte le corps et toute l'UI métier : lisibilité maximale en plein soleil, à petite taille, sur le terrain. La paire est **délibérée**, à la place de la combinaison Jakarta + Inter qui serait le réflexe « par défaut » des outils IA en 2026.

### Hierarchy
- **Display** (Fredoka 700, `clamp(2.6rem, 5vw, 4rem)`, line-height 1.05) : titre de héros uniquement. Une seule occurrence par page.
- **Headline** (Fredoka 700, `clamp(1.875rem, 3vw, 2.5rem)`, line-height 1.1) : titres de sections principales.
- **Title** (Fredoka 600, 1.125–1.25rem, line-height 1.3) : titres de cartes, sous-sections, items de liste forte.
- **Body** (Inter 400, 1rem, line-height 1.6) : corps de texte ; ligne plafonnée à 65–75ch.
- **Label** (Inter 700, 0.75rem, letter-spacing 0.18em, uppercase) : kickers en `lagon-600` au-dessus des titres ; badges statut.

### Named Rules

**La règle du grand contraste d'échelle.** ≥ 1.25× entre deux niveaux successifs. Pas d'échelle plate ; la hiérarchie se lit au coup d'œil, même au pouce.

**La règle « Fredoka pour les mots, Inter pour les chiffres ».** Toute valeur numérique (pH, chlore, dates, totaux) est en Inter avec `tabular-nums` pour rester alignée en grille. Fredoka sert les mots, pas les colonnes.

## 4. Elevation

Le système est **majoritairement plat**, avec une élévation **subtile et teintée**. Pas de grandes ombres dramatiques : les surfaces s'enlèvent du fond par de l'air (espacement généreux) et un ton tiède (sable vs blanc) plutôt que par du flou. Les ombres existent et marquent les éléments interactifs et les flottants, mais elles sont toujours teintées vers le **marine** (hue 250), jamais grises.

### Shadow Vocabulary
- **shadow-xs** (`0 1px 2px oklch(0.29 0.07 250 / 0.06)`) : présence minimale ; stats du dashboard, micro-élévation.
- **shadow-sm** (`0 1px 2px / 0 4px 12px -6px`, teinte marine 0.10) : cartes au repos (services, témoignages, derniers comptes-rendus).
- **shadow-md** (`0 2px 4px / 0 14px 30px -10px`, teinte marine 0.16) : cartes au hover, bouton flottant WhatsApp mobile, photos détachées (le « 12 villas » qui dépasse).
- **shadow-lg** (`0 4px 8px / 0 30px 60px -16px`, teinte marine 0.24) : éléments véritablement lévitants (téléphone-mockup, modales).

### Named Rules

**La règle « teinté marine, jamais gris ».** Toutes les ombres utilisent une teinte marine (hue ≈ 250) en OKLCH avec opacité. Une ombre grise neutre lit comme un défaut de driver.

**La règle « plat sauf à l'état actif ».** Au repos, on s'appuie sur un ring 1px (souvent `navy-900/8`) plutôt que sur une ombre. L'ombre n'arrive qu'au hover, ou pour des éléments structurellement flottants (téléphone, bouton mobile WhatsApp). On ne décolle pas un élément au repos juste pour faire « moderne ».

## 5. Components

### Buttons
- **Shape :** angles modérément arrondis (`rounded.md` = 0.875rem). Pleins, jamais d'ombre interne, jamais de bordure latérale colorée.
- **Primary (Azur)** : `azure-500` sur texte sable, hauteur 3.25rem, padding horizontal 1.5rem. Hover → `azure-600`. C'est le bouton d'action principal partout (Demander un devis, Enregistrer le passage, Se connecter).
- **Dark (Marine)** : `navy-900` sur texte sable. Le bouton « confiance » secondaire, posé sur fond clair (Devenir partenaire). Hover → `navy-800`.
- **Ghost** : fond sable, texte `azure-700`, ring 1px `azure-200`. Hover → fond `azure-50`. Actions tertiaires.
- **WhatsApp** : `#25D366` exact sur texte sable. Couleur de canal **intouchable**. Glyphe officiel uniquement (chemin Simple Icons).
- **Sun (rare)** : `sun-500` sur encre marine. Réservé aux moments humains/chauds (« Ma piscine est verte ? »). À garder rare.
- **États :** transition `background-color` 200ms `cubic-bezier(0.22, 1, 0.36, 1)` (`ease-out-quint`). **Jamais de transform: scale** au hover : ça décale la mise en page et trahit le « franc et rassurant ».

### Chips
- **Style** : `rounded.full`, fond `azure-50` ou `lagon-500/12`, texte `azure-700` / `lagon-700`. Police Inter 600, 0.875rem.
- **State** : variante « eau saine » → fond `success/10`, ring `success/30`. Variante « hors-ligne / en attente » → fond `warn/15`, ring `warn/30`. Variante « synchronisé » → `lagon-500/12`.

### Cards / Containers
- **Corner Style** : `rounded.lg` (1.25rem) pour cartes standard ; `rounded.xl` (1.75rem) pour cartes-features (hospitalité, espace client teaser, téléphone-mockup).
- **Background** : sable (`sand-50`) sur fond clair. Variante sombre : `navy-800` / `navy-900` (cartes-features, hospitalité, footer).
- **Shadow Strategy** : `shadow-sm` au repos ; `shadow-md` au hover. Voir la règle « plat sauf à l'état actif ».
- **Border** : ring 1px `navy-900/8` sur cartes claires ; ring `white/10` sur cartes sombres. Jamais de bordure latérale colorée comme accent.
- **Internal Padding** : 1.5–1.75rem selon densité.

### Inputs / Fields
- **Style** : fond `sand-50`, ring 1px `sand-200`, `rounded.md`, hauteur 3rem (mobile) à 3.5rem (saisie terrain).
- **Focus** : ring 2px `azure-500`, fond `sand-50`/blanc, offset 2px ; visible au clavier (`:focus-visible`).
- **Error** : ring `danger`, texte d'erreur `danger` sous le champ. **Disabled** : opacité 50%, fond `sand-100`, curseur `not-allowed`.

### Steppers (signature)
La paire `[ − ] [ valeur ] [ + ]` pour la saisie de mesures terrain (pH, chlore, TAC, sel). Le **+** est `azure-500` plein (action principale), le **−** est `sand-100` ring `sand-200` (action neutre, on incrémente plus souvent qu'on décrémente). Taille : 3.5rem × 3rem chacun, > 44px, atteignable au pouce d'une seule main. Le champ central est en Fredoka 700 `tabular-nums` 1.5rem, fond blanc, ring `sand-200`. Espacement entre boutons : 0.375rem.

### Navigation
- **Vitrine (brand)** : barre flottante en haut (`top-3 rounded-2xl`) sur fond `sand-50/85` `backdrop-blur`, ring `navy-900/10`, shadow-sm. Logo + liens + Espace client + WhatsApp. Mobile : compact + bouton flottant WhatsApp `fixed bottom-5 right-5`.
- **App pro** : sidebar verticale `navy-900` sur desktop, bottom-nav `sand-50/95` `backdrop-blur` sur mobile. Item actif : `bg-white/10` + texte blanc (desktop) / `text-azure-600` (mobile).

### Offline Banner (signature)
Bandeau ambré : fond `warn-bg`, ring 1px `oklch(0.85 0.09 82)`, icône wifi-off `oklch(0.5 0.11 72)`, titre `oklch(0.42 0.10 70)`, sous-titre `oklch(0.5 0.08 72)`. Texte rassurant (« Hors ligne — ta saisie est sauvegardée. Elle partira automatiquement au retour du réseau. »). N'apparaît que **hors ligne**, et disparaît sans drama au retour réseau.

## 6. Do's and Don'ts

### Do:
- **Do** utiliser l'azur `#0080ff` exact partout : c'est la couleur du logo, point. Aucune nuance « bleu » ne doit dériver du token.
- **Do** porter l'azur ou le marine sur 30–60 % d'au moins une surface forte par page (héros, bandeau hospitalité, footer).
- **Do** dire le hors-ligne en **ambre** (`warn`) avec un texte rassurant. Voir la règle anti-rouge.
- **Do** garder toute photo en photographie **réelle** : Pierre, vraies villas martiniquaises, vraie mer. Traitement uniforme `.photo-grade` (saturate 1.05, contrast 1.02).
- **Do** caler chaque bouton à hauteur ≥ 3.25rem (52px) ou stepper ≥ 3rem (48px). Cible tactile > 44px partout.
- **Do** teinter chaque ombre vers le marine (hue 250) avec opacité. Si l'ombre est grise, elle est mal.
- **Do** plafonner le corps à 65–75ch ; appliquer `tabular-nums` à toute valeur numérique (mesures, dates, totaux).
- **Do** respecter `prefers-reduced-motion` : transitions clampées à 0.001ms.

### Don't:
- **Don't** retomber dans le **stock tropical générique** : pas de palmiers vectoriels, pas de fausses piscines turquoise de banque d'images, pas de drapeau de la Martinique stylisé en accent. (anti-référence PRODUCT.md)
- **Don't** glisser vers le **corporate glacial / SaaS impersonnel** : pas de dégradés bleu génériques, pas de ton d'entreprise froid, pas d'illustrations vectorielles abstraites « peoples ». (anti-référence PRODUCT.md)
- **Don't** reproduire le **rendu builder bas de gamme** du site Zyro qu'on remplace : couleurs ternes, colonnes égales, photos stock incohérentes. (anti-référence PRODUCT.md)
- **Don't** céder au **surchargé / clinquant** : pas de glassmorphism décoratif, pas d'animations gadget, pas de scroll-jacking, pas de `hover:scale` qui décale la mise en page. (anti-référence PRODUCT.md)
- **Don't** utiliser `#000` ou `#fff` purs nulle part. Tout est teinté vers le marine (hue 250) ou le sable (hue 85). Si vous voyez un gris pur, c'est un bug.
- **Don't** mettre une **bordure latérale colorée** (`border-left: 3px solid …`) sur une carte, un callout ou une alerte. Anti-pattern ; refaire avec un ring complet, un fond teinté, ou rien.
- **Don't** faire du **texte en dégradé** (`background-clip: text` + gradient). Couleur solide toujours ; l'emphase passe par le poids ou la taille.
- **Don't** mettre du rouge sur le hors-ligne. Jamais. Le rouge = erreur critique, point.
- **Don't** dupliquer **Pierre par son nom** en hero/marketing copy. On le nomme dans la section « Le pisciniste » et le footer ; partout ailleurs, on laisse l'humanité passer par les photos et le ton.
- **Don't** générer **deux marques** parce qu'on a deux publics. Le même ton et le même soin servent particuliers et conciergeries — c'est la règle « deux publics, une seule voix » de PRODUCT.md.
