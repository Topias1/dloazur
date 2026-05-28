---
phase: 01-vitrine-fondations
plan: "06"
subsystem: vitrine-content
tags: [content-recovery, seo, zyro-migration, cgv, services, philosophie]
one_liner: "Récupération textuelle Zyro → 3 services avec checklists complètes, paragraphes SEO home (Martinique/algues), CGV réelles avec SIRET et correction franchise TVA"

dependency_graph:
  requires: [01-03]
  provides: [SITE-02-content, CGV-legal, SEO-home-dense]
  affects:
    - resources/views/vitrine/partials/services-detail.blade.php
    - resources/views/vitrine/partials/philosophie.blade.php
    - resources/views/vitrine/services.blade.php
    - resources/views/vitrine/home.blade.php
    - resources/views/vitrine/cgv.blade.php
    - tests/Feature/StaticPagesTest.php

tech_stack:
  added: []
  patterns:
    - "Partials atomiques par domaine de contenu (services-detail, philosophie)"
    - "Checklists Zyro converties en ul/li avec x-icon.check et grid 2 colonnes cards"
    - "Astuces Zyro en encart bg-azure-50 border-azure-200/60"

key_files:
  created:
    - resources/views/vitrine/partials/services-detail.blade.php
    - resources/views/vitrine/partials/philosophie.blade.php
  modified:
    - resources/views/vitrine/services.blade.php
    - resources/views/vitrine/home.blade.php
    - resources/views/vitrine/cgv.blade.php
    - tests/Feature/StaticPagesTest.php

decisions:
  - "services-detail.blade.php inséré entre services-grid (résumé visuel) et urgence-eau-verte — pas en remplacement"
  - "philosophie.blade.php inséré entre how-it-works et hospitality pour densifier le middle-funnel"
  - "CGV : TVA 8.5% Zyro remplacée par franchise de base art. 293 B CGI (correction légale critique)"
  - "CGV Zyro était une page 'mentions légales + CGV d'utilisation' mixte — notre CGV garde la structure prestations (plus pertinente) avec les vraies coordonnées Zyro"

metrics:
  duration_minutes: 35
  completed_date: "2026-05-28T13:15:00Z"
  tasks_completed: 4
  files_created: 2
  files_modified: 4
  tests_added: 4
  tests_total_in_file: 10
  tests_passing: 164
  tests_skipped: 1
---

# Plan 01-06 Content Recovery : Récupération contenu textuel Zyro

## Ce qui a été livré

### Avant / Après

| Fichier | Avant | Après |
|---------|-------|-------|
| `partials/services-detail.blade.php` | N'existait pas | Créé — 214 lignes, 3 services complets avec checklists |
| `partials/philosophie.blade.php` | N'existait pas | Créé — 2 blocs SEO denses Martinique |
| `partials/services-grid.blade.php` | 60 lignes, 4 cards courtes | Inchangé (résumé visuel conservé) |
| `views/vitrine/services.blade.php` | 4 inclusions | 5 inclusions (+services-detail) |
| `views/vitrine/home.blade.php` | 11 inclusions | 12 inclusions (+philosophie) |
| `views/vitrine/cgv.blade.php` | 52 lignes, stub 7 sections | 80 lignes, 10 sections juridiques complètes |
| `tests/Feature/StaticPagesTest.php` | 6 tests | 10 tests (+4 content assertions) |

### Mots-clés SEO récupérés

Services (services-detail.blade.php) :
- "climat tropical de la Martinique", "brume de sable", "pluies intenses"
- "piscines les plus oubliées", "bassins limpides", "nettoyage intensif"
- "pH, chlore ou sel, alcalinité, stabilisant", "chloration choc", "anti-algues"
- "backwash (rétrolavage)", "buses de refoulement", "skimmers"
- "piscine en acier, en bois ou autoportante", "filtration tropicale"
- "expertise locale adaptée au climat tropical"

Home philosophie (philosophie.blade.php) :
- "partenaire de confiance en Martinique pour le nettoyage, l'entretien et le traitement"
- "eau limpide et de services personnalisés"
- "votre sérénité est notre priorité"
- "températures élevées et l'humidité peuvent favoriser la prolifération des algues et des bactéries"
- "entretien rigoureux et adapté"
- "solutions professionnelles et sur mesure"

