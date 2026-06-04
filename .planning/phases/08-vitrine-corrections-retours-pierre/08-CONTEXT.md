# Phase 08: Vitrine — corrections retours Pierre - Context

**Gathered:** 2026-06-04
**Status:** Ready for planning

<domain>
## Phase Boundary

Corrections de contenu (copy + voix de marque) sur la vitrine + création d'une page service Dépannage. Périmètre : Blade views, routes, texte uniquement. Aucun data model, aucun JS, aucune migration.

4 chantiers distincts :
1. **V1 — Géo honnête** : hero en 3e personne + zone honnête + invitation à appeler ; purge "toute la Martinique" des pages service
2. **V5 — Page Dépannage** : nouvelle page `/services/depannage` légère (cohérence des cartes + SEO)
3. **V12/V14 — Voix de marque** : ≤2 mentions "call-center" (positives), fusion philosophie+engagements → "Notre approche"
4. **V6 — Ménage** : suppression du partial orphelin `urgence-eau-verte.blade.php`

**Hors périmètre (décidé) :**
- V7 avant/après slider drag → différé jusqu'à deux vraies photos de Pierre
- Espace client, admin, diagnostic → Phases 7/9/10

</domain>

<decisions>
## Implementation Decisions

### V1 — Hero géo et voix

