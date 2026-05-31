---
target: "vitrine staging (post-changements hero+tel:)"
total_score: 31
p0_count: 0
p1_count: 1
p2_count: 2
timestamp: 2026-05-29T14-16-28Z
slug: dloazur-main-s8e8er-laravel-cloud
---
## Design Health Score

| # | Heuristic | Score | Key Issue |
|---|-----------|-------|-----------|
| 1 | Visibility of System Status | 3 | Page marketing : peu d'état système ; le formulaire a bien loading/sent |
| 2 | Match System / Real World | 4 | Français naturel, sans jargon, « Nous écrire / Demander un devis » |
| 3 | User Control and Freedom | 3 | Nav claire, menu mobile, navigation libre |
| 4 | Consistency and Standards | 3 | Tokens cohérents ; mais multiplication des points d'entrée WhatsApp |
| 5 | Error Prevention | 3 | Formulaire validé + honeypot + fallback WhatsApp |
| 6 | Recognition Rather Than Recall | 4 | Libellés explicites, aucun jargon technique |
| 7 | Flexibility and Efficiency | 3 | Raccourci WhatsApp partout ; tel: en cours d'ajout |
| 8 | Aesthetic and Minimalist Design | 2 | Hero dense (eyebrow + h1 + sous-titre + 3 puces + 2 CTA) ; crop desktop cliché |
| 9 | Error Recovery | 3 | Erreurs formulaire gérées, repli WhatsApp |
| 10 | Help and Documentation | 3 | Services, how-it-works, philosophie présents |
| **Total** | | **31/40** | **Bon — solide, deux axes hero à corriger** |

## Anti-Patterns Verdict

**Est-ce que ça a l'air généré par IA ? Non.** C'est un design réel, ancré marque : système de tokens OKLCH sur-mesure (azure/navy/lagon/sun/sand), neutres tièdes (jamais #000/#fff), display Fredoka, photos authentiques de Pierre. Aucun des bans transversaux (pas de side-stripe, pas de gradient text, pas de hero-metric template, pas de grille de cartes identiques).

**Scan déterministe :** détecteur lancé sur header + formulaire + partials hero/final-cta/realisations. 7 « em-dash » signalés — **tous dans des commentaires Blade `{{-- --}}`** (lignes 1-5 des partials, lignes 14/175 de `app.blade.php`) : faux positifs, la règle vise la copie visible, pas le code. 3 `backdrop-blur` — header sticky + sheet mobile + bouton « Nous écrire » sur photo : **glass fonctionnel** (sur contenu défilant / photographie), légitime. **Aucun anti-pattern réel.**

**Overlays visuels :** non injectés (cible = URL distante ; preuve via captures desktop/mobile + inspection DOM, pas d'overlay in-page revendiqué).

## Overall Impression

Vitrine soignée et crédible, très loin du builder Zyro qu'elle remplace. Le plus gros levier n'est pas un défaut de code mais une **direction artistique du hero** : sur mobile le crop montre Pierre au travail (parfait, exactement la promesse « authentique pas touristique ») ; sur desktop large le même `object-top` recadre sur **ciel + palmiers**, donc l'écran d'accueil le plus visible tombe pile dans le cliché tropical que PRODUCT.md interdit. Corriger ce crop a plus d'impact que tout le reste.

## What's Working

- **Hero mobile authentique** : Pierre, épuisette, vraie baie martiniquaise. C'est la preuve-pas-le-superlatif, pile la personnalité de marque.
- **Identité visuelle propriétaire** : rampes OKLCH cohérentes, sand tiède en fond, Fredoka en display. Rien de générique SaaS.
- **Conversion solide** : audiences segmentées (particulier / hospitalité), preuve sociale nominative, formulaire de contact réel avec honeypot + repli WhatsApp.

## Priority Issues

- **[P1] Le crop desktop du hero trahit la promesse « authentique »**
  - **Pourquoi :** sur viewport large, `object-cover object-top` sur une photo portrait (1215×2160) affiche ciel + palmiers et masque Pierre/la piscine → lecture « stock tropical », anti-référence #1 de PRODUCT.md.
  - **Fix :** art-direction du cadrage desktop — ajuster `object-position`, ou servir une variante paysage (Pierre + bassin visibles) au-dessus de `sm`, via `<picture>` ou classes responsive.
  - **Commande suggérée :** `adapt`

- **[P2] Densité du hero, aggravée par la nouvelle accroche « plaisir »**
  - **Pourquoi :** l'eyebrow ajoutée (« Parce que votre piscine doit être que du plaisir ») empile un 6e bloc texte au-dessus d'un h1 déjà imposant, et répète « votre piscine ». Le hero contient déjà h1 + sous-titre + 3 puces + 2 CTA.
  - **Fix :** soit fondre le « plaisir » dans le sous-titre plutôt qu'en eyebrow, soit retirer une puce de réassurance ; garder l'eyebrow nettement subordonnée (taille/poids) au h1.
  - **Commande suggérée :** `distill`

- **[P2] Multiplication des points d'entrée WhatsApp**
  - **Pourquoi :** icône header + FAB flottant (mobile) + bouton final-CTA, et bientôt le lien `tel:` header. Sur mobile, deux affordances WhatsApp visibles simultanément diluent la hiérarchie.
  - **Fix :** une hiérarchie explicite — p.ex. FAB OU icône header, pas les deux en même temps ; réserver le final-CTA au formulaire.
  - **Commande suggérée :** `layout`

- **[P3] Encombrement du header mobile à venir (tel: + WhatsApp + burger)**
  - **Pourquoi :** mon ajout `tel:` met une 2e icône à côté de l'icône WhatsApp et du hamburger sur 390px ; combiné au FAB, ça fait beaucoup d'affordances de contact.
  - **Fix :** vérifier le rendu 360-390px une fois déployé ; envisager le `tel:` en icône seule très discrète, ou le réserver au menu mobile.
  - **Commande suggérée :** `adapt`

## Persona Red Flags

**Particulier martiniquais (premier visiteur, mobile, plein soleil)** : arrive sur un hero impeccable sur mobile (Pierre visible) — bon. Mais doit scroller à travers eyebrow + h1 + sous-titre + 3 puces avant les CTA ; deux boutons WhatsApp (header + FAB) peuvent semer le doute sur « lequel ». Réassurance prix absente above-the-fold (volontaire ?).

**Gestionnaire hospitalité (évalue un partenaire fiable, desktop)** : le crop desktop ciel/palmiers donne une première impression « site vitrine générique » avant de voir les preuves (villas suivies, témoignages) plus bas. Le sérieux B2B se mérite au scroll, pas au premier écran.

**Pierre (le pro, dont c'est l'image)** : le hero mobile le montre au travail — fidèle. Mais sur le poste client desktop, sa présence disparaît du premier écran, ce qui contredit le « vous parlez toujours à Pierre ».

## Minor Observations

- Eyebrow `lagon-300` sur overlay navy : contraste AA largement suffisant (à reconfirmer une fois déployé).
- Em-dashes présents dans les **commentaires** Blade : sans impact UI, mais à nettoyer si tu veux un scan détecteur 100 % vert.
- Le `?cb=` n'a rien changé : le déploiement Laravel Cloud de mes deux commits n'était pas encore propagé au moment du run.

## Questions to Consider

- Le hero a-t-il besoin de 6 blocs de texte, ou « eau claire toute l'année + 1 CTA » suffirait-il à porter la promesse ?
- Faut-il une seule porte WhatsApp forte plutôt que trois ?
- Une version paysage du hero (Pierre + bassin) sur desktop changerait-elle la première impression B2B ?
