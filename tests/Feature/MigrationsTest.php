<?php

/**
 * MigrationsTest — Plan 01-02 Task 1 behavior contract.
 *
 * Verifies D-07 (every business table exists) and D-08 (critical forward-compat
 * columns are present from the very first deploy). RED phase: all tests fail until
 * Task 2 migrations are created.
 *
 * Coverage: clients, piscines, produits, contrats, passages, photos_meta, factures,
 * signatures, diagnostics, google_reviews (D-28 amended).
 */

use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Test 1 — D-07: every business table exists
// ---------------------------------------------------------------------------

it('asserts every business table exists', function () {
    expect(Schema::hasTable('clients'))->toBeTrue('clients table missing');
    expect(Schema::hasTable('piscines'))->toBeTrue('piscines table missing');
    expect(Schema::hasTable('produits'))->toBeTrue('produits table missing');
    expect(Schema::hasTable('contrats'))->toBeTrue('contrats table missing');
    expect(Schema::hasTable('passages'))->toBeTrue('passages table missing');
    expect(Schema::hasTable('photos_meta'))->toBeTrue('photos_meta table missing');
    expect(Schema::hasTable('factures'))->toBeTrue('factures table missing');
    expect(Schema::hasTable('signatures'))->toBeTrue('signatures table missing');
    expect(Schema::hasTable('diagnostics'))->toBeTrue('diagnostics table missing');
    expect(Schema::hasTable('google_reviews'))->toBeTrue('google_reviews table missing');
});

// ---------------------------------------------------------------------------
// Test 2 — D-08: passages.client_uuid is unique
// ---------------------------------------------------------------------------

it('asserts passages.client_uuid is unique and indexed', function () {
    expect(Schema::hasColumn('passages', 'client_uuid'))->toBeTrue();

    $uuid = (string) Str::uuid();

    DB::table('passages')->insert([
        'client_uuid' => $uuid,
        'status'      => 'draft',
    ]);

    // Use a savepoint so PostgreSQL transaction state recovers after the exception
    try {
        DB::statement('SAVEPOINT before_unique_test');
        DB::table('passages')->insert([
            'client_uuid' => $uuid,
            'status'      => 'draft',
        ]);
        DB::statement('RELEASE SAVEPOINT before_unique_test');
        expect(false)->toBeTrue('Expected unique constraint violation but insert succeeded');
    } catch (UniqueConstraintViolationException $e) {
        DB::statement('ROLLBACK TO SAVEPOINT before_unique_test');
        DB::statement('RELEASE SAVEPOINT before_unique_test');
        expect(true)->toBeTrue();
    }
});

// ---------------------------------------------------------------------------
// Test 3 — D-08: passages.signature_path is nullable string
// ---------------------------------------------------------------------------

it('asserts passages.signature_path is nullable string', function () {
    expect(Schema::hasColumn('passages', 'signature_path'))->toBeTrue();

    // Without signature_path → must succeed
    $id = DB::table('passages')->insertGetId([
        'client_uuid' => (string) Str::uuid(),
        'status'      => 'draft',
    ]);
    expect($id)->toBeGreaterThan(0);

    // With a string value → must also succeed
    $id2 = DB::table('passages')->insertGetId([
        'client_uuid'    => (string) Str::uuid(),
        'status'         => 'draft',
        'signature_path' => 'signatures/2026/abc123.png',
    ]);
    expect($id2)->toBeGreaterThan(0);

    $row = DB::table('passages')->find($id2);
    expect($row->signature_path)->toBe('signatures/2026/abc123.png');
});

// ---------------------------------------------------------------------------
// Test 4 — Pitfall 5: factures.numero is nullable unique string (distinct from id)
// ---------------------------------------------------------------------------

it('asserts factures.numero is nullable unique string distinct from id', function () {
    expect(Schema::hasColumn('factures', 'numero'))->toBeTrue();

    // Insert a client first (FK required)
    $clientId = DB::table('clients')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Client',
    ]);

    // First facture with a numero
    DB::table('factures')->insert([
        'uuid'      => (string) Str::uuid(),
        'numero'    => 'F2026-0001',
        'client_id' => $clientId,
    ]);

    // Second facture with same numero → unique violation
    // Use a savepoint so PostgreSQL transaction state recovers after the exception
    try {
        DB::statement('SAVEPOINT before_unique_test');
        DB::table('factures')->insert([
            'uuid'      => (string) Str::uuid(),
            'numero'    => 'F2026-0001',
            'client_id' => $clientId,
        ]);
        DB::statement('RELEASE SAVEPOINT before_unique_test');
        expect(false)->toBeTrue('Expected unique constraint violation but insert succeeded');
    } catch (UniqueConstraintViolationException $e) {
        DB::statement('ROLLBACK TO SAVEPOINT before_unique_test');
        DB::statement('RELEASE SAVEPOINT before_unique_test');
        expect(true)->toBeTrue();
    }

    // Two factures with numero=null → both succeed (null is never "equal" in SQL unique)
    DB::table('factures')->insert([
        'uuid'      => (string) Str::uuid(),
        'numero'    => null,
        'client_id' => $clientId,
    ]);
    DB::table('factures')->insert([
        'uuid'      => (string) Str::uuid(),
        'numero'    => null,
        'client_id' => $clientId,
    ]);
    expect(DB::table('factures')->whereNull('numero')->count())->toBe(2);
});

