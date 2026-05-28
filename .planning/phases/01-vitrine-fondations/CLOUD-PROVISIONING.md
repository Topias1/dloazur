# Cloud Provisioning — Plan 01 Walking Skeleton

> Step-by-step handover for the human-only setup that has no CLI/API substitute (account creation, DNS verification). Once these are done, `git push origin main` triggers Laravel Cloud auto-deploy and the operator visits the staging URL to confirm the page loads.
>
> **Context:** Plan 01 Task 2-4 produced a Laravel 13 skeleton that runs locally and passes Pest CI (PostgreSQL **17** + PHP **8.4** — see "Stack reality" note below). Task 5 closes Phase 1 by wiring the cloud edge.
>
> **Stack reality (amended 2026-05-28 during Task 5):**
> - Laravel Cloud Neon-managed Postgres only offers 17/18 (no 16 — original plan), so the staging cluster runs **Postgres 17** (`misty-bird-14300504`, region `eu-central-1`, Dev tier ¼ vCPU / hibernate 300s).
> - composer.lock pinned Symfony v8 + Spatie packages require PHP **8.4** minimum; CI runner upgraded from 8.3 → 8.4 (`.github/workflows/tests.yml`). Laravel Cloud runtime is PHP 8.5 (further ahead, safe).
> - The Postgres resource was **attached out-of-band via the Laravel Cloud API** (env-vars set via `POST /api/environments/{env}/variables` with `method: append`) because the dashboard "Attach resource" flow didn't execute the link during Postgres creation. Stop+Start cycle on `/api/environments/{env}/{stop,start}` triggered the deploy that picked up the new env vars.

---

## 0. Inventory — what needs to exist before push

| Service | Plan | Cost | Region | Purpose |
|---------|------|------|--------|---------|
| Laravel Cloud | Hobby (free $5/14d, then ~4-7€/mo) | EU Central (Frankfurt) | App hosting + managed Postgres 17 (Dev tier, hibernate 300s) + scale-to-zero |
| Cloudflare R2 | Free tier (10 GB + zero egress) | EU (auto, fr-par class) | S3-compatible object storage for photo uploads (Phase 2) |
| Brevo (ex-Sendinblue) | Free (300 emails/day) | Paris, FR (only region) | Transactional mail — contact form (Plan 04) |
| GitHub | Existing | — | Repo `Topias1/dloazur` source of truth, auto-deploy hook |

**Budget total Phase 1 envelope:** ~5-7€/month once free credits expire (Laravel Cloud only — R2 + Brevo stay free at expected volume).

---

## 1. Laravel Cloud — staging project

### 1.1 Create account + project
1. Sign up at https://cloud.laravel.com (Pierre's GitHub handle for SSO).
2. **New Project** → name `dlo-azur-piscines` → region **EU Central (Frankfurt)** → click *Create*.
3. **Resources** → *Add Postgres* → instance class smallest available (Hobby tier).
4. **Environments** → confirm `production` exists (Laravel Cloud creates it by default; we treat it as *staging* until DNS cutover in Plan 06).

### 1.2 Connect GitHub
1. **Environments → production → Source** → *Connect GitHub*.
2. Authorize the Laravel Cloud GitHub App on `Topias1/dloazur`.
3. Branch: `main` → enable *Auto-deploy on push*.

### 1.3 Build + deploy commands
Set under **Environment → Build** (verbatim — copy-paste):

```
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

Set under **Environment → Deploy** (post-build, pre-traffic-switch):

```
php artisan migrate --force
# Plan 05 will add: php artisan db:seed --class=PierreSeeder --force
```

### 1.4 Secrets (Environment → Secrets)
Generate `APP_KEY` locally first: `php artisan key:generate --show` then paste.

| Key | Value | Source |
|-----|-------|--------|
| `APP_KEY` | `base64:…` (32-byte) | local `php artisan key:generate --show` |
| `APP_ENV` | `production` | static |
| `APP_DEBUG` | `false` | static (T-1-01 mitigation) |
| `APP_URL` | `https://<assigned>.laravel.cloud` (or `https://preprod.dloazurpiscines.com` if DNS already set) | Laravel Cloud project URL |
| `LOG_CHANNEL` | `stderr` | Logs land in Laravel Cloud dashboard, never response body (T-1-01) |
| `MAIL_MAILER` | `brevo` (after step 3 DNS verified) or `log` (until then) | switch when Brevo Active |
| `BREVO_API_KEY` | from Brevo dashboard (step 3) | https://app.brevo.com → SMTP & API → API Keys |
| `R2_ACCESS_KEY_ID` | from Cloudflare R2 API token (step 2) | Cloudflare dashboard |
| `R2_SECRET_ACCESS_KEY` | from Cloudflare R2 API token (step 2) | Cloudflare dashboard (shown once) |
| `R2_BUCKET` | `dlo-azur-staging` | matches bucket created in step 2 |
| `R2_ACCOUNT_ID` | `fr-par` | static (we use the fr-par class) |
| `R2_ENDPOINT` | `https://r2.cloudflarestorage.com` | static |
| `OPERATOR_NAME` | `Pierre ADAM` | static |
| `OPERATOR_EMAIL` | `pierre@dloazurpiscines.com` | static (used by Plan 05 PierreSeeder) |
| `OPERATOR_INITIAL_PASSWORD` | strong random value (placeholder until Plan 05) | generate locally, store in 1Password |
| `CONTACT_RECIPIENT` | `contact@dloazurpiscines.com` | static (Plan 04 contact form destination) |

