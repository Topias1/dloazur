---
target: "https://dloazur-main-s8e8er.laravel.cloud"
total_score: 27
p0_count: 1
p1_count: 1
timestamp: 2026-05-29T10-02-48Z
slug: dloazur-main-s8e8er-laravel-cloud
---
# Critique — Vitrine Dlo Azur Piscines (https://dloazur-main-s8e8er.laravel.cloud)

Brand register. Inspected `/`, `/services`, `/realisations`, `/contact`, `/blog` at 1440×900 + 390×844. Two independent assessments (design review + detector/browser evidence), synthesized.

## Design Health Score — 27/40 (solid, above the slop line)

| # | Heuristic | Score | Key issue |
|---|-----------|-------|-----------|
| 1 | Visibility of system status | 3 | Contact form required-markers clear; no success/error state inspected |
| 2 | Match system & real world | 4 | Voice excellent: "Une piscine verte ? On répond.", "pas un call-center", FR correct |
| 3 | User control & freedom | 3 | Blog "Lire l'article" → 404 dead ends |
| 4 | Consistency & standards | 3 | Tokens consistent; em-dash inconsistency in copy |
| 5 | Error prevention | 3 | Required fields marked, tel optional/labeled |
| 6 | Recognition over recall | 3 | Mobile nav clipped: hidden items need scroll, no affordance |
| 7 | Flexibility & efficiency | 3 | 3 contact paths — efficient but redundant |
| 8 | Aesthetic & minimalist | 2 | Homepage too long/repetitive (12 sections); CTA triplication |
| 9 | Help users with errors | 2 | Unbranded bare "Not Found" 404 |
| 10 | Help & documentation | 2 | Good service detail; no FAQ for B2B |
| **Total** | | **27/40** | Good real-world site; held back by repetition, redundancy, placeholders |

## Anti-Patterns Verdict — NOT AI slop

Real photography of the real operator (Pierre poolside, authentic avant/après pools), coherent OKLCH azure/marine system, Fredoka/Inter exactly per DESIGN.md. Reads as "a real pisciniste made an effort."

Absolute bans: side-stripe borders none; gradient text none; hero-metric template avoided (uses trust bullets); modal-as-first-thought none. **Em dashes: FAIL** (banned) — ~14 in home+services running copy. Identical-card-grid: partial (4 engagement cards home, 9 service cards). Glassmorphism: detector flagged 6 (info) but all functional nav/scrim over photo — false positives, not defects.

**Deterministic detector** (detect.mjs on rendered HTML, 5 files, exit 2, 10 findings): em-dash warn ×18 (home 6, realisations 4, services 8 — several in `<title>`/og meta = low relevance), identical-card-grid ×2 (real), glassmorphism info ×6 (false positives). Detector agrees with the review on em-dash + card grids.

## What's Working
1. **Real photo, real operator, real proof** — keeps it out of slop; avant/après green→clear is the emotional peak.
2. **Disciplined brand-color commitment** — navy hospitality band + azure CTA band full-bleed, OKLCH throughout, lagon/gold rare. Matches DESIGN.md.
3. **Voice + B2B in one voice** — hospitality served via one navy section, resists the "two brands" trap.

## Priority Issues

**[P0] Mobile primary nav clipped / sub-44px.** At 390px the nav is a 561px overflow-x strip in a 366px container; "Le pi…", "Espace client" off-screen; no hamburger, hidden scrollbar = no affordance. Nav pills 32px tall. **Espace client (the portal) is undiscoverable on mobile** — core value prop lost. Fix: real mobile menu (hamburger → sheet), pills ≥44px. → `adapt`

**[P1] Placeholder/broken trust assets in production.** Footer QR is a `QR / TODO` box, `/assets/brand/qr.png` 404s every page (masked by onerror). Blog "Lire l'article" → 404, unbranded "Not Found". Worst at the B2B reliability moment. Fix: real QR or remove; publish or unlink articles; branded 404. → `harden`

**[P2] Em dashes in copy (brand ban).** ~14 in home+services running copy ("Avant / après — 48h chrono", "expertise locale — solutions…"). Fix: commas/colons/periods. → `clarify`

**[P2] No price signal at all.** Hero price line removed AND `/tarifs` 404s → zero price anchor anywhere. Decide: is "devis gratuit" enough, or move friction to inbox? → `clarify`

**[P3] CTA redundancy.** Hero = 4 actions (devis + diagnostic + WhatsApp + FAB); contact page shows WhatsApp ~4× in one viewport. Choice paralysis. Fix: one primary + one secondary per surface. → `distill`

**[P3] Performance / payload.** `piscine-propre.jpg` 2.27 MB (3840×2160), hero 631 KB, no srcset — heavy for mobile-first Martinique networks. → `optimize`

## Persona Red Flags
- **Homeowner on phone in sunlight (primary):** can't reach Espace client (clipped nav); 13,000px scroll of repeated reassurance; no price anywhere. Light theme + navy scrims good for sunlight.
- **Villa/conciergerie manager (B2B reliability):** dedicated navy section is smart, then hits QR TODO box, uncaptioned amateur gallery, dead blog links, zero social proof (no testimonial, no villa logo, no "X piscines suivies"). Most-damaged persona.
- **First-time visitor:** strong hero but 4 CTAs + 12 repetitive sections make "what do I do / how much" fuzzy; avant/après saves it.

## Minor Observations
- Console warning every page: "Detected multiple instances of Alpine running" (Livewire + own Alpine) — dedupe.
- Service worker registers OK (scope `/build/`).
- No horizontal overflow at 390px (clean) except the intentional nav strip.
- All pages exactly one h1, clean outline, 0 missing alt, contact form fully labeled, honeypots aria-hidden.
- Reduced-motion honored.
- Hero light-blue body text (`oklch(0.908 …)`) + 10%-white glass buttons over the photo = at-risk contrast (not computable vs JPEG); manual AA check advised.

## Questions to Consider
1. If a villa manager only sees the homepage, what one piece of proof convinces them you're reliable at scale — and is it currently a TODO box?
2. With the hero price gone and /tarifs 404ing, the site has no price signal at all — intended, or accidental friction moved to the inbox?
3. Does the homepage need 12 sections, or would 6 with avant/après up top convert faster?
4. Three WhatsApp entry points on /contact — flexible, or signalling you don't trust your own form?
