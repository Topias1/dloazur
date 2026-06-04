# Phase 08: Vitrine — corrections retours Pierre — Research

**Researched:** 2026-06-04
**Domain:** Blade views / copy / routing — Laravel 13 vitrine
**Confidence:** HIGH

---

<user_constraints>
## User Constraints (from CONTEXT.md)

### Locked Decisions

**V1 — Géo honnête**
- D-01 : 3e personne — "ma tournée" → "notre zone d'intervention". Plus aucune occurrence de "je/ma/mon" dans le hero.
- D-02 : Communes non nommées dans le hero — les pages zones couvrent ce détail. Hero court > listing exhaustif.
- D-03 : Invitation à appeler conservée — "Un appel suffit pour voir si votre piscine entre dans notre zone."
- D-04 : Purge "toute la Martinique" — `entretien-recurrent.blade.php:105` et `analyse-eau.blade.php:117`.

**V5 — Page Dépannage**
- D-05 : Page légère — hero + pitch urgence + 4 bullets types de pannes + CTA WhatsApp.
- D-06 : CTA principal = WhatsApp direct (`wa.me/596696940054`), pas de renvoi formulaire contact.
- D-07 : Contenu bullets — Claude's discretion (pannes courantes Martinique).
- D-08 : Route `GET /services/depannage` → `vitrine/services/depannage.blade.php`.

**V12/V14 — Consolidation voix de marque**
- D-09 : Fusion philosophie + engagements → section unique "Notre approche". `pierre.blade.php` reste autonome.
- D-10 : Mentions "call-center / standard" ≤2 occurrences tournées positif :
  - `pierre.blade.php` — "vous échangez directement avec celui qui plonge l'épuisette"
  - `final-cta.blade.php` — "Vous parlez directement à Pierre"
  - Les autres (`philosophie`, `engagements`, `services-detail`) : supprimer la formule négative.
- D-11 : Zéro répétition mot-à-mot "interlocuteur unique" entre les sections.

**V6 — Ménage**
- D-12 : `resources/views/vitrine/partials/urgence-eau-verte.blade.php` supprimé.

### Claude's Discretion
- Copy exact de chaque section (tant que D-09/D-10/D-11 sont respectés)
- Wording précis des 4 bullets "pannes" de la page Dépannage
- Structure HTML interne de la section "Notre approche" fusionnée
- Ordre des items dans les sections modifiées

### Deferred Ideas (OUT OF SCOPE)
- V7 avant/après slider drag — différé jusqu'à deux vraies photos de Pierre
- V9 blog SEO — backlog
- SEO depannage — Phase 999.x
- Sitemap lastmod — micro-tâche à confirmer en review
</user_constraints>

<phase_requirements>
## Phase Requirements

| ID | Description | Research Support |
|----|-------------|------------------|
| V1 | Hero en 3e personne, zone honnête, invitation appeler ; purge "toute la Martinique" | hero.blade.php ligne 22 : "ma tournée" + entretien-recurrent:105 + analyse-eau:117 confirmés |
| V5 | Page détail `/services/depannage` légère, CTA WhatsApp | Route pattern dans vitrine.php ; gabarit spa.blade.php |
| V6 | Supprimer partial orphelin urgence-eau-verte.blade.php | ATTENTION : non-orphelin confirmé — voir Pitfall 1 |
| V12 | Réduire mentions "call-center/standard" à ≤2 occurrences positives | Occurrences confirmées dans 5 fichiers — voir audit ci-dessous |
| V14 | Dé-dupliquer "interlocuteur unique" ; fusionner philosophie+engagements | philosophie.blade.php + engagements.blade.php + pierre.blade.php confirmés |
</phase_requirements>

---

## Summary

Phase 8 est un chantier de copy et de structure Blade pur — aucune migration, aucun JS, aucun modèle à toucher. Quatre corrections distinctes sur la vitrine dérivées des retours de Pierre après la démo du 2026-06-03.

La phase touche 10 fichiers existants, en crée 2 (vue + entrée contrôleur) et en supprime 1. L'essentiel du travail est de la réécriture de texte Blade, un ajout de route/contrôleur/vue selon un pattern déjà établi, et la fusion de deux partials en un. Aucune dépendance externe, aucun package npm ou Composer.

