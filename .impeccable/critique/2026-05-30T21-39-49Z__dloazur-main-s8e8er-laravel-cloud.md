---
target: full app (vitrine 31/40 · métier 21 · portail 30 · diagnostic broken); 4 P0
total_score: 31
p0_count: 4
p1_count: 5
timestamp: 2026-05-30T21-39-49Z
slug: dloazur-main-s8e8er-laravel-cloud
---
# Critique — Dlo Azur Piscines, full app (vitrine + métier + portail + diagnostic)

**Target:** live staging `https://dloazur-main-s8e8er.laravel.cloud` — all features, pre-Pierre prototype review.
**Method:** Two independent assessments synthesized. **A** = design review by 3 independent sub-agents (brand vitrine / métier admin / portail+diagnostic), each over blade+JS source, rendered HTML, accessibility snapshots, and the May-29 home screenshots. **B** = deterministic detector (`detect.mjs`) on 9 rendered public pages + live console capture across every surface. Live screenshots/overlays were **not available** (5s tool timeout vs. slow staging tunnel); the visual layer was assessed from rendered DOM + source + the 4 existing home-breakpoint PNGs. Prior runs were vitrine-only (27 → 35); this run widened to all features, so the brand score moves under stricter, broader coverage, not because previously-fixed items regressed (re-verified: mobile nav, footer QR, blog links, branded 404 all still fixed).

---

## Design Health Score — by surface

| Surface | Register | Score | Band |
|---|---|---|---|
| Vitrine (home, services+details, réalisations, zones, blog, legal, contact, 404, diagnostic intro) | brand | **31 / 40** | Good, with rough edges |
| Métier admin app (dashboard, clients, **passage saisie**, auth) | product | **21 / 40** | Needs work |
| Client portal (magic-link, timeline) | product | **30 / 40** | Good (upper) |
| Diagnostic wizard (commercializable) | hybrid | **5 / 40 shipped** (≈28–30 if fixed) | Broken / unusable |

### Vitrine — Nielsen
| # | Heuristic | Score | Key issue |
|---|---|---|---|
| 1 | Visibility of status | 3 | Diagnostic CTAs feed a wizard that errors on load |
| 2 | Match real world | 4 | Pitch-perfect artisan French |
| 3 | User control | 3 | Good; menu escape, comparator keys |
| 4 | Consistency | 2 | Two button systems; emoji icons mixed with SVG; dead `bg-lagon-50/40` token |
| 5 | Error prevention | 3 | Dead footer FB/IG `href="#"` |
| 6 | Recognition | 4 | Labelled persistent nav |
| 7 | Flexibility | 3 | CTA-saturated but reachable |
| 8 | Aesthetic/minimalist | 3 | Strong home; emoji + 4 duplicate zone pages drag it |
| 9 | Error recovery | 3 | On-brand 404 ✓ |
| 10 | Help/docs | 3 | Service-detail pages double as help (excellent); zones offer none |
| **Total** | | **31/40** | |

### Métier admin — Nielsen
| # | Heuristic | Score | Key issue |
|---|---|---|---|
| 1 | Visibility of status | 1 | **No success confirmation after passage save** |
| 2 | Match real world | 2 | Steppers-from-zero; "Phase 2"/"Bonjour Démo" leak |
| 3 | User control | 3 | Back/retry/reset present |
| 4 | Consistency | 2 | 3 states of "Nouveau passage"; doubled title; literal `{{ }}` in clients/show title |
| 5 | Error prevention | 2 | **Orphan-passage hole** (no client picker, API nullable) |
| 6 | Recognition vs recall | 2 | Contextless saisie names no client |
| 7 | Flexibility/efficiency | 1 | pH 7.2 ≈ 72 taps; Sel 4.0 ≈ 40 taps |
| 8 | Aesthetic/minimalist | 3 | Clean; dual-search + "bientôt" clutter |
| 9 | Error recovery | 3 | 409 + soft-range toasts good; no photo retry on grid |
| 10 | Help/docs | 2 | No first-run model for offline/steppers |
| **Total** | | **21/40** | |

