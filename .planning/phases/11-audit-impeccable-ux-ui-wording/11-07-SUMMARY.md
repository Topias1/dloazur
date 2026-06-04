---
phase: 11-audit-impeccable-ux-ui-wording
plan: "07"
subsystem: vitrine-copy
tags: [p3-polish, em-dash, glyphs, cta-honesty, d-09, blog-admin]
dependency_graph:
  requires: ["11-04"]
  provides: [clean-glyphs, honest-cta, no-prose-emdash]
  affects: [vitrine-marketing, vitrine-services, blog-admin]
tech_stack:
  added: []
  patterns: [x-icon-check-chip, blade-copy]
key_files:
  created: []
  modified:
    - resources/views/vitrine/partials/philosophie.blade.php
    - resources/views/vitrine/services/depannage.blade.php
    - resources/views/vitrine/services/entretien-recurrent.blade.php
    - resources/views/vitrine/services/eau-verte-urgence.blade.php
    - resources/views/vitrine/partials/espace-client-teaser.blade.php
    - resources/views/admin/blog/edit.blade.php
    - resources/views/livewire/contact-form.blade.php
decisions:
  - "Prose em-dashes used as separators replaced with comma or middot; numeric en-dashes (7,2–7,6) left intact"
  - "Raw ✓ glyphs in list items and success state replaced with <x-icon.check> inside bg-success/10 chip"
  - "espace-client-teaser CTA relabeled from 'Voir un exemple d'espace client' to 'Demander un accès' — label now matches contact destination"
  - "blog/edit @section('title') em-dash separator changed to middot, matching clients/edit convention"
  - "contact-form D-09 residue fixed: 'Pierre vous répondra' → 'Nous vous répondrons' (auto-fix Rule 2, same file)"
metrics:
  duration: "8m"
  completed: "2026-06-04"
  tasks: 1
  files: 7
---

# Phase 11 Plan 07: Vitrine + Blog P3 Cosmetic Polish Summary

Replaced all prose em-dash separators, raw unicode checkmark glyphs, and the misleading espace-client CTA across 7 vitrine and blog-admin surfaces; canonical `<x-icon.check>` chips and honest copy now used throughout.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Vitrine P3 polish — em-dashes, off-token glyphs, misleading CTA + blog edit title | 3772445 | 7 files |

## Decisions Made

- Prose em-dashes in philosophie (line 32), depannage (CTA band line 56), entretien-recurrent (line 105) replaced with `,` or ` · `. Numeric ranges (7,2–7,6, 12–16 h, etc.) untouched.
- All raw `✓` unicode in depannage, entretien-recurrent, eau-verte-urgence list items and contact-form success state replaced with `<x-icon.check class="w-5 h-5 text-success" />` inside `<span class="inline-flex items-center justify-center rounded-full bg-success/10 p-1 shrink-0 mt-0.5">`.
- espace-client-teaser CTA: "Voir un exemple d'espace client" → "Demander un accès" — no new route; same `route('contact')` destination.
- blog/edit `@section('title')`: `{{ $post->title }} — Modifier · Dlo Azur` → `{{ $post->title }} · Modifier · Dlo Azur`.
- The `vitrine/partials/contact-form.blade.php` cited in the plan does not exist; the actual file is `resources/views/livewire/contact-form.blade.php` (confirmed by 11-04 SUMMARY). Edited the correct file.

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 2 - Missing fix] D-09 "Pierre vous répondra" in contact-form success state**
- **Found during:** Task 1, editing livewire/contact-form.blade.php for the raw glyph fix
- **Issue:** "Pierre vous répondra rapidement. En attendant, écrivez-lui directement sur WhatsApp." — D-09 violation missed by 11-04 (confirmed in 11-04 SUMMARY as not addressed).
- **Fix:** Changed to "Nous vous répondrons rapidement. En attendant, écrivez-nous directement sur WhatsApp."
- **Files modified:** resources/views/livewire/contact-form.blade.php
- **Commit:** 3772445

### Path Deviation

- Plan cited `resources/views/vitrine/partials/contact-form.blade.php` — file does not exist at that path. Actual file is `resources/views/livewire/contact-form.blade.php`. This matches the 11-04 SUMMARY finding. Edited the correct file.

## Known Stubs

None. All changes are copy/markup cosmetic only; no data stubs introduced.

## Threat Flags

None. Static vitrine copy, Livewire success state text, and a Blade `@section('title')` string — no new network endpoints, auth paths, or trust boundaries.

## Self-Check: PASSED

- `3772445` exists in git log
- All 7 files modified and committed
- `grep -q "x-icon.check" livewire/contact-form.blade.php` → OK
- `grep -q "x-icon.check" eau-verte-urgence.blade.php` → OK
- `grep "Voir un exemple d'espace client"` → no match
- `grep "—" blog/edit.blade.php` → no match
- `grep "Appeler Pierre" depannage.blade.php` → no match (11-04 revert intact)
