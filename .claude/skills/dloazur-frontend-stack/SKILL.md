---
name: dloazur-frontend-stack
description: Use when writing or editing frontend code in the dloazur Laravel app — Blade views/components, Livewire 3 components, Alpine.js, Tailwind v4 CSS/tokens, design colors, or the offline passage-entry / PWA sync code. Read this BEFORE exploring the repo to skip rediscovering stable conventions; it encodes the project's Tailwind v4, Livewire-vs-Alpine, and offline-data gotchas.
---

# Dlo Azur Frontend Stack

Mechanical conventions for this project's frontend. **This is a fast-reference to avoid re-exploring the codebase** — the patterns below are stable and verified against live code. For *visual/design* decisions (color choices, hierarchy, spacing, motion) defer to `DESIGN.md` + the `impeccable` skill; this skill is the *how the code is wired* layer only.

Stack: Laravel 13 · Livewire 3 · Alpine.js 3 · Tailwind v4 (CSS-first) · vite-plugin-pwa + IndexedDB offline.

## Tailwind v4 — CSS-first, no config file

- **There is NO `tailwind.config.js`** and there must not be. All config lives in `resources/css/app.css`.
- CSS entry is `@import "tailwindcss";` (line 1) — **never** the v3 `@tailwind base/components/utilities` directives.
- Vite uses the `@tailwindcss/vite` plugin (`vite.config.js`), not PostCSS.
- **Design tokens live in the `@theme { … }` block** in `app.css`. Adding a color = add an OKLCH tonal ramp (`--color-name-50 … -950`) to `@theme`; Tailwind auto-generates `bg-name-500`, `text-name-700`, `ring-name-200`, etc. No JS, no rebuild config.
- **Colors are OKLCH only.** Never `#000`/`#fff`/hex. The sole hex exception is WhatsApp `#25D366`. Greys are navy-tinted (hue ~250), use `text-ink-*` not `text-gray-*`. Existing families: `azure` (logo), `navy`, `lagon`, `sun`, `sand` (surfaces), `ink` (text), plus `success`/`warn`/`danger`.
- `@source` directives in `app.css` scan `.blade.php` **and** `.js` — Alpine directive classes in JS are discovered. New top-level asset dirs may need a `@source` line.
- `[x-cloak]{display:none}` is already defined in `app.css`. Always add `x-cloak` to Alpine elements that should not flash before init.

## Livewire 3 vs Alpine — the load-bearing decision

Pick the right tool per surface:

| Surface | Use | Never |
|---------|-----|-------|
| **Offline passage entry** (on-site form) | Alpine + IndexedDB + Service Worker | **Livewire** (needs network — hard project rule, CLAUDE.md) |
| Authenticated admin / client portal (online) | Livewire | — |
| Lightweight presentational toggle/dismiss/banner | Alpine + `localStorage` | Livewire (no server round-trip needed) |

- **`$persist` (Alpine persist plugin) is NOT registered.** For persisted client state, write `localStorage` manually (same as the diagnostic *carnet* pattern). Don't reach for `$persist`.
- **No `@entangle` anywhere** in this project. Alpine↔Livewire sync is done via `wire:model` or explicit `$wire.methodName()` calls from Alpine. Don't introduce `@entangle`.

## Livewire 3 gotchas

