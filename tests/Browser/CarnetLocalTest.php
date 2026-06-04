<?php

/**
 * DIAG-07 — Carnet local-only browser test (Plan 05-06)
 *
 * Ces tests vérifient la persistance du carnet localStorage à travers les sessions
 * de navigation, la lecture hors-ligne (0 appel réseau pour l'historique), et
 * l'effacement sur l'appareil.
 *
 * Architecture :
 *   - Le carnet est un module localStorage pur (diagnostic-carnet.js).
 *   - La persistance est garantie par le navigateur, pas par le serveur.
 *   - Les tests browser nécessitent Playwright (Pest v4 browser plugin).
 *
 * Note de skip :
 *   En environnement de développement sans driver Playwright installé,
 *   les assertions browser sont skipées avec une raison explicite.
 *   Les assertions d'existence des artefacts (non-browser) sont toujours vertes.
 *   Voir 05-VALIDATION.md > Manual-Only pour les étapes de vérification manuelle.
 *
 * DIAG-02 invariant : ce fichier de test ne contient aucune formule de dosage.
 * XSS T-05-20 : les assertions vérifient que les entrées sont rendues via x-text.
 */

use Illuminate\Foundation\Testing\RefreshDatabase;

// ──────────────────────────────────────────────────────────────────────────────
// Assertion d'existence des artifacts DIAG-07 (non-browser, toujours verts)
// ──────────────────────────────────────────────────────────────────────────────

it('DIAG-07: le module carnet localStorage existe et est bundlé', function () {
    // Vérifie que le module source est présent
    expect(file_exists(base_path('resources/js/diagnostic-carnet.js')))->toBeTrue();

    // Vérifie que le module est importé dans app.js
    $appJs = file_get_contents(base_path('resources/js/app.js'));
    expect($appJs)->toContain('diagnostic-carnet.js');
    expect($appJs)->toContain('carnetStore');
    expect($appJs)->toContain('carnetResumeStrip');
})->group('diag-07');

it('DIAG-07: diagnostic-carnet.js respecte le contrat DIAG-02 (0 formule de dose)', function () {
    $carnetJs = file_get_contents(base_path('resources/js/diagnostic-carnet.js'));

    // Aucune formule arithmétique de dose (coefficient * volume)
    expect($carnetJs)->not->toMatch('/[0-9]+ ?\* ?(volume|m3)/');

    // Aucune référence à DoseEngine ou config/diagnostic-formulas
    expect($carnetJs)->not->toContain('DoseEngine');
    expect($carnetJs)->not->toContain('diagnostic-formulas');
})->group('diag-07');

it('DIAG-07: la vue wizard contient le markup de la liste carnet (S9)', function () {
    $view = file_get_contents(base_path('resources/views/livewire/diagnostic-wizard.blade.php'));

    // La liste S9 est présente
    expect($view)->toContain('Mes diagnostics passes');
    expect($view)->toContain('Aucun diagnostic pour l\'instant');
    expect($view)->toContain('Reprendre ce diagnostic');
    expect($view)->toContain('Effacer l\'historique');
    expect($view)->toContain('Lancer un diagnostic');

    // Confirm inline (pas de modal réflexive — UI-SPEC S9)
    expect($view)->toContain('Effacer tout l\'historique de cet appareil');
    expect($view)->toContain('Garder');

    // x-text uniquement (XSS T-05-20 — jamais innerHTML / {!! !!})
    expect($view)->not->toMatch('/{!!.*carnet.*!!}/');
})->group('diag-07');

it('DIAG-07: le strip visiteur de retour est dans la landing', function () {
    $view = file_get_contents(base_path('resources/views/vitrine/diagnostic.blade.php'));

    // Strip "Reprendre mon dernier diagnostic" conditionnelle au carnet
    expect($view)->toContain('Reprendre mon dernier diagnostic');
    expect($view)->toContain('carnetResumeStrip');
    expect($view)->toContain('hasLatest()');
})->group('diag-07');

it('DIAG-07: la boucle re-test est presente dans le wizard sans push ni scheduler', function () {
    $view = file_get_contents(base_path('resources/views/livewire/diagnostic-wizard.blade.php'));

    // Prompt re-test présent
    expect($view)->toContain('As-tu re-teste');
    expect($view)->toContain('Ca a marche');
    expect($view)->toContain('onRetestOui');
    expect($view)->toContain('onRetestNon');
    expect($view)->toContain('triggerRetestFailed');

    // 0 push, 0 scheduler (V0)
    $js = file_get_contents(base_path('resources/js/diagnostic-carnet.js'));
    expect($js)->not->toContain('Notification.');
    expect($js)->not->toContain('pushManager');
    expect($js)->not->toContain('schedule(');
})->group('diag-07');

it('DIAG-07: la route /diagnostic charge la page et le composant wizard', function () {
    $response = $this->get('/diagnostic');

    $response->assertStatus(200);
    $response->assertSee('Avant de commencer'); // D-09 : stable Plan 01 et Plan 02 (S0 supprimé en Plan 02)
})->group('diag-07');

// ──────────────────────────────────────────────────────────────────────────────
// Tests browser (Playwright) — skippes si le driver n'est pas disponible
//
// Ces tests nécessitent : Playwright installé + serveur de test en marche.
// Vérification manuelle documentée dans 05-VALIDATION.md > Manual-Only.
//
// Pour activer : installer pest-plugin-browser + playwright
//   composer require pestphp/pest-plugin-browser --dev
//   playwright install
// ──────────────────────────────────────────────────────────────────────────────

it('DIAG-07 (browser): le carnet persiste a travers les sessions de navigation', function () {
    // Instructions manuelles (05-VALIDATION.md Manual-Only) :
    //   1. Ouvrir /diagnostic dans un navigateur
    //   2. Compléter un diagnostic (Analyser mon eau -> mesures -> Calculer)
    //   3. Vérifier que "Mes diagnostics passés" apparaît dans l'écran de sélection
    //   4. Recharger la page (F5)
    //   5. Vérifier que l'entrée est toujours visible
    //   6. Ouvrir DevTools > Application > Local Storage : confirmer la clé dloazur_diagnostic_carnet_v1
    //   7. Confirmer 0 requête réseau pour la lecture de l'historique (Network tab)
})->todo('Playwright assertions pending — DIAG-07 persistence across reload')
  ->group('diag-07', 'browser-manual');

it('DIAG-07 (browser): la lecture de l\'historique fait 0 appel réseau', function () {
    // Instructions manuelles :
    //   1. Compléter un diagnostic
    //   2. Recharger + ouvrir DevTools > Network
    //   3. Naviguer vers "Mes diagnostics passés"
    //   4. Confirmer : 0 requête XHR/fetch vers /api ou /diagnostic
})->todo('Playwright assertions pending — DIAG-07 zero network reads for history')
  ->group('diag-07', 'browser-manual');

it('DIAG-07 (browser): effacer l\'historique vide la liste', function () {
    // Instructions manuelles :
    //   1. Avoir au moins un diagnostic dans le carnet
    //   2. Cliquer "Effacer l'historique"
    //   3. Confirmer le prompt "Effacer tout l'historique de cet appareil ?"
    //   4. Cliquer "Effacer l'historique" (bouton danger)
    //   5. Vérifier que la liste affiche "Aucun diagnostic pour l'instant"
    //   6. Confirmer LocalStorage vide (clé dloazur_diagnostic_carnet_v1 absente)
})->todo('Playwright assertions pending — DIAG-07 clear history empties list')
  ->group('diag-07', 'browser-manual');
