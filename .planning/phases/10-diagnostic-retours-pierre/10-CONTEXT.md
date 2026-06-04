# Phase 10: diagnostic-retours-pierre - Context

**Gathered:** 2026-06-04
**Status:** Ready for planning

<domain>
## Phase Boundary

Retirer l'écran « mode » initial du wizard diagnostic (S0 — la fourche « Trouver mon problème » / « Analyser mon eau »), faire entrer l'utilisateur directement dans l'arbre symptôme (`step:'tree'`, `nodeId:'start'`), et reléguer « Analyser mon eau » + le Carnet (DIAG-07) en actions secondaires sur le disclaimer (S4). La logique serveur `$mode` (payload WhatsApp / `created_via`) est conservée intacte. Les tests Pest impactés sont adaptés avant toute suppression.

Périmètre : `resources/views/livewire/diagnostic-wizard.blade.php` (bloc S0, état Alpine initial, `resumeFromCarnet()`) + `tests/Feature/DiagnosticWizardTest.php`. Aucune migration, aucun modèle, aucune route nouvelle.

</domain>

<decisions>
## Implementation Decisions

### Écran S0 — suppression

- **D-01 : Retirer le bloc S0 intégralement** — Supprimer la `<div x-show="step === 'mode' && !showCarnet">` (lignes 221–285 du wizard) avec ses trois boutons (`data-mode-symptom`, `data-mode-chemistry`, `data-mode-carnet`).
- **D-02 : État Alpine initial** — Changer `step: 'mode'` → `step: 'tree'` dans le `x-data`. `nodeId` reste `'start'`. `mode` reste `null` (sera positionné par le lien « Analyser mon eau »).

### « Analyser mon eau » — action secondaire

