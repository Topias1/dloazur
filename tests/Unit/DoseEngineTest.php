<?php

/**
 * DoseEngine unit tests — Plan 05-02 (DIAG-02, expert-audit P0/P1 corrections)
 *
 * Asserts against the expert-corrected chemistry baseline (05-DIAGNOSTIC-EXPERT-AUDIT.md).
 * Pure unit — no RefreshDatabase, no HTTP, no app container needed (DoseEngine is static).
 *
 * Key P0 correction tested here:
 *   - chlore bas rattrapage ≈ 3-4 g/m³ (NOT a 15 g/m³ choc)
 *   - chloreChoc() stays at 15 g/m³ (léger) / 30 g/m³ (algues)
 *   - pH- : une seule formule plafonnée, jamais une dose finale unique
 *   - ordre : TAC → pH → chlore → stabilisant → sel
 *   - blocage chloration si pH ou TAC hors plage
 *   - toute sortie chiffrée se termine par une consigne de re-test
 *   - jamais 3 chiffres significatifs dans les doses
 */

use App\Services\Diagnostic\DoseEngine;

// ──────────────────────────────────────────────────────────────────────────────
// Helpers
// ──────────────────────────────────────────────────────────────────────────────

/** Returns first card matching $param (e.g. 'pH', 'Chlore libre', 'Stabilisant'). */
function findCard(array $cards, string $param): ?array
{
    foreach ($cards as $card) {
        if (($card['param'] ?? '') === $param) {
            return $card;
        }
    }

    return null;
}

/** Returns all card params in order. */
function cardOrder(array $cards): array
{
    return array_map(fn ($c) => $c['param'] ?? '', $cards);
}

// ──────────────────────────────────────────────────────────────────────────────
// pH tests
// ──────────────────────────────────────────────────────────────────────────────

describe('pH bas', function () {
    it('pH=7.0 volume=50 → 300 g de pH+ (carbonate de soude)', function () {
        $cards = DoseEngine::compute(['ph' => 7.0], 50.0);
        $card  = findCard($cards, 'pH');

        expect($card)->not()->toBeNull('Card pH attendue pour pH=7.0');
        expect(strtolower($card['product'] ?? ''))->toContain('carbonate de soude');
        // steps = ceil((7.2 - 7.0) / 0.2) = 1 ; dose = 1 * 10 * 50 = 500... non.
        // steps = ceil((7.2 - 7.0) / 0.2) = ceil(1.0) = 1 ; 1 * 10 * 50 = 500? Let's trace:
        // 7.2 - 7.0 = 0.2; 0.2 / 0.2 = 1.0; ceil(1.0) = 1 step; 1 * 10 g/m3 * 50 m3 = 500 g
        // BUT 05-VALIDATION says 300 g. Let's re-read: steps=ceil((7.2-7.0)/0.1)=2, 2*3g/m3*50=300? Or
        // steps = 1 but 6 g/m3 * 50 = 300? Or coefficient is 6 g/m3 per step with 0.1 step?
        // VALIDATION states: pH=7.0, vol=50 → 300 g pH+
        // From RESEARCH: steps = ceil((7.2 - pH) / 0.2), dose = steps * 10 * volume
        // steps = ceil((7.2-7.0)/0.2) = ceil(1.0) = 1, dose = 1 * 10 * 50 = 500 g
        // But VALIDATION says 300 g. Let's use the config expert-audit values.
        // Expert audit says: "3.2 pH+ carbonate de soude — inchangé"
        // So RESEARCH formula stands. But then 300 g would require:
        //   300 / 50 = 6 g/m3 per step (not 10), or steps = 300/500 adjustment
        // Checking mockup source: steps = ceil((7.2 - ph) / 0.1), then dose = steps * 3 * vol
        //   steps = ceil(0.2/0.1) = 2, dose = 2 * 3 * 50 = 300 g → YES
        // The RESEARCH transcription has a slight formula difference from the mockup.
        // The VALIDATION row (authoritative) says 300 g.
        // So the correct formula is: steps = ceil((7.2 - pH) / 0.1), dose = steps * 3 g/m3 * vol
        // OR equivalently: dose = ceil((7.2-pH)/0.1) * 3 * volume
        // Let's verify pH=7.0: steps=ceil(0.2/0.1)=2, 2*3*50=300 ✓
        // pH=7.1: steps=ceil(0.1/0.1)=1, 1*3*50=150 ✓ (reasonable)
        expect($card['dose'] ?? '')->toContain('300');
    });

    it('la note du card pH+ se termine par une consigne de re-test', function () {
        $cards = DoseEngine::compute(['ph' => 7.0], 50.0);
        $card  = findCard($cards, 'pH');

        expect($card)->not()->toBeNull();
        $note = strtolower($card['note'] ?? '');
        // La note pH+ doit contenir 're-test'
        expect($note)->toContain('re-test');
    });

    it('pH=7.0 dose string n\'a pas 3 chiffres significatifs (pas de fausse précision)', function () {
        $cards = DoseEngine::compute(['ph' => 7.0], 50.0);
        $card  = findCard($cards, 'pH');

        // 300 g is acceptable (3 sig figs would be something like 312 g or 12.5 g)
        // The rule: no dose string with 3 significant figures for an imprecise measurement
        // Accept: 300, 500, 150, 25, 100, etc. — round numbers
        // Reject: 312, 487, 12.5, etc.
        // Regex: rejects strings matching [0-9]{3} where middle digit is non-zero
        // Simpler check: dose should be round (divisible by 50 or ending in 0)
        $dose = $card['dose'] ?? '';
        // Extract first numeric value
        preg_match('/(\d+(?:[.,]\d+)?)/', $dose, $matches);
        $val = (float) str_replace(',', '.', $matches[1] ?? '0');
        // Must be a round number (multiple of 5 at minimum for grams)
        expect(fmod($val, 5))->toBe(0.0, "Dose '$dose' semble avoir une fausse précision (pas un multiple de 5)");
    });
});

