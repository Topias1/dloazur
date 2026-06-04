---
phase: 10-diagnostic-retours-pierre
reviewed: 2026-06-04T00:00:00Z
depth: standard
files_reviewed: 4
files_reviewed_list:
  - app/Livewire/DiagnosticWizard.php
  - resources/views/livewire/diagnostic-wizard.blade.php
  - tests/Feature/DiagnosticWizardTest.php
  - tests/Browser/CarnetLocalTest.php
findings:
  critical: 2
  warning: 4
  info: 2
  total: 8
status: issues_found
---

# Phase 10: Code Review Report

**Reviewed:** 2026-06-04
**Depth:** standard
**Files Reviewed:** 4
**Status:** issues_found

## Summary

Phase 10 removed the S0 mode-fork screen and introduced `setMode()` for chemistry/carnet deep-link attribution. The disclaimer gate, rate-limiting, and honeypot chains are structurally sound. Two critical bugs were found: the chemistry deep-link button bypasses disclaimer acceptance (the user can enter the wizard without accepting), and `updateTriedActions()` accepts an unbounded, unvalidated array from the client. Four warnings cover real-but-non-crashing issues in the Alpine state machine, the WhatsApp link double-encoding, idempotence gap in `keepDiagnostic`, and a missing test assertion. No XSS was found: tree data is passed via `@js()` (HTML-escaped) and all user-sourced values render through `x-text`.

---

## Critical Issues

### CR-01: Chemistry deep-link bypasses disclaimer acceptance

**File:** `resources/views/livewire/diagnostic-wizard.blade.php:268`

**Issue:** The "Analyser mon eau" button on the disclaimer screen calls `advance(…)` which transitions Alpine to `step = 'wizard'` *without* requiring `$wire.disclaimerAccepted` to be true first. The button fires `$wire.call('setMode', 'chemistry')` and immediately navigates — the server-side disclaimer flag is still `false`. The chemistry wizard (step === 'wizard') shows a second inline disclaimer block, but it only shows `x-show="!$wire.disclaimerAccepted"` and the user can proceed to the "Calculer" button without clicking it. The `computeDoses()` server guard (line 577) does catch this and will return an error — but the user reaches the form and fills it out before hitting the wall, which is a confusing and broken UX. More importantly, `acceptDisclaimer()` is never called for this path unless the user explicitly clicks the inline block's button, which has no visual prominence.

Reproduce: load `/diagnostic` → click "Vous avez vos mesures ? → Analyser mon eau" → skip the inline disclaimer → fill in pH → click "Calculer mon plan d'action" → error fires.

**Fix:** Guard the advance in the click handler:

```html
@click="
    if (!$wire.disclaimerAccepted) {
        $wire.call('acceptDisclaimer');
        $wire.disclaimerAccepted = true;
    }
    $wire.call('setMode', 'chemistry');
    advance({ value: 'chemistry', next: { kind: 'wizard', id: 'chemistry' } })
"
```

Or — simpler and safer — remove the secondary inline disclaimer block in the wizard and rely solely on the server guard, while ensuring the chemistry deep-link button *always* calls `acceptDisclaimer` before navigating. The inline block at line 410 is a maintenance burden that diverges from the canonical S4 gate.

---

### CR-02: `updateTriedActions()` accepts an unbounded, unvalidated client-supplied array

**File:** `app/Livewire/DiagnosticWizard.php:526`

**Issue:** `updateTriedActions(array $tried)` assigns `$this->triedActions = $tried` with no length cap, no per-item string validation, and no type coercion. Livewire 3 exposes all public methods as callable from the client via `$wire.call()`. A malicious client can call `$wire.call('updateTriedActions', [<10 000 long strings>])` to bloat the Livewire component state on every round-trip. More concretely, `triedActions` is included verbatim in `richContextPayload()` (line 414: `implode(', ', $this->triedActions)`) and sent to Pierre via `DiagnosticLead` mail and the WhatsApp deep-link. An attacker can inject arbitrary strings into the operator's email/WhatsApp without any server-side constraint.

The view's hardcoded chip list (`['Chlore choc', 'Brossage des parois', 'Anti-algues', 'Ajusté le pH', 'Backwash filtre', 'Rien encore']`) provides client-side restriction only; the server method is called directly and bypasses it.

