---
phase: quick-260529-qac
plan: 01
type: execute
wave: 1
depends_on: []
files_modified:
  - config/app.php
  - app/Http/Controllers/Portail/DemoLoginController.php
  - routes/portail.php
  - resources/views/portail/magic-link-request.blade.php
  - tests/Feature/DemoLoginTest.php
autonomous: true
requirements: [QAC-DEMO-LOGIN]
must_haves:
  truths:
    - "When config('app.demo_login') is true, /auth/magic shows two demo buttons"
    - "POST /auth/demo/client provisions a demo client (1 piscine, >=1 passage) and logs in via guard 'clients'"
    - "POST /auth/demo/admin provisions a verified demo admin and logs in via guard 'web'"
    - "When the flag is false, both POST routes return 404 and the buttons are hidden"
    - "Repeated demo logins are idempotent — exactly one demo client and one demo admin row"
  artifacts:
    - path: "app/Http/Controllers/Portail/DemoLoginController.php"
      provides: "Flag-gated demo login + lazy idempotent provisioning"
      contains: "abort_unless"
    - path: "config/app.php"
      provides: "demo_login feature flag"
      contains: "demo_login"
    - path: "tests/Feature/DemoLoginTest.php"
      provides: "Coverage for flag gate, both logins, idempotence"
      contains: "demo_login"
  key_links:
    - from: "resources/views/portail/magic-link-request.blade.php"
      to: "routes portail.demo.client / portail.demo.admin"
      via: "form action route()"
      pattern: "portail\\.demo\\."
    - from: "app/Http/Controllers/Portail/DemoLoginController.php"
      to: "config('app.demo_login')"
      via: "abort_unless gate"
      pattern: "config\\('app\\.demo_login'\\)"
---

<objective>
Add a DEV-ONLY demo login to the client-portal page `/auth/magic`. Two buttons — "Démo Client" and "Démo Admin" — visible and functional ONLY when `config('app.demo_login')` (env `DEMO_LOGIN_ENABLED`) is true.

Purpose: On Laravel Cloud every environment runs `APP_ENV=production`, so env-name gating is impossible. The ONLY gate is the config flag. Real production simply never receives `DEMO_LOGIN_ENABLED`. This lets reviewers explore the client portal and admin without a magic-link round-trip on staging.

Output: config flag, a flag-gated controller that lazily + idempotently provisions demo accounts, two POST routes, a flag-gated view block, and a Pest feature test.
</objective>

<execution_context>
@$HOME/.claude/get-shit-done/workflows/execute-plan.md
@$HOME/.claude/get-shit-done/templates/summary.md
</execution_context>

<context>
@.planning/STATE.md
@./CLAUDE.md

<interfaces>
<!-- Verified from codebase — use directly, no exploration needed. -->

Guards (config/auth.php): web → User model (Fortify), clients → Client model (magic link).
Admin dashboard route name: `admin.dashboard` (prefix `admin.` + `dashboard` in routes/admin.php).
Client portal landing route name: `portail.passages`.

Client $fillable: uuid, name, email, phone, address, notes, magic_link_token, magic_link_expires_at.
  Client::piscines(): HasMany, Client::passages(): HasMany.

Piscine $fillable: client_id, nom, volume_m3, type, filtration, traitement, equipements, notes.
  Realistic factory values: nom 'Piscine principale', type ∈ enterrée/hors-sol/spa,
  filtration ∈ sable/cartouche/diatomées, traitement ∈ chlore/sel/brome. volume_m3 decimal:2.

Passage $fillable: client_uuid, piscine_id, client_id, visited_at, status, ph_avant, ph_apres,
  chlore_libre, chlore_total, tac, th, sel_g_l, actions (array cast), notes, pdf_path, signature_path, synced_at.

User: only name/email/password are fillable. email_verified_at must be set via forceFill (see AdminSeeder pattern).

