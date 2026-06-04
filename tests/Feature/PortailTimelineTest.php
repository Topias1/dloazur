<?php

/**
 * PortailTimelineTest — Tests de régression sur l'historique dépliable du portail client
 *
 * T1 : Structure a11y accordéon — aria-expanded, aria-controls, id panneau
 * T2 : Contenu déplié — mesures pH/Cl/TAC avec format virgule décimale exact (scoped au panneau B)
 * T3 : Actions réalisées dans le panneau historique (scoped au panneau B)
 * T4 : Notes dans le panneau historique (scoped au panneau B)
 * T5 : Compteur photos absent quand aucune photo seedée (cas base sans R2)
 *
 * Seed : 2 passages requis pour que l'historique s'affiche ($passages->count() > 1).
 * Le passage A (récent, subDays(1)) va dans « Dernier passage » via skip(1).
 * Le passage B (historique, subDays(10)) apparaît dans l'accordéon — c'est lui qu'on assert.
 */

use App\Models\Client;
use App\Models\Passage;
use App\Models\Piscine;

uses(\Illuminate\Foundation\Testing\RefreshDatabase::class);

// ---------------------------------------------------------------------------
// Helpers : seed commun
// ---------------------------------------------------------------------------

/**
 * Crée un client + piscine + deux passages (A récent, B historique avec valeurs distinctives).
 * Retourne [$client, $passageA, $passageB].
 */
function seedTimelineFixture(): array
{
    $client  = Client::factory()->create(['name' => 'Client Timeline Test']);
    $piscine = Piscine::factory()->create(['client_id' => $client->id]);

    // Passage A — le plus récent → ira dans « Dernier passage » (skip(1) l'exclut de l'historique)
    $passageA = Passage::factory()->create([
        'client_id'    => $client->id,
        'piscine_id'   => $piscine->id,
        'visited_at'   => now()->subDays(1),
        'ph_avant'     => 7.2,
        'chlore_libre' => 1.5,
        'tac'          => null,
    ]);

    // Passage B — historique → apparaît dans l'accordéon dépliable
    // Valeurs DISTINCTIVES (non-défaut) pour asserter sans ambiguïté
    $passageB = Passage::factory()->create([
        'client_id'    => $client->id,
        'piscine_id'   => $piscine->id,
        'visited_at'   => now()->subDays(10),
        'ph_avant'     => 7.4,     // → « 7,4 » dans la vue
        'chlore_libre' => 2.3,     // → « 2,3 » dans la vue
        'tac'          => 95,      // → « 95 » dans la vue (null par défaut — forcé ici)
        'actions'      => ['Nettoyage filtre', 'Brossage parois'],
        'notes'        => 'Note historique XQ7Z',
    ]);

    return [$client, $passageA, $passageB];
}

/**
 * Extrait le fragment HTML du panneau accordéon pour un passage donné.
 * Cherche depuis l'attribut id="passage-panel-{id}" sur le <div> dépliable
 * et retourne une tranche suffisamment large pour couvrir le contenu du panneau.
 * Fait échouer le test si le panneau est introuvable.
 */
function extractAccordionPanel(string $html, int|string $passageId): string
{
    $needle = 'id="passage-panel-' . $passageId . '"';
    $pos    = strpos($html, $needle);

    expect($pos)->not->toBeFalse("Le panneau accordéon pour le passage #{$passageId} est introuvable dans le HTML rendu.");

    // 6 000 caractères couvrent un panneau complet avec Livewire blade-comment verbosity
    // (mesures 3×, actions N×, notes) — suffisant sans dépasser sur le panneau suivant.
    return substr($html, $pos, 6000);
}

