---
phase: quick-260531-nys-more-demo-data
plan: 01
type: execute
wave: 1
depends_on: []
files_modified:
  - database/seeders/DevDataSeeder.php
autonomous: true
requirements: [DEMO-SEED]
must_haves:
  truths:
    - "php artisan migrate:fresh --seed runs clean with no errors in local/testing"
    - "Dashboard, client portal, and diagnostic screens have populated data (clients, piscines, passages, diagnostics, contrats, factures, signatures)"
    - "Passages span multiple statuses (draft/signed/synced) and dates across several months"
    - "Production env gate in DatabaseSeeder is unchanged (DevDataSeeder stays local/testing-only)"
  artifacts:
    - path: "database/seeders/DevDataSeeder.php"
      provides: "Rich demo dataset generator"
      contains: "Passage"
  key_links:
    - from: "database/seeders/DevDataSeeder.php"
      to: "passages.client_id / passages.piscine_id"
      via: "Piscine::passages()->create / Passage factory with client_id+piscine_id"
      pattern: "passages\\(\\)->create|Passage::factory"
    - from: "database/seeders/DevDataSeeder.php"
      to: "signatures.passage_id"
      via: "Signature::create on signed passages"
      pattern: "Signature::create"
---

<objective>
Expand `DevDataSeeder` from 3 bare clients into a believable demo dataset so the pro
dashboard, client portal, and diagnostic screens look populated on local/staging demo.

Purpose: Empty screens make the app look broken in demos. A realistic spread of clients,
piscines, passages (across statuses/dates), diagnostics, contrats, factures and signatures
exercises every list and detail view.

Output: Rewritten `database/seeders/DevDataSeeder.php`. No migration, model, or env-gate
changes. DevDataSeeder remains local/testing-only.
</objective>

<execution_context>
@$HOME/.claude/get-shit-done/workflows/execute-plan.md
@$HOME/.claude/get-shit-done/templates/summary.md
</execution_context>

<context>
@CLAUDE.md
@database/seeders/DevDataSeeder.php
@database/seeders/DatabaseSeeder.php

<interfaces>
<!-- Schema + model contracts extracted from codebase — use directly, no exploration needed. -->

Passage status values (verified in app code): 'draft', 'signed', 'synced'.
- PassageController upserts only WHERE status='draft'; 'signed'/'synced' are closed passages.
- DemoLoginController seeds historical passages with status='synced', synced_at=now().

Facture statut: default 'brouillon' (no Facture UI exists yet; safe values: 'brouillon', 'envoyee', 'payee').
Facture tva_rate defaults 8.50 (Martinique). numero nullable until posted — leave null for
brouillon, set "FA-2026-000N" for payee/envoyee. lignes = array (json cast).

Model fillables / relations (verified):
- Client::factory() → uuid, name (fr_FR), email, phone, address (Martinique communes).
  Relations: piscines(), passages(), contrats(), factures() (all HasMany).
- Piscine::factory() → client_id, nom, volume_m3, type[enterrée|hors-sol|spa], filtration, traitement.
  Relation: passages() (HasMany). Override nom for 2nd piscine (e.g. 'Spa', 'Bassin enfants').
- Passage::factory() → client_uuid (fresh Str::uuid per row — UNIQUE), client_id, piscine_id,
  visited_at, status='draft' default, ph_avant/ph_apres/chlore_libre/chlore_total/tac/th/sel_g_l,
  actions (array/json), notes (text), pdf_path, signature_path, synced_at.
  fillable confirmed: client_uuid, piscine_id, client_id, visited_at, status, ph_avant, ph_apres,
  chlore_libre, chlore_total, tac, th, sel_g_l, actions, notes, pdf_path, signature_path, synced_at.
- Diagnostic::factory() → client_id, piscine_id (both nullable), volume_m3, type_probleme,
  mesures (array), recommandations (array), disclaimer_accepted_at, created_via ('wizard'|'lead'),
  prenom, commune, email, site_web (lead-capture columns).
