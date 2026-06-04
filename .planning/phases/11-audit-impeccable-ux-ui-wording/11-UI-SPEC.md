---
phase: 11
slug: audit-impeccable-ux-ui-wording
status: draft
shadcn_initialized: false
preset: none
created: 2026-06-04
---

# Phase 11 — UI Design Contract

> Audit remediation phase. The design system is LOCKED (OKLCH @theme in
> resources/css/app.css, Fredoka + Inter, DESIGN.md tokens). This contract
> locks the specific visual/interaction fixes this phase applies, so the
> planner and executor apply them consistently. Do NOT re-derive the system —
> read this contract as a delta on top of the existing system.

---

## Design System

| Property | Value |
|----------|-------|
| Tool | none (Tailwind v4 CSS-first @theme) |
| Preset | not applicable — tokens in resources/css/app.css @theme |
| Component library | none (Blade components, Livewire, Alpine.js) |
| Icon library | Heroicons via x-icon.* Blade components |
| Font | Fredoka (display/headings, 500–700) + Inter (body/UI, 400–700) |

Source: CONTEXT.md code_context §Integration Points + resources/css/app.css

---

## Spacing Scale

Standard 8-point scale is already locked in app.css @theme. Listed for
executor reference:

| Token | Value | Usage |
|-------|-------|-------|
| xs | 4px (--spacing-1) | Icon gaps, chip padding |
| sm | 8px (--spacing-2) | Compact element spacing |
| md | 16px (--spacing-4) | Default element spacing |
| lg | 24px (--spacing-6) | Card internal padding |
| xl | 32px (--spacing-8) | Layout gaps |
| 2xl | 48px (--spacing-12) | Section breaks |
| 3xl | 52px (--spacing-13) | Primary button / auth button height |

Exceptions:
- Stepper buttons (PWA offline form): **56px × 56px** (`w-14 h-14`) — up from
  current w-11/h-12 (44×48px). Required for confident thumb/sun use (D-05,
  FINDINGS P2). Apply to both +/− steppers at `create.blade.php:120-124,135-139`.
- Touch targets everywhere else: ≥ 44px minimum (PRODUCT.md accessibility, not
  changed by this phase).

Source: DESIGN.md §5 Components §Steppers; FINDINGS P2 steppers; D-05
(CONTEXT.md).

---

## Typography

Locked system — no changes in this phase. Listed for executor reference:

| Role | Size | Weight | Line Height | Font |
|------|------|--------|-------------|------|
| Display | clamp(2.6rem, 5vw, 4rem) | 700 | 1.05 | Fredoka |
| Headline | clamp(1.875rem, 3vw, 2.5rem) | 700 | 1.1 | Fredoka |
| Title | 1.25rem | 600 | 1.3 | Fredoka |
| Body | 1rem | 400 | 1.6 | Inter |
| Label / kicker | 0.75rem | 700 | 1.2 | Inter, letter-spacing 0.18em, uppercase |

Phase-specific rule: `@section('title', …)` interpolation in Blade must use
`.` concatenation, not `{{ }}` inside the string argument. Fix:
`admin/clients/edit.blade.php:3` → `@section('title', $client->name . ' · Modifier · Dlo Azur')`.
Source: FINDINGS P1 §Titre admin client edit.

---

## Color

Locked system — this phase adds ONE token override and THREE missing nuances.

### Locked palette (for reference)