### Client portal — Nielsen
| # | Heuristic | Score | Key issue |
|---|---|---|---|
| 1 | Visibility of status | 3 | No "last synced" provenance on pool card |
| 2 | Match real world | 4 | "Eau saine", "Mot du pisciniste" — excellent |
| 3 | User control | 2 | **History not clickable — no per-passage detail** |
| 4 | Consistency | 3 | Strong tokens; email diverges hard |
| 5 | Error prevention | 3 | Throttle + expiry surfaced |
| 6 | Recognition | 3 | History rows lack status color |
| 7 | Flexibility | 2 | No history filter/PDF/share for B2B |
| 8 | Aesthetic | 4 | Calm, one strong navy surface |
| 9 | Error recovery | 3 | WhatsApp fallback on login ✓; photo URL failure silent |
| 10 | Help/docs | 3 | No "what is TAC?" for nervous owners |
| **Total** | | **30/40** | |

---

## Anti-Patterns Verdict — does it look AI-made?

**LLM read: NO on the marquee surfaces, slop-adjacent on the long tail.** The home hero (real Pierre on the pole, Martinique baie, marine scrim), the keyboard-accessible before/after comparator, OKLCH azure/marine system, Fredoka/Inter — all specific and human. It reads "a real pisciniste made an effort." Aesthetic lane = warm coastal artisan, on North Star. Where it slips: the **four zone pages are one template with the city swapped** (with `[FAIT LOCAL REQUIS]` placeholder comments still in the source) — the doorway-page pattern; and **emoji-as-iconography** (✅💡🏆🤝🎯) in the service-detail pages, sitting next to the project's own custom SVG icons.

**Deterministic detector (`detect.mjs`, 9 rendered pages, exit 2, 23 findings):**
- **em-dash ✕ — REAL, systemic on conversion pages.** diagnostic 26 (incl. body "ton problème — gratuit" L243), service-detail 7 (visible cause list), réalisations 7, services 4, zone 2. Home / blog / cgv / confidentialité = 0. Brand bans em dashes; this is the clearest "AI prose" tell, and it ships on the exact pages meant to convert.
- **identical-card-grid ✕ — mild.** services 9×, engagements (home) 4×, zone 4×. Card-appropriate content, but the 9× services grid is the most slop-prone.
- **kicker-repetition — mostly false positive.** Flagged on 6 pages, but DESIGN.md defines kickers (lagon-600 uppercase labels over titles) as a deliberate system. Service-detail's 8× and diagnostic's 7× are heavy enough to glance at.
- **glassmorphism (info ×21) — false positives.** All are the functional floating nav, the mobile menu sheet, and photo-scrim pills. Functional glass is allowed; not defects.

**Visual overlays:** not available this run — script injection / screenshots blocked by tunnel latency. No user-visible overlay was produced; this is recorded as a fallback signal, not a clean overlay pass.

---

## Overall Impression

Two different products live here. The **engineering foundation is genuinely excellent** — the offline-first saisie plumbing (IndexedDB draft auto-save, amber-not-red offline banner, per-photo sync badges, HEIC→JPEG, retry backoff, 409 handling), the magic-link auth that defends against M365 SafeLinks token-burning, the server-only dose computation with hard-stop safety on dangerous chemicals. This is considered, not generated.

But the **experience fails at its two most important moments**, and both are exactly what Pierre will touch first:

1. The **commercializable diagnostic — the freshest deploy, promoted in the nav, literally "Validé par Pierre" — is dead on arrival.** A single stray `"` inside a French code comment crashes the whole client-side wizard; a visitor clicks the big CTA and gets a blank scroll.
2. The **passage-saisie screen — the entire reason the app exists — is the lowest-scoring surface.** Entering a normal pH takes ~72 taps, saving gives zero confirmation, and the most prominent "Nouveau passage" button silently creates orphan passages with no client attached.

