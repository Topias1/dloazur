# Phase 9: espace-client-finitions-retours-pierre - Context

**Gathered:** 2026-06-04
**Status:** Ready for planning

<domain>
## Phase Boundary

Trois finitions ciblées du portail client basées sur les retours Pierre :
1. **client-3** — Réécrire les sous-titres de « Mes documents » pour clarifier le statut teaser (facturation non encore branchée) ; tracer la dépendance Phase 3 dans ROADMAP.md.
2. **client-2** — Ajouter un test de régression Pest complet sur la timeline historique dépliable + corriger les nits a11y (`aria-controls`/`id` reliant bouton et panneau).
3. **client-4** — Retirer `loading="lazy"` du hero bandeau photo au-dessus de la ligne de flottaison (gain LCP réel sur mobile Martinique).

Périmètre : `resources/views/livewire/portail/passage-timeline.blade.php` + nouveau fichier test. Aucune migration, aucun modèle, aucun JS nouveau.

</domain>

<decisions>
## Implementation Decisions

### Mes documents — copy (client-3)

- **D-01 : Réécrire les deux sous-titres** — Remplacer le texte actuel des deux lignes « Mes documents » :
  - « Votre contrat et ses conditions. » → **« Disponible avec la mise en place de la facturation. »**
  - « Vos factures, à télécharger en PDF. » → **« Disponible avec la mise en place de la facturation. »**
  Le badge « Bientôt » reste inchangé sur chaque ligne.
- **D-02 : Dépendance Phase 3 tracée dans ROADMAP.md uniquement** — Pas de mention dans REQUIREMENTS.md. Une note dans la section Phase 9 du ROADMAP suffit pour tracer la dépendance.

### Test de régression historique (client-2)

- **D-03 : Pest Feature test + Livewire** — Pas de browser Playwright. Test en mémoire SQLite via les helpers Livewire/Pest existants.
- **D-04 : Couverture complète du contenu** — Le test vérifie :
  - Structure accordéon : présence de `aria-expanded`, `aria-controls` sur le bouton, `id` correspondant sur le panneau
  - Contenu déplié : valeurs pH, chlore libre, TAC affichées dans le panneau
  - Actions réalisées (si présentes dans le passage seedé)
  - Notes (si présentes)
  - Compteur photos (si présentes)
- **D-05 : Fichier `tests/Feature/PortailTimelineTest.php`** — Nouveau fichier dédié à la timeline (séparé de `PortailAccessTest.php`).

### Nits perf bandeau photo (client-4)

- **D-06 : Intégrer dans cette phase** — Pas différer.
- **D-07 : Retirer `loading="lazy"` du hero bandeau** — Le `<img>` du hero (ligne 35-38 de `passage-timeline.blade.php`) est au-dessus de la ligne de flottaison → `loading="lazy"` nuit au LCP. Le retirer.
- **D-08 : Pas de `<x-picture>` avec srcset webp/avif** — Les photos hero sont des URLs temporaires signées R2 ; la génération de variants webp/avif nécessiterait des conversions spatie/medialibrary non configurées. Hors scope. La seule modification est la suppression du `loading="lazy"`.

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Source de vérité — retours Pierre
- `.planning/feedback/pierre-2026-06-03-reponses.md` — points client-1..4 + §Décisions discuss (décision Antoine client-3 : garder le teaser)

### Fichier principal à modifier
- `resources/views/livewire/portail/passage-timeline.blade.php` — unique vue Livewire du portail client ; contient « Mes documents » (lignes ~310-340), l'accordéon historique (lignes ~204-300), et le hero bandeau photo (lignes ~31-42)

### Tests existants portail — lire avant d'écrire le nouveau
- `tests/Feature/PortailAccessTest.php` — helper d'auth client existant, patterns Livewire à réutiliser
- `tests/Feature/DemoLoginTest.php` — seeders demo utilisés pour les tests portail

### Contraintes projet
- `CLAUDE.md` — stack Laravel 13 + Tailwind v4 @theme + vouvoiement client-facing
- `.claude/skills/dloazur-frontend-stack/SKILL.md` — conventions Blade, tokens Tailwind v4

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- Composant `<x-picture>` — disponible (Phase 999.1) mais NON utilisé ici car URLs R2 signées incompatibles avec srcset statique
- Pattern `aria-expanded` — déjà présent sur le bouton accordéon (ligne 218) ; il manque `aria-controls` (bouton) et `id` (panneau)
- Seeder demo — `tests/Feature/PortailAccessTest.php` crée probablement un client + passages via `DemoSeeder` ou factory ; réutiliser ce pattern pour le nouveau test

### Established Patterns
- L'accordéon Alpine `x-data="{ open: false }"` + `@click="open = !open"` est le pattern en place — ne pas le changer, juste ajouter les attributs a11y
- Les tests portail utilisent `Livewire::actingAs()` (void statique, appelé avant `test()`) — voir mémoire `gsd-worktree-autoloader-poisoning` pour le gotcha `composer dump-autoload` en worktree
- Photo hero : URL générée via `Storage::disk($firstPhoto->disk ?? 'r2')->temporaryUrl(...)` avec try/catch + fallback JPG (`assets/brand/photos/piscine-propre.jpg`)

### Integration Points
- `resources/views/portail/passages.blade.php` — view qui inclut `<livewire:portail.passage-timeline />` ; aucune modification attendue dans cette phase
- ROADMAP.md Phase 9 — ajouter une note de dépendance Phase 3 pour « Mes documents »

</code_context>

<specifics>
## Specific Ideas

- Le wording exact des sous-titres réécris : **« Disponible avec la mise en place de la facturation. »** (même phrase pour Contrat et pour Factures — cohérence).
- Le badge « Bientôt » reste inchangé (style + label).
- L'`id` du panneau accordéon doit être unique par passage — utiliser l'ID du passage (`passage-panel-{{ $p->id }}`), et `aria-controls="passage-panel-{{ $p->id }}"` sur le bouton correspondant.

</specifics>

<deferred>
## Deferred Ideas

- **`<x-picture>` avec srcset webp/avif** — différé jusqu'à la configuration des conversions spatie/medialibrary sur le disque R2. Backlog perf.
- **Slider avant/après (V7 vitrine)** — hors scope, différé jusqu'à 2 vraies photos avant/après de Pierre.

</deferred>

---

*Phase: 09-espace-client-finitions-retours-pierre*
*Context gathered: 2026-06-04*