- Contrat (NO factory — create inline): client_id, type[ponctuel|forfait_mensuel|forfait_saisonnier],
  libelle, prix_ht_mensuel, jour_facturation, date_debut, date_fin, actif.
- Facture (NO factory — create inline): uuid (Str::uuid), numero, client_id, contrat_id, passage_id,
  lignes (array), total_ht, tva, total_ttc, tva_rate (8.50), statut, date_echeance.
- Signature (NO factory — create inline): passage_id, client_id, signature_data, signed_at,
  signer_name, ip, user_agent. Relation Passage::signature() is HasOne.
- Produit (NO factory — keep existing 5 inline): sku, libelle, prix_ht, unite, actif.
</interfaces>
</context>

<tasks>

<task type="auto">
  <name>Task 1: Seed rich client base + piscines + contrats + diagnostics</name>
  <files>database/seeders/DevDataSeeder.php</files>
  <action>
  Rewrite `DevDataSeeder::run()`. Keep the existing 5 `Produit::create(...)` block verbatim
  (catalogue is fine). Replace the 3-client loop with 10 clients via
  `Client::factory()->count(10)->create()`.

  For each client:
  - Create 1 piscine via `Piscine::factory()->create(['client_id' => $client->id])`.
  - For ~30% of clients (e.g. every 3rd by index) create a 2nd piscine with a distinct nom
    ('Spa' or 'Bassin enfants') so multi-piscine clients exist.
  - Create 1 contrat inline (as the existing seeder does). Vary `type` across the three values.
    For forfait types set `prix_ht_mensuel` (180–260), `jour_facturation` (1–28),
    `date_debut` (now()->subMonths(2..12)), `actif` true. Keep ponctuel contrats with null
    prix_ht_mensuel. libelle = 'Contrat entretien ' . $client->name. Real values only — no
    "v1"/placeholder reduction.

  Seed 6 diagnostics total (mix): ~3 attached diagnostics
  (`Diagnostic::factory()->create(['client_id'=>..., 'piscine_id'=>..., 'created_via'=>'wizard'])`)
  using existing client/piscine ids, and ~3 anonymous lead diagnostics
  (`created_via'=>'lead'`, client_id/piscine_id null, with prenom/commune/email/site_web filled
  via fake('fr_FR') — commune from the Martinique list: Fort-de-France, Le Lamentin, Schoelcher,
  Les Trois-Îlets, Sainte-Anne, Le Robert). Give realistic `mesures` (ph 6.8–7.6, chlore 0.5–2.0,
  alcalinite 70–140) and a short `recommandations` array of clean vouvoiement French strings
  (e.g. "Ajustez le pH avec un correcteur.", "Effectuez un traitement choc puis brossez les parois.").

  All French text must use vouvoiement (vous, never tu) and name the operator Pierre ADAM where
  an operator is referenced. Do NOT touch DatabaseSeeder or its env gate.
  </action>
  <verify>
    <automated>php artisan migrate:fresh --seed --env=testing 2>&1 | tail -5 && php artisan tinker --execute="echo App\Models\Client::count().'/'.App\Models\Piscine::count().'/'.App\Models\Diagnostic::count().'/'.App\Models\Contrat::count();" --env=testing</automated>
  </verify>
  <done>migrate:fresh --seed completes without error; counts show ~10 clients, >=12 piscines, ~6 diagnostics, ~10 contrats.</done>
</task>