The single biggest opportunity: **the chrome out-polishes the core.** Before showing Pierre, fix the four things he'll hit, and the foundation underneath will carry the demo.

---

## What's Working

1. **Offline-first saisie plumbing is real, not theater.** `crypto.randomUUID()` persisted at mount, debounced IndexedDB draft save, `online`/`visibilitychange` flush fallbacks for iOS, 2s/8s/30s backoff, 409 conflict tied to a server `WHERE status='draft'` upsert, magic-byte HEIC detection (distrusting iOS `file.type`), per-photo status badges. This is the load-bearing core and it's well-built.
2. **Brand discipline holds where it counts.** Real photography throughout, OKLCH everywhere, amber-not-red for offline/queued states honored end-to-end, the before/after comparator (drag + full keyboard + `role="slider"`) is the strongest single component on the site — it *shows* the 48h result instead of claiming it.
3. **The client portal + magic-link flow are trustworthy and humane.** SafeLinks-safe GET/POST split, WhatsApp "Pas d'email ?" recovery, "Connecté par lien sécurisé · aucun mot de passe", "idéal" qualifiers on in-range measures. 30/40, the quiet success of the build.
4. **The diagnostic's safety architecture (as designed) is responsible.** Doses server-side only, disclaimer server-guarded, amber safety block ("ne mélange jamais deux produits"), red hard-stop telling a homeowner *not* to attempt acid/230V cases, and an honest confidence chip that downgrades to "Indicatif — demande à Pierre" when data is thin. The best UX decision in the codebase, currently unreachable.

---

## Priority Issues (ranked across the whole app)

### [P0] The diagnostic wizard is dead on arrival
**What:** `/diagnostic` throws 32 JS errors; only the intro hero renders, the interactive wizard never mounts, both CTAs jump to an empty anchor. **Root cause (verified):** the inline Alpine `x-data` object in `resources/views/livewire/diagnostic-wizard.blade.php` (≈line 184) contains a JS comment `// …bouton "J'ai appliqué le plan")` — the literal `"` closes the `x-data="…"` HTML attribute early, truncating the object and cascading into `step/wizardStep/sizeMode/getNode/getResult/$wire is not defined`. A **second** latent bug: `confidenceLabel()` (L1549) is defined only in `diagnostic-carnet.js`, not in the wizard scope, so the carnet view will throw even after the quote fix. The 424 passing tests are server-side and never exercised the rendered Alpine.
**Why it matters:** The flagship commercializable tool, promoted in nav and "Validé par Pierre," renders nothing for an anxious owner with a green pool. Maximally trust-destroying on the exact surface meant to convert.
**Fix:** Remove the `"` from the comment (immediate). Then extract the 1655-line controller into `Alpine.data('diagnosticWizard', …)` in a JS module (it currently inlines a ~52KB attribute and embeds the decision tree twice), add `confidenceLabel`, and add ONE browser smoke test asserting the wizard mounts.
**Command:** `/gsd:debug` → `harden`

### [P0] Passage saisie: steppers from zero make field entry unusable
**What:** Each measure is a display `<span>` mutated only by `incr/decr` starting at `parseFloat(field) || 0` (`passage-form.js:136-148`, `create.blade.php:93-112`). No typeable input, no default, no hold-to-repeat, no last-value seed. pH 7.2 ≈ 72 taps; Sel 4.0 g/L ≈ 40 taps; TH 25 = 25 taps.
**Why it matters:** Directly violates Design Principle #1 ("saisie < 2 min, one hand, sunlight") on the one screen that "if everything else fails, must work."
**Fix:** Make the value an `inputmode="decimal"` field (tap-to-type) with steppers as fine-adjust; seed sensible defaults (pH 7.4 / last passage); add press-and-hold acceleration. Keep ≥48px targets.
**Command:** `harden` → `optimize`

