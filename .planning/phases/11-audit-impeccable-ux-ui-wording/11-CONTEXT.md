# Phase 11: Audit Impeccable — UX / UI / Wording - Context

**Gathered:** 2026-06-04
**Status:** Ready for planning

<domain>
## Phase Boundary

**Phase 11 corrige les findings de l'audit `/impeccable`** (du 2026-06-04, Health **14/20**) sur les **5 surfaces** : vitrine, PWA passage offline, admin pro, portail client, auth. L'audit est **déjà fait** — la liste autoritative des correctifs, avec `file:line` pour chaque item, est `11-FINDINGS.md`. Cette phase **implémente les correctifs**, elle ne ré-audite pas.

**Périmètre de sévérité (décidé) : TOUT — P0 → P3.** Les bloquants fonctionnels (perte/échouage de données, erreur avalée, faux témoignages live), le theming, le wording, les états, ET les items polish (em-dashes, alt, glyphes off-token). Le FINDINGS est la checklist exhaustive.

**Hors périmètre :**
- Pas de nouvelle capability (pas de nouvelle feature, route, ou modèle hors fix).
- Vouvoiement **client-facing** : audit = PASS total, **on n'y touche pas** (portail, emails, magic-link, login client restent `vous`).
- Items dépendants d'une **action Pierre** (vrais avis Google, vraie photo avant/après) : le fix livre un placeholder gaté, pas le contenu réel.

</domain>

<decisions>
## Implementation Decisions

### Périmètre / sévérité

