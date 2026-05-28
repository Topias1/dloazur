<?php

/**
 * ClientsGuardTest — Plan 02-01 Task 2 behavior contract.
 *
 * Verifies AUTH-02/03: Client model is Authenticatable, guard 'clients'
 * is operational and isolated from guard 'web'.
 * Covers: D-RESEARCH §Pattern 6, T-2-01 (elevation of privilege isolation).
 */

use App\Models\Client;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 1 — Le model Client est Authenticatable
// ---------------------------------------------------------------------------

it('Le model Client est Authenticatable', function () {
    expect(new Client())->toBeInstanceOf(Authenticatable::class);
});

// ---------------------------------------------------------------------------
// Test 2 — Auth::guard('clients') peut logger un Client puis le rappeler
// ---------------------------------------------------------------------------

it('Auth guard clients peut logger un Client puis le rappeler', function () {
    $client = Client::factory()->create();

    Auth::guard('clients')->login($client);

    expect(Auth::guard('clients')->check())->toBeTrue();
    expect(Auth::guard('clients')->user()->id)->toBe($client->id);
});

// ---------------------------------------------------------------------------
// Test 3 — Les guards 'web' et 'clients' sont isolés
// ---------------------------------------------------------------------------

it('Les guards web et clients sont isolés — login client ne loggue pas sur web', function () {
    $client = Client::factory()->create();

    Auth::guard('clients')->login($client);

    expect(Auth::guard('web')->check())->toBeFalse();
    expect(Auth::guard('clients')->check())->toBeTrue();
});
