# Deferred Items — Phase 06

## Pre-existing worktree Pest namespace-binding quirk (out of scope)

**Discovered during:** 06-01 execution (full test gate run from worktree).

**Symptom:** Feature tests **without** an explicit `uses(\Tests\TestCase::class)` (e.g. `BlogTest`, `HomePageTest`, every other `tests/Feature/*` file) fail from inside the git worktree with:

```
Call to undefined method claude\worktrees\agentXXXX\tests\Feature\BlogTest::get()
```

and container errors (`Target class [files] does not exist`, `Container::basePath()`).

**Root cause:** `vendor/` is symlinked from the worktree to the main repo (documented in MEMORY: "GSD worktree autoloader poisoning"). Pest's path-based `pest()->extend(Tests\TestCase::class)->in('Feature')` in `tests/Pest.php` resolves the relative `Feature` path against the **main repo root**, not the worktree cwd, so worktree test files get a wrong namespace (`claude\worktrees\...`) and never receive the `TestCase` bind. The worktree-absolute `->in($worktreePath.'/tests/Feature')` fallback in `tests/Pest.php` does not reliably re-bind under the symlinked-vendor path resolution.

**Why out of scope for 06-01:** Not a regression — it affects ALL pre-existing Feature tests identically and is independent of any 06-01 change. The 06-01 new test files (`PostModelTest`, `PostMigrationSeederTest`) work around it with an explicit `uses(\Tests\TestCase::class, ...)` (a line marked "Strip on merge" — `tests/Pest.php` already covers `Feature/` from the main repo). They pass 10/10.

**Resolution:** Post-merge, run the full suite from the **main repo** (where the harness binds correctly). Do NOT attempt to fix `tests/Pest.php` from within a worktree.
