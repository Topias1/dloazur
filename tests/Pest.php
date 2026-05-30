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
// Note: when vendor is symlinked to main repo, pest resolves relative 'Feature'/'Unit' paths
// against the main repo root (not cwd). We always add absolute worktree paths in that case.
$worktreePath = dirname(__DIR__);
if (str_contains($worktreePath, 'worktrees')) {
    pest()->extend(Tests\TestCase::class)->in($worktreePath . '/tests/Feature');
    pest()->extend(Tests\TestCase::class)->in($worktreePath . '/tests/Unit');

    // STRIP ON MERGE — worktree-only autoloader crutch (Plan 06-04).
    // vendor/ is symlinked to the MAIN repo, so its baked PSR-4 $baseDir points
    // App\ at the main repo's app/ — making this worktree's NEW classes (e.g.
    // App\Livewire\PostForm) invisible to the test runner. Prepend the worktree's
    // app/ to the App\ PSR-4 prefix so worktree sources win during tests, without
    // re-dumping the shared autoloader (which would poison main's classmap).
    // Remove this block at merge time — once merged, app/ lives in the repo root
    // and the normal autoloader resolves it correctly.
    foreach (spl_autoload_functions() ?: [] as $fn) {
        if (is_array($fn) && $fn[0] instanceof \Composer\Autoload\ClassLoader) {
            $fn[0]->addPsr4('App\\', $worktreePath . '/app', true);
            break;
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