// ---------------------------------------------------------------------------
// T1 : Structure a11y accordéon
// ---------------------------------------------------------------------------
test('T1 — structure a11y accordéon : aria-expanded, aria-controls et id panneau présents', function () {
    [$client, $passageA, $passageB] = seedTimelineFixture();

    $response = $this->actingAs($client, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);

    // aria-expanded est déjà présent sur le bouton avant nos modifications
    $response->assertSee('aria-expanded', false);

    // aria-controls doit pointer vers le panneau du passage historique (B)
    $response->assertSee('aria-controls="passage-panel-' . $passageB->id . '"', false);

    // Le panneau déplié porte l'id correspondant
    $response->assertSee('id="passage-panel-' . $passageB->id . '"', false);
});

// ---------------------------------------------------------------------------
// T2 : Contenu déplié — mesures pH / Cl libre / TAC (scoped au panneau B)
// ---------------------------------------------------------------------------
test('T2 — contenu déplié : mesures pH 7,4 / Cl 2,3 / TAC 95 rendues DANS le panneau accordéon B', function () {
    [$client, $passageA, $passageB] = seedTimelineFixture();

    $response = $this->actingAs($client, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);

    // Scoper les assertions au panneau du passage B — si le bloc TAC/pH/Cl est supprimé du
    // panneau, le test doit échouer même si les valeurs existent ailleurs dans la page.
    $panel = extractAccordionPanel($response->getContent(), $passageB->id);

    // La vue utilise number_format($val, 1, ',', '') → virgule décimale française
    expect($panel)->toContain('7,4');   // pH passage B
    expect($panel)->toContain('2,3');   // Cl libre passage B
    expect($panel)->toContain('95');    // TAC passage B (number_format 0 décimales)
});

// ---------------------------------------------------------------------------
// T3 : Actions réalisées dans le panneau historique (scoped au panneau B)
// ---------------------------------------------------------------------------
test('T3 — actions réalisées : libellés du passage historique visibles DANS le panneau accordéon B', function () {
    [$client, $passageA, $passageB] = seedTimelineFixture();

    $response = $this->actingAs($client, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);

    $panel = extractAccordionPanel($response->getContent(), $passageB->id);

    expect($panel)->toContain('Nettoyage filtre');
    expect($panel)->toContain('Brossage parois');
});

// ---------------------------------------------------------------------------
// T4 : Notes dans le panneau historique (scoped au panneau B)
// ---------------------------------------------------------------------------
test('T4 — notes : texte du passage historique visible DANS le panneau accordéon B', function () {
    [$client, $passageA, $passageB] = seedTimelineFixture();

    $response = $this->actingAs($client, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);

    $panel = extractAccordionPanel($response->getContent(), $passageB->id);

    expect($panel)->toContain('Note historique XQ7Z');
});

// ---------------------------------------------------------------------------
// T5 : Compteur photos — absent quand aucune photo seedée
// ---------------------------------------------------------------------------
test('T5 — compteur photos : icône caméra absente quand le passage historique n\'a pas de photo', function () {
    [$client, $passageA, $passageB] = seedTimelineFixture();

    // Aucune photo attachée à $passageB (relation photos vide par défaut en test)
    $response = $this->actingAs($client, 'clients')
        ->get('/portail/passages');

    $response->assertStatus(200);

    // Scoper au panneau B + son bouton d'en-tête (le span photo est dans le bouton, avant le panneau id)
    // On cherche depuis aria-controls="passage-panel-{id}" (dans le bouton) pour couvrir toute la <li>
    $html    = $response->getContent();
    $btnAnchor = 'aria-controls="passage-panel-' . $passageB->id . '"';
    $btnPos    = strpos($html, $btnAnchor);

    expect($btnPos)->not->toBeFalse("Le bouton accordéon pour le passage #{$passageB->id} est introuvable.");

    // Extraire depuis le bouton jusqu'à la fin du panneau (~4 000 car couvre bouton + panneau)
    $liFragment = substr($html, $btnPos, 4000);

    // Le span photo (caméra SVG + compteur) n'est rendu que si $p->photos->isNotEmpty().
    // Sans photo, le bloc est entièrement absent. On vérifie l'absence du début unique du
    // path SVG de la caméra, présent exclusivement dans ce bloc conditionnel.
    expect($liFragment)->not->toContain('M6.827 6.175A2.31');
});