| Role | Token | OKLCH | Usage |
|------|-------|-------|-------|
| Dominant surface | sand-50 | oklch(0.987 0.005 85) | Body bg, cards, inputs |
| Secondary surface | sand-100 | oklch(0.967 0.008 84) | Stepper minus, pre-input, chips |
| Primary action | azure-500 | oklch(0.615 0.211 256) | CTA buttons, links, focus ring |
| Dark surface | navy-900 | oklch(0.232 0.052 251) | Admin sidebar, hero, footer |
| Accent — lagon | lagon-500 | oklch(0.720 0.113 207) | Active states, synced badges, portail Pierre section |
| Accent — sun | sun-500 | oklch(0.760 0.150 72) | Google stars, rare human CTAs |
| Semantic success | success | oklch(0.700 0.150 155) | Sync OK, Eau saine badge (gated) |
| Semantic warn | warn | oklch(0.800 0.130 80) | Offline banner bg (always ambre, never rouge) |
| Semantic danger | danger | oklch(0.620 0.210 25) | Critical errors only |
| WhatsApp | whatsapp | #25D366 | WhatsApp button only — intouchable |

### D-02: White override (P1 fix — theming systemic)

Add to `@theme` block in `resources/css/app.css`:

```css
--color-white: var(--color-sand-50);
```

This single override redirects all `bg-white` / `text-white` occurrences
(~90 hits across vitrine + admin + email) to warm sable instead of pure
`oklch(1 0 0)`. No class-by-class sweep needed.

**Contrast verification:** `sand-50` ≈ `oklch(0.987)` vs azure-500
`oklch(0.615)` → contrast ratio unchanged from pure white. WCAG AA holds.
Confirm visually after applying, especially `text-white` on azure-500 and
navy-900 backgrounds. No regression expected (D-04).

**Exceptions that must NOT be affected** (D-03):
- WhatsApp `#25D366` — uses `--color-whatsapp`, not the white token. Safe.
- QR card intentional white background — if rendered via explicit hex or a
  dedicated token rather than `bg-white`, verify it does not inherit the
  override. If it does use `bg-white`, scope the override with a specific class
  (e.g. `bg-qr-white`) or use `bg-[oklch(1_0_0)]` inline on that card only.

### D-05: Missing @theme nuances (P1 fix — silent token gaps)

Add to `@theme` in `resources/css/app.css`. Choose one approach per token:

| Broken class | File:line | Resolution | New token value |
|---|---|---|---|
| `bg-lagon-50/40` | `philosophie.blade.php:4` | Add `--color-lagon-50` | `oklch(0.965 0.030 202)` (extrapolated from lagon-300 ramp, lighter than lagon-300 by ~0.11 L) |
| `text-warn-700` | `agenda/index.blade.php:59` | Add `--color-warn-700` | `oklch(0.550 0.110 72)` (darker warm amber, same hue as warn, ~0.25 L lower) |
| `text-ink-600` | `agenda/index.blade.php:76` | Remap to existing `text-ink-700` | No new token; substitute class in blade |

Rationale: `lagon-50` warrants a new step (the ramp currently starts at 300,
needing a very light tinted surface). `warn-700` warrants a new step (needed
for readable dark text on light warn-bg). `ink-600` does not exist and `ink-700`
(`oklch(0.445 0.030 250)`) is adequate — remap the class.

**Do NOT add `ink-600`** — the existing `ink-700` is the correct token. Use
`text-ink-700` in `agenda/index.blade.php:76`.

---

## Interaction Contracts

### Loading states — Livewire live-search lists (P1)

Target: `livewire/client-index.blade.php:22-28`, `post-index.blade.php:22-28`

Pattern (apply to both):
```html
<!-- On the list container -->
<div wire:loading.class="opacity-50 pointer-events-none" wire:target="search">
    {{-- existing list rows --}}
</div>
<!-- Skeleton row (shown only while loading) -->
<div wire:loading wire:target="search"
     class="h-12 bg-sand-100 rounded-xl animate-pulse"
     aria-hidden="true"></div>
```

Rules:
- Opacity `opacity-50` on container (not hidden) so layout does not jump.
- Skeleton uses `bg-sand-100`, not white. `animate-pulse` is acceptable (CSS
  animation on opacity only — compliant with `prefers-reduced-motion` clamp in
  app.css).
- `aria-hidden="true"` on skeleton (decorative).
- No spinner glyph required.