// ---------------------------------------------------------------------------
// Test 5 — D-08: factures.odoo_id is nullable bigint
// ---------------------------------------------------------------------------

it('asserts factures.odoo_id is nullable bigint', function () {
    expect(Schema::hasColumn('factures', 'odoo_id'))->toBeTrue();

    $clientId = DB::table('clients')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test Odoo Client',
    ]);

    $id = DB::table('factures')->insertGetId([
        'uuid'      => (string) Str::uuid(),
        'client_id' => $clientId,
        'odoo_id'   => 123456789012,
    ]);

    $row = DB::table('factures')->find($id);
    expect((int) $row->odoo_id)->toBe(123456789012);
});

// ---------------------------------------------------------------------------
// Test 6 — Pitfall 4: factures.tva_rate defaults to 8.50
// ---------------------------------------------------------------------------

it('asserts factures.tva_rate defaults to 8.50', function () {
    expect(Schema::hasColumn('factures', 'tva_rate'))->toBeTrue();

    $clientId = DB::table('clients')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'Test TVA Client',
    ]);

    // Insert WITHOUT specifying tva_rate — rely on DB default
    $id = DB::table('factures')->insertGetId([
        'uuid'      => (string) Str::uuid(),
        'client_id' => $clientId,
    ]);

    $row = DB::table('factures')->find($id);
    expect((float) $row->tva_rate)->toBe(8.50);
});

// ---------------------------------------------------------------------------
// Test 7 — clients.uuid is unique and UUID v4 valid
// ---------------------------------------------------------------------------

it('asserts clients.uuid is unique and is UUID v4 valid', function () {
    expect(Schema::hasColumn('clients', 'uuid'))->toBeTrue();

    $uuid = (string) Str::uuid();
    expect(Str::isUuid($uuid))->toBeTrue();

    DB::table('clients')->insert([
        'uuid' => $uuid,
        'name' => 'First Client',
    ]);

    // Use savepoint so PostgreSQL transaction state recovers after the exception
    try {
        DB::statement('SAVEPOINT before_unique_test');
        DB::table('clients')->insert([
            'uuid' => $uuid,
            'name' => 'Duplicate UUID Client',
        ]);
        DB::statement('RELEASE SAVEPOINT before_unique_test');
        expect(false)->toBeTrue('Expected unique constraint violation but insert succeeded');
    } catch (UniqueConstraintViolationException $e) {
        DB::statement('ROLLBACK TO SAVEPOINT before_unique_test');
        DB::statement('RELEASE SAVEPOINT before_unique_test');
        expect(true)->toBeTrue();
    }
});

// ---------------------------------------------------------------------------
// Test 8 — clients.magic_link_token + magic_link_expires_at exist (nullable)
// ---------------------------------------------------------------------------

it('asserts clients.magic_link_token and magic_link_expires_at exist and are nullable', function () {
    expect(Schema::hasColumn('clients', 'magic_link_token'))->toBeTrue();
    expect(Schema::hasColumn('clients', 'magic_link_expires_at'))->toBeTrue();

    // Insert without those columns → must succeed (both nullable)
    $id = DB::table('clients')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'No Magic Link Client',
    ]);

    $row = DB::table('clients')->find($id);
    expect($row->magic_link_token)->toBeNull();
    expect($row->magic_link_expires_at)->toBeNull();
});

// ---------------------------------------------------------------------------
// Test 9 — photos_meta.disk defaults to 'r2'
// ---------------------------------------------------------------------------

it('asserts photos_meta.disk defaults to r2', function () {
    expect(Schema::hasColumn('photos_meta', 'disk'))->toBeTrue();

    // Insert a passage first (FK)
    $passageId = DB::table('passages')->insertGetId([
        'client_uuid' => (string) Str::uuid(),
        'status'      => 'draft',
    ]);

    // Insert without disk — rely on DB default
    $id = DB::table('photos_meta')->insertGetId([
        'passage_id' => $passageId,
        'path'       => 'photos/2026/test.jpg',
    ]);

    $row = DB::table('photos_meta')->find($id);
    expect($row->disk)->toBe('r2');
});