Un point critique a été découvert pendant le scan du code : `urgence-eau-verte.blade.php` est classé "orphelin" dans le CONTEXT.md mais reste activement inclus dans `resources/views/vitrine/services.blade.php` ligne 10. La suppression directe casserait la page `/services`. Le plan doit traiter cela — soit retirer l'`@include` en même temps que le fichier, soit ne supprimer que s'il est possible de substituer un CTA équivalent.

**Primary recommendation:** Exécuter les 4 chantiers dans l'ordre : V1 (hero + purge), V5 (page dépannage + route), V12/V14 (fusion + dé-duplication), V6 (suppression partial). Chaque chantier est indépendant et peut aller dans un plan/wave séparé.

---

## Architectural Responsibility Map

| Capability | Primary Tier | Secondary Tier | Rationale |
|------------|-------------|----------------|-----------|
| Réécriture copy hero | Frontend (Blade) | — | Texte statique, pas de logique serveur |
| Purge "toute la Martinique" | Frontend (Blade) | — | Deux lignes de texte dans deux vues |
| Nouvelle page /services/depannage | Frontend (Blade) | API (route + contrôleur) | Vues héritent du layout vitrine via VitrineController |
| Mise à jour carte Dépannage | Frontend (Blade) | — | services-grid + services-detail : changement d'href |
| Fusion philosophie+engagements | Frontend (Blade) | — | Modifier partials + home.blade.php @include |
| Dé-duplication call-center | Frontend (Blade) | — | Réécriture copy dans 5 partials |
| Suppression partial orphelin | Frontend (Blade) + Routes | — | Retirer fichier + retirer @include dans services.blade.php |
| Sitemap `/services/depannage` | API (SitemapController) | — | Ajouter Url::create(route('services.depannage')) |

---

## Standard Stack

### Core (aucun package supplémentaire)

Ce chantier n'installe rien. Le stack existant suffit.

| Outil | Version | Rôle dans la phase |
|-------|---------|---------------------|
| Laravel 13 | 13.x | Routing + controller |
| Blade | (built-in) | Templates vitrine |
| Tailwind CSS 4 @theme | 4.x | Classes utilitaires CSS-first |
| `BreadcrumbSchema` | (existing app support) | JSON-LD breadcrumb sur la page Dépannage |
| `VitrineController` | (existing) | Pattern contrôleur à suivre |
| `SitemapController` | (existing) | Ajouter URL depannage |

**Installation :** aucune.

---

## Package Legitimacy Audit

> Phase sans installation de packages. Section non applicable.

Aucun package installé dans cette phase.

---

## Architecture Patterns

### System Architecture Diagram

```
Browser
  └─ GET /services/depannage
       └─ routes/vitrine.php (cache.headers:vitrine group)
            └─ VitrineController::depannage()
                 └─ view('vitrine.services.depannage', [...SEO vars...])
                      └─ @extends('layouts.app')
                           ├─ hero band (navy-900 bg)
                           ├─ content body (4 bullets pannes + CTA WhatsApp)
                           └─ CTA band (bg-navy-800)

GET / (home)
  └─ home.blade.php
       ├─ @include('vitrine.partials.hero')           ← réécriture V1
       ├─ @include('vitrine.partials.services-grid')  ← lien depannage V5
       ├─ @include('vitrine.partials.philosophie')    ← fusionner → "Notre approche" V12/V14
       ├─ @include('vitrine.partials.pierre')         ← garder call-center positif
       ├─ @include('vitrine.partials.engagements')    ← fusionner → "Notre approche" V12/V14
       └─ @include('vitrine.partials.final-cta')      ← garder call-center positif

GET /services
  └─ services.blade.php
       ├─ @include('vitrine.partials.services-grid')  ← lien depannage V5
       ├─ @include('vitrine.partials.services-detail')← supprimer mention call-center V12
       ├─ @include('vitrine.partials.urgence-eau-verte')← ATTENTION: retirer + supprimer fichier V6
       └─ @include('vitrine.partials.final-cta')
```

