# Handoff — Real Google reviews (5-star only) on the vitrine

**For:** `/gsd:quick` in a fresh session (run after `/clear`).
**Goal:** Replace the fake-testimonials placeholder on the vitrine with **real Google Maps reviews, filtered to 5-star only**.
**Branch:** `staging` (current). Do NOT touch prod (`main`) — staging only unless Antoine says otherwise.

## TL;DR — the pipeline already exists

There is a complete Google Reviews feature already built (Plan 04). This task is **wiring + config + retiring a placeholder**, not new code. Files:

| File | Role |
|------|------|
| `config/google-reviews.php` | env-driven config (api_key, place_id, min_rating, home_limit, business_url, `enabled`) |
| `app/Services/GoogleReviewsService.php` | `fetchAndUpsert()` (Places API), `latestFiltered($limit,$minRating)` = `where('rating','>=',$minRating)`, `averageRating()`, `totalCount()` |
| `app/Console/Commands/SyncGoogleReviewsCommand.php` | `php artisan reviews:sync` |
| `app/Models/GoogleReview.php` + migration `..._000010_create_google_reviews_table.php` | storage (`rating` int, `comment`, `author_name`, `profile_photo_url`, `reviewed_at`, …) |
| `app/Livewire/GoogleReviews.php` + `resources/views/livewire/google-reviews.blade.php` | display; self-hides if `!config('google-reviews.enabled')` OR `totalCount()===0` |
| `resources/views/vitrine/partials/testimonials.blade.php` | included by `resources/views/vitrine/home.blade.php:28`; currently shows a **placeholder** + conditionally `<livewire:google-reviews />` |
| `tests/Feature/GoogleReviewsServiceTest.php`, `GoogleReviewsComponentTest.php`, `tests/fixtures/google-places-response.json` | existing coverage (5 seeded reviews → "Google reviews synced" log) |

## The change set

### 1. Show only 5-star (the literal ask) — one line
Filtering already exists (`latestFiltered` uses `rating >= min_rating`). Set:
```
GOOGLE_REVIEWS_MIN_RATING=5
```
`config/google-reviews.php:20` defaults to 4. Setting the env to `5` ⇒ only 5-star shown. No code change needed for the filter itself.

### 2. Retire the fake placeholder in `testimonials.blade.php`
Remove the placeholder block (lines 15–18: `[Avis à fournir par Pierre…]`) so the section relies solely on the real `<livewire:google-reviews />`. Note the component **self-hides** when disabled/empty, so the whole section would vanish with no reviews — decide the empty fallback:
- **Option A (recommended):** keep the section heading + a soft "Voir nos avis Google" CTA to `business_url` even when 0 reviews, so the section never collapses to nothing.
- Option B: let the section disappear entirely when there are no 5-star reviews yet.

Also drop the hardcoded 5 `<x-icon.star>` (lines 6–12) if it should reflect the **real** average instead (the component already computes `averageRating()`); decide whether the section header stars come from the component or stay decorative.

### 3. Provision real data (the load-bearing decision — needs Antoine)
The component is `enabled` only when BOTH are set (`config/google-reviews.php:36`):
- `GOOGLE_PLACES_API_KEY` — **Google Cloud Places API key with billing enabled.** This is a vendor/cost commitment (low volume ≈ free tier, but requires a GCP billing account + key). Store in **Laravel Cloud Secrets**, never commit (T-4-11).
- `GOOGLE_PLACE_ID` — for Dlo Azur Piscines. From the Maps URL Antoine supplied: CID `0xcb47e0b13d67cf4`, knowledge-graph id `/g/11lzn7zfgs`. Resolve the canonical `ChIJ…` Place ID via the [Place ID finder](https://developers.google.com/maps/documentation/places/web-service/place-id) or a CID→place_id lookup.
- Optional: `GOOGLE_BUSINESS_URL` (defaults to `https://g.page/dlo-azur-piscines`) for the "Voir tous les avis" link.

Set these on the **staging** env via the `laravel-cloud-api` skill (env vars: `method:"append"`), then run `php artisan reviews:sync` on staging (`POST /environments/{env}/commands`).
Staging env id: `env-a1ee56ec-0d09-4e22-bedc-eaf6f0b2ebde`. Token in `.env.local` (`LARAVEL_CLOUD_API_KEY`).

## ⚠️ Constraint to surface to Antoine
The Google Places API returns **at most 5 reviews** per place (most-relevant or newest), and you **cannot ask the API for only 5-star** — filtering happens DB-side after fetch. So with `MIN_RATING=5`, if the 5 reviews Google returns include some 4-star, **fewer than 5 (possibly 0) will display.** "Only 5-star" is honored, but the pool is small and not controllable. Confirm this is acceptable, or keep `MIN_RATING=4` to guarantee content.

## Verify
- `php -d memory_limit=1024M ./vendor/bin/pest tests/Feature/GoogleReviewsServiceTest.php tests/Feature/GoogleReviewsComponentTest.php` (update/extend if the 5-star filter or placeholder removal changes assertions — e.g. tests seeding 4-star reviews must expect them filtered out at MIN_RATING=5).
- `bash bin/check-undeclared-tokens.sh` (token guard) if any classes change.
- Full suite green: `php -d memory_limit=1024M ./vendor/bin/pest` (128M OOMs — use 1024M).
- On staging after `reviews:sync`: load `/`, confirm only 5-star real reviews render and the placeholder is gone.

## Scope / guardrails
- Do not invent or hardcode review text (the original P0 was fabricated testimonials — `11-FINDINGS.md`). Real API data only.
- Keep Google attribution + "Voir tous les avis" link (Places API ToS).
- `profile_photo_url` already has `referrerpolicy="no-referrer"` (see `11-REVIEW.md` IN-03) — keep it.
- Staging only; no prod/DNS without explicit per-action authorization.
