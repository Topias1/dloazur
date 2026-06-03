# Phase 07: Espace admin — agenda, récap chimie & fix notes internes - Context

**Gathered:** 2026-06-03
**Status:** Ready for planning
**Source:** Retours Pierre (discuss tranché) — `.planning/feedback/pierre-2026-06-03-reponses.md` §admin-1/2/5 + §Décisions discuss

<domain>
## Phase Boundary

Trois chantiers de l'espace admin (l'outil métier du pro), tous issus des retours de Pierre. Indépendants entre eux mais regroupés car ils touchent le même cœur métier (passages + saisie offline) :

1. **[admin-2] FIX bug `notes_privees`** — la note interne est saisie, envoyée, validée par l'API, mais **perdue à la synchro** (colonne absente de la migration, de `$fillable`, et de l'upsert). C'est une vraie perte de donnée, saluée par Pierre puis cassée. **Priorité haute.**
2. **[admin-1] Agenda du jour** — la porte d'entrée que Pierre décrit (« voir mes piscines du jour → cliquer le client → saisir »). N'existe pas. La saisie aval existe déjà (`?client_id=X`, auto-sélection piscine, Alpine + IndexedDB).
3. **[admin-5] Récap mensuel + consommation chimie** — pivot `passage_produit` (produit + quantité + prix snapshot), mini-sélecteur « produits utilisés » dans la saisie offline, page « Récap mensuel par client » (nb passages + chimie consommée).

**Hors périmètre (Phase 3 facturation) :** génération de facture, modèle Document, Odoo, branchement « Mes documents » client. Le bouton « Générer la facture » du récap est un *teaser* vers la Phase 3.
</domain>

<decisions>
## Implementation Decisions

### [admin-2] Bug notes_privees — CORRIGER (priorité haute)
- Ajouter la colonne `notes_privees` (nullable text) à la table `passages` : **migration + `$fillable` + chemin d'upsert de synchro** (les trois, c'est là que ça casse).
- La note interne **persiste côté serveur ET reste invisible côté portail client** — c'est l'invariant de vie privée à tester.
- **Notif : ne RIEN promettre en push** (PWA push non fiable iOS/Safari, device Pierre inconnu — cf. memory pierre-device-platform). À la place : remonter un **flag « à revoir »** dans l'agenda du jour (admin-1). E-mail de rappel = différé (pas dans cette phase).
- Test obligatoire : « la note interne persiste après synchro offline → online ET n'apparaît jamais dans la timeline portail client ».

### [admin-1] Agenda du jour — dérivé d'une fréquence (zéro saisie de RDV)
- L'agenda est **DÉRIVÉ d'une fréquence / jour de passage** porté par la piscine (ex. « lundi », « 1×/semaine »), **PAS** des rendez-vous explicites. Zéro CRUD de RDV — c'est un pro solo, on minimise la saisie.
- Vue admin « Mon agenda du jour » : liste les piscines/clients **attendus aujourd'hui** (dérivés de la fréquence), chacun liant vers la saisie pré-remplie (`?client_id=X`, réutiliser le chemin existant).
- Y faire remonter les **flags « à revoir »** issus des notes internes (lien avec admin-2).
- Si la fréquence n'est pas encore modélisée sur `piscines`, l'ajouter (champ simple : jour de la semaine et/ou cadence).

### [admin-5] Récap chimie — pivot passage_produit + sélecteur offline
- **Schéma** : table pivot `passage_produit` { `passage_id`, `produit_id`, `quantite` (nullable/optionnel), `prix_snapshot` (prix du produit au moment du passage) }.
- **Saisie** : mini-sélecteur « produits utilisés » dans le formulaire de passage, **offline-first (Alpine + IndexedDB, jamais Livewire)** — même contrainte que la saisie existante. Produits **pré-listés** (depuis table `produits`), **quantité optionnelle** pour minimiser la saisie terrain.
- La consommation chimie doit traverser le **même chemin de synchro offline** que le reste du passage (ne pas refaire le bug admin-2 sur les produits).
- **Page « Récap mensuel par client »** : nb de passages + chimie consommée sur le mois, par client. Bouton **« Générer la facture »** présent mais **inerte / teaser** → relie la Phase 3 (admin-4).
- **TVA / prix** : `prix_snapshot` stocke un prix HT brut. **Aucun calcul TVA ici** — Pierre est en franchise art. 293 B (cf. memory pierre-statut-fiscal). La logique facture (mention 293 B, total) est Phase 3.