> **Never** commit any of these to the repo. The `.env` file is `.gitignore`d; only `.env.example` ships.

---

## 2. Cloudflare R2 — object storage bucket

### 2.1 Create account
1. Sign up at https://dash.cloudflare.com (free tier).
2. Confirm email.

### 2.2 Create bucket
1. **R2 Object Storage** (sidebar) → *Create bucket*.
2. Name: `dlo-azur-staging`.
3. Location: **auto** (Cloudflare picks the nearest EU datacenter — we don't pin a specific region because the free fr-par class doesn't expose pinning; data still lives EU per Cloudflare's data-locality commitments).
4. Visibility: **private** (no public read).
5. *Create bucket*.

### 2.3 Create API token (scope: R2 read+write on the bucket)
1. **R2 → Manage R2 API Tokens** → *Create API token*.
2. Name: `dlo-azur-staging-app`.
3. Permissions: **Object Read & Write**.
4. Specify bucket: `dlo-azur-staging` (do not give blanket account access).
5. *Create* → copy the **Access Key ID** and **Secret Access Key** (the secret is shown once — store in 1Password, then paste into Laravel Cloud secrets as `R2_ACCESS_KEY_ID` + `R2_SECRET_ACCESS_KEY`).

> Phase 1 wires the disk only. No upload runs until Phase 2 (mobile passage capture).

---

## 3. Brevo (Paris, FR) — transactional mail + DNS verification

### 3.1 Create account
1. Sign up at https://www.brevo.com (Free plan — 300 emails/day).
2. Region: Brevo is Paris-FR by default — no region toggle.

### 3.2 Verify sender domain `dloazurpiscines.com`
1. **Senders & IP → Domains** → *Add a domain* → enter `dloazurpiscines.com`.
2. Brevo displays SPF, DKIM, and DMARC records to add to your DNS provider.
3. **DNS provider:** Hostinger (per user memory). Log in, find the DNS zone editor for `dloazurpiscines.com`, and add the records Brevo shows:
   - `TXT` SPF record (something like `v=spf1 include:spf.brevo.com mx ~all`)
   - `TXT` DKIM record (the selector + public key Brevo provides)
   - `TXT` DMARC record (recommended, even if `p=none` initially)
4. Wait for Brevo to verify (typically <30 min, occasionally up to 24h).
5. Status must read **Active** before flipping `MAIL_MAILER=brevo` in Laravel Cloud.

> **Blocking dependency:** Plan 04 (SITE-05 contact form) **cannot send real email until this verification is Active**. Until then keep `MAIL_MAILER=log` in Laravel Cloud secrets and the contact form will log payloads to the dashboard instead of sending. This is a known acceptable degradation for Phase 1; Plan 04 documents the cutover.

### 3.3 Create API key
1. **SMTP & API → API Keys** → *Create a new API key* → name `dlo-azur-staging`.
2. Copy the key (shown once) → paste into Laravel Cloud secrets as `BREVO_API_KEY`.

---

## 4. Push + first deploy

1. **Locally**, verify everything is committed:
   ```
   git status
   git log --oneline -5
   ```
2. Push the Plan 01 commits to `main`:
   ```
   git push origin main
   ```
3. **Laravel Cloud** dashboard → *Environments → production → Deployments* → watch the build + deploy logs. First deploy will run `php artisan migrate --force` and create the default Laravel tables (users, sessions, cache, jobs, plus Fortify scaffolding from Plan 05).
4. **Verify staging URL** (the URL Laravel Cloud assigns, e.g., `https://dloazur-staging.laravel.cloud`):
   - `https://<url>/` → 200 + placeholder Blade view styled in OKLCH (sand-50 background, Fredoka wordmark in ink-950, azure-500 WhatsApp CTA).
   - Page title in browser tab: `Dlo Azur Piscines · Entretien de piscines en Martinique`.
   - `https://<url>/up` → `{"app":"ok","db":"ok"}` with HTTP 200.
   - View source → confirm `<html lang="fr">`, `<meta name="theme-color" content="#0080ff">`, Vite-emitted stylesheet link, at least one `wa.me/596696940054` href.

5. **Capture the live staging URL** — paste it into `.planning/phases/01-vitrine-fondations/01-01-SUMMARY.md` under the "Live staging URL" section so Wave 2 plans can reference it for their own verifications.

---

## 5. Smoke-test cheatsheet

```
# Health check (machine-readable)
curl -fsSL https://<url>/up | jq

# Home page (should return 200 + HTML)
curl -fsSLI https://<url>/

# Full page render (visual)
open https://<url>/

# Page source verification
curl -fsSL https://<url>/ | grep -E '(lang="fr"|wa.me/596696940054|theme-color|build/assets/app-)'
```

---

## 6. Rollback / blockers

If any step fails, capture the exact error message and respond with `blocked: <reason>` so the next Plan 01 spawn can either re-run the gate or adjust the provisioning recipe.

Common blockers:
- **DNS propagation slow** → mail temporarily in `log` mode, continue with rest of stack; Brevo "Active" status lights up later.
- **Cloudflare R2 quota** → free tier is 10 GB total + zero egress, plenty for Phase 1.
- **Laravel Cloud build OOM** → composer's autoload optimization needs ~512 MB; Hobby tier has 1 GB, should be fine.
- **`php artisan migrate --force` exits 1** → check Postgres resource is attached AND `DB_CONNECTION=pgsql` is *not* manually set as a secret (Laravel Cloud auto-injects it from the attached resource).