### Recommended Project Structure

Aucune nouvelle organisation de dossier nécessaire. La page Dépannage s'insère dans le pattern existant :

```
resources/views/vitrine/services/
├─ entretien-recurrent.blade.php   (existant)
├─ analyse-eau.blade.php           (existant)
├─ spa.blade.php                   (référence gabarit)
├─ eau-verte-urgence.blade.php     (existant)
└─ depannage.blade.php             (CRÉER — Phase 8)

resources/views/vitrine/partials/
├─ hero.blade.php                  (MODIFIER — V1)
├─ philosophie.blade.php           (MODIFIER/FUSIONNER — V12/V14)
├─ engagements.blade.php           (MODIFIER/FUSIONNER — V12/V14)
├─ pierre.blade.php                (MODIFIER — V12)
├─ final-cta.blade.php             (MODIFIER — V12)
├─ services-detail.blade.php       (MODIFIER — V12)
├─ services-grid.blade.php         (MODIFIER — V5 lien)
└─ urgence-eau-verte.blade.php     (SUPPRIMER + retirer @include — V6)
```

### Pattern 1 : Route service (à répliquer pour Dépannage)

```php
// Source : routes/vitrine.php (existant)
Route::middleware('cache.headers:vitrine')->group(function () {
    // ...
    Route::get('/services/depannage', [VitrineController::class, 'depannage'])
         ->name('services.depannage');
});
```

```php
// Source : app/Http/Controllers/VitrineController.php (pattern à suivre)
public function depannage(BreadcrumbSchema $breadcrumb): View
{
    return view('vitrine.services.depannage', [
        'title'            => 'Dépannage piscine en Martinique · Dlo Azur Piscines',
        'description'      => 'Panne de pompe, filtration HS, eau trouble : dépannage rapide en Martinique. Contactez Dlo Azur sur WhatsApp pour une intervention le jour même.',
        'canonical'        => url('/services/depannage'),
        'ogImage'          => asset('assets/brand/og-default.jpg'),
        'breadcrumbJsonLd' => $breadcrumb->toScript([
            ['name' => 'Accueil',    'url' => url('/')],
            ['name' => 'Services',   'url' => url('/services')],
            ['name' => 'Dépannage', 'url' => url('/services/depannage')],
        ]),
    ]);
}
```

### Pattern 2 : Vue service légère (gabarit spa.blade.php)

La page Dépannage suit le gabarit `spa.blade.php` — pas `entretien-recurrent.blade.php` (trop complet). Structure :

1. `@extends('layouts.app')` + `@section('content')`
2. Hero band `bg-navy-900` avec `h1`, pitch, CTA principal WhatsApp + CTA secondaire (optionnel)
3. Breadcrumb `<nav aria-label="Fil d'Ariane">` sous les CTAs (pattern spa.blade.php ligne 26)
4. Section corps `max-w-3xl` avec les 4 bullets pannes
5. CTA band `bg-navy-800` avec WhatsApp + devis

Le CTA principal est WhatsApp (`wa.me/596696940054`) — décision D-06. Pas de formulaire contact.

### Pattern 3 : Fusion philosophie + engagements

`home.blade.php` inclut les deux partials séparément (lignes 15 et 31). La fusion implique :

**Option A (recommandée) :** Réécrire `philosophie.blade.php` pour qu'il contienne la section fusionnée "Notre approche", vider ou supprimer `engagements.blade.php`, mettre à jour `home.blade.php` pour ne garder qu'un seul `@include`.

**Option B :** Créer un nouveau `notre-approche.blade.php`, mettre à jour `home.blade.php`, supprimer les deux anciens partials.

