# Local impeccable detector patch

## Why this exists

The upstream `pbakaus/impeccable` skill (installed via `skills-lock.json`) ships `scripts/detect.mjs` as a thin loader that imports `scripts/detector/detect-antipatterns.mjs` — **but the `detector/` subdirectory is not committed to the public upstream repo**. So out-of-the-box, `node detect.mjs --json …` fails with `Error: bundled detector not found.` This affects anyone installing the skill from GitHub.

This folder is our local fix.

## How to install / reinstall

```bash
# from repo root
mkdir -p .claude/skills/impeccable/scripts/detector
cp tools/impeccable-detector/detect-antipatterns.mjs \
   .claude/skills/impeccable/scripts/detector/detect-antipatterns.mjs
```

The `.claude/` directory is gitignored, so the copy is lost whenever the skill is reinstalled (e.g. when `skills update` re-fetches `pbakaus/impeccable`). Re-run the two lines above and `/impeccable critique` will use the detector again.

## What it checks

Tight subset of the impeccable shared design laws + brand/product reference bans:

| Rule | Severity | What it catches |
|---|---|---|
| `em-dash` | warn | `—` in copy (use commas / colons / `·`) |
| `side-stripe-border` | error | `border-l-[2-9]` or `border-left: ≥2px` colored accent |
| `gradient-text` | error | `bg-clip-text` + gradient |
| `glassmorphism` | info | every `backdrop-blur` (human verifies each is functional) |
| `hero-metric-template` | error | 3+ adjacent big display numbers (SaaS cliché) |
| `kicker-repetition` | warn | ≥ 3 uppercase tracked labels as section grammar |
| `identical-card-grid` | warn | ≥ 4 identical card class signatures |

Findings JSON shape:

```json
{ "scanned": 1, "findingCount": 2, "findings": [
  { "rule": "em-dash", "severity": "warn", "file": "mockups/vitrine.html", "count": 4, "samples": [...] }
] }
```

Exit code: `0` clean, `2` findings (matches the upstream contract `detect.mjs` expects).

## Not maintained upstream

When the upstream skill ships its own detector, delete `.claude/skills/impeccable/scripts/detector/detect-antipatterns.mjs` and let theirs take over. The tracked source in this folder stays around as a fallback / reference.