**Fix:**

```php
private const ALLOWED_TRIED_ACTIONS = [
    'Chlore choc', 'Brossage des parois', 'Anti-algues',
    'Ajusté le pH', 'Backwash filtre', 'Rien encore',
];

public function updateTriedActions(array $tried): void
{
    $this->triedActions = array_values(
        array_filter(
            array_slice($tried, 0, 10),
            fn ($v) => is_string($v) && in_array($v, self::ALLOWED_TRIED_ACTIONS, true)
        )
    );
}
```

---

## Warnings

### WR-01: Alpine `advance()` still handles `step === 'mode'` — dead branch that can expose a blank screen

**File:** `resources/views/livewire/diagnostic-wizard.blade.php:54`

**Issue:** The `advance()` function has an explicit branch `if (this.step === 'mode')` (line 54) that sets `this.mode = option.value`. Since S0 was removed, no node in the tree can set `step = 'mode'`, so this branch is permanently dead. However, `history.push({ step: this.step, … })` records the current step before advancing. If any future tree node introduces a `next: { kind: 'mode', … }` entry by accident, `advance()` would push `step = 'mode'` onto history, `back()` would restore it, and the Alpine conditional rendering has no `x-show="step === 'mode'"` panel — producing a blank wizard screen. The dead branch is also a maintenance hazard: it implies 'mode' is a valid step, which it is not.

**Fix:** Remove the `if (this.step === 'mode')` block from `advance()`. If mode attribution from the tree is needed in the future, it should be handled explicitly.

---

### WR-02: WhatsApp CTA double-encodes the summary

**File:** `resources/views/livewire/diagnostic-wizard.blade.php:1077-1078`

**Issue:** The chemistry-result WhatsApp link at line 1077 has a static `href` using PHP `urlencode($whatsappSummary)` and an `x-on:click.prevent` handler that opens a second URL using JS `encodeURIComponent(@js($whatsappSummary))`. The `@js()` helper HTML-escapes the string for safe embedding in the attribute, but `encodeURIComponent` then encodes it again. On desktop browsers the `href` is used (PHP-encoded, correct). On mobile "open in app" the JS handler fires (JS-encoded from `@js()`, also correct but redundant). The real risk: if a browser opens the `href` attribute directly (e.g., middle-click, link preview), the `href` payload is PHP `urlencode` which encodes spaces as `+` rather than `%20`. WhatsApp's `wa.me` endpoint does not decode `+` as space in the `text` query parameter — the message Pierre receives will have literal `+` characters instead of spaces.

The symptom-tree WhatsApp link at line 1389 uses Alpine `encodeURIComponent` only (correct).

**Fix:** Use `rawurlencode()` instead of `urlencode()` on line 1077 (encodes space as `%20`), or drop the static `href` entirely and rely solely on the `x-on:click.prevent` handler (which already does the right thing with `encodeURIComponent`).

---

### WR-03: `keepDiagnostic()` idempotence check is incomplete — `computeDoses()` re-entry can create a duplicate row

**File:** `app/Livewire/DiagnosticWizard.php:618-624`

**Issue:** `keepDiagnostic()` checks `$this->savedDiagnosticId !== null` for idempotence (line 614). But if `hasComputed` is false, it calls `$this->computeDoses()` inline (line 621). `computeDoses()` resets `$this->savedDiagnosticId = null` on line 593 ("Un nouveau calcul invalide une éventuelle persistance précédente"). So a race where:

1. User calls `keepDiagnostic()` → `hasComputed` is true → creates row → `savedDiagnosticId = 5`
2. User modifies a field (e.g. via `wire:model`) → Livewire re-renders, but `hasComputed` remains true
3. User calls `keepDiagnostic()` again → `savedDiagnosticId` is 5 → idempotence check passes → **no duplicate** ✓

That path is safe. But if `keepDiagnostic()` is called before any compute (e.g. a direct client-side `$wire.call('keepDiagnostic')` without prior `computeDoses`), it calls `computeDoses()` which sets `savedDiagnosticId = null`, then `keepDiagnostic()` continues and creates a row. Then `computeDoses()` is called again (e.g. user clicks "Calculer"), which sets `savedDiagnosticId = null`, and a second `keepDiagnostic()` call creates a second row. The orphaned first row has no lead data. While the session gate contains only the latest ID, the DB accumulates stale rows without a cleanup path.

