---
phase: 11-audit-impeccable-ux-ui-wording
reviewed: 2026-06-04T00:00:00Z
depth: standard
files_reviewed: 51
files_reviewed_list:
  - .github/workflows/tests.yml
  - app/Livewire/ClientForm.php
  - app/Livewire/PiscineForm.php
  - app/Livewire/Portail/PassageTimeline.php
  - app/Livewire/PostForm.php
  - bin/check-undeclared-tokens.sh
  - resources/css/app.css
  - resources/js/app.js
  - resources/js/passage-form.js
  - resources/js/sync-drawer.js
  - resources/js/upload-pipeline.js
  - resources/views/admin/agenda/index.blade.php
  - resources/views/admin/blog/edit.blade.php
  - resources/views/admin/clients/edit.blade.php
  - resources/views/admin/clients/show.blade.php
  - resources/views/admin/dashboard.blade.php
  - resources/views/admin/passages/create.blade.php
  - resources/views/auth/forgot-password.blade.php
  - resources/views/auth/login.blade.php
  - resources/views/auth/reset-password.blade.php
  - resources/views/components/admin/mobile-bottom-nav.blade.php
  - resources/views/components/admin/sidebar.blade.php
  - resources/views/components/admin/topbar.blade.php
  - resources/views/emails/magic-link.blade.php
  - resources/views/errors/404.blade.php
  - resources/views/layouts/admin.blade.php
  - resources/views/layouts/app.blade.php
  - resources/views/livewire/client-index.blade.php
  - resources/views/livewire/contact-form.blade.php
  - resources/views/livewire/google-reviews.blade.php
  - resources/views/livewire/passage-index.blade.php
  - resources/views/livewire/portail/passage-timeline.blade.php
  - resources/views/livewire/post-index.blade.php
  - resources/views/offline.blade.php
  - resources/views/portail/confirm.blade.php
  - resources/views/portail/magic-link-request.blade.php
  - resources/views/vitrine/diagnostic.blade.php
  - resources/views/vitrine/partials/espace-client-teaser.blade.php
  - resources/views/vitrine/partials/final-cta.blade.php
  - resources/views/vitrine/partials/philosophie.blade.php
  - resources/views/vitrine/partials/testimonials.blade.php
  - resources/views/vitrine/services/analyse-eau.blade.php
  - resources/views/vitrine/services/depannage.blade.php
  - resources/views/vitrine/services/eau-verte-urgence.blade.php
  - resources/views/vitrine/services/entretien-recurrent.blade.php
  - resources/views/vitrine/zones/fort-de-france.blade.php
  - resources/views/vitrine/zones/le-lamentin.blade.php
  - resources/views/vitrine/zones/les-trois-ilets.blade.php
  - resources/views/vitrine/zones/schoelcher.blade.php
findings:
  critical: 2
  warning: 6
  info: 4
  total: 12
status: issues_found
---

# Phase 11: Code Review Report

**Reviewed:** 2026-06-04T00:00:00Z
**Depth:** standard
**Files Reviewed:** 51
**Status:** issues_found

## Summary

Reviewed the phase-11 UX/UI/wording audit changeset: 4 Livewire components, 4 offline-first JS modules, the Tailwind v4 token guardrail, CI workflow, and ~43 Blade views (admin shell, portal, auth, vitrine).

The auth views, magic-link flow, contact form, and portal timeline are well-built and security-conscious (correct `client_id` isolation, escaped output, honeypot, no auto-submit on the SafeLinks-sensitive confirm page). However two correctness defects ship broken UI to the operator: (1) the client detail page references **non-existent passage columns** so the passage-history preview line is always blank, and (2) the dashboard "À synchroniser" card uses the **undeclared `amber` Tailwind token family** which emits zero CSS in v4 CSS-first mode — the card's active state has no styling, and the CI guardrail that exists specifically to catch this does NOT check `amber`. Several robustness and dead-code warnings follow.

## Critical Issues

### CR-01: Client passage-history preview references non-existent model attributes (always renders blank)

**File:** `resources/views/admin/clients/show.blade.php:135-139`
**Issue:** The recent-passages summary line reads `$passage->chlore`, `$passage->ph`, and `$passage->actions_effectuees`. None of these columns exist. The migration (`2026_05_28_000005_create_passages_table.php`) and `App\Models\Passage` `$fillable`/`$casts` define `chlore_libre`, `ph_avant`, and `actions` (cast to array). There are no accessors aliasing the old names (verified — no `getChloreAttribute`, `getPhAttribute`, etc.). Eloquent returns `null` for undefined attributes, so `@if ($passage->chlore || $passage->ph)` is always false and the mesure/actions summary never renders. The operator sees a date with an empty subtitle for every passage on every client fiche.
**Fix:**
```blade
<p class="text-xs text-ink-500 truncate mt-0.5">
    @if ($passage->chlore_libre !== null || $passage->ph_avant !== null)
        Cl {{ $passage->chlore_libre ?? '—' }} · pH {{ $passage->ph_avant ?? '—' }}
    @endif
    @if (!empty($passage->actions))
        · {{ Str::limit(implode(', ', (array) $passage->actions), 40) }}
    @endif
</p>
```
(Note `actions` is a JSON-cast array, not a string, so `Str::limit` on it directly would also error — implode first.)

