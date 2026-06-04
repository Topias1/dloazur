---
phase: 11-audit-impeccable-ux-ui-wording
plan: "04"
subsystem: vitrine-copy
tags: [fake-testimonials, google-reviews, pierre-naming, d-09, oklch-tokens, p0, p1, p3]
dependency_graph:
  requires: []
  provides: [clean-testimonials, token-google-stars, d09-pierre-naming]
  affects: [vitrine-seo, vitrine-marketing]
tech_stack:
  added: []
  patterns: [blade-placeholder, tailwind-token-classes]
key_files:
  created: []
  modified:
    - resources/views/vitrine/partials/testimonials.blade.php
    - resources/views/livewire/google-reviews.blade.php
    - resources/views/vitrine/partials/philosophie.blade.php
    - resources/views/vitrine/partials/final-cta.blade.php
    - resources/views/vitrine/zones/fort-de-france.blade.php
    - resources/views/vitrine/zones/le-lamentin.blade.php
    - resources/views/vitrine/zones/schoelcher.blade.php
    - resources/views/vitrine/zones/les-trois-ilets.blade.php
    - resources/views/vitrine/services/depannage.blade.php
    - resources/views/vitrine/services/analyse-eau.blade.php
    - resources/views/vitrine/diagnostic.blade.php
decisions:
  - "D-09 enforced: Pierre named only in partials/pierre.blade.php + footer + legal; all marketing surfaces use nous/Dlo Azur"
  - "SC-3 satisfied: no fabricated attributed testimonials on live vitrine; replaced with explicit placeholder"
metrics:
  duration: "12m"
  completed: "2026-06-04"
  tasks: 2
  files: 11
---

# Phase 11 Plan 04: Vitrine P0/P1/P3 Remediation Summary

Removed fabricated attributed testimonials (SC-3/P0), fixed Google Reviews stars to use token colors and accurate filled/empty logic (P1), and reverted Pierre-by-name in 10 marketing surfaces to nous/Dlo Azur per D-09, plus tokenized inline oklch on the diagnostic fallback panel (P3).

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Remove fake testimonials + fix Google Reviews stars | db63d9b | testimonials.blade.php, google-reviews.blade.php |
| 2 | Revert Pierre naming (D-09) + tokenize inline oklch | ae87caf | 9 files across partials, zones, services, diagnostic |

## Decisions Made

- Replaced two fabricated blockquotes (Sandrine M., Conciergerie du Sud) with a single clearly-labeled placeholder — no invented social proof ships on the live site.
- Google Reviews summary and per-review stars now loop over 5 with filled (`text-sun-500`) / empty (`text-sun-300`) spans driven by `round($avg)` / `$review->rating`, not hardcoded unicode strings.
- `number_format` narrow-no-break-space separator changed from single-quote literal `'\u{202F}'` to double-quote PHP escape `"\u{202F}"` — actual unicode character now emitted.
- All four zone pages and depannage: "Appeler Pierre" button label -> "Nous appeler"; "Appelez Pierre" CTA band copy -> "Appelez-nous".
- contact-form.blade.php not found in the vitrine partials tree — plan cited it but file does not exist at that path. No action taken (file absent, not a regression).
- diagnostic.blade.php fallback block replaced `Contacter Pierre` with `Contacter Dlo Azur`; inline `oklch(...)` style attributes replaced with Tailwind token classes `bg-lagon-500/12 text-lagon-600` and `ring-lagon-500/40`.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Duplicate class attribute on diagnostic reprendre link**
- **Found during:** Task 2, oklch inline replacement
- **Issue:** First edit introduced a second `class=` attribute on the `<a>` element (HTML ignores duplicates, Blade would render both)
- **Fix:** Merged into single class attribute with token classes
- **Files modified:** resources/views/vitrine/diagnostic.blade.php
- **Commit:** ae87caf

### Missing File

**contact-form.blade.php** — plan listed `resources/views/vitrine/partials/contact-form.blade.php` as a target (Pierre occurrences at lines 12, 24). File does not exist at that path in the codebase. The `final-cta.blade.php` embeds `<livewire:contact-form />` (the Livewire component), and the plan's Pierre references in `final-cta` were fixed there. No fabricated content at risk; tracked as informational.

## Known Stubs

- `testimonials.blade.php`: placeholder `[Avis à fournir par Pierre — capture Google ou citation vérifiée]` is intentional — this is the correct gated state per UI-SPEC. To be resolved when Pierre provides real Google captures.

## Threat Flags

None. Changes are vitrine static copy and Livewire read-only render — no new network endpoints, auth paths, or trust boundaries introduced.

## Self-Check: PASSED

- [x] testimonials.blade.php: no "Sandrine", no "Conciergerie du Sud" — confirmed
- [x] google-reviews.blade.php: no "text-yellow-400", has "text-sun-" — confirmed
- [x] No "Appeler Pierre" in resources/views/vitrine/ — confirmed (worktree grep returns empty)
- [x] No inline oklch() in diagnostic.blade.php — confirmed (worktree grep returns empty)
- [x] Commits db63d9b and ae87caf exist in git log
