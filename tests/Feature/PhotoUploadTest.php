<?php

/**
 * PhotoUploadTest — Plan 02-06 Task 1 behavior contract (RED).
 *
 * Vérifie PASS-04: POST /api/passages/{uuid}/photos UPSERT idempotent
 * sur photos_meta.client_uuid (D-42), mime JPEG check, max 10 MB (D-48).
 * Covers: T-6-02 (mime check), D-42 (idempotence), D-48 (taille max).
 */

use App\Models\Passage;
use App\Models\PhotoMeta;
use App\Models\User;
use Database\Seeders\PierreSeeder;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Setup commun
// ---------------------------------------------------------------------------

function makePierreForPhoto(): User
{
    putenv('OPERATOR_EMAIL=pierre@dloazurtest.local');
    putenv('OPERATOR_INITIAL_PASSWORD=correct-horse-battery-staple');
    (new PierreSeeder())->run();

    return User::where('email', 'pierre@dloazurtest.local')->first();
}

// ---------------------------------------------------------------------------
// Test 1 — Upload JPEG frais : 200, fichier sur r2, ligne photos_meta créée
// ---------------------------------------------------------------------------

it('POST /api/passages/{uuid}/photos avec un JPEG et un photo client_uuid frais retourne 200, crée un fichier sur r2 et une ligne photos_meta', function () {
    Storage::fake('r2');

    $pierre       = makePierreForPhoto();
    $passageUuid  = (string) Str::uuid();
    $photoUuid    = (string) Str::uuid();

    Passage::factory()->create(['client_uuid' => $passageUuid, 'status' => 'draft']);

    $fakeFile = UploadedFile::fake()->image('photo.jpg', 1024, 768)->size(500);

    $this->actingAs($pierre)
         ->postJson("/api/passages/{$passageUuid}/photos", [
             'photo'       => $fakeFile,
             'client_uuid' => $photoUuid,
             'captured_at' => now()->toIso8601String(),
         ])
         ->assertStatus(200)
         ->assertJson(['ok' => true]);

    $meta = PhotoMeta::where('client_uuid', $photoUuid)->first();
    expect($meta)->not->toBeNull();
    Storage::disk('r2')->assertExists($meta->path);
});

// ---------------------------------------------------------------------------
// Test 2 — Upload idempotent : même photo_uuid POSTé 2x → 1 seule ligne
// ---------------------------------------------------------------------------

it('Photo upload idempotent : le même client_uuid photo POSTé 2x reste 1 ligne photos_meta', function () {
    Storage::fake('r2');

    $pierre      = makePierreForPhoto();
    $passageUuid = (string) Str::uuid();
    $photoUuid   = (string) Str::uuid();

    Passage::factory()->create(['client_uuid' => $passageUuid, 'status' => 'draft']);

    $fakeFile1 = UploadedFile::fake()->image('photo.jpg', 800, 600)->size(200);
    $fakeFile2 = UploadedFile::fake()->image('photo2.jpg', 640, 480)->size(150);

    $this->actingAs($pierre)
         ->postJson("/api/passages/{$passageUuid}/photos", [
             'photo'       => $fakeFile1,
             'client_uuid' => $photoUuid,
             'captured_at' => now()->toIso8601String(),
         ])
         ->assertStatus(200);

    // Deuxième POST — même photo_uuid
    $this->actingAs($pierre)
         ->postJson("/api/passages/{$passageUuid}/photos", [
             'photo'       => $fakeFile2,
             'client_uuid' => $photoUuid,
             'captured_at' => now()->toIso8601String(),
         ])
         ->assertStatus(200);

    expect(PhotoMeta::where('client_uuid', $photoUuid)->count())->toBe(1);
});

// ---------------------------------------------------------------------------
// Test 3 — Mime non-JPEG ou photo manquante retourne 422
// ---------------------------------------------------------------------------

