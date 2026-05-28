---
phase: 01-vitrine-fondations
plan: 04
subsystem: blog, contact-form, google-reviews
tags: [blog, markdown, livewire, mail, honeypot, rate-limit, google-places, scheduler, sitemap, seo]
dependency_graph:
  requires: [01-01]
  provides: [blog-routes, contact-form, google-reviews-cache, sitemap-blog-seam, testimonials-partial]
  affects: [01-03, 01-06]
tech_stack:
  added:
    - spatie/yaml-front-matter (blog YAML parsing)
    - spatie/laravel-markdown with html_input=strip (XSS safe_mode)
    - spatie/laravel-honeypot UsesSpamProtection trait (Livewire integration)
    - spatie/schema-org Article JSON-LD
    - danharrin/livewire-rate-limiting WithRateLimiting trait
    - Laravel Http facade → Google Places Details API
  patterns:
    - BlogRepository: singleton, optional $dir ctor for test fixture override, Cache::remember skipped in testing env
    - ContactForm: WithRateLimiting + UsesSpamProtection + HoneypotData, #[Validate] attributes, Livewire submit() flow
    - GoogleReviewsService: fetchAndUpsert (updateOrCreate fingerprint) + latestFiltered/averageRating/totalCount
    - cross-plan guard: class_exists(\App\Livewire\GoogleReviews::class) in testimonials.blade.php
key_files:
  created:
    - app/Support/BlogRepository.php
    - app/Providers/BlogServiceProvider.php
    - app/Http/Controllers/BlogController.php
    - app/Http/Controllers/SitemapController.php
    - app/Livewire/ContactForm.php
    - app/Livewire/GoogleReviews.php
    - app/Mail/ContactMessage.php
    - app/Models/GoogleReview.php
    - app/Services/GoogleReviewsService.php
    - app/Console/Commands/SyncGoogleReviewsCommand.php
    - config/contact.php
    - config/google-reviews.php
    - config/markdown.php
    - database/migrations/2026_05_28_000010_create_google_reviews_table.php
    - resources/content/blog/2026-05-bienvenue-dlo-azur.md
    - resources/views/blog/index.blade.php
    - resources/views/blog/show.blade.php
    - resources/views/emails/contact-message.blade.php
    - resources/views/livewire/contact-form.blade.php
    - resources/views/livewire/google-reviews.blade.php
    - resources/views/vitrine/contact.blade.php
    - resources/views/vitrine/partials/testimonials.blade.php
    - tests/Feature/BlogTest.php
    - tests/Feature/ContactFormTest.php
    - tests/Feature/GoogleReviewsServiceTest.php
    - tests/Feature/GoogleReviewsComponentTest.php
    - tests/fixtures/blog/test-post.md
    - tests/fixtures/google-places-response.json
  modified:
    - bootstrap/providers.php (BlogServiceProvider added)
    - routes/blog.php (blog.index + blog.show routes)
    - routes/vitrine.php (/contact route added)
    - routes/web.php (sitemap.xml route added)
    - routes/console.php (Schedule::command reviews:sync daily 04:30 UTC)
    - phpunit.xml (APP_BASE_PATH, HONEYPOT_SECONDS=0, HONEYPOT_VALID_FROM_TIMESTAMP=false, HONEYPOT_RANDOMIZE=false)
decisions:
  - "GoogleReview model + migration created in Plan 04 (not Plan 02) — Plan 02 hasn't shipped yet and Task 3 requires the table for test isolation via RefreshDatabase"
  - "HONEYPOT_RANDOMIZE=false added to phpunit.xml so honeypot trip test can set extraFields.my_name deterministically"
  - "SitemapController created in Plan 04 (deviation) — required by BlogTest Test 8 sitemap-blog seam which runs in the same suite; no separate plan for it since it's 15 lines"
  - "config/markdown.php published and set html_input=strip, code_highlighting off (irrelevant for pisciniste blog)"
  - "TestCase in XSS test uses app()->instance(BlogRepository::class, $repo) override to point to tests/fixtures/blog — no Storage::fake needed"
metrics:
  duration: "~4 hours (including worktree setup + Pest rootPath debugging from previous session)"
  completed_date: "2026-05-28"
  tasks: 3
  files: 29
---

