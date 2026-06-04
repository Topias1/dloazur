---
phase: 11-audit-impeccable-ux-ui-wording
plan: "03"
subsystem: auth-portail-ux
tags: [auth, magic-link, portail, ux, alpine, tailwind, email]
dependency_graph:
  requires: []
  provides: [SC-2, SC-6, P0-MAGICLINK-ERROR, P1-SUBMITTING, P1-EMAIL-STRIPE, P2-LOGIN-TAB, P2-DEMO-BTN, P3-WHATSAPP-HEX, P3-CONFIRM-EXPIRY, P3-AUTH-BTN-HEIGHT]
  affects: [portail/magic-link-request, portail/confirm, auth/login, auth/forgot-password, auth/reset-password, emails/magic-link, errors/404]
tech_stack:
  added: []
  patterns: [alpine-x-data-sending, tailwind-token-bg-whatsapp, blade-error-bag-ml]
key_files:
  created: []
  modified:
    - resources/views/portail/magic-link-request.blade.php
    - resources/views/portail/confirm.blade.php
    - resources/views/emails/magic-link.blade.php
    - resources/views/auth/login.blade.php
    - resources/views/auth/forgot-password.blade.php
    - resources/views/auth/reset-password.blade.php
    - resources/views/errors/404.blade.php
decisions:
  - "Login client tab replaced with direct link to portail.magic-link.request (live page exists, dead stub removed)"
  - "confirm.blade.php dead error blocks removed — controller only redirects to magic-link-request, never confirm"
  - "Expiry copy standardised to '48h / jusqu'a 3 fois' matching backend MagicLink::create(2880, 3)"
metrics:
  duration: "12m"
  completed: "2026-06-04"
  tasks_completed: 2
  tasks_total: 2
  files_changed: 7
---

# Phase 11 Plan 03: Auth & Portail UX Polish Summary

SC-2 satisfied (expired magic-link error visible) and SC-6 satisfied (submitting states on all auth buttons preventing double-submit).

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | Wire @error('ml'), remove dead confirm blocks, email side-stripe fix | 87c2a9f | magic-link-request, confirm, emails/magic-link |
| 2 | Submitting states, login tab, demo demote, whatsapp token, h-13 | d321403 | login, forgot-password, reset-password, magic-link-request, confirm, 404 |

## What Was Built

**P0 fix — @error('ml') wired:** `magic-link-request.blade.php` now renders the controller's non-disclosing recovery messages (expired/invalid/missing token) via `@error('ml')` with danger styling. Controller logic untouched (anti-enumeration D-52 intact).

**Dead blocks removed:** `confirm.blade.php` had `session('error')` and `$errors->any()` blocks that could never trigger — the controller redirects errors to `portail.magic-link.request`, not to confirm. Removed.

**Expiry copy aligned:** `confirm.blade.php` note now reads "48 h · Utilisable jusqu'a 3 fois" matching `MagicLink::create(2880, 3)`. Previously said "Usage unique par session" (wrong).

**Email side-stripe removed:** `magic-link.blade.php` security note replaced `border-left: 3px solid` with full `border: 1px solid` + `background: #fefdf8` (sand-50). Hex→token mapping comments added throughout.

**Submitting states (SC-6):** All five auth/portail submit forms wrapped with `x-data="{ sending: false }" @submit="sending = true"`. Buttons get `:disabled="sending"`, `opacity-60 cursor-not-allowed`, and `x-text` label swap (Envoi... / Connexion...). No spinner SVG, no animate-spin. Double-submit blocked on the magic-link form which has a deliberate 1-3s usleep.

**Login dead tab fixed:** "Bientot disponible" disabled client pane replaced with a live link to `route('portail.magic-link.request')`. No dead-end coexists with the live magic-link page.

**Demo button demoted:** "Demo Client" changed from `bg-azure-500` primary to `bg-sand-50 ring-azure-200 text-azure-700` secondary — two stacked azure primaries collapsed to one.

**WhatsApp hex tokenised:** `bg-[#25D366]` replaced by `bg-whatsapp` (token `--color-whatsapp: #25d366` already declared in `app.css`) in both `magic-link-request.blade.php` and `404.blade.php`.

**Button height:** Primary auth submit buttons bumped from `h-12` (48px) to `h-13` (52px) across all five forms, matching the 404 page's existing `h-13`.

## Deviations from Plan

None — plan executed exactly as written.

## Threat Surface Scan

No new network endpoints, auth paths, file access patterns, or schema changes introduced. All changes are Blade/Alpine presentation layer only. T-11-03-01 (non-disclosing @error message), T-11-03-02 (double-submit prevention), T-11-03-03 (vouvoiement) — all mitigations applied. Controller anti-enumeration logic untouched.

## Self-Check

Files exist:
- resources/views/portail/magic-link-request.blade.php — modified
- resources/views/portail/confirm.blade.php — modified
- resources/views/emails/magic-link.blade.php — modified
- resources/views/auth/login.blade.php — modified
- resources/views/auth/forgot-password.blade.php — modified
- resources/views/auth/reset-password.blade.php — modified
- resources/views/errors/404.blade.php — modified

Commits: 87c2a9f, d321403

## Self-Check: PASSED
