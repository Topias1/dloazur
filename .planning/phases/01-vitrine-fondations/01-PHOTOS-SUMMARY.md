---
phase: "01"
plan: "PHOTOS"
subsystem: "vitrine/assets"
tags: ["photos", "logo", "seo", "assets", "blade"]
dependency_graph:
  requires: ["01-03"]
  provides: ["real-photo-assets", "favicon-svg", "og-meta-wired"]
  affects: ["vitrine-public-pages", "seo-crawlers", "og-preview"]
tech_stack:
  added: []
  patterns: ["asset() helper pour chemins publics", "width/height attrs CLS prevention"]
key_files:
  created:
    - "public/assets/brand/favicon.svg"
    - "public/assets/brand/logo.png"
    - "public/assets/brand/og-default.jpg"
    - "public/assets/brand/photos/hero-pierre-piscine.jpg"
    - "public/assets/brand/photos/pierre-portrait.jpg"
    - "public/assets/brand/photos/entretien-dos-logo.jpg"
    - "public/assets/brand/photos/avant-apres.jpg"
    - "public/assets/brand/photos/piscine-propre.jpg"
    - "public/assets/brand/photos/piscine-hors-sol.jpg"
    - "public/assets/brand/photos/montage-hors-sol.jpg"
    - "public/assets/brand/photos/balai-detail.jpg"
    - "public/assets/brand/photos/villa-hospitality.jpg"
  modified:
    - "resources/views/vitrine/partials/hero.blade.php"
    - "resources/views/vitrine/partials/pierre.blade.php"
    - "resources/views/vitrine/partials/services-grid.blade.php"
    - "resources/views/vitrine/partials/urgence-eau-verte.blade.php"
    - "resources/views/vitrine/partials/realisations-grid.blade.php"
    - "resources/views/vitrine/partials/hospitality.blade.php"
    - "resources/views/vitrine/realisations.blade.php"
    - "resources/views/vitrine/services/eau-verte-urgence.blade.php"
    - "resources/views/components/seo/meta.blade.php"
decisions:
  - "piscine-propre.jpg et piscine-hors-sol.jpg pointent vers la même source (07-piscine-hors-sol-sainte-anne.jpeg) — deux contextes distincts dans les templates"
  - "villa-hospitality.jpg ← 02-hero-piscine-accueil.jpg (piscine sombre/coque noire — meilleure option disponible, pas idéale)"
  - "OG image = portrait 1215×2160 Pierre ADAM (option a — crawlers recadrent), paysage dédié différé"
  - "Logo SVG dans navbar/footer reste x-icon.drop (décision Plan 01-03) — logo.svg utilisé uniquement en favicon"
metrics:
  duration: "~20 min"
  completed: "2026-05-28"
  tasks: 3
  files: 21
---

# Phase 01 Photos : Intégration photos réelles + logo officiel

Photo phare Pierre ADAM + 11 photos de chantier wired dans les partials vitrine, logo SVG officiel en favicon, OG meta pointant vers une vraie photo.

## Ce qui a été fait

### Assets copiés (12 fichiers)

Source : livraison Pierre ADAM, `Downloads/DLO_PHOTO/` (extraction site Zyro, 2026-05-27).

| Fichier cible (`public/assets/brand/`) | Source | Utilisation Blade |
|---|---|---|
| `photos/hero-pierre-piscine.jpg` | `03-pierre-au-travail.jpg` (1215×2160) | `partials/hero.blade.php` — hero accueil |
| `photos/pierre-portrait.jpg` | `03-pierre-au-travail.jpg` (1215×2160) | `partials/pierre.blade.php` — portrait section À propos |
| `photos/entretien-dos-logo.jpg` | `09-nettoyage-entretien-piscine.jpg` (1620×2160) | `partials/pierre.blade.php` (inline) + `partials/services-grid.blade.php` (feature card) |
| `photos/avant-apres.jpg` | `05-avant-apres-nettoyage.jpeg` (1440×1908) | `partials/urgence-eau-verte.blade.php`, `partials/realisations-grid.blade.php`, `realisations.blade.php`, `services/eau-verte-urgence.blade.php` |
| `photos/piscine-propre.jpg` | `07-piscine-hors-sol-sainte-anne.jpeg` (3840×2160) | `partials/urgence-eau-verte.blade.php` (après traitement), `partials/realisations-grid.blade.php`, `realisations.blade.php`, `services/eau-verte-urgence.blade.php` |
| `photos/piscine-hors-sol.jpg` | `07-piscine-hors-sol-sainte-anne.jpeg` (3840×2160) | `partials/realisations-grid.blade.php`, `realisations.blade.php` |
| `photos/montage-hors-sol.jpg` | `10-montage-piscine-hors-sol.jpg` (1062×720) | `partials/realisations-grid.blade.php`, `realisations.blade.php` |
| `photos/balai-detail.jpg` | `06-coup-epuisette-piscine.jpg` (1200×800) | `partials/realisations-grid.blade.php`, `realisations.blade.php` |
| `photos/villa-hospitality.jpg` | `02-hero-piscine-accueil.jpg` (2880×2160) | `partials/hospitality.blade.php` |
| `og-default.jpg` | `03-pierre-au-travail.jpg` (1215×2160) | `components/seo/meta.blade.php` — OG/Twitter image par défaut |
| `favicon.svg` | `01_Logo/Logo vecto.svg` | `components/seo/meta.blade.php` — favicon SVG |
| `logo.png` | `01-logo-dlo-azur.png` (824×1000) | `components/seo/meta.blade.php` — favicon PNG fallback |

