---
phase: 09-espace-client-finitions-retours-pierre
fixed_at: 2026-06-04T00:00:00Z
review_path: .planning/phases/09-espace-client-finitions-retours-pierre/09-REVIEW.md
iteration: 1
findings_in_scope: 4
fixed: 3
skipped: 1
status: partial
---

# Phase 09: Code Review Fix Report

**Fixed at:** 2026-06-04
**Source review:** `.planning/phases/09-espace-client-finitions-retours-pierre/09-REVIEW.md`
**Iteration:** 1

**Summary:**
- Findings in scope: 4 (CR-01, WR-01, WR-02, WR-03)
- Fixed: 3 (CR-01, WR-01, WR-03)
- Skipped: 1 (WR-02 — intentional locked decision)

## Fixed Issues

### CR-01: Scope regression test assertions to accordion panel

**Files modified:** `tests/Feature/PortailTimelineTest.php`
**Commit:** df828ef
**Applied fix:** Added `extractAccordionPanel()` helper that locates `id="passage-panel-{passageB->id}"` in the HTML and returns a 6000-char slice covering the full panel content. T2/T3/T4 now assert pH, Cl, TAC, actions, and notes within that scoped fragment rather than anywhere in the response. If any of those values are removed from the accordion panel div, the tests will fail. Increased slice to 6000 chars after initial 3000-char window was too narrow for Livewire blade-comment verbosity around the actions block.

---

### WR-01: Fix T5 photo-counter assertion

**Files modified:** `tests/Feature/PortailTimelineTest.php`
**Commit:** df828ef
**Applied fix:** Replaced `not->toContain('photos->count()')` (PHP source string — always true) with a scoped check: extracts the `<li>` fragment starting from `aria-controls="passage-panel-{id}"` (covers the button header where the camera span lives) and asserts absence of `M6.827 6.175A2.31` — the unique SVG path prefix of the camera icon, which only renders inside the `@if ($p->photos->isNotEmpty())` conditional. If the guard is removed and the camera block always renders, T5 will now fail.

---

### WR-03: Add `noreferrer` to WhatsApp external link

**Files modified:** `resources/views/livewire/portail/passage-timeline.blade.php`
**Commit:** 1c11eb4
**Applied fix:** Changed `rel="noopener"` to `rel="noopener noreferrer"` on the WhatsApp CTA anchor (line 351). Prevents the portal URL from being sent as a `Referer` header to WhatsApp's servers. `loading="lazy"` count confirmed at exactly 1 (unchanged).

---

## Skipped Issues

### WR-02: `loading="lazy"` on passage photo — inconsistency with hero img

**File:** `resources/views/livewire/portail/passage-timeline.blade.php:193`
**Reason:** Intentional locked decision (D-07/D-08 per fix scope instructions). The passage photo's `loading="lazy"` is deliberately kept — only the hero img was to lose lazy. Not touched.
**Original issue:** Potential inconsistency between hero img (no lazy) and passage photo (lazy) below-fold treatment.

---

_Fixed: 2026-06-04_
_Fixer: Claude (gsd-code-fixer)_
_Iteration: 1_
