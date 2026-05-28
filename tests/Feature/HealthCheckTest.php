<?php

/**
 * Health endpoint contract — Plan 01-01 Task 3b behavior.
 *
 * GET /up MUST return:
 *   - 200 + {"app":"ok","db":"ok"} when DB connection is alive
 *   - 503 + {"db":"fail"} when DB ping throws
 *
 * The contract is intentionally minimal — no driver name, no version, no host
 * (T-1-07 mitigation — see threat model).
 */

use Illuminate\Support\Facades\DB;

it('GET /up returns 200 with {app:ok, db:ok} when DB connection is alive', function () {
    $response = $this->getJson('/up');
    $response->assertOk();
    $response->assertExactJson(['app' => 'ok', 'db' => 'ok']);
});

it('GET /up returns 503 with {app:ok, db:fail} when DB connection throws', function () {
    // Swap the default db connection to one that cannot connect.
    config(['database.default' => 'broken']);
    config(['database.connections.broken' => [
        'driver' => 'pgsql',
        'host' => '127.0.0.1',
        'port' => 1,            // unreachable
        'database' => 'nope',
        'username' => 'nope',
        'password' => 'nope',
    ]]);
    DB::purge('broken');

    $response = $this->getJson('/up');
    $response->assertStatus(503);
    $response->assertExactJson(['app' => 'ok', 'db' => 'fail']);
});

it('GET /up does not leak DB driver, version, or host', function () {
    $body = $this->getJson('/up')->getContent();
    expect($body)->not->toContain('pgsql');
    expect($body)->not->toContain('sqlite');
    expect($body)->not->toContain('127.0.0.1');
    expect($body)->not->toContain('localhost');
});
