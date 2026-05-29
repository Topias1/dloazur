<?php

/**
 * AdminSeederTest — Plan 01-05 Task 1 behavior contract (RED).
 *
 * Verifies AUTH-01 + D-09 production-safe seeder:
 * - Idempotent upsert keyed on OPERATOR_EMAIL
 * - Hashed password, verified email, correct name
 * - Runnable in production env (not env-gated like DatabaseSeeder)
 */

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Support\Facades\Hash;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 1 — creates exactly one user with env credentials
// ---------------------------------------------------------------------------

it('pierre seeder creates exactly one user with the env credentials', function () {
    config(['app.env' => 'testing']);
    putenv('OPERATOR_EMAIL=admin@test.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret123');

    (new AdminSeeder())->run();

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('users', [
        'email' => 'admin@test.local',
        'name'  => 'Pierre ADAM',
    ]);

    $user = User::first();
    expect(Hash::check('secret123', $user->password))->toBeTrue();
});

// ---------------------------------------------------------------------------
// Test 2 — idempotent (running twice yields exactly 1 row)
// ---------------------------------------------------------------------------

it('pierre seeder is idempotent — running twice yields exactly 1 user', function () {
    putenv('OPERATOR_EMAIL=admin@test.local');
    putenv('OPERATOR_NAME=Pierre ADAM');
    putenv('OPERATOR_INITIAL_PASSWORD=secret123');

    (new AdminSeeder())->run();
    (new AdminSeeder())->run();

    $this->assertDatabaseCount('users', 1);
});

// ---------------------------------------------------------------------------
// Test 3 — email_verified_at is not null (skip email verification per CONTEXT.md)
// ---------------------------------------------------------------------------

it('pierre seeder sets email_verified_at to a non-null timestamp', function () {
    putenv('OPERATOR_EMAIL=admin@test.local');

    (new AdminSeeder())->run();

    $user = User::where('email', 'admin@test.local')->first();
    expect($user->email_verified_at)->not->toBeNull();
});

// ---------------------------------------------------------------------------
// Test 4 — callable in production env (AdminSeeder is NOT env-gated)
// ---------------------------------------------------------------------------

it('pierre seeder runs in production env without env gate', function () {
    putenv('OPERATOR_EMAIL=admin@test.local');

    // Temporarily fake production environment for this assertion only
    $originalEnv = app()->environment();

    app()->detectEnvironment(fn () => 'production');

    (new AdminSeeder())->run();

    // Restore testing env
    app()->detectEnvironment(fn () => $originalEnv);

    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseHas('users', ['email' => 'admin@test.local']);
});