**Fix:** Add a DB unique constraint or move `savedDiagnosticId = null` inside `computeDoses()` only when parameters actually changed (a dirty-check). Simpler: document that `keepDiagnostic()` must not be called more than once per compute session, and add a guard:

```php
// In computeDoses(), only reset if inputs changed:
if ($this->hasComputed) {
    $this->hasComputed = false;
    $this->savedDiagnosticId = null; // reset only on re-compute
}
```

---

### WR-04: Rate limiter key in the test is fragile and tests the wrong component method

**File:** `tests/Feature/DiagnosticWizardTest.php:434`

**Issue:** The rate limiter test (line 430) manually constructs the key:
```php
$key = 'livewire-rate-limiter:' . sha1(DiagnosticWizard::class . '|submitLead|' . request()->ip());
```
This key format is internal to `danharrin/livewire-rate-limiting` and not part of its public contract. If the package changes its key format (as it has between v1 and v2), the test silently stops clearing/checking the right key, meaning the rate limiter is never actually cleared before the test and the test can either always pass or always fail depending on prior state.

Additionally, the five "successful" submissions (lines 445–451) each create a new `Livewire::test()` instance. Each instance gets a fresh component state with `disclaimerAccepted = false` by default, so the `submitLead()` calls will fail validation (`prenom`/`commune` required), not the rate limiter. The 6th call also fails validation rather than rate limiting. The test asserts `assertHasErrors(['throttle'])` on the 6th call, which may pass if the rate limiter fires first, but the precondition (5 successful sends) is not what the test actually exercises.

**Fix:** Use the same `Livewire::test()` component instance for all 6 calls, and call `$component->set('disclaimerAccepted', true)->set('prenom', …)->set('commune', …)` before each `submitLead()`. Use `RateLimiter::tooManyAttempts()` to assert the state rather than re-constructing the private key.

---

## Info

### IN-01: `mesures()` includes `sel: false` even when no other sel fields are present

**File:** `app/Livewire/DiagnosticWizard.php:487`

**Issue:** `mesures()` uses `array_filter(…, fn ($v) => $v !== null)`. The `sel` key is `$this->sel` which is a `bool` — `false` is not `null`, so `sel: false` is always included in the returned array even when the user has not touched the sel toggle. `DoseEngine::compute()` receives `sel: false` on every call, which is likely benign (false is the correct default), but it makes the persisted `mesures` JSON noisier and the `richContextPayload()` sel block at line 397 (`if (!empty($mesures['sel']) && isset($mesures['selPpm']))`) accidentally relies on PHP truthiness: `!empty(false)` is `false`, so the sel line is correctly suppressed — but only by coincidence. If `sel: true` ever needs to be filtered out, the pattern breaks.

**Fix:** Exclude `sel` from `mesures()` when false, or always include it explicitly outside of `array_filter`:

```php
$base = array_filter([…], fn ($v) => $v !== null);
$base['sel'] = $this->sel; // always present, explicit
return $base;
```

---

### IN-02: Carnet browser tests are placeholder `expect(true)->toBeTrue()` — they provide zero coverage

**File:** `tests/Browser/CarnetLocalTest.php:125,136,149`

**Issue:** The three browser tests that cover localStorage persistence, zero-network reads, and clear behaviour all contain only `expect(true)->toBeTrue()` and are unconditionally skipped. They are documented as "manual/CI verification required" but there is no CI job that enforces the manual steps. This means DIAG-07's primary acceptance criteria (persistence survives page reload, 0 network for history) have no automated coverage path. If pest-plugin-browser is available in future, the test bodies need to be written — right now they would pass even if the feature were completely broken.

**Fix:** Either implement the Playwright assertions (preferred) or convert to a `->todo()` with an explicit tracking issue reference, which would make the gap visible in `pest --list-todos` output rather than silently passing.

---

_Reviewed: 2026-06-04_
_Reviewer: Claude (gsd-code-reviewer)_
_Depth: standard_