// ---------------------------------------------------------------------------
// Test 10 — diagnostics.disclaimer_accepted_at exists for DIAG-03
// ---------------------------------------------------------------------------

it('asserts diagnostics.disclaimer_accepted_at exists for DIAG-03', function () {
    expect(Schema::hasColumn('diagnostics', 'disclaimer_accepted_at'))->toBeTrue();
});

// ---------------------------------------------------------------------------
// Test 11 — contrats.type accepts the three contract kinds
// ---------------------------------------------------------------------------

it('asserts contrats.type accepts the three contract kinds', function () {
    $clientId = DB::table('clients')->insertGetId([
        'uuid' => (string) Str::uuid(),
        'name' => 'Contrat Client',
    ]);

    foreach (['ponctuel', 'forfait_mensuel', 'forfait_saisonnier'] as $type) {
        $id = DB::table('contrats')->insertGetId([
            'client_id' => $clientId,
            'type'      => $type,
        ]);
        $row = DB::table('contrats')->find($id);
        expect($row->type)->toBe($type);
    }
});

// ---------------------------------------------------------------------------
// Test 12 — D-09: DatabaseSeeder is a no-op in production
// ---------------------------------------------------------------------------

it('asserts DatabaseSeeder is a no-op in production', function () {
    // Production env gate: no Client rows created
    putenv('APP_ENV=production');
    app()->detectEnvironment(fn () => 'production');

    $seeder = new \Database\Seeders\DatabaseSeeder;
    // Silence the info() call that references $this->command
    // by running via artisan call which has a command instance
    $countBefore = DB::table('clients')->count();
    // Run seeder directly — it checks app()->environment()
    // which is now 'production', so DevDataSeeder must NOT be called
    try {
        app()->call([$seeder, 'run']);
    } catch (\Throwable $e) {
        // If it tries to call $this->command->info() without a command context, catch it
        // but still verify no clients were created
    }
    expect(DB::table('clients')->count())->toBe($countBefore);

    // Restore local env — DevDataSeeder must run
    putenv('APP_ENV=local');
    app()->detectEnvironment(fn () => 'local');

    $countBeforeLocal = DB::table('clients')->count();
    app()->call([$seeder, 'run']);
    expect(DB::table('clients')->count())->toBeGreaterThan($countBeforeLocal);

    // Restore testing env
    putenv('APP_ENV=testing');
    app()->detectEnvironment(fn () => 'testing');
});

// ---------------------------------------------------------------------------
// Test 13 — D-28 amended: google_reviews table + critical columns
// ---------------------------------------------------------------------------

it('asserts google_reviews table and critical columns for D-28 amended', function () {
    expect(Schema::hasTable('google_reviews'))->toBeTrue();
    expect(Schema::hasColumn('google_reviews', 'google_review_id'))->toBeTrue();
    expect(Schema::hasColumn('google_reviews', 'rating'))->toBeTrue();
    expect(Schema::hasColumn('google_reviews', 'reviewed_at'))->toBeTrue();

    // Insert a review
    DB::table('google_reviews')->insert([
        'google_review_id'          => 'review_abc123',
        'author_name'               => 'Jean Dupont',
        'rating'                    => 5,
        'relative_time_description' => 'il y a 2 mois',
        'language'                  => 'fr',
        'reviewed_at'               => now(),
        'fetched_at'                => now(),
    ]);

    // Duplicate google_review_id → unique violation
    // Use savepoint so PostgreSQL transaction state recovers after the exception
    try {
        DB::statement('SAVEPOINT before_unique_test');
        DB::table('google_reviews')->insert([
            'google_review_id'          => 'review_abc123',
            'author_name'               => 'Marie Martin',
            'rating'                    => 4,
            'relative_time_description' => 'il y a 1 mois',
            'language'                  => 'fr',
            'reviewed_at'               => now(),
            'fetched_at'                => now(),
        ]);
        DB::statement('RELEASE SAVEPOINT before_unique_test');
        expect(false)->toBeTrue('Expected unique constraint violation but insert succeeded');
    } catch (UniqueConstraintViolationException $e) {
        DB::statement('ROLLBACK TO SAVEPOINT before_unique_test');
        DB::statement('RELEASE SAVEPOINT before_unique_test');
        expect(true)->toBeTrue();
    }

    // Insert with rating=5 succeeds
    DB::table('google_reviews')->insert([
        'google_review_id'          => 'review_def456',
        'author_name'               => 'Marie Martin',
        'rating'                    => 5,
        'relative_time_description' => 'il y a 1 mois',
        'language'                  => 'fr',
        'reviewed_at'               => now(),
        'fetched_at'                => now(),
    ]);
    expect(DB::table('google_reviews')->where('rating', 5)->count())->toBe(2);
});