MagicLinkController.logout pattern (guard usage): Auth::guard('clients')->login(...); $request->session()->regenerate();
Controllers extend App\Http\Controllers\Controller and return typed responses (RedirectResponse/View). French comments are the house style.
</interfaces>
</context>

<tasks>

<task type="auto">
  <name>Task 1: Add demo_login feature flag to config/app.php</name>
  <files>config/app.php</files>
  <action>Add `'demo_login' => env('DEMO_LOGIN_ENABLED', false),` to the returned config array. Place it in a clearly labelled block near the top app settings (e.g. just after the `'env'` / `'debug'` entries) with a short comment block in the existing house style noting: this gates the DEV-ONLY demo login on /auth/magic; on Laravel Cloud every env is APP_ENV=production, so the flag (not env name) is the sole gate; real production never sets DEMO_LOGIN_ENABLED. Default MUST be false so the feature is off unless explicitly enabled.</action>
  <verify>
    <automated>php artisan tinker --execute="echo config('app.demo_login') === false ? 'OK-default-false' : 'FAIL';"</automated>
  </verify>
  <done>`config('app.demo_login')` resolves to false by default and reads `DEMO_LOGIN_ENABLED` when set. A labelled comment explains the dev-only purpose.</done>
</task>

<task type="auto" tdd="true">
  <name>Task 2: Create DemoLoginController with flag-gated, idempotent provisioning</name>
  <files>app/Http/Controllers/Portail/DemoLoginController.php</files>
  <behavior>
    - With flag false, every public action aborts 404 (gate is the FIRST line).
    - client(): provisions demo-client@dloazur.test idempotently; on first run creates 1 Piscine + 4 synced Passages; logs in via guard 'clients'; regenerates session; redirects to portail.passages.
    - admin(): provisions demo-admin@dloazur.test idempotently; sets email_verified_at if null; logs in via guard 'web'; regenerates session; redirects to admin.dashboard.
    - Second call to client() creates NO duplicate client, piscine, or passages.
  </behavior>
  <action>Create `app/Http/Controllers/Portail/DemoLoginController.php` in namespace App\Http\Controllers\Portail, extending App\Http\Controllers\Controller. Import: Client, Piscine, Passage, User models; Illuminate\Http\{Request, RedirectResponse}; Auth, Hash, Str facades. French comments matching MagicLinkController/AdminSeeder style.

  Each public action's FIRST executable line: `abort_unless((bool) config('app.demo_login'), 404);`

  Method `client(Request $request): RedirectResponse`:
  - `$client = Client::firstOrCreate(['email' => 'demo-client@dloazur.test'], ['uuid' => (string) Str::uuid(), 'name' => 'Démo Client', 'phone' => '0696 00 00 00', 'address' => 'Lagon démo, Martinique']);`
  - If `$client->piscines()->doesntExist()` call a private helper `seedDemoData($client)` (the doesntExist guard makes provisioning happen exactly once).
  - `Auth::guard('clients')->login($client);` then `$request->session()->regenerate();` then `return redirect()->route('portail.passages');`

  Private `seedDemoData(Client $client): void`:
  - Create one Piscine via `Piscine::create([...])` with client_id = $client->id, nom 'Piscine principale', volume_m3 32, type 'enterrée', filtration 'sable', traitement 'chlore'.
  - Create 4 Passages via `$piscine->passages()->create([...])` (or Passage::create with explicit ids). For each: client_id = $client->id, piscine_id = $piscine->id, client_uuid = $client->uuid, status 'synced', synced_at = now(), visited_at = now()->subWeeks(0/2/4/6) across the four rows, realistic water values (ph_avant ~7.0–7.4, ph_apres ~7.2, chlore_libre ~1.2–1.8, tac ~80–120), French notes (e.g. 'Nettoyage filtre, contrôle pH', 'Traitement choc préventif'). Use only Passage $fillable fields.

  Method `admin(Request $request): RedirectResponse`:
  - `$user = User::firstOrCreate(['email' => 'demo-admin@dloazur.test'], ['name' => 'Démo Admin', 'password' => Hash::make(Str::random(32))]);`
  - If `$user->email_verified_at === null` then `$user->forceFill(['email_verified_at' => now()])->save();` (mirror AdminSeeder — only name/email/password are fillable).
  - `Auth::guard('web')->login($user);` then `$request->session()->regenerate();` then `return redirect()->route('admin.dashboard');`

  Do NOT gate on app environment. Do NOT add or modify any seeder. No fenced code in production — directive only.</action>
  <verify>
    <automated>php -l app/Http/Controllers/Portail/DemoLoginController.php</automated>
  </verify>
  <done>Controller file exists, lints clean, each public action gates on the flag first, provisioning is idempotent via firstOrCreate + doesntExist guard, and both methods log in on the correct guard and redirect to the right route.</done>
