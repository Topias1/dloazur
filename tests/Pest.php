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

// Worktree support: when run from a git worktree, the absolute path differs from the main repo.
// Adding the worktree path ensures ->extend() is applied to worktree test files.
$worktreePath = dirname(__DIR__);
if (str_contains($worktreePath, 'worktrees')) {
    pest()->extend(Tests\TestCase::class)->in($worktreePath . '/tests/Feature');
    pest()->extend(Tests\TestCase::class)->in($worktreePath . '/tests/Unit');
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