### Claude's Discretion
- Forme exacte du champ fréquence sur `piscines` (enum jour vs cadence vs les deux).
- UI/placement précis du sélecteur produits et de la page récap (suivre design system `impeccable`, register `product`).
- Format d'affichage du récap (tableau, totaux).
</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Décisions & retours (source de vérité)
- `.planning/feedback/pierre-2026-06-03-reponses.md` — réponses point-par-point + §Décisions discuss (admin-1/2/5)

### Bug notes_privees + chemin de synchro offline (admin-2, admin-5)
- `database/migrations/2026_05_28_000005_create_passages_table.php` — schéma passages (colonne manquante)
- `app/Models/Passage.php` — `$fillable` (colonne manquante)
- `app/Http/Controllers/Api/PassageController.php` — upsert de synchro (où la note est silencieusement effacée)
- `resources/js/offline-queue.js`, `resources/js/passage-form.js`, `resources/js/sync-drawer.js` — file IndexedDB + POST de synchro
- `resources/views/admin/passages/create.blade.php` — formulaire de saisie (champ « Note interne » + futur sélecteur produits)
- `app/Livewire/Portail/PassageTimeline.php` + `resources/views/livewire/portail/passage-timeline.blade.php` — portail client (l'invariant : `notes_privees` ne doit JAMAIS y apparaître)

### Agenda du jour (admin-1)
- `app/Http/Controllers/Admin/DashboardController.php`, `app/Http/Controllers/Admin/PassageCreateController.php` — back-office admin + saisie pré-remplie `?client_id=X`
- `app/Models/Piscine.php` — où ajouter la fréquence
- `app/Livewire/PassageIndex.php` — liste passages existante

### Récap chimie (admin-5)
- `app/Models/Produit.php`, `app/Models/Client.php` — modèles à relier via le pivot

### Contraintes projet
- `CLAUDE.md` — stack (Laravel 13, Livewire 3, Alpine, Tailwind v4 @theme, PWA offline = Alpine+IndexedDB jamais Livewire)
- `.claude/skills/dloazur-frontend-stack/SKILL.md` — conventions frontend stables
- Design : `PRODUCT.md`, `DESIGN.md`, skill `impeccable` (register `product`)
</canonical_refs>

<specifics>
## Specific Ideas

- L'agenda dérivé = la « grosse » idée UX : un pro solo ne saisit pas de RDV, l'app déduit sa tournée du jour à partir de la cadence portée par chaque piscine.
- Le sélecteur produits doit rester **léger** : pré-liste + quantité optionnelle. Si Pierre ne renseigne pas la quantité, le récap compte quand même le produit utilisé.
- Réutiliser au maximum le chemin de saisie existant (`?client_id=X` + auto-sélection piscine) — ne pas réinventer la saisie.
</specifics>

<deferred>
## Deferred Ideas

- **Phase 3 (facturation)** : génération réelle de facture depuis le récap (bouton « Générer la facture »), modèle Document, mention TVA 293 B, branchement « Mes documents » côté client (client-3), Odoo (POC plan Custom).
- **Notif** : e-mail de rappel sur les notes internes « à revoir » (push exclu).
- Ménage hors-scope : suppression du partial orphelin `urgence-eau-verte.blade.php` (V6), test régression historique dépliable (client-2) — relèvent des Phases 8/9.
</deferred>

<scope_fence>
## Scope Fence — NE PAS faire dans cette phase

- ❌ Aucune logique de facturation / PDF / Document / Odoo (Phase 3). Le bouton « Générer la facture » est inerte.
- ❌ Aucun calcul de TVA (franchise 293 B — Phase 3 traitera la mention).
- ❌ Aucune notification push (non fiable iOS/Safari).
- ❌ Aucun CRUD de rendez-vous explicites (l'agenda est dérivé d'une fréquence).
- ❌ Ne pas utiliser Livewire pour la saisie offline (Alpine + IndexedDB obligatoire).
</scope_fence>

---

*Phase: 07-espace-admin-retours-pierre*
*Context gathered: 2026-06-03 depuis les retours Pierre (discuss déjà tranché)*