</task>

<task type="auto">
  <name>Task 3: Register routes and add the flag-gated demo block to the view</name>
  <files>routes/portail.php, resources/views/portail/magic-link-request.blade.php</files>
  <action>routes/portail.php — add `use App\Http\Controllers\Portail\DemoLoginController;` to the imports. Register the two routes OUTSIDE the `guest:clients` group (so the admin demo works even if a client session exists; these are not guest-only) and OUTSIDE the `auth:clients` group — at top level of the file (the file is registered under `web` middleware in bootstrap/app.php):
    Route::post('/auth/demo/client', [DemoLoginController::class, 'client'])->name('portail.demo.client');
    Route::post('/auth/demo/admin', [DemoLoginController::class, 'admin'])->name('portail.demo.admin');
  Add a short French comment marking this as the dev-only demo login block.

  resources/views/portail/magic-link-request.blade.php — insert AFTER the status/error block that follows the magic-link `<form>` (i.e. after the `@error('throttle')` block, line ~69, still inside the white card `<div>` ending line 71) and BEFORE the closing `</div>` of the card / the WhatsApp fallback. Wrap the whole insertion in `@if (config('app.demo_login')) ... @endif`:
  - A subtle divider labelled "Serveur de démo": e.g. a flex row with two `h-px flex-1 bg-sand-200` hairlines around a `text-xs uppercase tracking-wide text-ink-400` label, margin-top ~mt-6.
  - A vertical stack (`flex flex-col gap-3 mt-4`) of two `<form method="POST">`, each with `@csrf`:
    * action `{{ route('portail.demo.client') }}` — primary button: `w-full h-12 rounded-xl bg-azure-500 text-white font-bold hover:bg-azure-600 transition-colors`, label "Démo Client".
    * action `{{ route('portail.demo.admin') }}` — secondary button: `w-full h-12 rounded-xl bg-white text-navy-900 font-semibold ring-1 ring-navy-900/15 hover:bg-sand-50 transition-colors`, label "Démo Admin".
  Reuse only tokens already present in the file (rounded-xl, ring, ink-/azure-/navy-/sand-). Keep it clean and on-brand.</action>
  <verify>
    <automated>php artisan route:list --path=auth/demo</automated>
  </verify>
  <done>`php artisan route:list --path=auth/demo` lists both POST routes named portail.demo.client and portail.demo.admin. The view renders the demo block only when the flag is true, with two on-brand stacked buttons posting to the correct routes.</done>
</task>

