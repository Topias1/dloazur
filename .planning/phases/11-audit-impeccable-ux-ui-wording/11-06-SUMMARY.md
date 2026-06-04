---
phase: 11-audit-impeccable-ux-ui-wording
plan: "06"
subsystem: admin-ui
tags: [dashboard, a11y, livewire, ux, operator-copy, loading-states]
dependency_graph:
  requires: ["11-01"]
  provides: [agenda-led-dashboard, clean-landmarks, recap-nav, loading-states, tu-empty-states, inline-success]
  affects: [admin-dashboard, admin-layout, admin-sidebar, admin-mobile-nav, client-index, post-index, passage-index, client-form, piscine-form, post-form]
tech_stack:
  added: []
  patterns: [livewire-wire-loading-skeleton, session-flash-inline-success, blade-section-concatenation]
key_files:
  created: []
  modified:
    - resources/views/admin/dashboard.blade.php
    - resources/views/layouts/admin.blade.php
    - resources/views/components/admin/sidebar.blade.php
    - resources/views/components/admin/mobile-bottom-nav.blade.php
    - resources/views/livewire/client-index.blade.php
    - resources/views/livewire/post-index.blade.php
    - resources/views/livewire/passage-index.blade.php
    - resources/views/admin/clients/edit.blade.php
    - resources/views/admin/clients/show.blade.php
    - resources/views/components/admin/topbar.blade.php
    - app/Livewire/ClientForm.php
    - app/Livewire/PiscineForm.php
    - app/Livewire/PostForm.php
decisions:
  - "Inline success banners placed on redirect-destination pages (index/show), not on form views, because session()->flash survives wire:navigate redirect and the form view is never re-rendered post-save"
  - "Agenda du jour hoisted to navy-900 card at dashboard top; actionable cards in 2-col grid; vanity demoted to text strip (D-10)"
  - "Topbar search hidden via routeIs() on non-list routes rather than conditionally disabled"
metrics:
  duration: "~25 min"
  completed: "2026-06-04"
  tasks_completed: 2
  files_changed: 13
---

# Phase 11 Plan 06: Admin Structure Remediation Summary

Admin layout, dashboard, and operator-facing UX fixed: agenda-led dashboard, clean a11y landmarks, Recap in nav, loading states, tu register throughout, inline form success.

## Tasks Completed

| Task | Name | Commit | Files |
|------|------|--------|-------|
| 1 | D-10 dashboard + landmarks + Recap nav + bottom-nav | d2e5435 | dashboard, layouts/admin, sidebar, mobile-bottom-nav |
| 2 | Loading states + tu empty-states + client-edit title + history + topbar + inline success | 825dab4 | client-index, post-index, passage-index, clients/edit, clients/show, topbar, ClientForm.php, PiscineForm.php, PostForm.php |

## What Was Built

**Task 1 — Dashboard restructure + structural fixes:**
- Dashboard leads with an agenda du jour block (navy-900 card, full width): shows today's pools with Saisir CTAs and "A revoir" flags inline
- "A synchroniser" and "Eau a surveiller" cards are `<a>` links to `passages.index` with filtered params (`status=pending`, `needs_attention=1`), amber/danger coloring when non-zero, neutral otherwise; chevron affordance
- Vanity counts (Clients actifs, Passages cette semaine) demoted to a `text-sm text-ink-500` text strip
- `layouts/admin.blade.php`: outer `<aside>` → `<div>`, outer `<header>` → `<div>`, dead empty `<nav>` removed; `<x-admin.sync-drawer>` preserved
- Sidebar: new "Recap mensuel" item linking to `route('admin.recap.index')`
- Mobile bottom-nav: greyed Factures replaced with active Blog link; sync badge gets `aria-live="polite"` + visible label (mirrors desktop sidebar badge)

**Task 2 — Loading, copy, title, history, topbar, inline success:**
- `client-index` + `post-index` list containers: `wire:loading.class="opacity-50 pointer-events-none"` + `wire:target="search"` + `bg-sand-100 animate-pulse` skeleton row (aria-hidden)
- `passage-index.blade.php:118`: "Commencez par saisir" → "Commence par saisir" (tu imperative, D-07)
- `client-index:72`: "votre premier client" → "ton premier client" (tu)
- `post-index:78`: "Écrivez votre premier article" → "Écris ton premier article" (tu)
- `clients/edit.blade.php:3`: `@section('title', $client->name . ' · Modifier · Dlo Azur')` — no literal `{{ }}`, no em-dash
- `clients/show.blade.php`: history rows use `limit(5)` (no intra-card paginate reload), each row is an `<a>` to `passages.show`, shows date + chlore/pH/actions_effectuees summary; "Voir tout (N)" link when >5
- `topbar.blade.php`: disabled search hidden via `@if (request()->routeIs(...))` — only shown on clients/passages/blog routes
- `ClientForm`, `PiscineForm`, `PostForm`: `session()->flash('status', '...')` before redirect; destination views (client-index, clients/show, post-index) display a calm green `role="status"` banner

## Deviations from Plan

### Auto-fixed Issues

**1. [Rule 1 - Bug] Session flash on destination, not form view**
- **Found during:** Task 2
- **Issue:** Forms redirect immediately via `$this->redirect(..., navigate: true)` — the form view is never re-rendered, so a success banner on the form itself would never appear
- **Fix:** Banner placed on destination list/show pages where the flash is read; this is the correct Livewire + session flash pattern
- **Files modified:** client-index, post-index, clients/show (banners); ClientForm, PiscineForm, PostForm (flash)

## Known Stubs

None — all data flows are wired. Dashboard reads `$piscinesAujourdhui` and `$aRevoir` from the existing AgendaController/DashboardController variables (same source as agenda/index.blade.php). Client history uses `$client->passages()` query directly. Success banners render from real `session('status')` flash.

## Threat Flags

None — all changes are cosmetic/IA. Card links use existing authenticated admin routes with existing filter params (no new endpoints). Title concatenation is Blade auto-escaped. Session flash contains no sensitive data.

## Self-Check: PASSED

- `d2e5435` present: confirmed
- `825dab4` present: confirmed
- `resources/views/admin/dashboard.blade.php` leads with agenda du jour block: confirmed
- `recap.index` in sidebar: confirmed
- `<aside` absent from `layouts/admin.blade.php`: confirmed
- `sync-drawer` preserved in layout: confirmed
- `wire:loading` in client-index + post-index: confirmed
- `Commence par saisir` (tu) in passage-index: confirmed
- `$client->name . '` concatenation in edit title: confirmed