Source: CONTEXT.md code_context §Established Patterns; FINDINGS P1.

### Submitting states — auth buttons (P1)

Target: `login.blade.php:90`, `forgot-password.blade.php:55`,
`reset-password.blade.php:72`, `magic-link-request.blade.php:52`,
`confirm.blade.php:57`

Pattern (Alpine `@submit` on the `<form>`):
```html
<form @submit="$el.querySelector('[type=submit]').disabled = true;
               $el.querySelector('[type=submit]').textContent = 'Envoi…'">
```

Or extract to a small inline Alpine component with `x-data="{ sending: false }"`:
```html
<form x-data="{ sending: false }" @submit="sending = true">
    <button type="submit"
            :disabled="sending"
            :class="sending ? 'opacity-60 cursor-not-allowed' : ''"
            x-text="sending ? 'Envoi…' : 'Recevoir mon lien'">
    </button>
</form>
```

Rules:
- Label in sending state: **`Envoi…`** (for magic-link); **`Connexion…`**
  (for login submit); **`Envoi…`** (for forgot-password, reset-password,
  confirm). No spinner SVG — plain text label change is sufficient.
- Disabled state: `opacity-60 cursor-not-allowed`. Do NOT remove the button
  from the DOM.
- `prefers-reduced-motion`: the only "animation" here is the opacity on
  disable, which is an instantaneous state, not a transition. No motion
  concern. Do NOT add spinner `animate-spin` without `motion-safe:` guard.
- Button must NOT be bumped in size by this change. Auth buttons are already
  `h-13` (3.25rem) per DESIGN.md; respect that height.

Source: FINDINGS P1 §Aucun état submitting; CONTEXT.md §Established Patterns.

### Admin dashboard restructure — layout contract (D-10, P2)

Target: `admin/dashboard.blade.php`

**Hierarchy (top to bottom):**

1. **Agenda du jour** — primary block, full width or 2/3 width on desktop.
   Content: reuse the « Aujourd'hui » + « À revoir » section already rendered
   on `agenda/index`. Either include the partial or Livewire embed. This is
   the first thing Pierre sees; it must dominate visually.

2. **Signaux actionnables** (2-column row, below agenda):
   - Card « À synchroniser » (offline queue count) — clickable → `passages.index`
     filtered by `status=pending`. Must use `<a>` wrapper or `wire:navigate`,
     not just decorative.
   - Card « Eau à surveiller » (count of passages with out-of-range values) —
     clickable → `passages.index` filtered by `needs_attention=true` (or
     existing filter). Same requirement.
   - Both cards: on zero count, render in success/neutral state (not warn).
     On non-zero, render in `warn-bg` / `warn` ring.

3. **Vanity counts** (demoted, compact strip or removed):
   - « Clients actifs » + « Passages cette semaine » — either merge into a
     single compact strip with `text-ink-500 text-sm`, or remove entirely if
     they add no action path. Must NOT be the same visual weight as the
     actionable cards above.

**Visual breaking of 4-identical-cards uniformity (D-10d):**
- Agenda block must be visually distinct from stat cards (larger, different
  background — e.g. `navy-900` surface or prominently bordered).
- No four cards of identical size, identical background, identical border
  treatment. At minimum: agenda block takes full width; the two actionable
  cards take a 2-col grid; the vanity strip is text-only or uses `text-sm`.

**Clicability contract:**
- Clickable cards must show `cursor-pointer` and `hover:shadow-md` (per
  DESIGN.md §4 elevation rule: shadow appears at hover, not at rest).
- Clickable cards must NOT rely solely on color to signal interactivity;
  include a chevron icon or « Voir » label as affordance.

Source: DECISIONS D-10; FINDINGS P2 §Dashboard.

---

## Copywriting Contract

### Register rules (locked)

| Surface | Register | Authority |
|---------|----------|-----------|
| All admin UI, PWA offline | tu (align to « Ta semaine ») | D-07 |
| All client-facing (portail, emails, magic-link, login client) | vous strict | D-08 |

