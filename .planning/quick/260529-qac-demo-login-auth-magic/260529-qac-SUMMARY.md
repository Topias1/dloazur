---
quick_id: 260529-qac
slug: demo-login-auth-magic
status: complete
date: 2026-05-29
---

# Quick Task 260529-qac — Demo login dev-only sur /auth/magic

## Objectif
Ajouter un login démo réservé au serveur de dev sur la page portail `/auth/magic` :
boutons **Démo Client** et **Démo Admin**, visibles et fonctionnels uniquement
quand le flag `config('app.demo_login')` est vrai (env `DEMO_LOGIN_ENABLED`).

## Pourquoi (contexte de blocage)
Sur Laravel Cloud, `APP_ENV=production` sur **tous** les environnements (y compris
staging) → impossible de garder la feature sur le nom d'environnement. De plus
`MAIL_MAILER=log` sur staging (le magic link part dans les logs) et `DatabaseSeeder`
est gated `local/testing` → **aucun client seedé** sur staging. Le magic link était
donc inutilisable. La gate retenue est un flag explicite que la vraie prod n'aura jamais.

## Implémentation
- **config/app.php** : `'demo_login' => env('DEMO_LOGIN_ENABLED', false)` (+ commentaire dev-only).
- **app/Http/Controllers/Portail/DemoLoginController.php** (nouveau) :
  - Chaque action : `abort_unless((bool) config('app.demo_login'), 404)` en première ligne (mitigation EoP — gate porteuse).
  - `client()` : `firstOrCreate` du Client `demo-client@dloazur.test` ; provisioning lazy idempotent (guard `doesntExist`) d'1 piscine + 4 passages `synced` (valeurs eau réalistes, `client_uuid` unique par passage). Login guard `clients`, session regenerate, redirect `portail.passages`.
  - `admin()` : `firstOrCreate` du User `demo-admin@dloazur.test` + `forceFill(email_verified_at)`. Login guard `web`, redirect `admin.dashboard`.
- **routes/portail.php** : `POST /auth/demo/client` + `POST /auth/demo/admin`, **hors** du groupe `guest:clients`.
- **resources/views/portail/magic-link-request.blade.php** : bloc `@if (config('app.demo_login'))` avec séparateur « Serveur de démo » + 2 boutons (azure / navy outline).
- **tests/Feature/DemoLoginTest.php** : gate 404 (flag off), les 2 logins + redirections + guards, idempotence.
- **tests/Pest.php** : garde realpath sur le bloc support-worktree (évite double-registration quand le worktree a son propre vendor).

## Déviations (auto-corrigées)
- `passages.client_uuid` = clé d'idempotence par passage (D-08, unique), pas l'UUID client → `Str::uuid()` par passage.
- `piscines.equipements` est array-cast → valeur tableau.
- Fix `tests/Pest.php` (infra worktree) — sans effet sur main (bloc inactif hors worktree).

## Vérification
- `vendor/bin/pest tests/Feature/DemoLoginTest.php` → 4 passed, 18 assertions.
- Suite complète post-merge → 333 passed / 1 skipped / 0 failed.
- `php artisan route:list --path=auth/demo` → `portail.demo.client`, `portail.demo.admin`.

## Commits (code)
- `b89fd84` config flag
- `e115f7b` DemoLoginController
- `7e99342` routes + vue
- `a994eaf` test (+ fix Pest.php)
- merge `57a2182` dans main

## Infra / ops
- `DEMO_LOGIN_ENABLED=true` posé sur l'env staging (Laravel Cloud API) — la prod future ne l'aura pas.
- Comptes démo provisionnés à la volée par le contrôleur (pas de seeder).