it("Photo sans 'photo' ou avec mime non-JPEG retourne 422", function () {
    Storage::fake('r2');

    $pierre      = makePierreForPhoto();
    $passageUuid = (string) Str::uuid();

    Passage::factory()->create(['client_uuid' => $passageUuid, 'status' => 'draft']);

    // Sous-test 1 : champ photo absent
    $this->actingAs($pierre)
         ->postJson("/api/passages/{$passageUuid}/photos", [
             'client_uuid' => (string) Str::uuid(),
         ])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['photo']);

    // Sous-test 2 : PDF uploadé (mime = application/pdf)
    $pdfFile = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

    $this->actingAs($pierre)
         ->postJson("/api/passages/{$passageUuid}/photos", [
             'photo'       => $pdfFile,
             'client_uuid' => (string) Str::uuid(),
         ])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['photo']);
});

// ---------------------------------------------------------------------------
// Test 4 — Photo > 10 MB retourne 422 (D-48)
// ---------------------------------------------------------------------------

it('Photo > 10 MB retourne 422 (D-48)', function () {
    Storage::fake('r2');

    $pierre      = makePierreForPhoto();
    $passageUuid = (string) Str::uuid();

    Passage::factory()->create(['client_uuid' => $passageUuid, 'status' => 'draft']);

    $bigFile = UploadedFile::fake()->create('big.jpg', 11000, 'image/jpeg');

    $this->actingAs($pierre)
         ->postJson("/api/passages/{$passageUuid}/photos", [
             'photo'       => $bigFile,
             'client_uuid' => (string) Str::uuid(),
         ])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['photo']);
});

// ---------------------------------------------------------------------------
// Test 5 — Photo client_uuid manquant retourne 422
// ---------------------------------------------------------------------------

it('Photo client_uuid manquant retourne 422', function () {
    Storage::fake('r2');

    $pierre      = makePierreForPhoto();
    $passageUuid = (string) Str::uuid();

    Passage::factory()->create(['client_uuid' => $passageUuid, 'status' => 'draft']);

    $fakeFile = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(100);

    $this->actingAs($pierre)
         ->postJson("/api/passages/{$passageUuid}/photos", [
             'photo' => $fakeFile,
             // pas de client_uuid
         ])
         ->assertStatus(422)
         ->assertJsonValidationErrors(['client_uuid']);
});

// ---------------------------------------------------------------------------
// Test 6 — passage_uuid inexistant retourne 404
// ---------------------------------------------------------------------------

it('Photo passage_uuid inexistant retourne 404', function () {
    Storage::fake('r2');

    $pierre    = makePierreForPhoto();
    $fakeFile  = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(100);
    $wrongUuid = (string) Str::uuid();

    $this->actingAs($pierre)
         ->postJson("/api/passages/{$wrongUuid}/photos", [
             'photo'       => $fakeFile,
             'client_uuid' => (string) Str::uuid(),
         ])
         ->assertStatus(404);
});

// ---------------------------------------------------------------------------
// Test 7 — Sans auth retourne redirect /login
// ---------------------------------------------------------------------------

it('Photo sans auth retourne redirect /login', function () {
    Storage::fake('r2');

    $passageUuid = (string) Str::uuid();
    $fakeFile    = UploadedFile::fake()->image('photo.jpg', 640, 480)->size(100);

    $this->postJson("/api/passages/{$passageUuid}/photos", [
             'photo'       => $fakeFile,
             'client_uuid' => (string) Str::uuid(),
         ])
         ->assertRedirect('/login');
});

// ---------------------------------------------------------------------------
// Test 8 — Le path stocké suit le pattern passages/{passage_uuid}/photos/...
// ---------------------------------------------------------------------------

it('Le path stocké suit le pattern passages/{passage_uuid}/photos/{random}.jpg', function () {
    Storage::fake('r2');

    $pierre      = makePierreForPhoto();
    $passageUuid = (string) Str::uuid();
    $photoUuid   = (string) Str::uuid();

    Passage::factory()->create(['client_uuid' => $passageUuid, 'status' => 'draft']);

    $fakeFile = UploadedFile::fake()->image('photo.jpg', 800, 600)->size(200);

    $this->actingAs($pierre)
         ->postJson("/api/passages/{$passageUuid}/photos", [
             'photo'       => $fakeFile,
             'client_uuid' => $photoUuid,
             'captured_at' => now()->toIso8601String(),
         ])
         ->assertStatus(200);

    $meta = PhotoMeta::where('client_uuid', $photoUuid)->first();
    expect($meta)->not->toBeNull();
    expect($meta->path)->toStartWith("passages/{$passageUuid}/photos/");
});
