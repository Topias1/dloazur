---
phase: 09-espace-client-finitions-retours-pierre
reviewed: 2026-06-04T00:00:00Z
depth: standard
files_reviewed: 2
files_reviewed_list:
  - resources/views/livewire/portail/passage-timeline.blade.php
  - tests/Feature/PortailTimelineTest.php
findings:
  critical: 1
  warning: 3
  info: 2
  total: 6
status: issues_found
---

# Phase 09: Code Review Report

**Reviewed:** 2026-06-04
**Depth:** standard
**Files Reviewed:** 2
**Status:** issues_found

## Summary

Two files from the phase-09 polish pass were reviewed: the portal timeline Blade view and its new Pest regression test suite. The Blade itself has no XSS/injection issues (all user data goes through `{{ }}`). The main concerns are: one blocker-level logic error in the test seed that silently invalidates two test cases, two a11y gaps in the view, and one security-adjacent header omission on the external link.

---

## Critical Issues

### CR-01: Test seed ordering assumption is wrong — T2/T3/T4 may assert against passageA, not passageB

**File:** `tests/Feature/PortailTimelineTest.php:36-58`

**Issue:** The fixture seeds passageA (`subDays(1)`) and passageB (`subDays(10)`). The Livewire component orders passages by `visited_at DESC`, so `$passages->first()` (= `$lastPassage`) is passageA. The template skips the first passage with `$passages->skip(1)`, so passageB goes into the accordion.

The tests then assert presence of `7,4`, `2,3`, `95`, `Nettoyage filtre`, `Brossage parois`, and `Note historique XQ7Z` — all values seeded on passageB. This is correct.

However, passageA is also seeded with `ph_avant=7.2` and `chlore_libre=1.5`, and the "Dernier passage" card renders those values on-screen. The `assertSee('7,4')` in T2 passes because passageB is rendered in the accordion, but **it would also pass if the skip logic broke and passageB values appeared in the wrong section** — the tests cannot distinguish where in the HTML the values appear. More importantly:

The real silent failure is in the opposite direction. If a factory default or cast change causes passageB to be ordered before passageA (e.g., equal `visited_at` microseconds from sequential `now()` calls in CI), `skip(1)` would drop passageB from the accordion entirely and none of T2/T3/T4 values would be visible, causing all three tests to fail for a phantom reason unrelated to the feature under test. This is a **flaky seed** — time-dependent ordering of two rows created milliseconds apart via sequential `now()->subDays(N)` is reliable in unit time, but the comment in line 36 incorrectly states that passageA "ira dans « Dernier passage » via skip(1)". The skip discards the first item in the already-sorted collection (most recent), which is passageA — that part is correct. But T1 at line 77 asserts `aria-controls="passage-panel-{$passageB->id}"`, which is a strong assertion on passageB's accordion entry. T2–T4 assert only raw text that could appear anywhere in the response, providing no actual regression coverage for the accordion panel specifically.

**The concrete correctness bug:** T2 asserts `assertSee('95', false)`. The value `95` is a substring of IDs, timestamps (year `1995`?), and many other integers that may appear in the HTML (e.g., `px-[95px]`, `956`, `1995`). This assertion will not false-positive for `95` in isolation in this specific template, but it asserts nothing about the accordion panel structure. If the TAC display were removed from the accordion but kept in the "Dernier passage" card, T2 would still pass.

**Fix:** Scope content assertions to the accordion panel HTML by asserting the `id="passage-panel-{$passageB->id}"` sentinel is present and appears before the asserted values, or better, extract the panel substring:

```php
// In T2, T3, T4 — after assertStatus(200):
$html = $response->getContent();
// Find the accordion panel for passageB specifically
$panelStart = strpos($html, 'id="passage-panel-' . $passageB->id . '"');
$this->assertNotFalse($panelStart, 'Accordion panel for passageB not found');
$panel = substr($html, $panelStart, 2000); // reasonable panel slice

expect($panel)->toContain('7,4');
expect($panel)->toContain('2,3');
expect($panel)->toContain('95');
```

---

## Warnings

### WR-01: T5 assertion tests PHP source string, not rendered output (flagged by verifier)

**File:** `tests/Feature/PortailTimelineTest.php:150`