### Partials Blade mis à jour (9 fichiers)

- **hero.blade.php** : `src` réel, alt "Pierre ADAM, pisciniste Dlo Azur", `width`/`height` ajoutés, TODO supprimé
- **pierre.blade.php** : portrait + action photo réels, alt "Pierre ADAM" partout, `width`/`height` ajoutés, 2 TODO supprimés
- **services-grid.blade.php** : feature card entretien régulier wired, TODO supprimé
- **urgence-eau-verte.blade.php** : avant/après + après wired, `width`/`height`, 2 TODO supprimés
- **realisations-grid.blade.php** : 5 photos wired, `width`/`height`, 5 TODO supprimés
- **hospitality.blade.php** : photo piscine villa wired, `width`/`height`, TODO supprimé
- **realisations.blade.php** : section "Plus de chantiers" — 6 photos wired, commentaire placeholder supprimé, 6 TODO supprimés
- **eau-verte-urgence.blade.php** : gallery avant-après wired, commentaire "Photo gallery placeholder" supprimé, 2 TODO supprimés
- **components/seo/meta.blade.php** : commentaire placeholder supprimé, ajout `<link rel="icon" type="image/png">` fallback PNG

### Note sur les chemins d'assets

Les templates Plan 01-03 utilisaient `asset('assets/brand/photos/...')` (sous-dossier `brand/`), pas `assets/photos/` comme suggéré dans le process. Choix : conserver les chemins existants pour éviter les modifications de Blade supplémentaires. Les assets ont été copiés dans `public/assets/brand/photos/` pour correspondre.

## Tests

```
./vendor/bin/pest --compact
87 tests passés, 255 assertions — 0 échec
```

Les tests vitrine (rendu des pages, JSON-LD, présence éléments) restent tous verts. Aucun test n'assertait les chemins de photos stubs.

## Nom de l'opérateur

Confirmé : **Pierre ADAM** partout dans les alt-text photo (pas "Bertina").

## Éléments différés

| Item | Raison | Plan cible |
|---|---|---|
| Image OG paysage 1200×630 dédiée | Portrait 1215×2160 utilisé — les crawlers recadrent (option a acceptable) | Plan 01-06 ou future session design |
| Optimisation images (WebP, srcset responsive) | Non requis à ce stade — Laravel Cloud edge gère | Plan infra ou post-launch |
| `photo-enfants-piscine.png` (04) non utilisée | Pas de section famille/témoignage dans les templates actuels | Futur section témoignage |
| `villa-hospitality.jpg` (02-hero) non idéale | Piscine sombre/coque noire, pas turquoise — meilleure option disponible | Pierre à fournir photo villa standing |
| QR code pied de page | Encore TODO dans `layouts/app.blade.php` — hors périmètre photos | Plan 01-06 cutover |

## Commits

| Hash | Message |
|---|---|
| `af856ff` | feat(01): copy real photo+logo assets into public/assets/ (Pierre ADAM bundle) |
| `2ba40a2` | feat(01): wire real photos in vitrine partials (hero, pierre, services, réalisations, avant-après) |
| `92020fb` | feat(01): update OG meta + favicon with official Dlo Azur logo |

## Self-Check: PASSED

- [x] `public/assets/brand/favicon.svg` — présent (101 ko, SVG valide Inkscape)
- [x] `public/assets/brand/photos/hero-pierre-piscine.jpg` — présent (631 ko)
- [x] `public/assets/brand/og-default.jpg` — présent (631 ko)
- [x] Commits af856ff, 2ba40a2, 92020fb — vérifiés dans `git log`
- [x] 87/87 tests verts
- [x] 0 TODO photo restants dans les partials vitrine (TODO QR code dans layouts/app.blade.php hors périmètre)
