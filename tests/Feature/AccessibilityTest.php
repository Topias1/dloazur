<?php

/**
 * AccessibilityTest — Plan 01-06 Task 1
 *
 * Tests d'accessibilité légers (régression a11y) pour toutes les pages publiques.
 * D-22 — validation pré-cutover : lang, h1 unique, alt sur les images, skip link,
 * focus-visible et prefers-reduced-motion dans le CSS compilé.
 */

$publicHtmlRoutes = [
    '/',
    '/services',
    '/realisations',
    '/contact',
    '/mentions-legales',
    '/cgv',
    '/confidentialite',
    '/blog',
];

// ---------------------------------------------------------------------------
// Test 9 — chaque page publique déclare lang="fr"
// ---------------------------------------------------------------------------

it('every public page declares lang="fr"', function (string $url) {
    $body = $this->get($url)->getContent();
    expect($body)->toMatch('/<html[^>]+lang="fr"/');
})->with($publicHtmlRoutes);

// ---------------------------------------------------------------------------
// Test 10 — chaque page publique HTML a exactement un <h1>
// ---------------------------------------------------------------------------

it('every public page has exactly one <h1>', function (string $url) {
    $body = $this->get($url)->getContent();
    $count = substr_count($body, '<h1');
    expect($count)->toBe(1, "La page {$url} doit avoir exactement un <h1>, trouvé : {$count}");
})->with($publicHtmlRoutes);

// ---------------------------------------------------------------------------
// Test 11 — chaque <img> de la home a un attribut alt
// ---------------------------------------------------------------------------

it('every <img> on the home page has an alt attribute', function () {
    $body = $this->get('/')->getContent();

    // Extrait tous les tags <img ...>
    preg_match_all('/<img\s[^>]+>/i', $body, $matches);
    $imgTags = $matches[0];

    // Si la page n'a pas d'images, le test passe (rien à valider)
    if (empty($imgTags)) {
        expect(true)->toBeTrue();
        return;
    }

    foreach ($imgTags as $tag) {
        $hasAlt = str_contains($tag, 'alt=');
        expect($hasAlt)->toBeTrue("L'image suivante n'a pas d'attribut alt :\n{$tag}");
    }
});

// ---------------------------------------------------------------------------
// Test 12 — le skip link est le premier élément interactif de la page
// ---------------------------------------------------------------------------

$skipLinkRoutes = ['/', '/services'];

it('skip link appears before <main> on critical pages', function (string $url) {
    $body = $this->get($url)->getContent();

    $skipPos = strpos($body, 'Aller au contenu principal');
    $mainPos = strpos($body, '<main');

    expect($skipPos)->not->toBeFalse('Le skip link "Aller au contenu principal" est absent de ' . $url);
    expect($mainPos)->not->toBeFalse('<main> absent de ' . $url);
    expect($skipPos)->toBeLessThan($mainPos, "Le skip link doit apparaître avant <main> sur {$url}");
})->with($skipLinkRoutes);

// ---------------------------------------------------------------------------
// Test 13 — le CSS compilé contient focus-visible + outline
// ---------------------------------------------------------------------------

it('compiled CSS contains focus-visible and outline', function () {
    $css = getCompiledCss();

    expect($css)->not->toBeNull('CSS compilé absent — lancez `npm run build` avant les tests')
        ->toContain('focus-visible')
        ->toContain('outline');
})->skip(fn () => ! compiledCssExists(), 'CSS compilé absent — lancez `npm run build` avant les tests');

// ---------------------------------------------------------------------------
// Test 14 — le CSS compilé contient prefers-reduced-motion + 0.001ms
// ---------------------------------------------------------------------------

it('compiled CSS contains prefers-reduced-motion clamp', function () {
    $css = getCompiledCss();

    // Tailwind v4 génère `.001ms` (sans zéro initial) pour le clamp motion
    expect($css)->not->toBeNull('CSS compilé absent — lancez `npm run build` avant les tests')
        ->toContain('prefers-reduced-motion')
        ->toContain('.001ms');
})->skip(fn () => ! compiledCssExists(), 'CSS compilé absent — lancez `npm run build` avant les tests');

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------

/**
 * Vérifie si le manifest Vite et le fichier CSS compilé existent sur le disque.
 */
function compiledCssExists(): bool
{
    $manifestPath = public_path('build/manifest.json');

    if (! file_exists($manifestPath)) {
        return false;
    }

    $manifest = json_decode(file_get_contents($manifestPath), true);
    $cssEntry  = $manifest['resources/css/app.css'] ?? null;

    if ($cssEntry === null) {
        return false;
    }

    return file_exists(public_path('build/' . ($cssEntry['file'] ?? '')));
}

/**
 * Lit le CSS compilé depuis le manifest Vite. Retourne null si absent.
 */
function getCompiledCss(): ?string
{
    if (! compiledCssExists()) {
        return null;
    }

    $manifestPath = public_path('build/manifest.json');
    $manifest     = json_decode(file_get_contents($manifestPath), true);
    $cssEntry     = $manifest['resources/css/app.css'];
    $cssFile      = public_path('build/' . $cssEntry['file']);

    return file_get_contents($cssFile);
}