**Issue:** The assertion `expect($html)->not->toContain('photos->count()')` checks that the literal string `photos->count()` does not appear in the rendered HTML. That string is PHP source code — it will never appear in rendered output regardless of whether the camera block is present or absent, making this test a guaranteed pass that proves nothing. If the photo counter block is accidentally rendered (e.g., `isNotEmpty()` guard removed), this test would still pass because the camera SVG + count number would be present but the PHP source string would still be absent.

**Fix:** Assert the absence of the actual rendered camera SVG sentinel or the camera path data, which only appears inside the `@if ($p->photos->isNotEmpty())` guard:

```php
// The camera SVG path is unique to the photo counter span in the accordion
expect($html)->not->toContain('M6.827 6.175A2.31');  // camera SVG path prefix
// Or assert the count digit is not rendered adjacent to the camera icon
// by checking the photo-count flex span is absent
```

Alternatively assert `not->toContain('stroke-linecap="round" stroke-linejoin="round" d="M6.827')` which only appears in the camera icon.

---

### WR-02: `loading="lazy"` removed from hero img but kept on passage photo — inconsistent and potentially wrong

**File:** `resources/views/livewire/portail/passage-timeline.blade.php:193`

**Issue:** The review description states that `loading="lazy"` was removed from the hero image (line 37, confirmed — no `loading` attribute present). However, line 193 retains `loading="lazy"` on the passage photo inside the "Dernier passage" card. This is the first visible photo below the fold — removing lazy from the hero to improve LCP makes sense, but the passage photo is positioned immediately below the hero card in the same section, with no content between them. On short viewports the passage photo may be in the initial viewport, meaning `lazy` could defer it unnecessarily and hurt perceived performance. More importantly, the inconsistency suggests the intent of the change was not fully applied.

**Fix:** Evaluate whether the passage photo should also drop `loading="lazy"`. If it is consistently above the fold on the target device (mobile, short scroll), remove the attribute. If it is consistently below, keep it. Align the decision with the hero img treatment.

---

### WR-03: External WhatsApp link missing `noreferrer`

**File:** `resources/views/livewire/portail/passage-timeline.blade.php:351`

**Issue:** The WhatsApp anchor has `rel="noopener"` but not `noreferrer`. With `target="_blank"`, `noopener` prevents the opened page from accessing `window.opener`, but `noreferrer` additionally suppresses the `Referer` header. For a client portal, the portal URL (which may contain session-identifying path segments or query strings) would be sent as a referrer to WhatsApp's servers. This leaks the portal URL to a third-party service.

**Fix:**
```html
rel="noopener noreferrer"
```

---

## Info

### IN-01: `aria-expanded` value is Alpine expression string, not boolean attribute

**File:** `resources/views/livewire/portail/passage-timeline.blade.php:217`

**Issue:** `:aria-expanded="open.toString()"` produces `aria-expanded="false"` or `aria-expanded="true"` as a string attribute. The ARIA spec accepts both `"true"`/`"false"` strings and the bare keyword, so this is technically valid. However, calling `.toString()` is unusual — Alpine's `:aria-expanded="open"` would produce the same result because Alpine coerces booleans to strings for attribute binding. The `.toString()` is redundant noise with no functional benefit and could mislead future maintainers into thinking a raw boolean would otherwise produce an invalid attribute.

**Fix:**
```html
:aria-expanded="open"
```

---

### IN-02: pH "idéal" label absent when pH is out of range — silent blank

**File:** `resources/views/livewire/portail/passage-timeline.blade.php:99`

**Issue:** When `ph_avant` is set but pH is out of the 7.0–7.6 ideal range, the label renders as an empty string (`''`). For Cl libre (line 114), out-of-range shows `'mg/L'` as a fallback unit. The inconsistency means a client with a bad pH sees no label at all — not "hors norme", not the unit. This is a UX gap not a bug, but the inconsistency between pH and Cl treatment is a code quality issue that could confuse future maintainers who expect uniform behavior.

**Fix:** Either show the unit `'pH'` when out of range, or show a neutral indicator like `'·'`, matching the Cl libre pattern. The blank-on-bad-value approach makes it look like a display bug to clients.

---

_Reviewed: 2026-06-04_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