### CR-02: Dashboard "À synchroniser" card uses undeclared `amber` token family — emits zero CSS

**File:** `resources/views/admin/dashboard.blade.php:99,105,110-111,116-119`
**Issue:** The active (pending > 0) state uses `bg-amber-50 ring-amber-200 hover:ring-amber-300`, `text-amber-700`, `text-amber-400`. In Tailwind v4 CSS-first mode (this project's config), only tokens declared in the `@theme` block emit utilities. `amber` is NOT declared in `resources/css/app.css` (declared families: sand, azure, navy, lagon, sun, ink, warn, success, danger, whatsapp, white). These classes therefore produce **no CSS at all** — the "À synchroniser" card has no background, border, or text color when items are pending, which is exactly the state it needs to draw attention to (the project's core "offline → must sync" signal). The amber-vs-danger color rule documented inline at lines 90-93 is silently not applied.

This is also a **guardrail gap**: `bin/check-undeclared-tokens.sh` exists specifically to fail CI on undeclared token classes (D-06), but its `FAMILIES` regex (line 34) does not include `amber`, so this breakage passes CI undetected.
**Fix:** Remap to the declared `warn` family already used for this exact semantic elsewhere (agenda badge `text-warn-700`, save-bar `var(--warn-bg)`):
```blade
'bg-warn/15 ring-warn-200 hover:ring-warn/40' => $aSynchroniser > 0,
...
'text-warn-700' => $aSynchroniser > 0,
```
And harden the guardrail by adding the full Tailwind default-palette family list (amber|emerald|rose|slate|indigo|...) to the `FAMILIES` regex in `bin/check-undeclared-tokens.sh:34` so any future default-palette class is caught.

## Warnings

### WR-01: Passage list cards are dead links (`href="#"`) — clicking a passage jumps to top

**File:** `resources/views/livewire/passage-index.blade.php:63`
**Issue:** Each passage row is `<a href="#" ...>`. Clicking navigates to the page anchor (scrolls to top) instead of the passage detail. The client fiche (`clients/show.blade.php:130`) correctly links to `route('admin.passages.show', $passage)`, so the route exists — this list just was never wired. The card looks clickable (hover state, full-row anchor) but does nothing useful.
**Fix:** `<a href="{{ route('admin.passages.show', $p) }}" ...>` (and drop the now-stale disabled "Nouveau passage" placeholder at lines 6-17 — see WR-02).

### WR-02: Stale disabled "Nouveau passage" button + obsolete TODO in passage index

**File:** `resources/views/livewire/passage-index.blade.php:6-17`
**Issue:** The header still renders a permanently-disabled `<span aria-disabled="true" ... cursor-not-allowed opacity-50>` "Nouveau passage" button with comment "désactivé jusqu'à Plan 02-05 (route admin.passages.create non encore créée)" and a TODO to replace it once the route exists. The route `admin.passages.create` now exists and is used live in `topbar.blade.php:58`, `dashboard.blade.php:36`, `agenda/index.blade.php:41`, `mobile-bottom-nav.blade.php:43`. The operator sees a greyed-out, non-functional CTA on the passages list page.
**Fix:** Replace the `<span>` with an active `<a href="{{ route('admin.passages.create') }}">` and remove the obsolete TODO comment (line 17).

### WR-03: Photo re-upload loop re-scans every synced passage on every flush

**File:** `resources/js/passage-form.js:392-398`
**Issue:** `_flushQueue()` calls `db.getAll('passages')` and, for **every** passage with `status === 'synced'`, calls `_uploadPhotosForPassage()`. The comment claims it uploads "photos for passages that were just synced by this flush," but the code does not restrict to newly-synced records — it iterates the entire synced history. `_uploadPhotosForPassage` does filter out already-`synced` photos, so successful photos aren't re-sent, but any photo stuck in `error`/`pending` from an old passage is re-attempted on every single flush (online event, visibilitychange, manual sync) indefinitely, and the scan cost grows unbounded with passage history. A photo that fails permanently (e.g. corrupt blob) will retry forever.
**Fix:** Capture the set of `client_uuid`s synced by *this* `flushPipeline()` invocation (extend `flushPipeline` to return the synced uuids) and only upload photos for those; or mark a passage's photos as fully-processed and skip it thereafter.

### WR-04: `volume_m3` validation allows `min:1` but UI/steppers and "0" inputs can desync; empty-string default bypasses numeric check silently

