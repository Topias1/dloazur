---
phase: "05-diagnostic-commercialisable"
plan: "03"
subsystem: "diagnostic"
tags: ["livewire", "wizard", "dose-engine", "lead-capture", "mailer", "whatsapp", "DIAG-01", "DIAG-02", "DIAG-03", "DIAG-06", "Req5", "Req6", "Req7"]
dependency_graph:
  requires: ["05-01", "05-02"]
  provides:
    - "computeAndPersist() : DoseEngine::compute + Diagnostic::create + session diagnostic_ids seed (D-06)"
    - "submitLead() : persist lead additive columns + Mail DiagnosticLead à Pierre"
    - "DiagnosticLead mailer (clone ContactMessage, replyTo conditionnel)"
    - "Migration additive add_lead_columns (prenom/commune/email/site_web)"
    - "Wizard chimie S3 : 2 steps (infos piscine + mesures), filtre canonique, sel oui/non"
    - "Lead form S7 : Prénom/Commune req, Email/Site web opt, honeypot, wire:loading"
    - "whatsappSummary() côté serveur (DIAG-06 basique)"
    - "DiagnosticWizardTest : 16/16 vert (DIAG-03, Req5, Req6, DIAG-06, honeypot, rate-limit)"
  affects:
    - "app/Livewire/DiagnosticWizard.php (grow — mesures, lead, compute, persist, mail)"
    - "resources/views/livewire/diagnostic-wizard.blade.php (grow — S3, S7, dosage cards)"
    - "app/Models/Diagnostic.php (HasFactory, fillable +4)"
    - "tests/Feature/DiagnosticWizardTest.php (stub → full implementation)"
tech_stack:
  added: []
  patterns:
    - "computeAndPersist guard chain (ContactForm 5 étapes) — validateOnly(array) → validate(rules: array) Livewire 3"
    - "submitLead guard chain miroir ContactForm (rateLimit/honeypot/validate/update+mail/success)"
    - "Livewire::actingAs($client, 'clients') statique avant Livewire::test() — pas chaînable"
    - "validate(rules: array) en lieu de validateOnly(string) pour valider un sous-ensemble de champs"
    - "Additive migration Schema::table + down() dropColumn([...])"
key_files:
  created:
    - "database/migrations/2026_05_30_000010_add_lead_columns_to_diagnostics_table.php"
    - "app/Mail/DiagnosticLead.php"
    - "resources/views/emails/diagnostic-lead.blade.php"
    - "database/factories/DiagnosticFactory.php"
  modified:
    - "app/Livewire/DiagnosticWizard.php"
    - "resources/views/livewire/diagnostic-wizard.blade.php"
    - "app/Models/Diagnostic.php"
    - "tests/Feature/DiagnosticWizardTest.php"
decisions:
  - "validateOnly(array) rejeté par Livewire 3 — validate(rules array) utilisé pour cibler un sous-ensemble de champs"
  - "Livewire::actingAs() est une méthode statique (void) — doit être appelée avant Livewire::test(), pas chaînable"
  - "DiagnosticFactory créé (inexistant) pour permettre Diagnostic::factory()->create() dans les tests"
  - "mesures() filtre les null via array_filter pour ne pas envoyer les champs vides à DoseEngine"
  - "whatsappSummary() côté serveur puis urlencode() dans la vue Blade (pas Alpine :href ici — valeur serveur disponible)"
metrics:
  duration: "~70 min"
  completed_date: "2026-05-30"
  tasks_completed: 2
  files_changed: 8
---

# Phase 5 Plan 03: Conversion core — Wizard chimie, DoseEngine wiring, lead capture

Branchement du wizard Livewire Plan 01 sur le DoseEngine Plan 02 : wizard chimie 2 étapes (S3), action-aware multiselect, compute+persist serveur, capture de lead (S7) avec notification Pierre, escalade WhatsApp pré-remplie.

## Ce qui a été livré

### Task 1 — Wizard chimie S3 + action-aware + lead form S7 (commit bfb3e04)

**DiagnosticWizard.php — inputs + validation :**

