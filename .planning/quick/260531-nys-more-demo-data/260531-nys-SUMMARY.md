---
phase: quick-260531-nys-more-demo-data
plan: 01
status: complete
subsystem: database-seeding
tags: [seed, demo-data, dev-fixtures]
requires: [Client, Piscine, Contrat, Diagnostic, Passage, Signature, Facture, Produit models + factories]
provides: [rich-dev-demo-dataset]
affects: [database/seeders/DevDataSeeder.php]
key-files:
  modified:
    - database/seeders/DevDataSeeder.php
decisions:
  - "DevDataSeeder regenerated as a structured generator (helper methods + class-const copy tables) instead of the 3-client loop."
  - "Factures attach only to forfait contrats; 1–2 billed months per forfait contrat to reach the ~8 target."
metrics:
  duration: ~10m
  completed: 2026-05-31
  tasks: 2
  files: 1
---

# Quick 260531-nys: More Demo Data Summary

Rewrote `DevDataSeeder` from 3 bare clients into a believable demo dataset (10 clients, 14 piscines, 10 contrats, 6 diagnostics, 90 passages across all statuses, 62 signatures, 8 factures) so the pro dashboard, client portal and diagnostic screens render populated on local/staging demo.

## What changed

`database/seeders/DevDataSeeder.php` — full rewrite of `run()`, kept the 5 `Produit::create` catalogue verbatim (moved into a `seedProduits()` helper). New structured generator:

- **Clients/piscines/contrats** — `Client::factory()->count(10)`. Each client gets 1 piscine; every 3rd client (index `%3==0`) gets a 2nd distinct basin ('Spa' / 'Bassin enfants', type spa). 1 contrat per client cycling `ponctuel / forfait_mensuel / forfait_saisonnier`; forfait contrats get `prix_ht_mensuel` 180–260, `jour_facturation` 1–28, `date_debut` 2–12 months back; ponctuel keeps null prix.
- **Diagnostics** — 6 total: 3 wizard diagnostics attached to existing client+piscine ids, 3 anonymous `created_via=lead` with `prenom/commune/email/site_web` filled via `fake('fr_FR')` and commune drawn from the Martinique list. Realistic `mesures` (ph 6.8–7.6, chlore 0.5–2.0, alcalinite 70–140) and 2-item vouvoiement `recommandations`.
- **Passages** — 4–8 per piscine, every 2 weeks back over ~6 months via `$piscine->passages()->create([...])`. Each gets a fresh `client_uuid` (per-passage UNIQUE offline idempotence key, NOT the client uuid). Most-recent 1–2 are `draft` (synced_at null); older ones alternate `synced`/`signed`. Realistic chemistry, 1–3 vouvoiement `actions`, a vouvoiement `notes` line (operator Pierre ADAM).
- **Signatures** — `Signature::create` on every closed (signed/synced) passage: tiny base64 PNG stub, `signed_at` = passage `visited_at`, signer = client name, fake ipv4 + realistic mobile UA. 1:1 with closed passages (62/62).
- **Factures** — 8 total, only on forfait contrats; 1–2 billed months per forfait contrat until the target is hit. `lignes` json line, TVA computed at 8.50% (Martinique), `date_echeance` = visit + 30 days, statut cycled across `brouillon` (numero null) / `envoyee` / `payee` (numero `FA-2026-000N`).

All seeded French copy uses vouvoiement; operator referenced as Pierre ADAM. `DatabaseSeeder.php` and its production env gate untouched.

## Verification (ran against pgsql `testing` DB via --env=testing)

`.env.testing` points at a live local pgsql `testing` database (reachable), so verification ran there per the plan's command rather than falling back to the dev sqlite file.

```
migrate:fresh --seed --env=testing → DevDataSeeder DONE (no exceptions)

clients=10 piscines=14 diag=6 contrats=10
passages=90 statuses=draft,signed,synced
closed=62 sig=62           (1:1 signatures on closed passages)
fact=8 fact_statuts=envoyee,brouillon,payee
diag_wizard=3 diag_lead=3
```

- `grep -niE "\btu\b|\bton\b|\bta\b" DevDataSeeder.php` → no match (vouvoiement clean).
- `git diff database/seeders/DatabaseSeeder.php` → empty (env gate untouched).
- `grep -n "Passage|Signature::create|Facture::create"` → all new record types present.

All `must_haves.truths` and `success_criteria` satisfied.

## Deviations from Plan

None affecting behavior. The plan's facture count is "~8"; a single facture-per-forfait-client would have yielded 6, so the facture block was structured to bill 1–2 months per forfait contrat to land exactly on 8 across all three statuts. This is within the plan's stated `~8` and `vary statut` intent.

## Self-Check: PASSED

- `database/seeders/DevDataSeeder.php` — FOUND (modified, verified by clean seed run).
- `database/seeders/DatabaseSeeder.php` — unchanged (empty diff).