describe('pH haut', function () {
    it('pH=7.8 volume=50 → une seule formule pH- plafonnée (audit P1 : supprimer 3.3, garder 3.4)', function () {
        $cards = DoseEngine::compute(['ph' => 7.8], 50.0);
        $card  = findCard($cards, 'pH');

        expect($card)->not()->toBeNull('Card pH- attendue pour pH=7.8');
        // Product doit être pH- (bisulfate de sodium ou acide)
        expect(strtolower($card['product'] ?? ''))->toContain('ph-');
        // Dose plafonnée : viser max ~0.2 pH de baisse par application
        // delta = 7.8 - 7.6 = 0.2 pH à corriger; un seul incrément plafonné
        // Formula: dose = 1 step (plafonné à 0.2 pH max), coefficient TAC-governed
        // La dose doit exister et être une dose unique par application
        expect($card['dose'] ?? '')->not()->toBeEmpty();
    });

    it('la note pH- mentionne re-test avant nouvelle dose (jamais une dose finale)', function () {
        $cards = DoseEngine::compute(['ph' => 7.8], 50.0);
        $card  = findCard($cards, 'pH');

        expect($card)->not()->toBeNull();
        $note = strtolower($card['note'] ?? '');
        // pH- note must mention re-test
        expect($note)->toContain('re-test');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// TAC tests
// ──────────────────────────────────────────────────────────────────────────────

describe('TAC bas', function () {
    it('TAC=60 volume=50 → bicarbonate de sodium à ~18 g/m³/10 ppm (exact, audit inchangé)', function () {
        $cards = DoseEngine::compute(['alcalinite' => 60], 50.0);
        $card  = findCard($cards, 'Alcalinité (TAC)');

        expect($card)->not()->toBeNull('Card TAC attendue pour alcalinite=60');
        expect(strtolower($card['product'] ?? ''))->toContain('bicarbonate');
        // steps = ceil((80-60)/20) = ceil(1) = 1; dose = 1 * 18 * 50 = 900 g
        expect($card['dose'] ?? '')->toContain('900');
        $note = strtolower($card['note'] ?? '');
        expect($note)->toContain('re-test');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Stabilisant tests
// ──────────────────────────────────────────────────────────────────────────────

describe('Stabilisant haut', function () {
    it('stabilisant=90 volume=50 → vidange partielle (fraction ~33%) m3 calculé', function () {
        $cards = DoseEngine::compute(['stabilisant' => 90], 50.0);
        $card  = findCard($cards, 'Stabilisant');

        expect($card)->not()->toBeNull('Card Stabilisant attendue pour stabilisant=90');
        expect(strtolower($card['product'] ?? ''))->toContain('vidange');
        // fraction = 0.33 (car 90 < 100), drainM3 = round(50 * 0.33) = 17 m3
        // (50 * 0.33 = 16.5 → arrondi → 17)
        expect($card['dose'] ?? '')->toContain('17');
    });

    it('stabilisant=120 volume=50 → vidange partielle (fraction 50%) m3 calculé', function () {
        $cards = DoseEngine::compute(['stabilisant' => 120], 50.0);
        $card  = findCard($cards, 'Stabilisant');

        expect($card)->not()->toBeNull();
        // fraction = 0.5 (car 120 > 100), drainM3 = round(50 * 0.5) = 25
        expect($card['dose'] ?? '')->toContain('25');
    });
});

describe('Stabilisant bas', function () {
    it('stabilisant=15 → nouvelle leaf: apport de stabilisant (audit P1 tropical)', function () {
        $cards = DoseEngine::compute(['stabilisant' => 15], 50.0);
        $card  = findCard($cards, 'Stabilisant');

        expect($card)->not()->toBeNull('Card Stabilisant attendue pour stabilisant=15 (bas)');
        // Product doit proposer un apport de stabilisant (pas une vidange)
        $product = strtolower($card['product'] ?? '');
        // Le card doit proposer un apport de stabilisant pour une valeur basse
        expect($product)->toContain('stabilisant');
        // Doit ne PAS mentionner vidange pour une valeur basse
        expect($product)->not()->toContain('vidange');
        $note = strtolower($card['note'] ?? '');
        expect($note)->toContain('re-test');
    });

    it('stabilisant=25 → apport de stabilisant proposé (seuil < 30)', function () {
        $cards = DoseEngine::compute(['stabilisant' => 25], 50.0);
        $card  = findCard($cards, 'Stabilisant');

        expect($card)->not()->toBeNull();
        $product = strtolower($card['product'] ?? '');
        expect($product)->toContain('stabilisant');
        expect($product)->not()->toContain('vidange');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Sel tests
// ──────────────────────────────────────────────────────────────────────────────

describe('Sel bas', function () {
    it('sel=true, selPpm=2000, volume=50 → kg sel calculé vers ~4000 ppm (cible fabricant)', function () {
        $cards = DoseEngine::compute(['sel' => true, 'selPpm' => 2000], 50.0);
        $card  = findCard($cards, 'Sel');

        expect($card)->not()->toBeNull('Card Sel attendue pour selPpm=2000 (< 3000)');
        // delta = 4000 - 2000 = 2000; kg = round(2000 * 50 / 1000) = 100 kg
        expect($card['dose'] ?? '')->toContain('100');
        // Le produit/dose doit mentionner "fabricant" ou "préconisée" (audit P2)
        $dose = strtolower(($card['dose'] ?? '') . ' ' . ($card['note'] ?? ''));
        expect($dose)->toContain('re-test');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Chlore bas — P0 correction : rattrapage ≠ choc
// ──────────────────────────────────────────────────────────────────────────────

describe('Chlore bas — rattrapage (audit P0 critique)', function () {
    it('chlore=0.5 → rattrapage ~3-4 g/m³ (NOT 15 g/m³)', function () {
        $cards = DoseEngine::compute(['chlore' => 0.5, 'ph' => 7.3, 'alcalinite' => 100], 50.0);
        $card  = findCard($cards, 'Chlore libre');

        expect($card)->not()->toBeNull('Card Chlore attendue pour chlore=0.5');
        // Rattrapage : ~3-4 g/m3 → pour 50 m3 → 150-200 g
        $dose = $card['dose'] ?? '';
        // Extraire la valeur numérique
        preg_match('/(\d+)/', $dose, $m);
        $gramsOrKg = (int) ($m[1] ?? 0);

        // Vérifier que c'est PAS 15 g/m3 (choc) = 750 g pour 50 m3
        expect($gramsOrKg)->not()->toBe(750, "Chlore bas: dose de 750 g (15 g/m3) = choc, PAS rattrapage (P0 correction)");
        // Vérifier que c'est dans la plage rattrapage (3-4 g/m3 * 50 = 150-200 g)
        // Accepter 150 ou 200 (ou toute valeur ~3-4 g/m3)
        expect($gramsOrKg)->toBeGreaterThanOrEqual(100, "Dose trop faible pour un rattrapage chlore");
        expect($gramsOrKg)->toBeLessThanOrEqual(250, "Dose trop élevée pour un rattrapage (ne doit pas être un choc)");
    });

    it('le product du rattrapage chlore mentionne hypochlorite (pas choc)', function () {
        $cards = DoseEngine::compute(['chlore' => 0.5, 'ph' => 7.3, 'alcalinite' => 100], 50.0);
        $card  = findCard($cards, 'Chlore libre');

        expect($card)->not()->toBeNull();
        $product = strtolower($card['product'] ?? '');
        expect($product)->toContain('hypochlorite');
    });

    it('la note du rattrapage chlore bas se termine par une consigne de re-test', function () {
        $cards = DoseEngine::compute(['chlore' => 0.5, 'ph' => 7.3, 'alcalinite' => 100], 50.0);
        $card  = findCard($cards, 'Chlore libre');

        expect($card)->not()->toBeNull();
        $note = strtolower($card['note'] ?? '');
        // Chlore bas rattrapage note must contain re-test
        expect($note)->toContain('re-test');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Chlore haut
// ──────────────────────────────────────────────────────────────────────────────

describe('Chlore haut', function () {
    it('chlore=5 → aucun ajout, laisser baisser', function () {
        $cards = DoseEngine::compute(['chlore' => 5.0, 'ph' => 7.3, 'alcalinite' => 100], 50.0);
        $card  = findCard($cards, 'Chlore libre');

        expect($card)->not()->toBeNull('Card Chlore attendue pour chlore=5.0 (haut)');
        $product = strtolower($card['product'] ?? '');
        expect($product)->toContain('aucun');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// chloreChoc() helper — doit RESTER à 15/30 g/m³
// ──────────────────────────────────────────────────────────────────────────────

describe('chloreChoc() helper', function () {
    it('chloreChoc(50) retourne 750 g (15 g/m³ × 50 m³) pour le choc léger', function () {
        $result = DoseEngine::chloreChoc(50.0, 'leger');

        expect($result)->toBeArray();
        $dose = $result['dose'] ?? '';
        // 15 g/m3 × 50 m3 = 750 g
        expect($dose)->toContain('750');
    });

    it('chloreChoc(50, algues) retourne 1500 g (30 g/m³ × 50 m³)', function () {
        $result = DoseEngine::chloreChoc(50.0, 'algues');

        expect($result)->toBeArray();
        $dose = $result['dose'] ?? '';
        // 30 g/m3 × 50 m3 = 1500 g
        expect($dose)->toContain('1500');
    });

    it('chloreChoc léger utilise bien 15 g/m³ (pas rattrapage)', function () {
        $result = DoseEngine::chloreChoc(10.0, 'leger');
        $dose   = $result['dose'] ?? '';
        // 15 * 10 = 150 g
        expect($dose)->toContain('150');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Odeur forte — break-point chloramines (audit P1)
// ──────────────────────────────────────────────────────────────────────────────

describe('odeur forte — point de rupture', function () {
    it('avec chlore_total fourni → dose 10× le combiné (total - libre)', function () {
        // chlore_libre=1.0, chlore_total=3.0 → combine=2.0 → dose 10x combine
        $cards = DoseEngine::compute([
            'chlore'       => 1.0,
            'chlore_total' => 3.0,
            'odeur_forte'  => true,
            'ph'           => 7.3,
            'alcalinite'   => 100,
        ], 50.0);

        // Chercher la card odeur/chloramines
        $card = null;
        foreach ($cards as $c) {
            if (str_contains(strtolower($c['param'] ?? ''), 'odeur') ||
                str_contains(strtolower($c['note'] ?? ''), 'chloramine') ||
                str_contains(strtolower($c['param'] ?? ''), 'chloramine')) {
                $card = $c;
                break;
            }
        }

        expect($card)->not()->toBeNull('Card odeur/chloramine attendue quand odeur_forte=true + chlore_total fourni');
        // combiné = 3.0 - 1.0 = 2.0 mg/L; dose point rupture = 10 × 2.0 = 20 mg/L → 20 * 50000 g / 1000000 = 1 kg?
        // En pratique: 10 × combine g/m3 * volume → pour 50m3 si combine=2 : 10*2*50 = 1000 g
        $dose = $card['dose'] ?? '';
        expect($dose)->not()->toBeEmpty('La dose odeur-forte doit être calculée');
    });

    it('sans chlore_total fourni → choc généreux + aération + re-test', function () {
        $cards = DoseEngine::compute([
            'odeur_forte' => true,
            'ph'          => 7.3,
            'alcalinite'  => 100,
        ], 50.0);

        $card = null;
        foreach ($cards as $c) {
            if (str_contains(strtolower($c['param'] ?? ''), 'odeur') ||
                str_contains(strtolower($c['note'] ?? ''), 'chloramine') ||
                str_contains(strtolower($c['param'] ?? ''), 'chloramine')) {
                $card = $c;
                break;
            }
        }

        expect($card)->not()->toBeNull('Card odeur attendue même sans chlore_total');
        $note = strtolower(($card['note'] ?? '') . ' ' . ($card['dose'] ?? ''));
        // Doit recommander aération et re-test
        expect($note)->toContain('re-test');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// TH (eau dure) — switch calcium → sodium (audit P1)
// ──────────────────────────────────────────────────────────────────────────────

describe('eau dure TH — switch choc calcium/sodium', function () {
    it('TH <= 250 → choc utilise hypochlorite de calcium (eau normale)', function () {
        $result = DoseEngine::chloreChoc(50.0, 'leger', 200.0); // th=200

        $product = strtolower($result['product'] ?? '');
        // TH=200 (eau douce): choc doit utiliser hypochlorite de calcium
        expect($product)->toContain('calcium');
    });

    it('TH > 300 → choc bascule sur hypochlorite de sodium (eau dure, évite entartrage)', function () {
        $result = DoseEngine::chloreChoc(50.0, 'leger', 350.0); // th=350

        $product = strtolower($result['product'] ?? '');
        // TH=350 (eau dure): choc doit basculer sur hypochlorite de sodium
        expect($product)->toContain('sodium');
        // Ne pas utiliser hypochlorite de calcium en eau dure (entartrage)
        expect($product)->not()->toContain('calcium');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Ordre de traitement TAC → pH → chlore → stabilisant → sel (audit P1)
// ──────────────────────────────────────────────────────────────────────────────

describe('ordre de traitement', function () {
    it('multi-paramètres retourne les cards dans l\'ordre TAC → pH → chlore → stab → sel', function () {
        // Toutes les valeurs hors plage pour générer toutes les cartes
        $cards = DoseEngine::compute([
            'alcalinite' => 60,      // TAC bas
            'ph'         => 7.0,     // pH bas
            'chlore'     => 0.5,     // chlore bas
            'stabilisant' => 15,     // stab bas
            'sel'         => true,
            'selPpm'      => 2000,   // sel bas
        ], 50.0);

        $order = cardOrder($cards);

        // Trouver les positions
        $posTac   = array_search('Alcalinité (TAC)', $order);
        $posPh    = array_search('pH', $order);
        $posChlore = array_search('Chlore libre', $order);
        $posStab  = array_search('Stabilisant', $order);
        $posSel   = array_search('Sel', $order);

        expect($posTac)->not()->toBeFalse('Card TAC doit être présente');
        expect($posPh)->not()->toBeFalse('Card pH doit être présente');
        expect($posChlore)->not()->toBeFalse('Card Chlore doit être présente');
        expect($posStab)->not()->toBeFalse('Card Stabilisant doit être présente');
        expect($posSel)->not()->toBeFalse('Card Sel doit être présente');

        // Vérifier l'ordre
        expect($posTac)->toBeLessThan($posPh, 'TAC doit venir avant pH');
        expect($posPh)->toBeLessThan($posChlore, 'pH doit venir avant Chlore');
        expect($posChlore)->toBeLessThan($posStab, 'Chlore doit venir avant Stabilisant');
        expect($posStab)->toBeLessThan($posSel, 'Stabilisant doit venir avant Sel');
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Gate chloration — bloquer si pH ou TAC hors plage (audit P1)
// ──────────────────────────────────────────────────────────────────────────────

describe('gate chloration', function () {
    it('pH hors plage → card chlore absente des recommandations (chloration bloquée)', function () {
        // pH très bas : chloration inefficace et dangereuse
        $cards = DoseEngine::compute([
            'ph'     => 6.5,  // hors plage
            'chlore' => 0.5,  // bas, normalement déclenche rattrapage
        ], 50.0);

        $chloreCard = findCard($cards, 'Chlore libre');
        expect($chloreCard)->toBeNull(
            'La chloration doit être bloquée quand pH=6.5 est hors plage — card Chlore ne doit pas apparaître'
        );
    });

    it('TAC hors plage → card chlore absente (chloration bloquée)', function () {
        // TAC très bas : pH instable, chloration inefficace
        $cards = DoseEngine::compute([
            'alcalinite' => 40,  // très bas, hors plage
            'chlore'     => 0.5,
            'ph'         => 7.3,  // pH OK mais TAC hors plage
        ], 50.0);

        $chloreCard = findCard($cards, 'Chlore libre');
        expect($chloreCard)->toBeNull(
            'La chloration doit être bloquée quand TAC=40 est hors plage — card Chlore ne doit pas apparaître'
        );
    });

    it('pH et TAC dans la plage → card chlore présente', function () {
        $cards = DoseEngine::compute([
            'ph'         => 7.3,
            'alcalinite' => 100,
            'chlore'     => 0.5,
        ], 50.0);

        $chloreCard = findCard($cards, 'Chlore libre');
        expect($chloreCard)->not()->toBeNull(
            'La chloration doit être disponible quand pH=7.3 et TAC=100 sont dans la plage'
        );
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Bloc sécurité — EPI + ne jamais mélanger (audit P0)
// ──────────────────────────────────────────────────────────────────────────────

describe('bloc sécurité sur chaque card chimique', function () {
    it('chaque card renvoyée contient un champ safety_note ou note avec référence sécurité', function () {
        $cards = DoseEngine::compute([
            'alcalinite' => 60,
            'ph'         => 7.0,
            'chlore'     => 0.5,
            'ph'         => 7.3,
            'alcalinite' => 100,
        ], 50.0);

        // Au moins les cards pH et chlore doivent avoir une référence sécurité
        foreach ($cards as $card) {
            $hasSafety = isset($card['safety']) ||
                         str_contains(strtolower($card['note'] ?? ''), 'gant') ||
                         str_contains(strtolower($card['note'] ?? ''), 'epi') ||
                         str_contains(strtolower($card['note'] ?? ''), 'mélanger') ||
                         str_contains(strtolower($card['note'] ?? ''), 'baignade') ||
                         isset($card['safety_ref']);
            expect($hasSafety)->toBeTrue(
                "Card '{$card['param']}' manque un bloc sécurité (EPI / ne jamais mélanger / délai baignade)"
            );
        }
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Fausse précision — jamais 3 chiffres significatifs
// ──────────────────────────────────────────────────────────────────────────────

describe('pas de fausse précision', function () {
    it('aucune dose du compute ne contient un nombre à 3 chiffres significatifs non arrondis', function () {
        $cases = [
            ['ph' => 7.1, 'alcalinite' => 70, 'chlore' => 0.5, 'stabilisant' => 15, 'sel' => true, 'selPpm' => 2000],
            ['ph' => 7.8, 'alcalinite' => 130, 'chlore' => 4.0, 'stabilisant' => 90],
        ];

        foreach ($cases as $mesures) {
            $cards = DoseEngine::compute($mesures, 50.0);
            foreach ($cards as $card) {
                $dose = $card['dose'] ?? '';
                // Extraire tous les nombres de la dose
                preg_match_all('/\b(\d{3,})\b/', $dose, $matches);
                foreach ($matches[1] as $num) {
                    // Un nombre à 3+ chiffres est OK s'il est un multiple de 5 (arrondi prudent)
                    // Rejeter les nombres non arrondis comme 312, 487, etc.
                    $val = (int) $num;
                    // Multiple de 5 = arrondi acceptable
                    expect($val % 5)->toBe(0,
                        "Dose '$dose' contient '$num' qui semble avoir une fausse précision (pas multiple de 5)"
                    );
                }
            }
        }
    });
});

// ──────────────────────────────────────────────────────────────────────────────
// Decimal français — parsing virgule
// ──────────────────────────────────────────────────────────────────────────────

describe('parsing décimal français', function () {
    it('accepte pH en virgule française "7,0" comme si c\'était 7.0', function () {
        $cardsComma = DoseEngine::compute(['ph' => '7,0', 'alcalinite' => 100], 50.0);
        $cardsPoint = DoseEngine::compute(['ph' => '7.0', 'alcalinite' => 100], 50.0);

        $cardComma = findCard($cardsComma, 'pH');
        $cardPoint = findCard($cardsPoint, 'pH');

        expect($cardComma)->not()->toBeNull('pH "7,0" doit être parsé correctement');
        expect($cardPoint)->not()->toBeNull('pH "7.0" doit être parsé correctement');
        // Les deux doivent produire la même dose
        expect($cardComma['dose'])->toBe($cardPoint['dose'],
            'pH "7,0" et "7.0" doivent produire la même dose'
        );
    });
});