- Alpine root nested inside a Livewire component that holds its own UI state (steps, open/closed) **must** have `wire:ignore.self` — otherwise a Livewire re-render wipes the Alpine state. See `diagnostic-wizard.blade.php`.
- Call Livewire actions from Alpine with `$wire.method(args)` (it's injected into Alpine scope). No special binding needed.
- `wire:model.lazy` for inputs that trigger expensive server work (compute on blur); `wire:model.live` / `.live.debounce.300ms` for filter fields.

## Alpine wiring

- **Livewire 3 owns the single Alpine instance.** Do NOT `import Alpine from 'alpinejs'` or call `Alpine.start()` in `app.js` — a 2nd instance triggers "Detected multiple instances of Alpine running" and breaks `$wire` (e.g. the `!$wire.x` expressions on `/diagnostic`). Use the global `window.Alpine` Livewire provides.
- Component logic = `Alpine.data('name', factory)`; shared state = `Alpine.store('name', {…})`. **Register them inside `document.addEventListener('alpine:init', () => { … })`** (fires before Livewire starts Alpine), NOT at module top-level. Code running after start (`alpine:initialized` listeners, SW callbacks) should use `window.Alpine`.
- No custom `Alpine.directive()` exists — stay data/store-driven.
- Cross-component comms use a `window` CustomEvent (e.g. `window.dispatchEvent(new CustomEvent('passage-form:flush'))`), listened for in `app.js`. Reuse this rather than tight coupling.

## Offline / PWA data path (already fully built — extend, don't reinvent)

- IndexedDB: db `dloazur-offline-v1`, stores `passages` + `photos`, via the `idb` library (`resources/js/offline-queue.js`).
- A passage is stored as an opaque `payload_json` blob. **New form fields ride along automatically — do NOT bump the IDB `DB_VERSION`** (that risks in-flight queued passages on operators' devices).
- **The API write (`POST /api/passages`) is a hand-written raw-SQL UPSERT in `app/Http/Controllers/Api/PassageController.php`, not Eloquent.** Adding a DB column means: migration + model `$fillable` **+** add the column to the INSERT column list, VALUES, `ON CONFLICT DO UPDATE SET`, and the bindings array. `$fillable` alone will silently never persist it.
- Workbox `backgroundSync` is unsupported in Firefox/Safari; an `online`/`visibilitychange` flush fallback already exists in `passage-form.js`. Keep it.
- SW only registers in prod build (`devOptions.enabled:false`). `registerType:'prompt'` → updates surface via the `pwaUpdate` Alpine store + `<x-admin.pwa-update-toast>`.

## Blade components

- Anonymous components, kebab-case files; admin partials in `resources/views/components/admin/`, icons in `resources/views/components/icon/`.
- Icons: `@props(['size'=>30])` or class-merge, `fill="currentColor"`, `aria-hidden="true"`, `focusable="false"`. Use `<x-icon.NAME>`.
- Brand mark is `<x-icon.drop>` / `logo-mark.svg` — **never** the old hand-drawn `~` drop SVG.

## Key files

| Concern | File |
|---------|------|
| Tailwind tokens / theme | `resources/css/app.css` (`@theme`) |
| Vite + PWA config | `vite.config.js` |
| Alpine bootstrap, stores, data factories | `resources/js/app.js` |
| Offline IndexedDB queue | `resources/js/offline-queue.js` |
| Offline passage form | `resources/js/passage-form.js` |
| API write (raw-SQL UPSERT) | `app/Http/Controllers/Api/PassageController.php` |

## Common mistakes

| Mistake | Reality |
|---------|---------|
| Create/edit `tailwind.config.js` | Doesn't exist in v4 — tokens go in `@theme` in `app.css` |
| `@tailwind base/components/utilities` | Use `@import "tailwindcss";` |
| Hex / `#fff` / `text-gray-*` | OKLCH ramps only; `text-ink-*`; `#25D366` is the only hex |
| `@entangle` for Alpine↔Livewire | Not used here — `wire:model` or `$wire.method()` |
| `$persist` for client state | Plugin not registered — manual `localStorage` |
| Livewire for the offline form | Hard rule: Alpine + IndexedDB + SW only |
| Bump IDB `DB_VERSION` for a new field | Payload is opaque JSON — new fields ride free |
| Add column to `$fillable` only | Write path is raw SQL — also edit INSERT/VALUES/ON CONFLICT/bindings |
| Alpine state lost on Livewire re-render | Add `wire:ignore.self` to the Alpine root |
| `import Alpine` + `Alpine.start()` in `app.js` | Livewire owns Alpine; register stores/data in `alpine:init`, never start a 2nd instance |