<task type="auto" tdd="true">
  <name>Task 4: Pest feature test for gate, both logins, and idempotence</name>
  <files>tests/Feature/DemoLoginTest.php</files>
  <behavior>
    - Flag OFF: POST portail.demo.client → 404; POST portail.demo.admin → 404.
    - Flag ON, client: redirect to portail.passages; assertAuthenticated('clients'); demo client exists with >=1 piscine and >=1 passage.
    - Flag ON, admin: redirect to admin.dashboard; assertAuthenticated('web'); demo admin exists with email_verified_at not null.
    - Idempotence: two client logins → exactly 1 Client with email demo-client@dloazur.test and no duplicated piscines/passages.
  </behavior>
  <action>Create `tests/Feature/DemoLoginTest.php` using Pest syntax with `uses(RefreshDatabase::class);`. Import Client, Piscine, Passage, User models.

  Tests:
  - it('returns 404 when flag is off for client and admin'): `config(['app.demo_login' => false]);` then `$this->post(route('portail.demo.client'))->assertNotFound();` and same for admin. (CSRF middleware is disabled by default in feature tests; if a token error appears, wrap with `withoutMiddleware`.)
  - it('logs in the demo client and provisions data when flag on'): `config(['app.demo_login' => true]);` POST client → `assertRedirect(route('portail.passages'))`; `$this->assertAuthenticated('clients');` assert `Client::where('email','demo-client@dloazur.test')->exists()`, the client has >=1 piscine and `Passage::count() >= 1` for that client.
  - it('logs in the demo admin when flag on'): flag true; POST admin → `assertRedirect(route('admin.dashboard'))`; `assertAuthenticated('web')`; assert User demo-admin@dloazur.test exists and email_verified_at is not null.
  - it('is idempotent for the demo client'): flag true; POST client twice (re-resolve the app between calls if needed for a fresh session — or just call post twice); assert `Client::where('email','demo-client@dloazur.test')->count() === 1` and that piscines/passages are not duplicated (piscine count for the client === 1, passage count === 4).</action>
  <verify>
    <automated>vendor/bin/pest tests/Feature/DemoLoginTest.php</automated>
  </verify>
  <done>`vendor/bin/pest tests/Feature/DemoLoginTest.php` passes: 404 gate, both guard logins + redirects, provisioning, and idempotence all green.</done>
</task>

</tasks>

<threat_model>
## Trust Boundaries

| Boundary | Description |
|----------|-------------|
| browser → /auth/demo/* | Unauthenticated POST that grants an authenticated session |

## STRIDE Threat Register

| Threat ID | Category | Component | Disposition | Mitigation Plan |
|-----------|----------|-----------|-------------|-----------------|
| T-qac-01 | Elevation of Privilege | /auth/demo/admin grants guard 'web' (admin) | mitigate | `abort_unless(config('app.demo_login'), 404)` first line of every action; default flag false; real prod never sets DEMO_LOGIN_ENABLED |
| T-qac-02 | Spoofing | Demo accounts use fixed *.dloazur.test emails | accept | Non-routable .test TLD; accounts only exist where the flag is on (staging); admin password is random 32 chars, never disclosed |
| T-qac-03 | Tampering | Repeated POSTs could duplicate data / inflate DB | mitigate | firstOrCreate keyed on email + `piscines()->doesntExist()` guard make provisioning idempotent |
| T-qac-SC | Tampering | npm/composer installs | accept | No new dependencies introduced by this plan |
</threat_model>

<verification>
- `config('app.demo_login')` defaults to false (Task 1).
- `php artisan route:list --path=auth/demo` shows both named POST routes (Task 3).
- `vendor/bin/pest tests/Feature/DemoLoginTest.php` is green (Task 4).
- Manual (staging only, flag on): /auth/magic shows the "Serveur de démo" block; "Démo Client" lands on the passages timeline with seeded data; "Démo Admin" lands on the admin dashboard.
- With the flag off, the buttons are absent and POSTing the routes returns 404.
</verification>

<success_criteria>
- DEV-ONLY demo login is gated solely by `config('app.demo_login')` (no env-name gating).
- Demo client + admin accounts are provisioned lazily by the controller, idempotently — no seeder added, DatabaseSeeder untouched.
- Client login lands on `portail.passages` with 1 piscine + 4 synced passages; admin login lands on `admin.dashboard` pre-verified.
- All Pest tests pass; routes registered; view block on-brand and flag-gated.
- No changes that could affect the legacy prod Zyro site.
</success_criteria>

<output>
Create `.planning/quick/260529-qac-demo-login-auth-magic/260529-qac-SUMMARY.md` when done
</output>