- **D-03 : Placement sur le disclaimer (S4)** — Le lien « Analyser mon eau » est inséré juste sous le bouton principal du disclaimer (« J'accepte et commence »).
- **D-04 : Forme lien texte sobre** — Un `<a>` ou `<button type="button">` de style lien discret, pas un bouton outline. Libellé : « Vous avez vos mesures ? → Analyser mon eau ». Clic → `advance({ value: 'chemistry', next: { kind: 'wizard', id: 'chemistry' } })`, ce qui pose `mode: 'chemistry'` via l'history.

### Carnet (DIAG-07) — action secondaire

- **D-05 : Placement sur le disclaimer aussi** — Le bouton Carnet est inséré sous le lien chimie sur le disclaimer, conditionnel `x-show="carnetEntries.length > 0"`. Même zone, entrée unique pour les deux alternatives.
- **D-06 : `x-init="loadCarnetEntries()"` migre sur le disclaimer** — Actuellement sur l'ancien bouton S0 ; le déplacer sur la div du disclaimer pour déclencher le chargement au bon moment.

### `resumeFromCarnet()` — nouvelle cible

- **D-07 : Rediriger vers `step:'tree'`, `nodeId:'start'`** — Remplacer `this.step = 'mode'` → `this.step = 'tree'` dans `resumeFromCarnet()`. L'utilisateur repart du disclaimer pour un nouveau diagnostic.

### Adaptation des tests

- **D-08 : `assertSee('Trouver mon problème')` → `assertSee('J\'accepte')`** — Ce texte est le CTA principal du disclaimer, premier contenu visible avec `step:'tree'`/`nodeId:'start'`/`disclaimerAccepted:false`. Robuste, lié au flux réel.
- **D-09 : Adapter tous les tests ciblant les attributs `data-mode-*`** — Si d'autres tests sélectionnent `data-mode-symptom`, `data-mode-chemistry`, `data-mode-carnet` dans le DOM, les adapter ou les supprimer. (Grep préalable requis — aucun match trouvé dans les fichiers Feature actuels, mais à vérifier dans les browser tests éventuels.)

</decisions>

<canonical_refs>
## Canonical References

**Downstream agents MUST read these before planning or implementing.**

### Retours Pierre — source de vérité

- `.planning/feedback/pierre-2026-06-03-reponses.md` — points diag-1, diag-2 + §Décisions discuss (défaut acté : diag gratuit lead magnet, pas de monétisation)

### Fichier principal à modifier

- `resources/views/livewire/diagnostic-wizard.blade.php` — wizard complet ; bloc S0 lignes 221–285, état Alpine initial ligne 24, `resumeFromCarnet()` ligne 173–182, bloc disclaimer S4 lignes 292–340+

### Tests à adapter

- `tests/Feature/DiagnosticWizardTest.php` — test `assertSee('Trouver mon problème')` (ligne ~18) à adapter ; vérifier les autres tests pour toute dépendance aux attributs `data-mode-*`
- `tests/Feature/DiagnosticRouteTest.php` — lire avant d'adapter (peut aussi cibler du contenu S0)

### Contraintes projet

- `CLAUDE.md` — stack Laravel 13 + Alpine.js (pas Livewire pour la navigation wizard) ; vouvoiement client-facing
- `.claude/skills/dloazur-frontend-stack/SKILL.md` — conventions Blade, tokens Tailwind v4

### ROADMAP phase 10

- `.planning/ROADMAP.md` §"Phase 10: Diagnostic — fidélité au proto" — success criteria officiels (lignes 239–246)

</canonical_refs>

<code_context>
## Existing Code Insights

### Reusable Assets

- Bloc disclaimer S4 (`x-show="step === 'tree' && nodeId === 'start' && !$wire.disclaimerAccepted"`) — c'est ici qu'on greffe le lien chimie et le bouton Carnet, en dessous du CTA principal existant
- `advance({ value, next })` — méthode Alpine existante ; le lien chimie l'appelle exactement comme S0 le faisait → pas de nouvelle logique côté JS
- `loadCarnetEntries()` — méthode Alpine existante à `x-init` sur le disclaimer plutôt que sur l'ancien bouton S0

### Established Patterns

- `$wire.call('setMode', value)` dans `advance()` quand `step === 'mode'` — ce call synchronise `$mode` côté serveur. Avec D-02 (`step` initial = `'tree'`), la branche `if (this.step === 'mode')` ne sera plus jamais franchie au démarrage, mais le `setMode` reste accessible via le lien chimie (le `advance` chimie ne passe pas par la branche mode — vérifier la condition et adapter si besoin pour que `setMode('chemistry')` soit quand même appelé).
- `data-mode-*` attrs — tags de test/tracking sur les anciens boutons S0 ; une fois S0 supprimé, ces attributs disparaissent. Grep final dans tous les tests avant suppression.

### Integration Points

- `app/Livewire/DiagnosticWizard.php` — méthode `setMode($mode)` doit être **conservée** ; elle nourrit `$mode` (payload WhatsApp / `created_via`). Le mode 'symptom' sera positionné implicitement (flux par défaut) ; 'chemistry' sera positionné via le lien secondaire.
- `tests/Feature/DiagnosticWizardTest.php` — 12+ tests existants ; l'adaptation du test `assertSee` est chirurgicale (une ligne) ; la vérification des `data-mode-*` est préventive.

</code_context>

<specifics>
## Specific Ideas

- **Libellé exact du lien chimie :** « Vous avez vos mesures ? → Analyser mon eau » (ton sobre, vouvoiement, sous le bouton J'accepte).
- **Condition Carnet :** `x-show="carnetEntries.length > 0"` — invisible si le carnet est vide, exactement comme sur l'ancien S0.
- **`resumeFromCarnet()` :** remplacer lignes 177–179 par `this.showCarnet = false; this.step = 'tree'; this.nodeId = 'start'; this.history = []; this.resultId = null;` (supprimer `this.step = 'mode'`).

</specifics>

<deferred>
## Deferred Ideas

- **Slider avant/après (V7 vitrine)** — différé jusqu'à 2 vraies photos avant/après de Pierre (cf. phase 8).
- **Monétisation diagnostic (DIAG-04)** — différé V2 ; lead magnet gratuit acté.
- **Suivi multi-mesures (DIAG-05)** — différé V2.
- **`setMode` lors de l'entrée chimie via le lien secondaire** — si le `advance()` chimie ne déclenche pas `setMode('chemistry')` côté serveur car la condition `if (this.step === 'mode')` ne matche plus, c'est un bug potentiel à investiguer au plan. Pas un choix différé — à traiter dans le plan.

</deferred>

---

*Phase: 10-diagnostic-retours-pierre*
*Context gathered: 2026-06-04*