# Phase 01 Plan 04: Blog + Contact Form + Google Reviews Summary

Blog markdown-in-repo (SITE-04) + Contact form Livewire+Brevo+honeypot+rate-limit (SITE-05) + Google Reviews server-side cache (D-28 amended), all green-lit by 45/45 Pest tests.

## What Was Built

### Task 1 + 2 — Blog + Contact Form (SITE-04/SITE-05)

**Blog (SITE-04)**
- `BlogRepository` parses `resources/content/blog/*.md` via `spatie/yaml-front-matter`. Singleton registered in `BlogServiceProvider` so `SitemapController` (Plan 03) can auto-detect blog URLs via `app()->bound(BlogRepository::class)`.
- `BlogController::show()` builds `Schema::article()` JSON-LD (headline, datePublished, author "Pierre ADAM"). Route constraint `[a-z0-9-]+` prevents path traversal (T-4-09).
- `spatie/laravel-markdown` with `html_input=strip` — Test 7 (`assertDontSee('alert(1)')`) is the regression guard for T-4-04.
- Inaugural article at `resources/content/blog/2026-05-bienvenue-dlo-azur.md`.
- French long dates via Carbon `isoFormat('LL')`.
- `SitemapController` emits `/`, `/blog`, `/contact` + all blog post URLs.

**Contact Form (SITE-05)**
- `ContactForm` Livewire component: `WithRateLimiting (5/60s)` + `UsesSpamProtection` + `#[Validate]` attributes + `HoneypotData $extraFields`.
- `submit()` flow: rateLimit → protectAgainstSpam → validate → Mail::to → $sent=true.
- `ContactMessage` Mailable: replyTo the submitter's address, Blade template with `{{ }}` auto-escape everywhere (T-4-05).
- WhatsApp fallback `https://wa.me/596696940054` visible in form view per D-16.
- `config/contact.php`: recipient, whatsapp_url, rate_limit params.
- Mail driver: `log` (local dev), `array` (tests), `brevo` (production per D-15 RGPD).

### Task 3 — Google Reviews (D-28 amended)

- `GoogleReview` model + migration `000010` (columns: google_review_id unique fingerprint, author_name/url, profile_photo_url, rating, comment, relative_time_description, language, reviewed_at, fetched_at; composite index on rating+reviewed_at).
- `GoogleReviewsService::fetchAndUpsert()` calls Google Places Details API (`language=fr`), upserts via `author_url#time` fingerprint — idempotent. Returns 0 and logs error on any failure without throwing into the user request path (T-4-14).
- `SyncGoogleReviewsCommand` (`reviews:sync`): calls `fetchAndUpsert()`, logs count + duration, exit 0 always.
- Scheduler: `Schedule::command('reviews:sync')->dailyAt('04:30')->withoutOverlapping()->onOneServer()`.
- `GoogleReviews` Livewire component: `$hidden=true` if config disabled OR table empty. When visible: renders avg rating with French decimal comma (`number_format($avg, 1, ',', ...)`), ≤5 most recent ≥4★ reviews, "Voir tous les avis" link to `business_url`.
- `google-reviews.blade.php`: no `<script>`, no `<iframe>`, `referrerpolicy="no-referrer"`, `loading="lazy"` on avatars (T-4-13 RGPD posture).
- `config/google-reviews.php`: `enabled` auto-derives from `filled(api_key) && filled(place_id)`.

## Cross-Plan Seams Closed

| Seam | Status |
|------|--------|
| Plan 03 SitemapController detects blog via `app()->bound(BlogRepository::class)` | Closed — BlogServiceProvider singleton registered |
| Plan 03 `contact.blade.php` `@if(class_exists)` guard removed | Closed — ContactForm exists, guard removed from the view |
| Plan 03 `testimonials.blade.php` guard for `<livewire:google-reviews />` | Closed — partial created here with `class_exists(\App\Livewire\GoogleReviews::class)` guard |

## Mail Driver State Per Environment

| Environment | Driver | Notes |
|-------------|--------|-------|
| Local dev | `log` | Emails written to `storage/logs/laravel.log` |
| Testing | `array` | `Mail::fake()` overrides; `phpunit.xml` baseline is `MAIL_MAILER=array` |
| Production | `brevo` | Brevo (Paris, FR) per D-15 RGPD; BREVO_API_KEY in Laravel Cloud Secrets only (T-4-10) |