### Operator-facing copy fixes (D-07)

Apply `tu` consistently; current `vous` occurrences to fix:

| Location | Current (wrong) | Correct |
|----------|----------------|---------|
| `client-index.blade.php:72` empty state | « votre premier client » | « ton premier client » |
| `post-index.blade.php:78` empty state | « votre premier article » | « ton premier article » |
| `offline.blade.php:16-17` | « vous » form | `tu` form |
| `passage-form.js:184` toast | « vérifiez » | « vérifie » |

The anchor is `dashboard.blade.php:19-21` « Ta semaine » — this is already
correct and must not be changed.

### Pierre naming — vitrine copy (D-09)

Pierre is named in code at two permitted locations only:
- `resources/views/vitrine/partials/pierre.blade.php` (the "Le pisciniste" section)
- Footer + mentions légales

All other vitrine surfaces must use « nous » / « Dlo Azur » / « notre équipe »:

| Location | Broken form | Replace with |
|----------|------------|--------------|
| `philosophie.blade.php:32,54` | « Pierre … » | « nous » / « notre approche » |
| `final-cta.blade.php:11,24` | « Pierre … » | « Nous » / « Dlo Azur » |
| `contact-form.blade.php:12,24` | « Pierre … » | « nous » |
| `zones/*.blade.php` (4 files) « Appeler Pierre » | « Appeler Pierre » | « Nous appeler » |
| `services/depannage.blade.php:14,20,53` | « Pierre … » | « nous » |
| `services/analyse-eau.blade.php:79,82` | « Pierre … » | « nous » |
| `diagnostic.blade.php:161,165` | « Pierre … » | « nous » |

Source: D-09; DESIGN.md §6 Don't « dupliquer Pierre par son nom en hero/marketing copy ».

### Key CTA labels (phase-specific)

| Element | Copy |
|---------|------|
| Magic-link request button (default state) | « Recevoir mon lien » |
| Magic-link request button (sending state) | « Envoi… » |
| Login button (sending state) | « Connexion… » |
| Dashboard card — sync empty | « Tout est synchronisé » |
| Dashboard card — sync non-zero | « N passage(s) en attente · Synchroniser » (clickable) |
| Dashboard card — eau à surveiller non-zero | « N piscine(s) à vérifier · Voir » (clickable) |
| Portail empty state — 1 passage | « Votre premier passage est ci-dessus. L'historique se remplira à chaque entretien. » |
| Portail — photo absente | « Photo non disponible pour ce passage. » |
| Portail — temporaryUrl fallback | « Photo non disponible pour ce passage. » (same, shown in @else of @if ($firstPhotoUrl)) |
| Témoignages placeholder (P0) | « [Avis à fournir par Pierre — capture Google ou citation vérifiée] » |
| « Eau saine » badge (gated) | Badge rendered only when `$phOk && $clOk && $tacOk`. Hidden otherwise — no replacement text needed. |

### Error / edge-state copy

| Element | Copy |
|---------|------|
| Magic-link error `@error('ml')` | Use message from controller (already written) — do not override. Render in `text-danger bg-danger/10 ring-1 ring-danger/30 rounded-xl p-3 mt-4 text-sm`. |
| Zombie uploading recovery (silent) | No visible copy — recovery is silent (re-queue to pending on init). Confirmation appears only after successful flush (see below). |
| Sync success confirmation | « Tout est synchronisé ✓ » — shown briefly after flush, token `success`, calm (not toast-assertive). |

---

## Phase-Specific Fix Contracts

### Google Reviews stars (P1)

Target: `livewire/google-reviews.blade.php:17,49`