- Champs mesures avec `#[Validate]` : volume/surface/profondeur/filtration (in:sable,verre,cartouche,diatomees)/ph/chlore/alcalinite/stabilisant/selPpm/chloreTotal/th
- Champs lead avec `#[Validate]` : prenom/commune (required string max:80), email (nullable email max:160), siteWeb (nullable url max:255) — miroir ContactForm exact
- `mount()` enrichi : pré-remplit filtration + volume depuis piscine client (normalisation hint libre-texte → valeur canonique, D-08 gap)
- `mesures()` accessor : assemble le tableau pour DoseEngine
- `whatsappSummary()` : résumé texte serveur-side (mode, volume, filtre, mesures, tried, recs)
- `volumeEffectif()` : calcul surface×profondeur côté serveur (géométrie, pas dose — DIAG-02)

**Vue diagnostic-wizard.blade.php — surfaces S3 + S7 :**

- Step indicator 1/2 (Azure rempli = actif, sable = inactif)
- Toggle size-mode Volume m³ / Surface×profondeur avec readout live (Alpine géométrique)
- Select filtre contraint : sable / verre / cartouche / diatomées (4 valeurs canoniques, FLOCULANT-BRANCH-SPEC §2)
- Piscine au sel oui/non avec hasSel Alpine
- Mesures pH/chlore/TAC/stabilisant/selPpm avec inputmode="decimal", placeholder "ex: 7,4", error copy français
- Champs optionnels chloreTotal/TH dans `<details>` (audit P1 intake)
- Seul wire:click du wizard : « Calculer mon plan d'action » avec wire:loading swap
- Section résultats S5 : safety block ambre, cards plan ordonnées (numbered, param/current/target/product/dose/note), CTA WhatsApp navy
- Lead form S7 : honeypot aria-hidden, wire:loading button swap, success state "C'est noté, merci !", errors français

**DIAG-02 invariant :** `grep -rE "[0-9]+ ?\* ?(volume|m3)" resources/js/` → vide

### Task 2 — Migration + Mailer + computeAndPersist/submitLead + DiagnosticWizardTest (commit aa17f73)

**Migration additive (D-03) :**
- `add_lead_columns_to_diagnostics_table.php` : prenom(80), commune(80), email(160), site_web(255) nullable after created_via
- `down()` : `dropColumn([prenom, commune, email, site_web])`
- `Diagnostic::$fillable` += 4 colonnes lead
- `Diagnostic::HasFactory` + DiagnosticFactory créé

**DiagnosticLead mailer (clone ContactMessage) :**
- Props readonly : prenom/commune/email?/siteWeb?/summary/mesures/triedActions/diagId
- `envelope()` : sujet "Nouveau diagnostic — Dlo Azur Piscines", replyTo conditionnel (email optionnel, Req6)
- `content()` → view emails.diagnostic-lead
- Vue email : inline-CSS, header #0080ff, champs @if optionnels, mesures, tags triedActions, résumé message-box, ID

**computeAndPersist() — guard chain + DIAG-02/03/D-04/Req5/D-06 :**
1. rateLimit(5, 60)
2. protectAgainstSpam()
3. validate(règles mesures uniquement)
4. if (!$disclaimerAccepted) → addError('disclaimer') return (DIAG-03 server guard)
5. DoseEngine::compute + Diagnostic::create (client_id = null si anonyme Req5, disclaimer_accepted_at = now() D-04)
6. session()->put('diagnostic_ids', ...) seed D-06

**submitLead() — guard chain miroir ContactForm :**
1. rateLimit(5, 60)
2. protectAgainstSpam()
3. validate(règles lead uniquement)
4. Diagnostic::find($savedDiagnosticId)->update(lead columns) + Mail::to(config contact.recipient)->send(DiagnosticLead)
5. $this->leadSent = true

**DiagnosticWizardTest — 16/16 verts :**
- DIAG-03 gate : computeAndPersist rejette + 0 rows quand disclaimerAccepted = false
- D-04 : persisted row has non-null disclaimer_accepted_at
- Req5 anon : client_id null + mesures + recommandations stored
- Req5 auth : Livewire::actingAs($client, 'clients') → client_id = $client->id
- D-06 : savedDiagnosticId exposé
- Req6 : prenom+commune required, email optionnel, persist additive columns, DiagnosticLead::assertSent, leadSent = true
- DIAG-06 : vue contient 596696940054, whatsappSummary() contient "Dlo Azur" non vide
- Honeypot : DiagnosticLead::assertNothingSent
- Rate-limit : assertHasErrors(['throttle']) après 5 soumissions

## Résultats des tests

```
./vendor/bin/pest --filter DiagnosticWizard
tests: 16 passed (39 assertions)

./vendor/bin/pest
tests: 404 passed, 1 skipped, 15 incomplete
```

(Avant ce plan : 400 passés, 27 incomplets — 12 stubs DiagnosticWizardTest devenus réels.)