### [P0] Passage saisie: no success state after save
**What:** `submit()` (`passage-form.js:280-290`) flips `saving=false` and returns — no success toast, redirect, or form reset. Conflicts and warnings have toasts; the happy path is silent.
**Why it matters:** "Ne jamais perdre une donnée" is perceived as much as actual. After ~70 taps the operator taps Save and the screen is byte-identical — he can't tell saved-and-synced from saved-locally from nothing, and may re-enter or abandon. The emotional payoff of the core flow is missing.
**Fix:** Add a success state distinguishing *synced* (green/lagon "Passage enregistré ✓") from *queued offline* (amber "partira au retour réseau"), then return to the client's history or reset for the next passage.
**Command:** `craft` → `clarify`

### [P0] Contextless "Nouveau passage" creates silent orphan passages
**What:** Topbar, sidebar, and bottom-nav all link "Nouveau passage" → `/admin/passages/create` with no `client_id`; the screen has no client/pool picker (`PassageCreateController` only reads `?client_id`); `Api/PassageController:31` validates `client_id => nullable`. Result: a passage saves 200, syncs, and is invisible in the client portal. Meanwhile `clients/show` — the only place that could pass `client_id` — has **no "Nouveau passage" CTA at all**.
**Why it matters:** Breaks the core value chain (operator logs → client sees proof) silently and permanently. Worse than a crash because nothing signals failure.
**Fix:** Add a required client/pool picker on the saisie screen when no `client_id` is present (offline-cached client list); add a prominent "Nouveau passage" CTA on `clients/show`; require `client_id` server-side or flag orphans.
**Command:** `harden` → `layout`

### [P1] Em dashes ship across conversion copy
**What:** Diagnostic hero, service-detail cause list, réalisations, services, zone (detector counts above). DESIGN.md bans em dashes; home/blog/legal already clean.
**Why it matters:** Explicit brand rule + the strongest AI-prose tell, on the pages meant to convert.
**Fix:** Sweep `—` → comma/colon/period across `vitrine/**` + diagnostic; add a build/lint gate so it can't reship.
**Command:** `clarify` / `typeset`

### [P1] Zone pages are duplicate doorway templates
**What:** schoelcher / le-lamentin / fort-de-france / les-trois-îlets share an identical hero, CTA band, and service list; only ~3 prose paragraphs differ, and all four contain `[FAIT LOCAL REQUIS]` placeholder comments; the brand name is stuffed inside the H1.
**Why it matters:** Reads as doorway-page SEO to Google and to a B2B manager who opens two; contradicts "authentique / à taille humaine."
**Fix:** Give each zone a real local photo + one genuine local fact, differentiate the hero, drop the brand from the H1, remove placeholders before launch.
**Command:** `adapt` → `clarify`

### [P1] Portal has no per-passage detail / photo proof beyond the latest visit
**What:** History rows (`passage-timeline.blade.php:297-331`) are non-interactive; the photo lightbox is bound only to the latest passage. The demo seeder attaches no photos, so even the latest shows none on staging.
**Why it matters:** "Preuve photo horodatée pour ses propriétaires" is the explicit B2B driver — surfacing proof for one day defeats it.
**Fix:** Make history items open a per-passage detail (reusing the measure grid + lightbox + notes); add per-passage PDF/share for B2B forwarding; show a thumbnail in "Dernier passage."
**Command:** `/gsd:plan-phase` (passage-detail slice) → `layout`

### [P1] Internal roadmap/demo language leaks into the operator UI
**What:** "Tableau de bord opérationnel en **Phase 2**" (`dashboard.blade.php:21`), "Bonjour **Démo**", "bientôt"/"Phase 2" nav items, doubled `<title>` ("… · Dlo Azur · Dlo Azur Piscines"), literal `{{ $client->name }}` in `clients/show` `@section('title')`.
**Why it matters:** Reads unfinished; undercuts the "irréprochable" standard, and Pierre will see "Phase 2" on his own dashboard during the demo.
**Fix:** Operator-facing subtitle ("Voici ta semaine"), fix the title interpolation + de-dupe site name, strip "Phase N" from shipped UI.
**Command:** `clarify` → `polish`