- **D-01 : Corriger TOUT le FINDINGS — P0, P1, P2 et P3.** Pas de cutline. Le planner séquence par sévérité (P0 data/légal d'abord), mais tous les items sont en scope dans cette phase. Le FINDINGS §"Plan d'action recommandé" donne l'ordre.

### Theming — correctif `#fff` systémique (P1)

- **D-02 : Override token global.** Ajouter `--color-white: var(--color-sand-50);` dans le bloc `@theme` de `resources/css/app.css`. Couvre les ~90 occurrences `bg-white`/`text-white` + l'email en 1 point. **Pas de sweep classe-par-classe.**
- **D-03 : Exceptions à préserver.** WhatsApp `#25D366` et la carte QR blanche intentionnelle ne doivent pas virer sable. Vérifier qu'aucune n'hérite par erreur de l'override (elles utilisent des hex/tokens dédiés, donc OK, mais à confirmer).
- **D-04 : Vérifier le contraste `text-white` sur fonds colorés.** `sand-50` ≈ blanc chaud (`oklch ~0.98`), le contraste sur `azure-500`/`navy-900` reste équivalent — confirmer visuellement après l'override, pas de régression WCAG attendue.

### Theming — tokens Tailwind v4 non définis (P1)

- **D-05 : Corriger les classes qui n'émettent aucun CSS.** `bg-lagon-50/40` (philosophie:4), `text-warn-700` (agenda:59), `text-ink-600` (agenda:76) → soit ajouter les nuances manquantes à `@theme`, soit remapper sur tokens existants (`bg-sand-100`/`bg-azure-50/40`, token ambre déclaré, `text-ink-700`).
- **D-06 : Ajouter un garde-fou CI** (grep sur classes de tokens non déclarés) pour empêcher la rupture silencieuse de récidiver. Recommandation du FINDINGS, adoptée. Implémentation au choix du planner (script grep dans le workflow tests).

### Wording — registre tu/vous opérateur (P1)

- **D-07 : Registre opérateur = `tu`.** ~~Aligner sur `tu` toute la copie admin/PWA actuellement en `vous`~~ **RENVERSÉ le 2026-06-05** (quick `vouvoiement-outil-metier`, décision Antoine) : registre opérateur repassé en **`vous`** pour une voix unique et irréprochable sur toute l'app (« deux publics, une seule voix », PRODUCT.md §5). Toute la copie admin/PWA/JS est désormais en `vous` ; client-facing inchangé (D-08).
- **D-08 : Client-facing reste `vous` strict.** Verrouillé — portail, emails, magic-link, login client. Aucune modification de registre côté client.

### Wording — Pierre en copie marketing vitrine (P1, décision Antoine)

- **D-09 : Retour à « nous »/« Dlo Azur » — respect strict DESIGN.md §6.** Les ~10 surfaces qui nomment Pierre hors zones autorisées reviennent à « nous »/« Dlo Azur ». Inclut « Appeler Pierre » → reformuler (« Nous appeler » / « Appeler Dlo Azur »). Pierre nommé reste **uniquement** dans `partials/pierre.blade.php` + footer + mentions légales.
- Surfaces concernées (FINDINGS) : `philosophie.blade.php:32,54`, `final-cta.blade.php:11,24`, `contact-form.blade.php:12,24`, les 4 `zones/*.blade.php`, `services/depannage.blade.php:14,20,53`, `services/analyse-eau.blade.php:79,82`, `diagnostic.blade.php:161,165`.

### Admin — dashboard (P2)

- **D-10 : Restructure complète du dashboard.** (a) Remonter le bloc agenda du jour (« Aujourd'hui » + « À revoir », qui existe déjà sur `agenda/index`) sur le dashboard. (b) Rendre les cartes warn/offline **cliquables** vers vues filtrées. (c) Démoter/fusionner les 2 comptes vanity (« Clients actifs », « Passages cette semaine »). (d) Casser l'uniformité de la grille 4-cartes-identiques (tell IA résiduel).

### Claude's Discretion

- **P0 PWA offline (le cœur de valeur)** : monter `<x-admin.sync-drawer>` dans le layout admin + déplacer la logique flush/upload dans un store partagé (`Alpine.store('offlineQueue').flush()`) appelé par `app.js` ; récupérer les zombies `uploading` (re-queue `uploading`→`pending` au `init()`/`flushAll`). Approche technique au choix du planner, fidèle au FINDINGS §P0.
- **P0 magic-link avalé** : câbler `@error('ml')` dans `magic-link-request.blade.php` (message déjà écrit, juste pas branché) ; supprimer les blocs d'erreur morts de `confirm.blade.php:38-50`.
- **P0 faux témoignages** : gater derrière `GoogleReviews` réel ou placeholder `[À fournir par Pierre]` — ne pas livrer de noms inventés. Choix de la forme du placeholder au planner.
- Tous les P1/P2/P3 restants (états loading/submitting, étoiles Google reviews, email side-stripe, badge « Eau saine » gaté in-range, titre client edit `@section`, landmarks imbriqués, nav Récap/Blog mobile, steppers 56px, empty-states portail, `temporaryUrl` cassé, em-dashes, alt hero, glyphes off-token, etc.) : exécution mécanique directe depuis `11-FINDINGS.md`, forme exacte au planner tant que l'intention du finding est respectée.
- Re-run `/impeccable audit` après corrections pour vérifier la remontée du score (non obligatoire dans cette phase, mais recommandé en verify).

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Source autoritative — la checklist de correctifs (LIRE EN PREMIER)
- `.planning/phases/11-audit-impeccable-ux-ui-wording/11-FINDINGS.md` — **LE document de scope.** Tous les findings P0→P3 avec `file:line`, impact, et fix proposé pour chaque item. C'est la liste exhaustive que cette phase implémente. Inclut §"Forces à préserver" (ne pas casser) et §"Plan d'action recommandé" (ordre de priorité).
- `.planning/ROADMAP.md` §"Phase 11" — résumé du périmètre, pointe vers le FINDINGS.

### Design / brand — règles à respecter
- `DESIGN.md` §6 — loi « jamais #000/#fff » (→ D-02) et règle « Pierre nommé = 1 zone seulement » (→ D-09). Les do's/don'ts et le système de tokens OKLCH.
- `PRODUCT.md` — personnalité, 5 principes, « la preuve remplace le superlatif » (→ badge « Eau saine » gaté, faux témoignages).
- `resources/css/app.css` — bloc `@theme` Tailwind v4 CSS-first ; cible de D-02 (`--color-white`) et D-05 (nuances `lagon`/`warn`/`ink` manquantes). Les tokens sont déclarés ici, pas dans un `tailwind.config.js` (v4).

### Mémoires projet pertinentes (rappeler par nom)
- `voice-vouvoiement` — client-facing `vous`, jamais `tu` (→ D-08, verrou).
- `photos-disk-is-r2-not-scaleway` — le disk photos est Cloudflare `r2` ; pertinent pour le fix `temporaryUrl` cassé du portail (P2).
- `operator-name` — Pierre ADAM (→ D-09, où il reste nommé).

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets
- `<x-admin.sync-drawer>` (monté seulement sur `passages/create`) — à monter dans `layouts/admin.blade.php` (P0 D-discretion).
- `<x-icon.check>` SVG — réutiliser à la place des glyphes emoji nus (P2/P3).
- Logique in-range portail déjà calculée (`$phOk`/`$clOk`/`$tacOk`) — réutiliser pour gater le badge « Eau saine » (P1).
- `GoogleReviews` Livewire (déjà câblé) — gate des témoignages (P0).
- Bloc agenda du jour (« Aujourd'hui »/« À revoir ») de `agenda/index` — à remonter sur le dashboard (D-10).

### Established Patterns
- Tailwind v4 CSS-first : seules les nuances déclarées dans `@theme` (`resources/css/app.css`) émettent des utilities — d'où les ruptures silencieuses D-05.
- Offline-first : UUID persisté en IDB avant saisie, autosave debouncé, échecs transitoires → `pending` (pas `error`). NE PAS casser cette discipline (FINDINGS §Forces).
- Livewire `wire:loading.class` / `wire:target` — pattern à appliquer pour les états loading des listes live-search (P1).
- Alpine `@submit` pour désactiver les boutons (états submitting auth, P1) — respecter `prefers-reduced-motion`.

### Integration Points
- `resources/js/app.js:88-92` — relais global `passage-form:flush` ; doit appeler un store partagé, pas juste rafraîchir le compteur (P0).
- `resources/js/offline-queue.js` / `passage-form.js` / `sync-drawer.js` — logique de file/statuts ; cible des fixes zombies `uploading` (P0).
- `resources/css/app.css` `@theme` — point d'entrée des fixes theming (D-02, D-05).

</code_context>

<specifics>
## Specific Ideas

- Override `#fff` = exactement `--color-white: var(--color-sand-50)` (D-02), pas un sweep.
- « Appeler Pierre » → « Nous appeler »/« Appeler Dlo Azur » (D-09).
- Registre opérateur cohérent en `vous` (D-07 **renversé 2026-06-05** : voix unique sur toute l'app ; voir détail plus haut).
- Garde-fou CI = grep sur classes de tokens non déclarés (D-06).

</specifics>

<deferred>
## Deferred Ideas

None — le périmètre « Tout (P0→P3) » absorbe l'intégralité du FINDINGS. Les seuls éléments non livrés en contenu réel sont les **dépendances action-Pierre** (vrais avis Google, vraie photo avant/après) : la phase livre un placeholder gaté, le contenu réel reste une action Pierre hors-code, déjà tracée ailleurs.

</deferred>

---

*Phase: 11-audit-impeccable-ux-ui-wording*
*Context gathered: 2026-06-04*