Rules:
- Use `text-sun-500` (not `text-yellow-400`). `sun-500` = `oklch(0.760 0.150 72)` — the declared token.
- Summary stars: render filled/empty over 5 based on rounded `$avg`. Pattern:
  ```blade
  @for ($i = 1; $i <= 5; $i++)
      @if ($i <= round($avg))
          <span class="text-sun-500">★</span>
      @else
          <span class="text-sun-300">★</span>
      @endif
  @endfor
  ```
- Per-review stars: same pattern over `$review->rating` (filled) vs remainder to 5 (empty, `text-sun-300`).
- `number_format` separator: change `'\u{202F}'` (P3) to `"\u{202F}"` (double quotes for PHP unicode escape) or use the literal narrow no-break space character directly.

Source: FINDINGS P1 §Étoiles Google Reviews; P3 §number_format.

### Email magic-link (P1)

Target: `resources/views/emails/magic-link.blade.php`

Rules:
- Remove `border-left: 3px solid …` side-stripe. Replace with a solid ring or
  tinted background block for emphasis. No side-stripe anywhere (DESIGN.md §6
  Don't "bordure latérale colorée").
- Hex values in email are acceptable (email clients strip OKLCH); add a comment
  mapping each hex to its token:
  - `background: #fefdf8` → `sand-50`
  - `color: #1a2c40` → `navy-900` (approx)
  - `color: #0080ff` → `azure-500`
  - `background: #154c79` → `navy-600`
- Verify `#0080ff` and `#154c79` match `azure-500`/`navy-600` rendered values.

### Portail @if temporaryUrl guard (P2)

Target: `passage-timeline.blade.php:183-196`

```blade
@if ($firstPhotoUrl)
    <img src="{{ $firstPhotoUrl }}" alt="Photo du passage du {{ $passage->date->format('d/m/Y') }}" class="photo-grade …">
@else
    <p class="text-ink-500 text-sm">Photo non disponible pour ce passage.</p>
@endif
```

Photo disk is Cloudflare R2 (not Scaleway — see memory `photos-disk-is-r2-not-scaleway`).

### Portail 1-passage history state (P2)

Target: `passage-timeline.blade.php:208` (branch `count() > 1`)

When `count() === 1`, show:
```html
<p class="text-ink-500 text-sm mt-4">
    Votre premier passage apparaît ci-dessus.
    L'historique se remplira à chaque entretien.
</p>
```

When `isEmpty()`, existing empty state applies (no change).

### Portail « Eau saine » gate (P1)

Target: `livewire/portail/passage-timeline.blade.php:64-70,99-100,114-115,129-130`

Replace `@if ($lastPassage)` guard on the badge with:
```blade
@if ($lastPassage && $phOk && $clOk && $tacOk)
    {{-- badge Eau saine --}}
@endif
```

`$phOk`, `$clOk`, `$tacOk` are already computed in the component (per
CONTEXT.md code_context §Reusable Assets). Do not recompute.

### Auth tab client / demo button (P2)

- `login.blade.php:97-121` disabled client tab: change the disabled CTA to
  a link → `route('portail.magic-link.request')`, or remove the tab entirely.
  Do not leave a "Bientôt disponible" dead-end coexisting with a live magic-link page.
- `magic-link-request.blade.php` demo button « Démo Client »: change from
  `bg-azure-500` primary to secondary style (`bg-sand-50 ring-1 ring-azure-200
  text-azure-700`) matching « Démo Admin » style.

### A11y fixes (P1/P2)

| Fix | Location | Contract |
|-----|----------|---------|
| `aria-live` offline banner | `create.blade.php:76` | Change `assertive` → `polite`. Offline state is reassuring, not urgent. |
| Landmarks admin layout | `layouts/admin.blade.php:18-20,25-27,35` | Outer wrappers become `<div>`; semantic landmarks live in the included components. Remove dead `<nav>` at L35. |
| Badge sync mobile `aria-live` | `mobile-bottom-nav.blade.php:55` | Add `aria-live="polite"` matching desktop sidebar (`sidebar.blade.php:93`). |

### Bottom-nav mobile (P2)

Target: `mobile-bottom-nav.blade.php`

- Remove « Factures » (grisé désactivé) from the 5-slot grid.
- Replace with « Blog » if Pierre uses the blog admin on mobile; otherwise
  reduce to 4 slots (`grid-cols-4`).
- Badge `aria-live="polite"` + visible label on mobile (mirrors desktop).

### Recap nav (P2)

Add « Récap » item to `sidebar.blade.php` pointing to `admin.recap.index`.
Consider adding to `mobile-bottom-nav` if slot available after the above fix.

### Glyphes off-token (P2/P3)

Replace bare unicode checkmarks with `<x-icon.check>` in a chip:

| Location | Current | Replace with |
|----------|---------|-------------|
| `contact-form.blade.php:10` | `✓` text-3xl | `<x-icon.check class="w-5 h-5 text-success">` inside `<span class="inline-flex items-center justify-center rounded-full bg-success/10 p-1">` |
| `eau-verte-urgence.blade.php:124,139` | unicode checkmark | same pattern |

### Chip count agenda (P3)

Target: `agenda/index.blade.php:29-31`

Change azur chip for count → neutral: `bg-sand-100 text-ink-500`. Azure is
reserved for selection/active state.

### Topbar search (P3)

Target: `topbar.blade.php:28-31`

Hide the search input except on routes where it is wired (`clients.index`,
`passages.index`, `posts.index`). On other routes (dashboard, agenda, recap,
show), either hide entirely or render as a link → `clients.index?search=`.

### OKlch inline diagnostic (P3)

Target: `diagnostic.blade.php:121,125,133`

Replace inline `oklch(…)` declarations for lagon with token classes:
`bg-lagon-500/12 text-lagon-600`.

### WhatsApp hex inline (P3)

Target: `magic-link-request.blade.php:109`, `404.blade.php:23`

Replace `bg-[#25D366]` → `bg-whatsapp`. Token `--color-whatsapp: #25d366` is
already declared in @theme.

### Em-dashes prose (P3)

Target: `philosophie.blade.php:32`, `depannage.blade.php:56`,
`entretien-recurrent.blade.php:105`, `clients/edit.blade.php:3` (title),
`blog/edit.blade.php:3` (title).

Replace `—` em-dashes used as prose separators with `,` or `·`. En-dashes
`–` in numeric ranges (e.g. `7,2–7,6`) are correct — do not change those.

---

## Registry Safety

| Registry | Blocks Used | Safety Gate |
|----------|-------------|-------------|
| shadcn official | none | not applicable — no shadcn |
| Third-party | none | not applicable |

No new component registries in this phase. All fixes use existing Blade
components, Livewire patterns, and Alpine.js.

---

## Forces to Preserve (do not break)

From FINDINGS §Forces à préserver — executor must not regress these:

1. **Offline discipline**: UUID in IDB before any input, autosave debounced,
   failed uploads → `pending` (not `error`), soft-fail sync. Do not change
   this flow while fixing P0.
2. **Ambre hors-ligne tone**: offline banner stays `warn-bg` / `warn` ring,
   reassuring copy. Never add red to an offline state.
3. **Auth copy**: anti-enumeration, non-disclosing messages, random 1-3s delay.
   Do not change controller logic while wiring `@error('ml')`.
4. **Vouvoiement portail**: every client-facing surface stays `vous`. D-08 is
   a hard lock.
5. **OKLCH system**: never introduce `#000`, `#fff` pure, or neutral-grey
   shadows. All new tokens must use OKLCH.

---

## Checker Sign-Off

- [ ] Dimension 1 Copywriting: PASS
- [ ] Dimension 2 Visuals: PASS
- [ ] Dimension 3 Color: PASS
- [ ] Dimension 4 Typography: PASS
- [ ] Dimension 5 Spacing: PASS
- [ ] Dimension 6 Registry Safety: PASS

**Approval:** pending