### [P1] Magic-link email violates the design system
**What:** `emails/magic-link.blade.php` uses a banned `border-left: 3px solid` side-stripe (L37), the hand-drawn fake-drop SVG instead of the real logo mark, and raw neutral-gray hex.
**Why it matters:** The email is the first brand contact and the gateway to the portal — currently the least on-brand artifact in the system.
**Fix:** Full tinted panel instead of side-stripe, real logo mark, marine-tinted grays (keep table layout / hex for email-client compatibility).
**Command:** `polish`

---

## Persona Red Flags

**Pierre (operator — one hand, sunlight, offline):**
- Taps "+" 72 times for pH; taps Save and gets no "done"; taps the obvious big "Nouveau passage" and creates an orphan; a failed photo shows "Erreur" with no retry on the grid. The screen built "for the field" is the one that fails the field.
- His dashboard greets "Bonjour Démo" and says "Phase 2."

**Anxious owner with a green pool (diagnostic):**
- Clicks "Trouver mon problème" → blank scroll → "is my phone broken?" The brand promises *soulagement*; the product delivers *is this broken?* The honest "Indicatif — demande à Pierre" reassurance is locked behind the dead init.

**B2B villa / conciergerie manager (the reliability persona):**
- A visibly broken flagship tool is disqualifying for a reliability read. Opens two zone pages, sees the same page. Taps a past date in the portal to forward photo proof to an owner — it isn't clickable. The one job this audience needs is half-built.

**First-time visitor / first-time operator:**
- Dead footer FB/IG read as "abandoned." A new operator taps the disabled topbar search first (dead), and "Passages" nav lands on a blank contextless form, not their list.

---

## Minor Observations

- "Multiple instances of Alpine running" console warning on **every** page — Alpine bundled in `app.js` AND shipped by Livewire; register once.
- `bg-lagon-50/40` is an undefined token → philosophie section background silently absent (`philosophie.blade.php`). Build swallows unknown color utilities.
- `pierre.blade.php` uses the hand-drawn `~` fake-drop SVG (memory flag) instead of `<x-icon.drop>`.
- Doubled admin shell: `layouts/admin.blade.php` wraps content in `<main>` and has an empty placeholder `<nav>`, while saisie injects its own `min-h-screen`+`<main>` — nested scroll containers on desktop.
- CGV "Dernière mise à jour : mai 2025" (stale); legal + blog body run ~80–90ch (over the 65–75ch cap).
- Invalid inline `@media {...}` inside `style="…"` attributes (diagnostic rise animation, eau-verte FAQ chevron) — silently dropped.
- Bottom-nav pending badge is `bg-warn text-white` (contrast + breaks the dark-amber-text convention used elsewhere).
- Saisie steppers are h-12 (3rem) — within ≥44px but below DESIGN.md's own 3.5rem "saisie terrain" target.
- PDF report uses `border-left` side-stripes — acceptable DomPDF (CSS-2.1) exception; worth a code comment so it isn't "fixed" into a broken ring. Its `confidence-low` → red can fire for safe "Indicatif" results; prefer amber.
- "Validé par Pierre (pisciniste)" names Pierre in marketing copy (DESIGN.md reserves naming for the "Le pisciniste" section + footer).

---

## Questions to Consider

1. Why is the operator's most-clicked button ("Nouveau passage") wired to a contextless screen that can silently destroy the client association — shouldn't a passage be un-saveable without a client?
2. If a pisciniste reads "7.2" off a tester, why is the fastest path to enter it 72 taps? What said steppers (vs. a seeded number pad) were right for *measured* values?
3. You have 424 passing tests and a public tool that renders nothing. Where is the one browser test that loads `/diagnostic` and asserts a button appears?
4. The brand bible forbids em dashes, yet they ship on every conversion page except the home. If it isn't enforced in a lint step, is it a rule or a wish?
5. "La preuve remplace le superlatif" is the thesis — so why is the proof (historical photos) the one thing the B2B persona can't retrieve?
6. The chrome is more finished than the core. Is that a priority inversion worth correcting before Pierre sees it?
