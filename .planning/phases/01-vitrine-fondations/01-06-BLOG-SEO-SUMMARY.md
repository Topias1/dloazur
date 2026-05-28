---
phase: "01"
plan: "06"
subsystem: "blog"
tags: ["seo", "content-migration", "zyro", "blog", "redirects"]
dependency_graph:
  requires: ["01-06 initial redirect commit (1a22c11)"]
  provides: ["2 Zyro articles live at canonical slugs", "5-redirect map"]
  affects: ["routes/web.php", "resources/content/blog/", "public/assets/blog/", "ZyroRedirectTest"]
tech_stack:
  added: []
  patterns: ["Markdown content file with YAML frontmatter", "Static cover images in public/"]
key_files:
  created:
    - resources/content/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines.md
    - resources/content/blog/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique.md
    - public/assets/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines.jpg
    - public/assets/blog/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique.jpg
  modified:
    - routes/web.php
    - tests/Feature/ZyroRedirectTest.php
    - .planning/phases/01-vitrine-fondations/ZYRO-URL-INVENTORY.md
    - .planning/phases/01-vitrine-fondations/CUTOVER.md
decisions:
  - "Option 1 (SEO recovery) retenue immédiatement — articles Zyro portés en Markdown, slugs préservés"
  - "Couvertures: même image blog-piscine-rectangle-caraibes pour les 2 articles (seule image disponible couvrant les 2)"
  - "Date publiée: 2025-01-01 — aucune balise article:published_time ni datetime dans le HTML Zyro"
metrics:
  duration: "~25 minutes"
  completed: "2026-05-28"
  tasks_completed: 5
  files_changed: 8
---

# Phase 01 Plan 06: Blog SEO Recovery — Zyro Article Import Summary

**One-liner:** 2 articles Zyro portés en Markdown avec slugs préservés, remplaçant 2 redirects /blog par des pages vivantes avec couvertures téléchargées.

## Articles récupérés

| # | Titre | Slug préservé | Source Zyro | Taille MD |
|---|-------|--------------|-------------|-----------|
| 1 | De la passion à l'entrepreneuriat : l'histoire de Dlo Azur piscines | `de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | `/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines` | 56 lignes |
| 2 | Les 3 étapes indispensables pour un entretien de piscine parfait en Martinique | `les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique` | `/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique` | 84 lignes |

Structure HTML Zyro : article 1 utilisait `<section id="article">`, article 2 utilisait `.block-blog-header` + sections contenu. Extraction PHP DOMDocument custom écrite pour gérer les deux patterns.

## Couvertures

| Article | Fichier | Source | Taille |
|---------|---------|--------|--------|
| Article 1 | `public/assets/blog/de-la-passion-a-lentrepreneuriat-lhistoire-de-dlo-azur-piscines.jpg` | assets.zyrosite.com (1920w) | 645 KB |
| Article 2 | `public/assets/blog/les-3-etapes-indispensables-pour-un-entretien-de-piscine-parfait-en-martinique.jpg` | assets.zyrosite.com (1440x756) | 237 KB |

Les deux articles partagent la même image de fond Zyro (`blog-piscine-rectangle-caraibes`). Aucune OG image distincte dans le HTML.

## Route change

| Avant | Après |
|-------|-------|
| 7 redirects 301 (dont 2 vers `/blog` pour articles) | 5 redirects 301 |
| `/de-la-passion-…-piscines` → `/blog` (301) | **Servi par `BlogController@show`** |
| `/les-3-etapes-…` → `/blog` (301) | **Servi par `BlogController@show`** |
| `/de-la-passion-…-piscine` (typo) → `/blog` (301) | `/de-la-passion-…-piscine` → `/blog/de-la-passion-…-piscines` (301) |

## Tests

| Avant | Après |
|-------|-------|
| `zyro_redirects` dataset : 7 entrées | 5 entrées (typo variant target mis à jour) |
| — | `it('recovered Zyro article — Pierre histoire — responds 200')` |
| — | `it('recovered Zyro article — 3 étapes entretien — responds 200')` |

Suite complète : **160 tests, 159 passed, 1 skipped** (le skip préexistant est non lié).

Vérification effectuée en appliquant temporairement les changements sur le dépôt principal (le worktree n'a pas de répertoire `vendor/` propre).

## Commits

| Hash | Message |
|------|---------|
| `d27d1b0` | feat(01-06): import 2 Zyro blog articles to resources/content/blog with preserved slugs (SEO recovery) |
| `90f3328` | feat(01-06): download cover images for recovered Zyro articles to public/assets/blog |
| `45a77af` | refactor(01-06): drop redundant article redirects, route typo variant to canonical article slug |
| `35de23a` | test(01-06): update ZyroRedirectTest dataset + add recovered-article smoke tests |
| `1da0b4f` | docs(01-06): update ZYRO-URL-INVENTORY + CUTOVER for SEO recovery strategy |

## Différé (non bloquant)

- **OG meta par-article** : les articles héritent des balises OG site-wide. À améliorer dans un plan ultérieur (ajouter `og:image` = cover dans `show.blade.php`).
- **Date de publication précise** : Zyro ne publie pas `article:published_time` ni `<time datetime>` dans le HTML des articles. Utilisé `2025-01-01` comme fallback — peu d'impact SEO pour des articles qui ne changent pas.
- **Images inline** : le script d'extraction PHP conserve les URLs zyrosite.com dans le Markdown (images dans le corps). À migrer vers `public/assets/blog/` si Zyro coupe son CDN après le DNS switch.
- **Images responsives cover** : les covers sont servis en taille fixe. À optimiser avec `srcset` quand le layout blog show est enrichi.

## Sécurité production

- Aucun `git push` effectué.
- Aucune communication avec le site live `dloazurpiscines.com` autre que 2 `curl -sL` en lecture seule pour les couvertures.
- Le site Zyro reste intact et opérationnel.

## Self-Check: PASSED

All 4 content/image files present. All 5 commits verified in git log.