- **D-01 : 3e personne** — "ma tournée" → "notre zone d'intervention". Plus aucune occurrence de "je/ma/mon" dans le hero.
- **D-02 : Communes non nommées dans le hero** — La vraie couverture (Lorrain↔Vauclin Atlantique / Schoelcher↔Rivière-Salée Caraïbe) n'est *pas* listée dans le hero ; les pages zones (`resources/views/vitrine/zones/`) couvrent ce détail. On diverge ici du SC1 du ROADMAP sur ce point précis (décision Antoine : hero court > listing exhaustif).
- **D-03 : Invitation à appeler conservée** — "Un appel suffit pour voir si votre piscine entre dans notre zone."
- **D-04 : Purge "toute la Martinique"** — `entretien-recurrent.blade.php:105` et `analyse-eau.blade.php:117` → reformuler honnêtement (zone d'intervention, pas "toute l'île").

### V5 — Page Dépannage

- **D-05 : Page légère** — hero + pitch urgence (intervention rapide) + 4 bullets types de pannes + CTA WhatsApp pré-rempli (`wa.me/…`). Pas de structure complète comme entretien-recurrent.
- **D-06 : CTA principal = WhatsApp direct** — cohérent avec l'urgence ; pas de renvoi vers le formulaire contact.
- **D-07 : Contenu bullets** — Claude's discretion : pannes courantes Martinique (pompe bloquée, fuite, eau verte incontrôlable, panne de filtration). Voix marque = 3e personne, vouvoiement côté client.
- **D-08 : Route et vue** — `GET /services/depannage` → `vitrine/services/depannage.blade.php`. La carte "Dépannage" dans `services-grid.blade.php` et `services-detail.blade.php` est mise à jour pour pointer vers cette route.

### V12/V14 — Consolidation voix de marque

- **D-09 : Fusion philosophie + engagements** — Les deux partials sont fusionnés en une seule section **"Notre approche"** (`philosophie.blade.php` ou nouvelle inclusion dans `home.blade.php`). `pierre.blade.php` reste une section autonome (biographie = angle différent).
- **D-10 : Mentions "call-center / standard" ≤2** — Deux occurrences positives conservées :
  - `pierre.blade.php` — rester dans la section biographie, tourner positif ("vous échangez directement avec celui qui plonge l'épuisette")
  - `final-cta.blade.php` — dernière impression avant conversion, tourner positif ("Vous parlez directement à Pierre")
  - Les autres (`philosophie`, `engagements`, `services-detail`) : supprimer la formule négative, remplacer par un argument positif orthogonal.
- **D-11 : Dé-duplication "interlocuteur unique"** — La section fusionnée "Notre approche" porte un seul angle ; `pierre` porte la biographie. Zéro répétition mot-à-mot de "interlocuteur unique" entre les deux.

### V6 — Ménage

- **D-12 : Orphelin supprimé** — `resources/views/vitrine/partials/urgence-eau-verte.blade.php` supprimé (plus inclus nulle part depuis Phase 1 ; Claude's discretion).

### Claude's Discretion

- Copy exact de chaque section (tant que les règles D-09/D-10/D-11 sont respectées)
- Wording précis des 4 bullets "pannes" de la page Dépannage
- Désactiver le faux curseur de l'avant/après si trivial (V7 — hors scope, mais si un `cursor-pointer` sans handler existe, le retirer est acceptable sans ajouter de travail)
- Structure HTML interne de la section "Notre approche" fusionnée
- Ordre des items dans les sections modifiées

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Source de vérité — retours Pierre
- `.planning/feedback/pierre-2026-06-03-reponses.md` — analyse point-par-point + §Décisions discuss (V1, V5, V6, V7, V12, V14)

### Fichiers vitrine à modifier (Phase 8)
- `resources/views/vitrine/partials/hero.blade.php` — réécrire le paragraphe descriptif (3e personne + zone honnête)
- `resources/views/vitrine/services/entretien-recurrent.blade.php` — ligne 105, purger "toute la Martinique"
- `resources/views/vitrine/services/analyse-eau.blade.php` — ligne 117, purger "toute la Martinique"
- `resources/views/vitrine/partials/philosophie.blade.php` — fusionner dans "Notre approche"
- `resources/views/vitrine/partials/engagements.blade.php` — fusionner dans "Notre approche"
- `resources/views/vitrine/partials/pierre.blade.php` — garder mention call-center, tourner positif
- `resources/views/vitrine/partials/final-cta.blade.php` — garder mention call-center, tourner positif
- `resources/views/vitrine/partials/services-detail.blade.php` — supprimer la mention call-center (tourner positif)
- `resources/views/vitrine/partials/services-grid.blade.php` — carte Dépannage → pointer vers `/services/depannage`
- `resources/views/vitrine/partials/urgence-eau-verte.blade.php` — SUPPRIMER

### Fichier à créer
- `resources/views/vitrine/services/depannage.blade.php` — nouvelle page service

### Patterns existants à suivre
- `resources/views/vitrine/services/spa.blade.php` — gabarit page service légère (référence pour depannage)
- `resources/views/vitrine/services/entretien-recurrent.blade.php` — exemple page complète (ne PAS copier la structure — page Dépannage reste légère)
- `routes/web.php` — ajouter `Route::get('/services/depannage', ...)` selon le pattern des autres services

### Contraintes projet
- `CLAUDE.md` — stack Laravel 13 + Tailwind v4 @theme + voix vouvoiement
- `.claude/skills/dloazur-frontend-stack/SKILL.md` — conventions Blade, tokens Tailwind v4
- Design : `PRODUCT.md`, `DESIGN.md` — registre `brand` pour la vitrine

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- Pages zones existantes (`resources/views/vitrine/zones/*.blade.php`) — couvrent déjà le détail géo commune par commune ; le hero n'a pas à les répéter
- Composants `<x-icon.*>`, `<x-picture>` — disponibles pour la page Dépannage
- Lien WhatsApp pré-rempli — motif déjà établi dans `pierre.blade.php` et `final-cta.blade.php` ; réutiliser le numéro et format exact

### Established Patterns
- Toutes les pages service (`spa`, `entretien-recurrent`, `analyse-eau`, `eau-verte-urgence`) héritent du layout vitrine via `@extends('layouts.vitrine')` + `@section('content')`
- Breadcrumb et JSON-LD BreadcrumbSchema présents sur les pages service (voir `999.1-03-PLAN.md`) — à inclure sur la page Dépannage
- `home.blade.php` inclut les partials via `@include('vitrine.partials.xxx')` — la fusion philosophie+engagements implique de modifier soit les partials soit l'include dans home

### Integration Points
- La carte "Dépannage rapide" dans `services-grid.blade.php` et dans `services-detail.blade.php` pointe vers `route('services.index')` — à changer pour pointer vers la nouvelle route `services.depannage`
- Le sitemap doit inclure `/services/depannage` (voir `999.1-06-PLAN.md` — URLs sitemap)

</code_context>

<specifics>
## Specific Ideas

- Le hero final doit passer du couple "nord-atlantique au centre de la Martinique / ma tournée" à quelque chose du type : "Un service à taille humaine dans notre zone d'intervention. Un appel suffit pour voir si votre piscine entre dans notre zone." (3e personne, honnête, court)
- La page Dépannage doit sentir l'urgence sans être alarmiste — titre court, CTA WhatsApp immédiat, bullets directs
- La section "Notre approche" fusionnée doit trancher : pas de liste-promesse générique — un ou deux angles forts (ex. : suivi en ligne + accès direct à Pierre), pas de négations

</specifics>

<deferred>
## Deferred Ideas

- **V7 avant/après slider drag** — différé jusqu'à la livraison de 2 vraies photos avant/après par Pierre. Phase future (hors 8/9/10).
- **V9 blog SEO** — idée de chantiers réels comme contenu blog ; noté pour le backlog.
- **SEO depannage** — si la page Dépannage doit être approfondie pour ranker sur "dépannage piscine Martinique", c'est un chantier Phase 999.x (SEO growth), pas Phase 8.
- **Sitemap lastmod** — si la Phase 8 modifie des URLs existantes, vérifier que `lastmod` est mis à jour ; micro-tâche à confirmer en review.

</deferred>

---

*Phase: 08-vitrine-corrections-retours-pierre*
*Context gathered: 2026-06-04*