## Déviations par rapport au plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] validateOnly(array) invalide en Livewire 3**
- **Trouvé pendant :** Task 2 première exécution
- **Problème :** `$this->validateOnly([...])` — `validateOnly()` en Livewire 3 attend une `string` (un seul field), pas un array. L'array était converti en string → `ErrorException: Array to string conversion`.
- **Correction :** Remplacé par `$this->validate(['field' => 'rules', ...])` avec les règles explicites pour `computeAndPersist` (champs mesures) et `submitLead` (champs lead).
- **Fichier :** `app/Livewire/DiagnosticWizard.php`
- **Commit :** aa17f73

**2. [Rule 1 - Bug] Livewire::test()->actingAs() chaîné invalide**
- **Trouvé pendant :** Task 2 (test logged-in)
- **Problème :** `Livewire::test(Component::class)->actingAs($user, 'guard')` — `actingAs()` est une méthode **statique** retournant void en Livewire 3 (pas `$this`), donc le chaînage retournait `null` → `Call to a member function set() on null`.
- **Correction :** Appel `Livewire::actingAs($client, 'clients')` AVANT `Livewire::test(...)`, sans chaîner.
- **Fichier :** `tests/Feature/DiagnosticWizardTest.php`
- **Commit :** aa17f73

**3. [Rule 2 - Missing critical functionality] DiagnosticFactory manquant**
- **Trouvé pendant :** Task 2 (préparation des tests)
- **Problème :** `Diagnostic::factory()->create()` dans les stubs Plan 01 nécessite un DiagnosticFactory — absent de `database/factories/`.
- **Correction :** Création de `database/factories/DiagnosticFactory.php` avec des valeurs par défaut valides.
- **Fichier :** `database/factories/DiagnosticFactory.php`
- **Commit :** aa17f73

## Known Stubs

Aucun. Le wizard chimie compute les doses réelles depuis DoseEngine. Le lead form persiste sur la DB. Le mailer notifie Pierre.

Surface S9 (carnet local) est indiquée comme Plan 05-04/05 dans le code — intentionnel.

## Threat Surface Scan

Mitigation des 5 menaces du plan :

| Threat | Mitigation appliquée |
|--------|----------------------|
| T-05-07 (bot spam lead) | Honeypot + WithRateLimiting 5/60s dans submitLead — testé (assertNothingSent + throttle) |
| T-05-08 (disclaimer bypass) | Guard serveur dans computeAndPersist (step 4, avant toute persistance) — testé (assertHasErrors['disclaimer'], 0 rows) |
| T-05-09 (PII lead) | EU-hosted ; colonnes additive sur diagnostic (D-03 — pas de table séparée) |
| T-05-10 (XSS via mesures/lead) | `{{ }}` Blade auto-escape dans emails/diagnostic-lead.blade.php ; urlencode() sur whatsappSummary |
| T-05-11 (formules client) | Smoke grep `resources/js/` → exit 1 (aucun coefficient) ; DoseEngine appel via wire:click uniquement |

## Self-Check: PASSED

Fichiers créés :
- database/migrations/2026_05_30_000010_add_lead_columns_to_diagnostics_table.php ✓
- app/Mail/DiagnosticLead.php ✓
- resources/views/emails/diagnostic-lead.blade.php ✓
- database/factories/DiagnosticFactory.php ✓

Fichiers modifiés :
- app/Livewire/DiagnosticWizard.php ✓
- resources/views/livewire/diagnostic-wizard.blade.php ✓
- app/Models/Diagnostic.php ✓
- tests/Feature/DiagnosticWizardTest.php ✓

Commits :
- bfb3e04 feat(05-03): wizard chimie S3 (2 steps) + lead form S7 + action-aware multiselect ✓
- aa17f73 feat(05-03): lead migration + DiagnosticLead mailer + computeAndPersist/submitLead + DiagnosticWizardTest green ✓

Migration :
- php artisan migrate → "Nothing to migrate." (déjà appliquée) ✓

Tests :
- ./vendor/bin/pest --filter DiagnosticWizard → 16/16 passed ✓
- ./vendor/bin/pest → 404/405 passed, 1 skipped, 15 incomplete ✓

DIAG-02 : `grep -rE "[0-9]+ \* (volume|m3)" resources/js/` → exit 1 ✓
D-06 : `grep -n "session.*diagnostic_ids" DiagnosticWizard.php` → ligne 391 ✓
