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

// Worktree support: when Pest is invoked from a git worktree directory (CWD contains 'worktrees'),
// the symlinked vendor resolves Pest.php from the MAIN repo (not the worktree). The relative
// ->in('Feature') above covers the main repo's tests/Feature. Worktree test files get a different
// namespace path and are NOT covered. We add the worktree's absolute tests/ paths explicitly.
//
// Detection: use CWD (not __DIR__) since __DIR__ always points to the main repo's tests/ when
// the vendor is symlinked and Pest loads its Pest.php from the main repo.
$cwd = getcwd();
if ($cwd && str_contains($cwd, 'worktrees')) {
    $worktreeFeature = realpath($cwd . '/tests/Feature');
    $mainFeature     = realpath(dirname(__DIR__) . '/tests/Feature');

    // Only register if paths differ (avoid "folder already uses the test case" collision).
    if ($worktreeFeature && $worktreeFeature !== $mainFeature) {
        pest()->extend(Tests\TestCase::class)->in($worktreeFeature);
        $worktreeUnit = realpath($cwd . '/tests/Unit');
        if ($worktreeUnit) {
            pest()->extend(Tests\TestCase::class)->in($worktreeUnit);
        }
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
