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

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});
