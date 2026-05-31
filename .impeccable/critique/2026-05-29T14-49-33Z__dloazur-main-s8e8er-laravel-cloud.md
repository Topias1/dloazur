---
target: vitrine staging (post-fix P1-P3, deploy 9fafaa1)
total_score: 35
p0_count: 0
p1_count: 0
p2_count: 1
timestamp: 2026-05-29T14-49-33Z
slug: dloazur-main-s8e8er-laravel-cloud
---
## Design Health Score — post-corrections (P1→P3)

| # | Heuristique | Score | Δ | Note |
|---|-----------|-------|---|------|
| 1 | Visibilité de l'état | 3 | | Formulaire loading/sent |
| 2 | Adéquation au réel | 4 | | Français naturel |
| 3 | Contrôle & liberté | 3 | | Nav claire |
| 4 | Cohérence & standards | 4 | +1 | Points d'entrée WhatsApp hiérarchisés (FAB révélé au scroll) |
| 5 | Prévention des erreurs | 3 | | Formulaire validé + honeypot |
| 6 | Reconnaissance vs rappel | 4 | | Libellés explicites |
| 7 | Flexibilité & efficacité | 4 | +1 | Canal écrit (formulaire inline) ET instantané (WhatsApp), tel: desktop |
| 8 | Esthétique & minimalisme | 4 | +2 | Hero authentique (Pierre cadré), hero allégé, bento éditorial |
| 9 | Récupération d'erreur | 3 | | Repli WhatsApp |
| 10 | Aide & documentation | 3 | | Services / how-it-works |
| **Total** | | **35/40** | **+4** | **Très bon** |

## Corrections vérifiées sur le staging (deploy 9fafaa1 live)

- **[P1 résolu] Crop hero desktop** — `sm:object-[center_40%]` : Pierre (chapeau, épuisette), le bassin et la baie sont cadrés ; le plan ciel/palmiers seul a disparu. Confirmé visuellement desktop + mobile.
- **[P2 résolu] Densité hero** — eyebrow retirée, ton « plaisir » fondu dans le sous-titre. Hero à 5 blocs au lieu de 6.
- **[P2 résolu] Multiplication WhatsApp** — FAB mobile révélé seulement après le hero (scrollY>600) ; plus de double WhatsApp au premier écran. Aucun canal supprimé.
- **[P3 résolu] Header mobile** — `tel:` réservé desktop/tablette, basculé dans le menu mobile ; barre mobile = logo + WhatsApp + burger.
- **[Bonus] Final-CTA** — bouton mailto retiré, vrai formulaire de contact inline (composant Livewire) sur carte claire, deux colonnes ; comme l'ancien site mais mieux.
- **[Bonus] Réalisations** — grille reskinnée en bento éditorial (tailles variées, eyebrow, scrim au survol), contenu/photos conservés.

## Reste à surveiller (mineur)

- **[P3] Doublon photo bento** — `piscine-propre.jpg` et `piscine-hors-sol.jpg` sont quasi identiques (même villa hors-sol) ; les deux tuiles de droite se ressemblent. Contenu, pas design → remplacer une des deux par un autre chantier quand Pierre fournit une photo.
- Le ton « plaisir » apparaît maintenant dans le hero ET la section philosophie SEO (légère redite, acceptable).
- Composition hero desktop encore un peu chargée en palmiers à gauche ; un cadrage paysage dédié l'améliorerait si besoin.
