---
phase: 6
slug: blog-admin-crud
type: UI-SPEC
register: product
status: ready
created: 2026-05-30
design_source: DESIGN.md + .impeccable/design.json
mirrors: app/Livewire/ClientIndex.php + ClientForm.php + resources/views/admin/clients/* + layouts/admin.blade.php + components/admin/sidebar.blade.php
scope_note: >
  Outil métier mono-utilisateur (Pierre, non-dev). Le CRUD admin RÉPLIQUE le pattern Clients
  déjà conçu (layout, sidebar, topbar, cartes blanches ring-navy-900/8, boutons azure-500).
  Ce contrat ne spécifie QUE les 3 surfaces nouvelles : liste articles avec statut,
  formulaire éditeur (EasyMDE + cover + slug + toggle statut), action destructive dépublier.
  Tout le reste : copier Clients à l'identique. Pragmatique, zéro pixel-polish superflu.
---

# Phase 6 — UI-SPEC : Blog admin CRUD (`/admin/blog`)

## Register & North Star

**Register : product** (le design SERT la tâche). Test de slop produit : Pierre, non-dev, doit publier un article sans hésiter sur un seul contrôle. La familiarité est une feature — l'interface réplique le CRUD Clients qu'il connaît déjà. North star projet « l'artisan du lagon » : franc, rassurant, à taille humaine. Vocabulaire de Pierre en FR partout (« brouillon », « publié », « dépublier »).

**Thème : clair** (cohérent avec tout l'admin ; fond `sand-50`, encre `ink-950`). Pas de dark — l'admin partage la lisibilité plein-soleil du terrain.

**Stratégie couleur : Restrained.** Neutres tièdes (sand/ink) + accent azur `azure-500` pour les actions primaires et la sélection. Sémantique d'état réservée : `success` (publié), `ink-500`/`sand-100` (brouillon), `warn` (confirmation dépublier), `danger` (erreur seule). Le turquoise/soleil ne servent PAS ici (réservés aux moments vivants de la vitrine/portail).

---

## Surface 1 — Liste des articles `/admin/blog`

Réplique exacte de `client-index.blade.php` (composant `livewire:post-index`, `WithPagination`, recherche `wire:model.live.debounce.300ms`). Conteneur `<div class="px-5 sm:px-8 py-7 space-y-7">`.

### Header
- Titre `<h1 class="font-display font-semibold text-2xl sm:text-3xl text-ink-950">Blog</h1>`.
- Bouton primaire à droite, identique au « Nouveau client » : `h-11 px-5 rounded-xl bg-azure-500 text-white font-bold shadow-sm hover:bg-azure-600` + icône plus (path `M12 5v14M5 12h14`), label **« Nouvel article »**. Lien → `route('admin.blog.create')`.

### Recherche (optionnelle, garder si >10 articles)
- Identique au champ recherche Clients : `wire:model.live.debounce.300ms="search"`, placeholder **« Rechercher un article… »**, icône loupe `ink-400`. Recherche sur titre. Filtre sur `title` (ILIKE pgsql).

### Liste — cartes article (reverse-chronological, `status` puis `date desc`)
Carte = pattern carte Clients (`rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-4 flex items-center gap-4 hover:bg-sand-50 min-h-[64px]`), cliquable → `route('admin.blog.edit', $post)`.

Composition gauche→droite :
1. **Vignette cover** (à la place de l'avatar initiales) : `h-11 w-14 rounded-lg object-cover ring-1 ring-navy-900/8 shrink-0`, source `$post->getFirstMediaUrl('cover','thumbnail')`. Fallback sans cover : bloc `bg-azure-50` avec `<x-icon.drop>` azur centré (jamais une fausse image).
2. **Bloc texte** (`flex-1 min-w-0`) :
   - Titre `<p class="font-semibold text-ink-900 truncate">{{ $post->title }}</p>`
   - Méta `<p class="text-sm text-ink-500 truncate">` : date d'édition formatée `d/m/Y` (Inter `tabular-nums`) + ` · ` + temps de lecture si dispo.
3. **Badge statut** (voir Composant § Badge statut) — `shrink-0`, masqué `hidden sm:inline-flex` si l'espace manque (le badge reste visible au moins en ligne mobile sous le titre — choix planner).
4. **Chevron** `ink-400` (path `m9 18 6-6-6-6`), identique Clients.

### États
- **Vide (aucun article)** : carte `rounded-2xl bg-sand-50 ring-1 ring-sand-200 p-8 text-center` —
  - `<h2 class="font-display font-semibold text-xl text-ink-950">Aucun article pour l'instant.</h2>`
  - `<p class="text-ink-500 mt-2">Écrivez votre premier article : il apparaîtra sur le blog une fois publié.</p>`
  - Bouton **« Écrire un article »** (azure-500, h-11) → create.
- **Aucun résultat de recherche** : `rounded-2xl bg-white ring-1 ring-sand-200 p-6 text-center`, « Aucun résultat pour « {search} ». »
- **Pagination** : `{{ $posts->links() }}` si `hasPages()`, identique Clients.

### Sidebar — entrée « Blog »
Ajouter dans `components/admin/sidebar.blade.php` un item **actif** (pas grisé), entre Passages et les items futurs grisés, suivant le pattern `@class` exact :
```
'flex items-center gap-3 h-11 px-3 rounded-xl transition-colors',
'bg-white/10 text-white'            => request()->routeIs('admin.blog.*'),
'hover:bg-white/8 hover:text-white' => !request()->routeIs('admin.blog.*'),
```
+ `aria-current="page"` quand actif. Icône : feather « file-text » (`<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6"/><path d="M16 13H8M16 17H8M10 9H8"/>`), stroke 2, 20×20. Label **« Blog »**. Idem `mobile-bottom-nav` si la place le permet, sinon laisser au menu desktop (Pierre publie depuis un poste, pas sur le terrain).

---

## Surface 2 — Formulaire créer / éditer

Réplique `admin/clients/edit.blade.php` : conteneur `px-5 sm:px-8 py-7 max-w-2xl space-y-6`, header avec bouton retour (`h-10 w-10 rounded-xl bg-white ring-1 ring-sand-200`, path `m15 18-6-6 6-6`) + titre `font-display font-semibold text-xl text-ink-950` (« Nouvel article » ou le titre du post). Composant `livewire:post-form`. Les champs vivent dans UNE carte `rounded-2xl bg-white ring-1 ring-navy-900/8 shadow-xs p-6`, titre interne `font-display font-semibold text-base text-ink-900 mb-5` = **« Article »**.

Vocabulaire de champ commun (mirror ClientForm) : label `text-sm font-medium text-ink-700 mb-1.5`, input `h-12 w-full rounded-xl bg-sand-50 ring-1 ring-sand-200 px-4 focus:ring-2 focus:ring-azure-500 outline-none`, erreur `text-danger text-sm mt-1` sous le champ (`@error`). Espacement vertical `space-y-5`.

### Champ 1 — Titre
- Input texte standard, label **« Titre »**, `wire:model.blur="title"`. `#[Validate('required|string|max:160')]`.

### Champ 2 — Slug (dérivé, conditionnel)
- **Tant que `status=draft`** : champ éditable, label **« Adresse de l'article (slug) »**, helper `text-xs text-ink-500 mt-1` : « se génère depuis le titre — modifiable tant que c'est un brouillon ». Préfixe visuel non-éditable `dloazurpiscines.com/blog/` en `ink-400` collé à gauche de l'input (span dans un wrapper `flex items-center`).
- **Une fois `status=published`** : champ **verrouillé** — afficher en lecture seule : pastille `rounded-lg bg-sand-100 ring-1 ring-sand-200 px-3 py-2 text-ink-700` avec l'URL complète + petit cadenas `<svg>` `ink-400` + helper « verrouillé après publication (préserve le référencement) ». PAS d'input désactivé grisé qui inviterait au clic.
- Auto-slug `Str::slug` calculé côté serveur ; `wire:model` sur un champ `slug` éditable seulement si `!published`.

### Champ 3 — Éditeur Markdown (EasyMDE) — surface nouvelle clé
- Label **« Contenu »**.
- Conteneur **`wire:ignore`** obligatoire (sinon Livewire morphe le DOM et casse CodeMirror — cf. RESEARCH) :
  ```
  <div wire:ignore class="easymde-wrap" x-data="postEditor(@js($body))" x-init="init($refs.ta)">
      <textarea x-ref="ta">{{ $body }}</textarea>
  </div>
  ```
- Sync : `editor.codemirror.on('change', () => $wire.set('body', editor.value(), false))` (3e arg `false` = pas de re-render réseau à chaque frappe).
- **Scoping CSS Tailwind 4** : importer `easymde/dist/easymde.min.css` dans `app.js`, puis dans `resources/css/app.css` un bloc `@layer components { .easymde-wrap .EasyMDEContainer { … } }` qui re-pose les styles toolbar/preview que le preflight Tailwind v4 neutralise. **Ne jamais désactiver preflight globalement.** Aligner les couleurs de la toolbar sur les tokens : fond toolbar `sand-100`, icônes `ink-700`, bouton actif `azure-600`, bordure CodeMirror `sand-200`, `rounded-xl`. Police de l'éditeur : Inter (corps), pas Fredoka.
- Toolbar minimale et lisible pour un non-dev : **gras, italique, titre, liste, lien, image, aperçu, plein écran** — pas plus (pas de tableaux/HTML brut). `spellChecker:false`, `status:false` ou `['lines','words']` seulement.
- Hauteur min `min-height: 320px` ; le split preview est l'aperçu live de la mise en forme.
- Validation : `#[Validate('required|string')]` sur `body`.

### Champ 4 — Extrait
- `<textarea>` standard 3 lignes, label **« Extrait »**, helper « résumé court affiché dans la liste du blog et les partages ». `wire:model.blur="excerpt"`, `#[Validate('nullable|string|max:300')]`.

### Champ 5 — Image de couverture (cover) — surface nouvelle
- Label **« Image de couverture »**, helper `text-xs text-ink-500` : « format paysage, ~1200×630, sert aussi à l'aperçu sur les réseaux ».
- **Dropzone Livewire** (`wire:model="cover"`), pas un `<input type=file>` nu :
  - Au repos : zone `rounded-xl border-2 border-dashed border-sand-200 bg-sand-50 p-6 text-center hover:border-azure-300 transition-colors cursor-pointer`, icône upload `ink-400` + texte « Glissez une image ou cliquez pour choisir ».
  - **Aperçu après sélection** : remplacer la dropzone par l'image `rounded-xl ring-1 ring-navy-900/8 w-full aspect-[1200/630] object-cover` + bouton texte **« Remplacer »** / **« Retirer »** (`text-sm text-ink-500 hover:text-ink-900`).
  - **Pendant l'upload** : `wire:loading` sur le champ → barre ou overlay `bg-sand-50/80` + spinner discret + « Envoi… » (pas de spinner plein écran). Livewire temp-upload va vers S3 (`LIVEWIRE_TEMPORARY_FILE_UPLOAD_DISK=s3`).
  - Validation : `#[Validate('nullable|image|max:4096')]`.
- Pas de cover → fallback `og-default.jpg` géré côté SEO, ne rien afficher d'alarmant.

### Champ 6 — Statut (toggle brouillon / publié) — surface nouvelle
- **Pas une checkbox brute.** Un **segmented control** à deux états, vocabulaire de Pierre :
  ```
  [ Brouillon ]  [ Publié ]
  ```
  - Wrapper `inline-flex rounded-xl bg-sand-100 ring-1 ring-sand-200 p-1 gap-1`.
  - Segment actif : `bg-white text-ink-900 font-semibold shadow-xs rounded-lg px-4 h-9` ; pour « Publié » actif, le point/texte prend `text-success` + pastille.
  - Segment inactif : `text-ink-500 hover:text-ink-900 px-4 h-9 rounded-lg`.
  - `wire:model.live="status"` (la bascule live met aussi à jour le verrouillage du slug en temps réel).
- Helper sous le toggle : si Brouillon → « invisible sur le blog public » (`ink-500`) ; si Publié → « visible immédiatement sur le blog » (`success` texte discret).

### Barre d'action (bas de carte)
- Mirror ClientForm submit : bouton primaire **« Enregistrer »** (`bg-azure-500 h-12 px-6 rounded-xl text-white font-bold hover:bg-azure-600`), `wire:loading.attr="disabled"` + label `wire:loading` « Enregistrement… ».
- Lien secondaire **« Annuler »** (`text-ink-500 hover:text-ink-900`) → retour liste.
- Toast/redirect au succès : `redirect(route('admin.blog.index'), navigate: true)` + flash « Article enregistré » (réutiliser le mécanisme de flash existant Clients).

---

## Surface 3 — Action destructive « Dépublier » (inline confirm)

**Jamais une modale réflexe.** Confirm inline Alpine, cohérent avec le projet (cf. carnet local Phase 5).

Emplacement : sur la carte article de la liste (action discrète) ET/OU en bas du formulaire d'édition d'un article publié.

- Déclencheur : bouton/lien texte **« Dépublier »** `text-sm text-ink-500 hover:text-ink-900` (PAS rouge au repos — dépublier n'est pas une erreur).
- Au clic (`x-data="{ confirming:false }"`), révéler inline (même ligne ou juste dessous, pas d'overlay) :
  - Question `text-sm text-ink-700` : **« Dépublier cet article ? Il disparaîtra du blog public. »**
  - Bouton confirm **« Dépublier »** danger-doux : fond `warn` n'est pas adapté (réservé hors-ligne) → utiliser `bg-danger text-white h-9 px-4 rounded-lg font-semibold` UNIQUEMENT sur le bouton de confirmation, pas au repos.
  - Bouton **« Garder publié »** : `bg-sand-100 text-ink-700 ring-1 ring-sand-200 h-9 px-4 rounded-lg`.
- Action : passe `status` → `draft`. Conséquence SEO (410 vs 404) gérée côté contrôleur, transparente pour Pierre.
- **Pas de suppression dure** dans cette phase (dépublier = brouillon ; les 3 .md restent en backup, D-06). Ne pas exposer de « Supprimer définitivement ».

---

## Composant — Badge statut (réutilisable)

Pastille `chip` (jamais bordure latérale), Inter 600, `rounded-full px-2.5 py-0.5 text-xs inline-flex items-center gap-1.5` :

| Statut | Fond | Texte | Ring | Pastille |
|--------|------|-------|------|----------|
| **Publié** | `success/10` | `oklch(0.42 0.12 155)` (vert foncé lisible) | `success/30` | point `bg-success` 6px |
| **Brouillon** | `sand-100` | `ink-500` | `sand-200` | point `bg-ink-400` 6px |

Labels exacts : **« Publié »**, **« Brouillon »**. Aucune autre valeur (enum 2 états, D-03).

---

## Tokens utilisés (tous depuis DESIGN.md @theme)

- **Surfaces** : `sand-50` (fond page), `white`/`sand-50` (cartes), `sand-100` (puits, segment inactif, badge brouillon), `navy-900` (sidebar).
- **Encre** : `ink-950` (titres h1), `ink-900` (titres carte, valeurs), `ink-700` (labels, texte fort), `ink-500` (méta, helpers), `ink-400` (icônes, préfixe slug).
- **Accent** : `azure-500` (primaire) / `azure-600` (hover) / `azure-50`+`azure-700` (vignette fallback), `azure-300` (hover dropzone).
- **Sémantique** : `success` (publié), `danger` (confirm dépublier + erreurs), jamais `warn`/turquoise/soleil ici.
- **Rings** : `navy-900/8` (cartes), `sand-200` (champs, segments, dropzone).
- **Rayons** : `rounded-2xl` (cartes liste), `rounded-xl` (boutons, inputs, dropzone), `rounded-lg` (vignette, segments), `rounded-full` (badges).
- **Ombres** : `shadow-xs` au repos uniquement (règle « plat sauf actif »), teintées marine via les tokens existants.
- **Type** : `font-display` (Fredoka) pour h1/h2 titres uniquement ; Inter partout ailleurs ; `tabular-nums` sur dates.

---

## Copywriting (FR — voix de Pierre)

| Élément | Texte |
|---------|-------|
| Titre page liste | Blog |
| CTA création | Nouvel article |
| Recherche | Rechercher un article… |
| Vide | Aucun article pour l'instant. / Écrivez votre premier article : il apparaîtra sur le blog une fois publié. |
| Label titre | Titre |
| Label slug (draft) | Adresse de l'article (slug) — se génère depuis le titre, modifiable tant que c'est un brouillon |
| Slug verrouillé | verrouillé après publication (préserve le référencement) |
| Label contenu | Contenu |
| Label extrait | Extrait — résumé court affiché dans la liste du blog et les partages |
| Label cover | Image de couverture — format paysage, ~1200×630 |
| Dropzone | Glissez une image ou cliquez pour choisir |
| Toggle | Brouillon / Publié |
| Helper brouillon | invisible sur le blog public |
| Helper publié | visible immédiatement sur le blog |
| Enregistrer | Enregistrer (loading : Enregistrement…) |
| Dépublier (déclencheur) | Dépublier |
| Dépublier (confirm) | Dépublier cet article ? Il disparaîtra du blog public. → Dépublier / Garder publié |
| Flash succès | Article enregistré |

Aucun em dash. Tutoiement cohérent avec le reste de l'app (« ta saisie »). Pas de jargon (« publish », « unpublish », « status » → brouillon/publié/dépublier).

---

## Accessibilité

- **WCAG AA** : contraste fort partout. Badge « Publié » : vert foncé `oklch(0.42 0.12 155)` sur `success/10` (≥ 4.5:1), pas le `success` clair sur clair.
- **Cibles ≥ 44px** : boutons `h-11`/`h-12`, segments `h-9` acceptables car desktop souris (Pierre publie au poste), dropzone large.
- **Clavier** : segmented control = `role="radiogroup"` + radios accessibles (flèches), pas deux `<div>` cliquables. Slug verrouillé = vrai `readonly`/texte, annoncé. Focus `:focus-visible` ring 2px `azure-500` offset 2px partout.
- **EasyMDE** : la `<textarea>` sous-jacente garde un `<label for>` ; le mode aperçu reste optionnel (le Markdown brut est éditable au clavier).
- **Upload** : `wire:loading` annoncé `aria-live="polite"` (« Envoi… »).
- `prefers-reduced-motion` : transitions `background-color`/`opacity` 150–250ms, clampées si réduit. Aucune sur layout.

---

## Do / Don't (spécifiques à cette surface)

**Do**
- Répliquer le pattern Clients à l'identique pour tout le squelette (cartes, header, retour, flash, pagination). La cohérence écran-à-écran EST l'affordance.
- Mettre le conteneur EasyMDE en `wire:ignore` et scoper son CSS sous `.easymde-wrap` dans `@layer components`.
- Verrouiller visiblement le slug une fois publié (pastille + cadenas), pas un input grisé.
- Confirmer « Dépublier » inline ; rouge uniquement sur le bouton de confirmation.
- Vignette cover réelle ou fallback `<x-icon.drop>` azur — jamais de fausse image.

**Don't**
- Pas de modale pour dépublier (modale = réflexe paresseux).
- Pas de `warn`/ambre ici (réservé hors-ligne), pas de turquoise/soleil décoratif.
- Pas de bordure latérale colorée sur les cartes article (anti-pattern projet).
- Pas de Fredoka dans les labels/boutons/données — Fredoka = titres uniquement.
- Pas de checkbox brute ni de toggle iOS-style pour le statut : segmented control FR.
- Ne pas désactiver le preflight Tailwind globalement pour faire marcher EasyMDE.
- Pas de « Supprimer définitivement » (hors scope ; dépublier suffit, .md en backup).

---

## Handoff au planner

- Composants Livewire à créer : `PostIndex` (mirror `ClientIndex`, `WithPagination` + `search`), `PostForm` (mirror `ClientForm` : `#[Validate]`, `mount(?int $postId)`, `submit()`, slug-lock conditionnel, `WithFileUploads` pour cover).
- Vues : `resources/views/admin/blog/{index,create,edit}.blade.php` (mirror `admin/clients/*`, thin `@extends('layouts.admin')`), `resources/views/livewire/post-index.blade.php`, `resources/views/livewire/post-form.blade.php`.
- JS : `resources/js/post-editor.js` (Alpine `postEditor` data : init EasyMDE, sync `$wire.set('body', …, false)`), importé + `easymde` CSS dans `app.js`.
- CSS : bloc `@layer components { .easymde-wrap … }` dans `resources/css/app.css`.
- Sidebar : ajouter l'item « Blog » actif dans `components/admin/sidebar.blade.php` (+ mobile-bottom-nav optionnel).
- Le badge statut peut devenir un petit composant Blade `<x-admin.status-badge :status="...">` réutilisé liste + form.