## Test Results

- BlogTest: 8/8 passing
- ContactFormTest: 8/8 passing
- GoogleReviewsServiceTest: 6/6 passing
- GoogleReviewsComponentTest: 5/5 passing
- **Total: 45/45 passing** (all existing tests maintained)

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 3 - Blocking] GoogleReview model + migration created in Plan 04**
- **Found during:** Task 3 setup
- **Issue:** Plan 02 (which owns google_reviews table per `key_links`) hasn't run yet. Task 3 tests require RefreshDatabase with the migration in place.
- **Fix:** Created `GoogleReview` model and `2026_05_28_000010_create_google_reviews_table.php` migration directly in Plan 04. Plan 02's migration slot (`000010`) is reserved — when Plan 02 ships, it must either reuse or supersede this migration.
- **Files modified:** `app/Models/GoogleReview.php`, `database/migrations/2026_05_28_000010_create_google_reviews_table.php`
- **Commit:** `4566580`

**2. [Rule 3 - Blocking] HONEYPOT_RANDOMIZE=false added to phpunit.xml**
- **Found during:** Task 2 GREEN (honeypot trip test)
- **Issue:** With `HONEYPOT_VALID_FROM_TIMESTAMP=false` (already set), the only spam check remaining is the name field being non-empty. The test couldn't set the honeypot name field deterministically because randomization made the field name unpredictable in tests.
- **Fix:** Added `HONEYPOT_RANDOMIZE=false` to `phpunit.xml` so the field stays `my_name`. Updated test to set `extraFields.my_name` to `'I am a bot'`.
- **Files modified:** `phpunit.xml`, `tests/Feature/ContactFormTest.php`
- **Commit:** `7284036`

**3. [Rule 2 - Deviation] SitemapController created in Plan 04 (not listed in plan files_modified)**
- **Found during:** Task 1 (BlogTest Test 8 `sitemap.xml includes blog URLs`)
- **Issue:** BlogTest Test 8 asserts `/sitemap.xml` includes `/blog` URLs. Plan 03 `SitemapController` depends on this singleton detection seam being closed — but Plan 03 itself hadn't run in this worktree. Creating a minimal SitemapController here (15 lines) closes the seam and makes all 8 BlogTests pass.
- **Fix:** Created `app/Http/Controllers/SitemapController.php` + `resources/views/sitemap.blade.php` + registered route in `routes/web.php`.
- **Files modified:** `app/Http/Controllers/SitemapController.php`, `resources/views/sitemap.blade.php`, `routes/web.php`
- **Commit:** `7284036`

**4. [Rule 1 - Bug] compact() undefined $hidden in GoogleReviews::render()**
- **Found during:** Task 3 GREEN first test run
- **Issue:** Used `compact('reviews', ..., 'hidden')` but `$hidden` was `$this->hidden` (property), not a local variable.
- **Fix:** Replaced `compact()` call with explicit array `['reviews' => $reviews, ..., 'hidden' => false]`.
- **Files modified:** `app/Livewire/GoogleReviews.php`
- **Commit:** `4566580`

## Known Stubs

None. All components have wired data sources:
- `BlogRepository` reads real markdown files from `resources/content/blog/`
- `ContactForm` sends real mail via `Mail::to(config('contact.recipient'))->send()`
- `GoogleReviews` queries real `google_reviews` DB table (auto-masks if empty)

## Threat Flags

None. All T-4-* threats from the plan's threat register are mitigated as designed. No new attack surface was introduced beyond what the plan specified.

## Self-Check

- [x] `app/Support/BlogRepository.php` exists
- [x] `app/Providers/BlogServiceProvider.php` exists
- [x] `app/Http/Controllers/BlogController.php` exists
- [x] `app/Livewire/ContactForm.php` exists
- [x] `app/Services/GoogleReviewsService.php` exists
- [x] `app/Livewire/GoogleReviews.php` exists
- [x] `resources/views/vitrine/partials/testimonials.blade.php` exists with class_exists guard
- [x] `config/google-reviews.php` exists
- [x] `database/migrations/2026_05_28_000010_create_google_reviews_table.php` exists
- [x] 45/45 tests passing

## Self-Check: PASSED