CGV (cgv.blade.php) :
- SIRET 934 053 281 000 10 (DLO AZUR EI, 29 montée du Clapotage, 97231 Le Robert)
- Pierre ADAM — pisciniste indépendant
- Juridiction : tribunaux de Fort-de-France

### Corrections apportées (vs Zyro)

| Correction | Raison |
|------------|--------|
| TVA 8.5% → franchise de base art. 293 B CGI | Pierre ADAM est en franchise TVA (EI en dessous du seuil). Zyro affichait "TVA 8.5% DOM" incorrectement — une erreur légale sur les factures futures. |
| CGV Zyro était une page mixte (mentions légales + CGU + confidentialité) | Restructurée en vraies CGV de prestataire de services |
| Aucune section Médiation dans Zyro | Ajoutée (obligation légale B2C art. L.611-1 Code conso) |
| Aucune section Exécution dans Zyro | Ajoutée (pas de sous-traitance, conditions météo) |

### Contenu Zyro non récupéré (intentionnel)

| Page Zyro | Raison du non-portage |
|-----------|----------------------|
| `/realisations` Zyro | "Coming soon..." — rien à récupérer |
| Blog Zyro | Peu de contenu, traité par l'agent parallèle (blog SEO) |
| Mentions légales Zyro | Déjà dans `mentions-legales.blade.php` avec le nouveau hébergeur |
| Politique confidentialité Zyro | Déjà dans `confidentialite.blade.php` avec info RGPD correcte |

---

## Déviations par rapport au plan

### [Rule 2 - Missing content] CGV Zyro ne contenait pas de vraies CGV commerciales

**Trouvé pendant :** Task 3 — extraction contenu `/tmp/zyro-cgv.html`

**Problème :** La page `/conditions-generales` de Zyro est une page "Mentions légales + CGU + Confidentialité" générique. Elle ne contient pas de vraies CGV de prestataire de services (pas de section Exécution des prestations, pas de Médiation, section Tarifs incorrecte avec TVA 8.5%).

**Fix :** Notre stub CGV existant avait la bonne structure (CGV prestataire). Enrichi avec les vraies coordonnées Zyro (SIRET, adresse, Pierre ADAM), corrigé le régime TVA, ajouté Médiation et section Exécution.

**Correction légale TVA :** Pierre ADAM est entrepreneur individuel sous le régime de franchise en base de TVA (art. 293 B CGI). La TVA 8.5% mentionnée sur Zyro était incorrecte et aurait causé des erreurs sur les factures futures.

---

## Tests ajoutés

```
tests/Feature/StaticPagesTest.php
  ✓ services page contains 3 Zyro service sections recovered (content recovery 01-06)
  ✓ services page contains "Pourquoi nous faire confiance" footer block (content recovery 01-06)
  ✓ cgv page contains real legal sections with Pierre ADAM identity (content recovery 01-06)
  ✓ home page contains Zyro SEO paragraphs "Pourquoi choisir" and "eau parfaite" (content recovery 01-06)
```

Suite complète : **164 tests, 163 passing, 1 skipped, 0 failed** (vérifiée avec les vues portées dans le main repo).

---

## Stubs connus

Aucun. Tout le contenu est câblé directement dans les vues Blade. Pas de données mockées.

---

## Sécurité

Aucun déploiement. Aucun push. Uniquement modifications locales de vues Blade et tests.
`routes/web.php` non touché (autre agent parallèle).

---

## Self-Check: PASSED

| Vérification | Résultat |
|---|---|
| `resources/views/vitrine/partials/services-detail.blade.php` existe | FOUND |
| `resources/views/vitrine/partials/philosophie.blade.php` existe | FOUND |
| `resources/views/vitrine/services.blade.php` inclut services-detail | OK |
| `resources/views/vitrine/home.blade.php` inclut philosophie | OK |
| `resources/views/vitrine/cgv.blade.php` contient SIRET 934 053 281 000 10 | OK |
| `resources/views/vitrine/cgv.blade.php` contient art. 293 B | OK |
| `tests/Feature/StaticPagesTest.php` a 10 tests (était 6) | OK |
| Commit fe683c1 (services-detail) existe | FOUND |
| Commit ed815c2 (philosophie) existe | FOUND |
| Commit 0b07541 (cgv) existe | FOUND |
| Commit b45a97b (tests) existe | FOUND |
| Suite Pest : 164 tests, 163 passed, 1 skipped, 0 failed | PASSED |
| `routes/web.php` non modifié | CONFIRMED |
| Aucun `git push` | CONFIRMED |