Option A est moins risquée (moins de fichiers à toucher, `engagements.blade.php` n'est inclus que dans `home.blade.php` — vérifié).

### Pattern 4 : WhatApp pré-rempli

Numéro établi dans le projet : `https://wa.me/596696940054`
Présent dans `pierre.blade.php`, `final-cta.blade.php`, `spa.blade.php`. Réutiliser tel quel.

### Anti-Patterns à éviter

- **Ne pas créer de composant Blade** pour la page Dépannage — suivre le pattern vue simple héritant du layout.
- **Ne pas utiliser Livewire** dans la page Dépannage (la page est statique, pas de formulaire).
- **Ne pas réutiliser la structure de `entretien-recurrent.blade.php`** pour la page Dépannage — trop complète, décision D-05 = page légère.
- **Ne pas supprimer `urgence-eau-verte.blade.php` sans retirer l'`@include`** dans `services.blade.php`.

---

## Don't Hand-Roll

| Problème | Ne pas construire | Utiliser plutôt | Pourquoi |
|----------|-------------------|-----------------|----------|
| Breadcrumb JSON-LD | Markup à la main | `BreadcrumbSchema::toScript()` (déjà dans le projet) | Pattern établi sur toutes les pages service |
| Sitemap entry | Logique custom | `Url::create(route('services.depannage'))` dans SitemapController | Pattern existant ligne par ligne |
| CTA WhatsApp | Lien custom | `href="https://wa.me/596696940054"` (pattern réutilisé) | Numéro centralisé, cohérence |

---

## Common Pitfalls

### Pitfall 1 : `urgence-eau-verte.blade.php` n'est PAS orphelin

**Ce qui se passe :** Le CONTEXT.md décrit ce fichier comme "orphelin, plus inclus nulle part depuis Phase 1". C'est inexact.

**Découverte :** `resources/views/vitrine/services.blade.php` ligne 10 contient encore `@include('vitrine.partials.urgence-eau-verte')`. Le fichier est donc activement rendu sur `/services`.

**Comment éviter :** La tâche V6 doit (1) retirer la ligne `@include` dans `services.blade.php` ET (2) supprimer le fichier. Sinon la suppression du fichier seule lève une `InvalidArgumentException` de Blade à l'exécution.

**Signe d'alerte :** Tester `/services` après la suppression du fichier — si 500, l'@include est encore là.

### Pitfall 2 : Cohérence de la carte Dépannage sur deux endroits

**Ce qui se passe :** La carte "Dépannage rapide" existe dans DEUX partials distincts :
- `services-grid.blade.php` ligne 29 — pointe vers `route('services')` aujourd'hui
- `services-detail.blade.php` ligne 203 — bloc "Service client réactif & amical" avec mention "sans standard téléphonique ni rotation d'interlocuteurs"

**Comment éviter :** Mettre à jour `services-grid.blade.php` pour pointer vers `route('services.depannage')` (V5). Traiter `services-detail.blade.php` séparément pour la mention call-center (V12) — les deux tâches sont différentes sur le même fichier.

### Pitfall 3 : `engagements.blade.php` a une mention call-center indirecte

**Ce qui se passe :** La carte "Joignable sur WhatsApp" dans `engagements.blade.php` ligne 25 dit déjà "Vous tombez sur Pierre, **jamais sur un standard**". C'est une formulation négative — à reformuler positif selon D-10.

**Comment éviter :** Lors de la fusion philosophie+engagements, retravailler cette carte pour tourner positif ("Vous parlez directement à Pierre") sans garder "jamais sur un standard".

### Pitfall 4 : `final-cta.blade.php` — la mention call-center est déjà positive

**Ce qui se passe :** `final-cta.blade.php` ligne 11 dit "Vous parlez directement à Pierre, **jamais à un standard**." — c'est l'une des 2 occurrences à **conserver** selon D-10, mais elle porte encore "jamais à un standard" = formulation partiellement négative.

**Décision :** D-10 dit "tourner positif". La reformulation exacte est à la discrétion de Claude (D-07 élargi). Suggestion : "Vous parlez directement à Pierre, sans intermédiaire." — supprime la négation, garde l'idée de proximité.

### Pitfall 5 : `analyse-eau.blade.php:117` — la cible exacte

**Ce qui se passe :** Le CONTEXT.md pointe la ligne 117 de `analyse-eau.blade.php`. La lecture du fichier confirme : ligne 117 = "Réservez une analyse professionnelle : intervention rapide sur **toute la Martinique**." (dans le CTA band, pas dans le corps).

**Comment éviter :** Reformuler uniquement cette phrase dans le CTA band. Le reste du fichier est correct.

### Pitfall 6 : `entretien-recurrent.blade.php:105` — "toute la Martinique" dans le corps

**Ce qui se passe :** Ligne 105 = "Un service client réactif, professionnel et personnalisé. Interventions sur **toute la Martinique** : Fort-de-France, Le Lamentin, Schoelcher, Les Trois-Îlets et communes alentour."

**Comment éviter :** Remplacer "toute la Martinique" par une formulation honnête : "dans notre zone d'intervention" ou "sur le corridor atlantique et caraïbe" puis laisser les pages zones pour le détail.

### Pitfall 7 : home.blade.php — ne pas rompre l'ordre des sections

**Ce qui se passe :** `home.blade.php` a 11 `@include` dans un ordre documenté ("Section order per UI-SPEC §Page Structure — non-negotiable"). La fusion philosophie+engagements modifie 2 lignes de cet ordre.

**Comment éviter :** Remplacer les deux `@include` séparés par un seul `@include('vitrine.partials.notre-approche')` (ou `philosophie` réécrit) en gardant la même position dans le fichier.

---

## Code Examples

### Audit complet des mentions "call-center / standard"

Résultat du scan du code (confirmé visuellement fichier par fichier) :

| Fichier | Ligne | Formulation actuelle | Action D-10 |
|---------|-------|----------------------|-------------|
| `partials/pierre.blade.php` | 19 | "Pas de centre d'appel, pas de sous-traitance : vous échangez directement avec celui qui plonge l'épuisette." | **CONSERVER** — retirer "Pas de centre d'appel," → garder "vous échangez directement avec celui qui plonge l'épuisette." |
| `partials/final-cta.blade.php` | 11 | "Vous parlez directement à Pierre, jamais à un standard." | **CONSERVER** — reformuler positif : "Vous parlez directement à Pierre, sans intermédiaire." |
| `partials/engagements.blade.php` | 25 | "Vous tombez sur Pierre, jamais sur un standard." | **SUPPRIMER** la négation → "Vous parlez directement à Pierre." (dans section fusionnée) |
| `partials/philosophie.blade.php` | 24 | "Un seul interlocuteur qui connaît votre bassin, pas de sous-traitance" | **FUSIONNER** dans "Notre approche", reformuler sans négation : "Pierre connaît votre bassin et l'entretient lui-même." |
| `partials/services-detail.blade.php` | 203 | "sans standard téléphonique ni rotation d'interlocuteurs" | **SUPPRIMER** → remplacer par argument positif orthogonal |

Total après correction : 2 occurrences (pierre + final-cta), conformes à D-10.

### Mentions "interlocuteur unique" à dé-dupliquer

| Fichier | Formulation |
|---------|-------------|
| `partials/pierre.blade.php` ligne 17 | "Un seul interlocuteur, qui connaît votre bassin" (callout visuel) |
| `partials/pierre.blade.php` ligne 21 | "Un seul interlocuteur,\nqui connaît votre bassin." (display pull-quote) |
| `partials/philosophie.blade.php` ligne 24 | "Un seul interlocuteur qui connaît votre bassin, pas de sous-traitance" |
| `partials/engagements.blade.php` ligne 44 | "Toujours le même interlocuteur" (titre carte) |

Action : La section fusionnée "Notre approche" ne doit pas répliquer "interlocuteur unique" — choisir un angle différent (ex. : suivi en ligne, compte-rendu après chaque passage). `pierre.blade.php` garde l'angle biographie + proximité humaine, mais le pull-quote ligne 21 peut être reformulé.

---

## State of the Art

| Ancienne approche | Approche actuelle | Impact |
|-------------------|-------------------|--------|
| 2 partials séparés (philosophie + engagements) | 1 section "Notre approche" fusionnée | Réduit la répétition sémantique, home moins longue |
| Hero 1e personne ("ma tournée") | Hero 3e personne ("notre zone") | Cohérence voix de marque sur tout le site |
| 5 mentions call-center/standard | ≤2 mentions tournées positif | Argument moins défensif, plus convaincant |

---

## Assumptions Log

| # | Claim | Section | Risque si faux |
|---|-------|---------|-----------------|
| A1 | `engagements.blade.php` n'est inclus que dans `home.blade.php` (vérifié grep partiel) | Architecture Patterns | Si inclus ailleurs, la fusion touche d'autres pages — vérifier avec grep complet avant |
| A2 | La page Dépannage peut utiliser `bg-navy-800` pour le CTA band (même pattern que `spa.blade.php`) | Code Examples | Cosmétique uniquement |
| A3 | WhatsApp number `596696940054` est correct et stable | Code Examples | CTA inopérant si numéro changé — vérifier une fois avant commit |

---

## Open Questions

1. **V6 — Que remplace-t-on dans `/services` quand on retire urgence-eau-verte ?**
   - Ce qu'on sait : le partial est encore inclus dans `services.blade.php` et présente un CTA vers `/services/eau-verte-urgence`.
   - Ce qui est flou : faut-il laisser un "trou" visuel dans `/services` ou substituer un micro-CTA vers la nouvelle page Dépannage ?
   - Recommandation : retirer le partial sans substitut — la page `/services` garde déjà `services-grid.blade.php` qui couvre l'eau verte avec sa propre carte colorée. Pas de trou fonctionnel.

2. **Fusion philosophie+engagements — quel angle pour "Notre approche" ?**
   - Ce qu'on sait : D-09 impose la fusion, D-11 interdit "interlocuteur unique" comme angle central.
   - Ce qui est flou : quels 2 angles forts retenir ? (ex. : "suivi en ligne après chaque passage" + "accès direct à Pierre") — Claude's discretion selon CONTEXT.
   - Recommandation : "compte-rendu après chaque passage" (différenciateur mesurable) + "WhatsApp direct, pas d'intermédiaire" (différenciateur humain). Évite toute négation.

---

## Environment Availability

> Phase code/Blade uniquement — pas de dépendances externes au-delà du stack Laravel existant. Section SKIPPED.

---

## Validation Architecture

### Test Framework

| Property | Value |
|----------|-------|
| Framework | Pest PHP 4.7 |
| Config file | `phpunit.xml` / `pest.config.php` |
| Quick run command | `./vendor/bin/pest --filter='VitrinePage\|Sitemap' --stop-on-failure` |
| Full suite command | `./vendor/bin/pest --ci` |

### Phase Requirements → Test Map

| Req ID | Comportement | Type de test | Commande automatisée | Fichier existe ? |
|--------|-------------|--------------|----------------------|-----------------|
| V1 | Hero ne contient plus "ma tournée" ni "toute la Martinique" | Feature / browser-lite | `./vendor/bin/pest --filter='HeroV1' -x` | Non — Wave 0 |
| V5 | GET /services/depannage retourne 200 avec h1 "Dépannage" + lien WhatsApp | Feature HTTP | `./vendor/bin/pest --filter='DepannageRoute' -x` | Non — Wave 0 |
| V5 | Carte Dépannage dans services-grid pointe vers route('services.depannage') | Feature HTML | inclus dans DepannageRoute | — |
| V12 | ≤2 occurrences de "call-center\|standard\|centre d'appel" dans le rendu home | Feature / string assert | `./vendor/bin/pest --filter='CallCenterVoix' -x` | Non — Wave 0 |
| V14 | "Notre approche" section présente sur home, philosophie + engagements fusionnés | Feature HTML | inclus dans CallCenterVoix | — |
| V6 | GET /services retourne 200 (partial supprimé sans 500) | Feature HTTP | `./vendor/bin/pest --filter='ServicesPage' -x` | Existe probablement — vérifier |
| V6 | urgence-eau-verte.blade.php n'existe plus sur disque | Filesystem assert | Manuel en Wave 0 ou pest custom | Non |

### Sampling Rate
- Par tâche commit : `./vendor/bin/pest --filter='DepannageRoute\|HeroV1\|CallCenterVoix\|ServicesPage' --stop-on-failure`
- Par wave merge : `./vendor/bin/pest --ci`
- Phase gate : suite complète verte avant `/gsd:verify-work`

### Wave 0 Gaps

- [ ] `tests/Feature/Vitrine/HeroV1Test.php` — V1 : 3e personne + pas de "toute la Martinique"
- [ ] `tests/Feature/Vitrine/DepannageRouteTest.php` — V5 : route 200, lien WA, breadcrumb JSON-LD
- [ ] `tests/Feature/Vitrine/CallCenterVoixTest.php` — V12/V14 : occurrences call-center ≤2 sur home + présence "Notre approche"
- [ ] Vérifier si `tests/Feature/Vitrine/ServicesPageTest.php` existe — si oui, il couvre déjà la 200 de `/services` et détectera le 500 si V6 est mal appliqué

---

## Security Domain

> Phase Blade/copy uniquement. Pas de nouveaux endpoints qui traitent de l'input utilisateur, pas de logique d'auth modifiée. La route `/services/depannage` est statique GET, couverte par le middleware `cache.headers:vitrine` (pas de session/cookie).

| ASVS Category | Applicable | Contrôle standard |
|---------------|-----------|-------------------|
| V5 Input Validation | Non | Aucun input utilisateur dans la phase |
| V4 Access Control | Non | Routes publiques, pas d'auth |
| V2 Authentication | Non | Pas de changement auth |

Seule vérification pertinente : confirmer que la nouvelle route est dans le groupe `cache.headers:vitrine` (routes statiques publiques) et **pas** dans un groupe auth.

---

## Sources

### Primary (HIGH confidence — lecture directe du code)

- `resources/views/vitrine/partials/hero.blade.php` — occurrence "ma tournée" ligne 22 confirmée
- `resources/views/vitrine/partials/philosophie.blade.php` — structure + occurrences "interlocuteur unique" ligne 24 confirmées
- `resources/views/vitrine/partials/engagements.blade.php` — occurrence "jamais sur un standard" ligne 25 confirmée
- `resources/views/vitrine/partials/pierre.blade.php` — occurrence "Pas de centre d'appel" ligne 19 confirmée
- `resources/views/vitrine/partials/final-cta.blade.php` — occurrence "jamais à un standard" ligne 11 confirmée
- `resources/views/vitrine/partials/services-detail.blade.php` — occurrence "sans standard téléphonique" ligne 203 confirmée
- `resources/views/vitrine/partials/services-grid.blade.php` — carte Dépannage → `route('services')` ligne 29 confirmée
- `resources/views/vitrine/services.blade.php` — `@include('vitrine.partials.urgence-eau-verte')` ligne 10 confirmé (PITFALL 1)
- `resources/views/vitrine/services/entretien-recurrent.blade.php` ligne 105 — "toute la Martinique" confirmé
- `resources/views/vitrine/services/analyse-eau.blade.php` ligne 117 — "toute la Martinique" confirmé
- `routes/vitrine.php` — pattern route dans groupe `cache.headers:vitrine` confirmé
- `app/Http/Controllers/VitrineController.php` — pattern méthode + BreadcrumbSchema confirmé
- `app/Http/Controllers/SitemapController.php` — pattern Url::create(route(...)) confirmé

### Secondary (MEDIUM confidence — CONTEXT.md + feedback Pierre)

- `.planning/phases/08-vitrine-corrections-retours-pierre/08-CONTEXT.md` — décisions D-01 à D-12
- `.planning/feedback/pierre-2026-06-03-reponses.md` — retours V1, V5, V6, V12, V14 + décisions discuss

---

## Metadata

**Confidence breakdown :**
- Fichiers cibles et occurrences exactes : HIGH — lecture directe du code
- Pattern route/contrôleur/vue : HIGH — confirmé sur 4 services existants
- Copy final des sections : LOW — Claude's discretion, non prescrit par la recherche
- Pitfalls : HIGH — découverts par scan du code, pas de training knowledge

**Research date :** 2026-06-04
**Valid until :** Stable — pas de packages, pas de changelog à suivre. Valide tant que la branche `claude/pierre-feedback-website-app-A59QE` n'est pas modifiée en parallèle.
