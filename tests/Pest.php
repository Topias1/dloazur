<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Pest 4 bootstrap. RefreshDatabase is opt-in per test via `uses(RefreshDatabase::class)`
| rather than globally — sqlite :memory: trips a nested-transaction error when the
| trait wraps an already-migrating connection. Plan 02 will revisit once the DB
| schema is fleshed out and a dedicated test DB is in place.
|
*/

pest()->extend(Tests\TestCase::class)->in('Feature');
pest()->extend(Tests\TestCase::class)->in('Unit');
pest()->extend(Tests\TestCase::class)->in('Browser');

// Worktree support: when run from a git worktree, the absolute path differs from the main repo.
// Adding the worktree path ensures ->extend() is applied to worktree test files.
//
// Fix (07-01): The relative ->in('Feature') above resolves via Pest's internal path resolution,
// which uses the symlinked vendor's perspective (main repo), NOT cwd. This means worktree test
// files get the namespace `P\claude\worktrees\...\tests\Feature\...` but the TestCase extension
// only covers `{main_repo}/tests/Feature`. Always register the absolute worktree path when
// we detect we ARE in a worktree, skipping only when it would collide (same path as main repo).
$worktreePath = dirname(__DIR__);
if (str_contains($worktreePath, 'worktrees')) {
    $mainRepoFeature  = realpath(dirname($worktreePath, 3) . '/tests/Feature');
    $worktreeFeature  = realpath($worktreePath . '/tests/Feature');

    // Only register if different paths (avoid duplicate-registration error when paths coincide)
    if ($mainRepoFeature !== $worktreeFeature) {
        pest()->extend(Tests\TestCase::class)->in($worktreeFeature);
        pest()->extend(Tests\TestCase::class)->in(realpath($worktreePath . '/tests/Unit'));
    } else {
        // Same directory resolved — still register by absolute path to be safe
        pest()->extend(Tests\TestCase::class)->in($worktreeFeature);
    }
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
