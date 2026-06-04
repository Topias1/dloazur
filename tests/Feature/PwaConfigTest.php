<?php

/**
 * PWA infrastructure contract — Plan 02-04.
 *
 * Tests 1-5 dépendent du build prod (public/build/sw.js + manifest.webmanifest).
 * En local sans build : ->skip() automatique.
 * En CI : npm run build est exécuté avant ./vendor/bin/pest — tous les tests passent.
 *
 * Test 6-7 : route GET /offline — accessible sans auth, affiche "Tu es hors ligne" (registre opérateur tu, D-07).
 */

use Illuminate\Support\Facades\File;

// ── Tests build (skip si build prod absent) ─────────────────────────────────

it('Le fichier public/build/manifest.webmanifest existe après build', function () {
    expect(File::exists(public_path('build/manifest.webmanifest')))->toBeTrue();
})->skip(fn () => ! file_exists(base_path('public/build/sw.js')), 'Build prod requis (npm run build)');

it("Le manifest contient name 'Dlo Azur · Métier'", function () {
    $manifest = json_decode(File::get(public_path('build/manifest.webmanifest')), true);
    expect($manifest['name'])->toBe('Dlo Azur · Métier');
})->skip(fn () => ! file_exists(base_path('public/build/manifest.webmanifest')), 'Build prod requis (npm run build)');

it("Le manifest a start_url='/admin/passages/create'", function () {
    $manifest = json_decode(File::get(public_path('build/manifest.webmanifest')), true);
    expect($manifest['start_url'])->toBe('/admin/passages/create');
})->skip(fn () => ! file_exists(base_path('public/build/manifest.webmanifest')), 'Build prod requis (npm run build)');

it('Le manifest a 2 icônes (192x192 + 512x512)', function () {
    $manifest = json_decode(File::get(public_path('build/manifest.webmanifest')), true);
    expect(count($manifest['icons']))->toBe(2);
})->skip(fn () => ! file_exists(base_path('public/build/manifest.webmanifest')), 'Build prod requis (npm run build)');

it("Le SW généré contient 'passages-queue' (BackgroundSync queueName)", function () {
    expect(File::get(public_path('build/sw.js')))->toContain('passages-queue');
})->skip(fn () => ! file_exists(base_path('public/build/sw.js')), 'Build prod requis (npm run build)');

// ── Tests route (toujours actifs) ────────────────────────────────────────────

it("La route GET /offline retourne 200 et affiche 'Tu es hors ligne'", function () {
    $this->get('/offline')
        ->assertStatus(200)
        ->assertSee('Tu es hors ligne');
});

it('La route /offline est accessible sans authentification', function () {
    // Pas de actingAs — anonyme doit pouvoir accéder à la page offline
    $this->get('/offline')->assertStatus(200);
});