<task type="auto">
  <name>Task 2: Seed passages across statuses/dates + signatures + factures</name>
  <files>database/seeders/DevDataSeeder.php</files>
  <action>
  Extend `DevDataSeeder::run()` (same file as Task 1) to generate passages, signatures and
  factures over the data created in Task 1. Loop over the created piscines.

  For each piscine, create 4–8 passages spread over the last ~6 months via
  `$piscine->passages()->create([...])` with `client_id` = the piscine's client. Each passage:
  - Fresh `client_uuid => (string) Str::uuid()` (UNIQUE constraint — one per passage, NOT the
    client uuid; this is the offline idempotence key per D-08).
  - `visited_at` = now()->subWeeks(N), spaced out (e.g. every 2 weeks).
  - Vary `status`: most recent 1–2 passages 'draft' (open, synced_at null); older ones alternate
    'signed' and 'synced' with `synced_at => now()`.
  - Realistic water chemistry: ph_avant 6.9–7.4, ph_apres ~7.2, chlore_libre 0.8–2.0,
    tac 70–110, occasional th/sel_g_l.
  - `actions` = array of 1–3 clean vouvoiement French task strings (e.g.
    "Nettoyage du préfiltre de pompe", "Brossage des parois", "Ajout de chlore lent",
    "Contrôle et réglage du pH").
  - `notes` = short realistic French sentence (vouvoiement; operator = Pierre ADAM where relevant).

  For every passage with status 'signed' or 'synced', create a `Signature::create([...])`:
  passage_id, client_id, signature_data => a tiny placeholder data-URI stub
  ("data:image/png;base64,iVBORw0KGgo..."), signed_at => the passage visited_at,
  signer_name => client name, ip => fake()->ipv4(), user_agent => a realistic mobile UA string.

  Create ~8 factures total tied to forfait clients/contrats. Each `Facture::create([...])`:
  uuid => (string) Str::uuid(), client_id, contrat_id (the client's contrat), passage_id (one of
  the client's synced passages, optional), lignes => array of one line
  (['libelle'=>'Forfait entretien mensuel','qte'=>1,'prix_ht'=>...]), total_ht, tva computed at
  8.50%, total_ttc, tva_rate => 8.50, date_echeance => visited date + 30 days. Vary statut across
  'brouillon' (numero null), 'envoyee' and 'payee' (numero => sprintf('FA-2026-%04d', $i)).

  Keep the seeder readable and structured (helper closures/arrays welcome). Stay vouvoiement,
  operator Pierre ADAM. Do NOT touch DatabaseSeeder or its env gate.
  </action>
  <verify>
    <automated>php artisan migrate:fresh --seed --env=testing 2>&1 | tail -5 && php artisan tinker --execute="echo App\Models\Passage::count().' passages; statuses='.App\Models\Passage::distinct()->pluck('status')->implode(',').'; sig='.App\Models\Signature::count().'; fact='.App\Models\Facture::count();" --env=testing</automated>
  </verify>
  <done>Seed runs clean; >=40 passages with all three statuses present (draft, signed, synced); signatures exist for every signed/synced passage; ~8 factures across brouillon/envoyee/payee.</done>
</task>

</tasks>

<verification>
- `php artisan migrate:fresh --seed --env=testing` exits 0 with no exceptions.
- `grep -n "Passage\|Signature::create\|Facture::create" database/seeders/DevDataSeeder.php` confirms new record types are seeded.
- `grep -ni "\btu\b\|\bton\b\|\bta\b" database/seeders/DevDataSeeder.php` returns no tutoiement in French copy (vouvoiement check).
- `git diff database/seeders/DatabaseSeeder.php` is empty (env gate untouched).
</verification>

<success_criteria>
- DevDataSeeder produces ~10 clients, >=12 piscines, ~10 contrats, ~6 diagnostics (wizard + lead),
  >=40 passages across draft/signed/synced statuses spanning ~6 months, signatures on all closed
  passages, and ~8 factures across statuses.
- Production env gate unchanged; seeder stays local/testing-only.
- All seeded French copy uses vouvoiement; operator referenced as Pierre ADAM.
</success_criteria>

<output>
Create `.planning/quick/260531-nys-more-demo-data/260531-nys-SUMMARY.md` when done.
</output>
