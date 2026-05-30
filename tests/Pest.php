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
// Guard against double-registration: when the worktree has its own (non-symlinked) vendor,
// the relative `'Feature'`/`'Unit'` paths above already resolve to the worktree, so adding the
// absolute worktree path again collides ("folder already uses the test case"). We only add the
// absolute paths when the relative resolution points OUTSIDE the worktree (symlinked-vendor case).
$worktreePath = dirname(__DIR__);
if (str_contains($worktreePath, 'worktrees')) {
    $relativeFeature = realpath(getcwd() . '/tests/Feature');
    $worktreeFeature = realpath($worktreePath . '/tests/Feature');

    if ($relativeFeature !== $worktreeFeature) {
        pest()->extend(Tests\TestCase::class)->in($worktreePath . '/tests/Feature');
        pest()->extend(Tests\TestCase::class)->in($worktreePath . '/tests/Unit');
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