**File:** `app/Livewire/PiscineForm.php:19,64-65`
**Issue:** `volume_m3` is typed `string` with `#[Validate('nullable|numeric|min:1|max:1000')]`. On save it is persisted as `$this->volume_m3 ?: null`. Because the default is `''` (empty string), an unedited create passes validation as `nullable` and stores `null` — acceptable. But the `?:` falsy coalesce also converts a legitimately-entered `"0"` (or `"0.0"`) to `null` rather than letting `min:1` reject it, so a user typing 0 gets a silent null instead of a validation error. The same `?:` null-coalesce pattern affects every other field but is benign for strings; for the numeric volume it masks invalid input.
**Fix:** Either keep `min:1` and surface the error (don't pre-coalesce `0` away — use `=== '' ? null : $this->volume_m3`), or document that 0 is intentionally treated as "unknown."

### WR-05: "Accueil" mobile-nav tab is hardcoded active on every admin page

**File:** `resources/views/components/admin/mobile-bottom-nav.blade.php:12-14`
**Issue:** Every other bottom-nav item conditionally applies `text-azure-600` + `aria-current="page"` via `request()->routeIs(...)`. The "Accueil" (dashboard) item hardcodes `class="... text-azure-600"` and `aria-current="page"` unconditionally. On Clients, Agenda, Passages, or Blog pages the Accueil tab still renders as the active tab, and screen readers announce two `aria-current="page"` elements (Accueil + the real active tab), which is invalid and confusing.
**Fix:**
```blade
<a href="{{ route('admin.dashboard') }}"
   class="flex flex-col items-center justify-center gap-1 {{ request()->routeIs('admin.dashboard') ? 'text-azure-600' : 'text-ink-400' }}"
   @if(request()->routeIs('admin.dashboard')) aria-current="page" @endif>
```

### WR-06: `recoverOrphans` resets attempt counter, weakening backoff progression

**File:** `resources/js/upload-pipeline.js:25-27`
**Issue:** `recoverOrphans()` re-queues `uploading` items with `markStatus('passages', item.id, 'pending', item.attempts ?? 0)`. `uploadPassage` increments `attempts` only on permanent (4xx) or transient-exhaustion failures, not when a tab is killed mid-`uploading`. An orphan that is repeatedly interrupted (flaky network on the field, app backgrounded each attempt) keeps `attempts` at its pre-upload value, so it can loop through the full 2s/8s/30s backoff every recovery with no escalation or dead-letter ceiling. Combined with the boot-time + every-`flushAll` recovery triggers, a chronically-failing record retries forever with no visibility beyond the badge count.
**Fix:** Increment `attempts` on orphan recovery, and add a max-attempts threshold that moves a record to a terminal `error` state surfaced in the sync drawer for manual intervention.

## Info

### IN-01: Unescaped JSON-LD output relies on library encoding

**File:** `resources/views/layouts/app.blade.php:17,23`
**Issue:** `{!! $jsonLd !!}` and `{!! $breadcrumbJsonLd !!}` emit raw HTML. Values come from `app/Support/SchemaOrg/BreadcrumbSchema::toScript()` / spatie schema-org, which JSON-encodes with proper `<`/`>` escaping, so this is currently safe. Flagged for awareness: if a future contributor feeds user-controlled strings (e.g. a blog post title) into a hand-rolled JSON-LD string instead of the library, this becomes a stored-XSS sink. Keep all structured-data construction inside the encoding library.

### IN-02: `clients/show.blade.php` issues N+1 count queries for passage history

**File:** `resources/views/admin/clients/show.blade.php:118,122,125`
**Issue:** `$client->passages()->count()` is called twice (lines 122, 125) plus a separate `->limit(5)->get()` (line 118) — three queries where one `loadCount` + the fetched collection would do. Not a correctness bug (and performance is out of v1 scope), noted only because the duplicate `count()` is trivially de-dupable into a single `@php $total = $client->passages()->count(); @endphp`.

### IN-03: `google-reviews.blade.php` renders external image URL into `src` from DB

**File:** `resources/views/livewire/google-reviews.blade.php:37`
**Issue:** `src="{{ $review->profile_photo_url }}"` outputs a stored Google profile URL. Blade escapes HTML entities and `src` does not execute `javascript:` URLs on `<img>`, so this is not exploitable; `referrerpolicy="no-referrer"` is correctly set. Noted only to confirm the source is the trusted Google Places import, not user input.

### IN-04: `_uploadPhoto` duplicates the backoff/header logic already centralized in upload-pipeline.js

**File:** `resources/js/passage-form.js:424-480`
**Issue:** `_uploadPhoto` reimplements the `[2000, 8000, 30000]` backoff array and a private `_headers()` that duplicates `buildHeaders()` from `upload-pipeline.js` (which exports `UPLOAD_DELAYS` precisely to avoid duplicated literals, per its own docstring). The passage-upload path correctly imports the shared module; the photo path does not. Consolidate to `UPLOAD_DELAYS` and `buildHeaders` to keep one source of truth.

---

_Reviewed: 2026-06-04T00:00:00Z_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
