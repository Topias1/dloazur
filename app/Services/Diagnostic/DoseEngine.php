<?php

namespace App\Services\Diagnostic;

/**
 * Moteur de dosage piscine — fonctions pures côté serveur.
 *
 * Lit les coefficients et seuils depuis config('diagnostic-formulas').
 * Ne contient aucun effet de bord (I/O, DB, HTTP) — pure static functions only.
 *
 * DIAG-02 invariant : aucune formule ni coefficient ne doit atteindre le JS client.
 * Déclenché par un wire:click Livewire (round-trip serveur), jamais Alpine.
 *
 * Chimie : baseline expert-auditée (05-DIAGNOSTIC-EXPERT-AUDIT.md).
 * Surface de revue Pierre : config/diagnostic-formulas.php (version = 1).
 *
 * Ordre de traitement : TAC → pH → chlore → stabilisant → sel (audit section 7).
 * Gate chloration : si pH ou TAC hors plage, la card chlore est withheld.
 */
final class DoseEngine
{
    /**
     * Calcule les recommandations de dosage à partir des mesures et du volume.
     *
     * Returns an ordered array of recommendation cards:
     *   ['param', 'current', 'target', 'product', 'dose', 'note', 'safety_ref']
     *
     * Ordre garanti : TAC → pH → chlore → stabilisant → sel (audit P1 section 7).
     * La card chlore est omise si pH ou TAC sont hors plage (gate chloration, audit P1).
     *
     * @param  array<string, mixed>  $mesures  Mesures indexées :
     *         ph (float|string), alcalinite (float|string), chlore (float|string),
     *         chlore_total (float|string, optionnel), stabilisant (float|string),
     *         sel (bool), selPpm (float|string), th (float|string, optionnel),
     *         odeur_forte (bool, optionnel)
     * @param  float  $volume  Volume du bassin en m³
     * @return array<int, array<string, string>>  Cards de recommandations
     */
    public static function compute(array $mesures, float $volume): array
    {
        $cfg = config('diagnostic-formulas');

        // ── Parsing des mesures (virgule française acceptée) ──────────────────
        $ph         = self::toFloat($mesures['ph'] ?? null);
        $alcalinite = self::toFloat($mesures['alcalinite'] ?? null);
        $chlore     = self::toFloat($mesures['chlore'] ?? null);
        $chloreTotal = self::toFloat($mesures['chlore_total'] ?? null);
        $stabilisant = self::toFloat($mesures['stabilisant'] ?? null);
        $selPpm     = self::toFloat($mesures['selPpm'] ?? null);
        $hasSel     = (bool) ($mesures['sel'] ?? false);
        $odeurForte = (bool) ($mesures['odeur_forte'] ?? false);
        $th         = self::toFloat($mesures['th'] ?? null);

        $safetyRef = $cfg['safety']['ref'];
        $cards     = [];

        // ── Gate chloration (audit P1 section 7) ─────────────────────────────
        $phMin  = $cfg['targets']['ph_chloration_min'];
        $phMax  = $cfg['targets']['ph_chloration_max'];
        $tacMin = $cfg['targets']['tac_chloration_min'];

        $phOk  = ($ph === null)  || ($ph >= $phMin  && $ph <= $phMax);
        $tacOk = ($alcalinite === null) || ($alcalinite >= $tacMin);
        $chlorationOk = $phOk && $tacOk;

        // ── 1. TAC ────────────────────────────────────────────────────────────
        if ($alcalinite !== null) {
            $tacLow  = $cfg['targets']['tac_low_trigger'];
            $tacHigh = $cfg['targets']['tac_high_trigger'];

            if ($alcalinite < $tacLow) {
                $stepSize = $cfg['coefficients']['tac_plus_step_size'];
                $gPerStep = $cfg['coefficients']['tac_plus_per_step'];
                $rawSteps = round(($tacLow - $alcalinite) / $stepSize, 9);
                $steps    = max(1, (int) ceil($rawSteps));
                $dose_gm3 = $steps * $gPerStep;
                $doseG    = $volume > 0 ? (int) round($dose_gm3 * $volume) : (int) ($dose_gm3);
                // Arrondi prudent (multiple de 5)
                $doseG    = self::roundCautious($doseG);

                $cards[] = [
                    'param'      => 'Alcalinité (TAC)',
                    'current'    => $mesures['alcalinite'] . ' mg/L (trop bas)',
                    'target'     => '80 — 120 mg/L',
                    'product'    => 'TAC+ (bicarbonate de sodium)',
                    'dose'       => $volume > 0
                        ? "{$doseG} g (~" . (int) $dose_gm3 . ' g/m³)'
                        : (int) $dose_gm3 . ' g/m³',
                    'note'       => 'Verser directement dans le bassin, filtration en marche. Re-tester après 24 h avant toute nouvelle dose. ' . $cfg['safety']['baignade_delay'],
                    'safety_ref' => $safetyRef,
                ];
            }

            if ($alcalinite > $tacHigh) {
                $cards[] = [
                    'param'      => 'Alcalinité (TAC)',
                    'current'    => $mesures['alcalinite'] . ' mg/L (trop haut)',
                    'target'     => '80 — 120 mg/L',
                    'product'    => 'pH- progressif (en plusieurs petites doses)',
                    'dose'       => 'Ajustement progressif — plusieurs jours',
                    'note'       => 'Baisser le pH à 7,0 pendant quelques jours fait redescendre le TAC naturellement. Re-tester chaque jour avant nouvelle dose. Processus lent (plusieurs jours à semaines). ' . $cfg['safety']['epi'],
                    'safety_ref' => $safetyRef,
                ];
            }
        }

        // ── 2. pH ─────────────────────────────────────────────────────────────
        if ($ph !== null) {
            $phLow  = $cfg['targets']['ph_low_trigger'];
            $phHigh = $cfg['targets']['ph_high_trigger'];

            if ($ph < $phLow) {
                $stepSize = $cfg['coefficients']['ph_plus_step_size'];
                $gPerStep = $cfg['coefficients']['ph_plus_per_step'];
                // round() before ceil() to avoid PHP float precision artifacts
                // e.g. (7.2 - 7.0) / 0.1 = 2.0000000000000004 → ceil = 3 without rounding
                $rawSteps = round(($phLow - $ph) / $stepSize, 9);
                $steps    = max(1, (int) ceil($rawSteps));
                $dose_gm3 = $steps * $gPerStep;
                $doseG    = $volume > 0 ? (int) round($dose_gm3 * $volume) : (int) $dose_gm3;
                $doseG    = self::roundCautious($doseG);

                $cards[] = [
                    'param'      => 'pH',
                    'current'    => $mesures['ph'] . ' (trop bas)',
                    'target'     => '7.2 — 7.4',
                    'product'    => 'pH+ (carbonate de soude)',
                    'dose'       => $volume > 0
                        ? "{$doseG} g (~" . (int) $dose_gm3 . ' g/m³)'
                        : (int) $dose_gm3 . ' g/m³',
                    'note'       => 'Diluer dans un seau d\'eau, verser devant les buses, filtration en marche 4 h. Re-tester avant toute nouvelle dose. ' . $cfg['safety']['baignade_delay'],
                    'safety_ref' => $safetyRef,
                ];
            }

            if ($ph > $phHigh) {
                // Audit P1 : supprimer 3.3, garder 3.4 — une seule formule plafonnée
                // Plafond : max 0.2 pH de baisse par application (TAC-governed)
                $capDelta = $cfg['coefficients']['ph_minus_cap_delta'];
                $delta    = min($ph - 7.4, $capDelta); // plafonner à cap
                $gPer01PhPer10m3 = $cfg['coefficients']['ph_minus_g_per_01ph_per_10m3'];

                // dose = (delta / 0.1) * g_per_0.1ph * (volume / 10)
                $doseFull = ($delta / 0.1) * $gPer01PhPer10m3 * ($volume / 10);
                // Arrondi prudent : à la baisse sur acide (audit section 8)
                $doseG = self::roundCautiousDown($doseFull);

                $cards[] = [
                    'param'      => 'pH',
                    'current'    => $mesures['ph'] . ' (trop haut)',
                    'target'     => '7.2 — 7.4',
                    'product'    => 'pH- (bisulfate de sodium)',
                    'dose'       => $volume > 0
                        ? "{$doseG} g pour cette application (plafond " . round($capDelta * 10) / 10 . " pH)"
                        : (int) (($delta / 0.1) * $gPer01PhPer10m3) . ' g par 10 m³',
                    'note'       => 'Une seule dose par application, plafonnée à ' . round($capDelta, 1) . ' unité pH. Re-tester avant toute nouvelle dose — jamais une correction en une seule fois. Diluer dans un seau d\'eau. ' . $cfg['safety']['epi'],
                    'safety_ref' => $safetyRef,
                ];
            }
        }

        // ── 3. Chlore (gate active si pH/TAC hors plage) ─────────────────────
        if ($chlorationOk) {
            if ($chlore !== null) {
                $chloreLow  = $cfg['targets']['chlore_min'];
                $chloreHigh = $cfg['targets']['chlore_max'];

                if ($chlore < $chloreLow) {
                    // P0 correction : rattrapage ~3-4 g/m³ (NOT choc 15 g/m³)
                    $rGm3  = $cfg['coefficients']['chlore_rattrapage_gm3']; // 3.5 g/m³
                    $doseG = $volume > 0 ? self::roundCautiousDown($rGm3 * $volume) : (int) $rGm3;

                    $cards[] = [
                        'param'      => 'Chlore libre',
                        'current'    => $mesures['chlore'] . ' mg/L (trop bas)',
                        'target'     => '1 — 3 mg/L',
                        'product'    => 'Rattrapage chlore — hypochlorite de calcium en poudre (sans stabilisant)',
                        'dose'       => $volume > 0
                            ? "{$doseG} g (~" . round($rGm3, 1) . ' g/m³) — dose rattrapage prudente'
                            : round($rGm3, 1) . ' g/m³',
                        'note'       => 'Dose de rattrapage (pas un choc). Diluer dans un seau d\'eau, verser autour du bassin pompe en marche. Re-tester après 4 h avant toute nouvelle dose. ' . $cfg['safety']['baignade_delay'],
                        'safety_ref' => $safetyRef,
                    ];
                }

                if ($chlore > $chloreHigh) {
                    $cards[] = [
                        'param'      => 'Chlore libre',
                        'current'    => $mesures['chlore'] . ' mg/L (trop haut)',
                        'target'     => '1 — 3 mg/L',
                        'product'    => 'Aucun ajout — laisser baisser naturellement',
                        'dose'       => 'Stopper toute chloration et aérer le bassin',
                        'note'       => 'Attendre 24 à 48 h — le chlore redescend naturellement avec les UV et l\'aération. Re-tester avant baignade. ' . $cfg['safety']['baignade_delay'],
                        'safety_ref' => $safetyRef,
                    ];
                }
            }

            // Odeur forte — point de rupture chloramines (audit P1 section 5)
            if ($odeurForte) {
                if ($chloreTotal !== null && $chlore !== null) {
                    // (a) avec chlore_total : combine = total - libre ; dose = 10× combine
                    $combine  = max(0.0, $chloreTotal - $chlore);
                    $factor   = $cfg['coefficients']['odeur_forte_breakpoint_factor'];
                    // dose en g/m³ = factor × combine (mg/L = g/m³ même unité)
                    $doseGm3  = $factor * $combine;
                    $doseG    = $volume > 0 ? self::roundCautiousDown($doseGm3 * $volume) : (int) $doseGm3;

                    $cards[] = [
                        'param'      => 'Odeur forte / Chloramines',
                        'current'    => 'Chlore combiné estimé : ' . round($combine, 1) . ' mg/L',
                        'target'     => 'Chloramines éliminées (< 0,5 mg/L combiné)',
                        'product'    => 'Choc point de rupture — hypochlorite de calcium en poudre',
                        'dose'       => $volume > 0
                            ? "{$doseG} g (10× le combiné de " . round($combine, 1) . ' mg/L × ' . $volume . ' m³)'
                            : round($doseGm3, 1) . ' g/m³ (10× le combiné)',
                        'note'       => 'L\'odeur forte = chloramines, signe de choc — pas un excès de chlore. Aérer le bassin après traitement. Re-tester avant baignade (chlore libre < 3 mg/L). ' . $cfg['safety']['baignade_delay'],
                        'safety_ref' => $safetyRef,
                    ];
                } else {
                    // (b) sans chlore_total : choc généreux + aération + re-test
                    $chocGm3 = 10.0; // viser ~10-15 ppm libre (milieu prudent)
                    $doseG   = $volume > 0 ? self::roundCautiousDown($chocGm3 * $volume) : (int) $chocGm3;

                    $cards[] = [
                        'param'      => 'Odeur forte / Chloramines',
                        'current'    => 'Chlore combiné non mesuré',
                        'target'     => 'Chloramines éliminées',
                        'product'    => 'Choc généreux — hypochlorite de calcium en poudre',
                        'dose'       => $volume > 0
                            ? "{$doseG} g (choc indicatif ~" . $chocGm3 . ' g/m³)'
                            : $chocGm3 . ' g/m³',
                        'note'       => 'Sans mesure du chlore total, dose indicative. Aérer abondamment. Re-tester après 24 h — fournir le chlore total pour une dose précise au prochain test. ' . $cfg['safety']['baignade_delay'],
                        'safety_ref' => $safetyRef,
                    ];
                }
            }
        }

        // ── 4. Stabilisant ───────────────────────────────────────────────────
        if ($stabilisant !== null) {
            $stabLow    = $cfg['thresholds']['stabilisant_low'];   // < 30 → apport
            $stabHigh   = $cfg['thresholds']['stabilisant_high'];  // > 75 → vidange

            if ($stabilisant < $stabLow) {
                // Audit P1 : cause racine tropicale — apport de stabilisant
                $apportGm3 = $cfg['coefficients']['stabilisant_apport_gm3'];
                $doseG     = $volume > 0 ? self::roundCautious((int) round($apportGm3 * $volume)) : (int) $apportGm3;

                $cards[] = [
                    'param'      => 'Stabilisant',
                    'current'    => $mesures['stabilisant'] . ' mg/L (trop bas)',
                    'target'     => '30 — 50 mg/L',
                    'product'    => 'Apport de stabilisant (acide cyanurique)',
                    'dose'       => $volume > 0
                        ? "{$doseG} g (~" . (int) $apportGm3 . ' g/m³) — apport indicatif'
                        : (int) $apportGm3 . ' g/m³',
                    'note'       => 'En tropical, un stabilisant bas est une cause racine fréquente de perte rapide du chlore (dégradation UV). Verser devant les buses, filtration en marche. Re-tester après 24 h. ' . $cfg['safety']['epi'],
                    'safety_ref' => $safetyRef,
                ];
            } elseif ($stabilisant > $stabHigh) {
                $fracThreshold = $cfg['thresholds']['stabilisant_fraction_threshold'];
                $fraction      = $stabilisant > $fracThreshold
                    ? $cfg['thresholds']['stabilisant_fraction_high']
                    : $cfg['thresholds']['stabilisant_fraction_low'];

                $drainM3 = $volume > 0 ? (int) round($volume * $fraction) : 0;
                $pct     = (int) round($fraction * 100);

                $cards[] = [
                    'param'      => 'Stabilisant',
                    'current'    => $mesures['stabilisant'] . ' mg/L (trop élevé)',
                    'target'     => '30 — 50 mg/L',
                    'product'    => 'Vidange partielle + recomplétion eau du réseau',
                    'dose'       => $drainM3 > 0
                        ? "Vidanger {$drainM3} m³ (~{$pct} % du bassin) puis recompléter à l'eau du réseau"
                        : "Vidanger {$pct} % du bassin puis recompléter",
                    'note'       => 'Le stabilisant ne se dégrade pas : seule la dilution le fait baisser. Utiliser hypochlorite de calcium (sans stabilisant) pour les prochains chocs. Re-tester après recomplétion et rééquilibrage.',
                    'safety_ref' => $safetyRef,
                ];
            }
        }

        // ── 5. Sel ────────────────────────────────────────────────────────────
        if ($hasSel && $selPpm !== null) {
            $selTarget   = $cfg['targets']['sel_target'];
            $selLow      = $cfg['targets']['sel_low_trigger'];
            $selHigh     = $cfg['targets']['sel_high_trigger'];
            $selFracCap  = $cfg['thresholds']['sel_high_fraction_cap'];
            $selOffset   = $cfg['thresholds']['sel_high_offset'];

            if ($selPpm < $selLow) {
                $delta  = $selTarget - $selPpm;
                $kgSel  = $volume > 0 ? (int) round($delta * $volume / 1000) : 0;

                $cards[] = [
                    'param'      => 'Sel',
                    'current'    => $mesures['selPpm'] . ' ppm (trop bas)',
                    'target'     => 'Valeur préconisée par le fabricant (à défaut ~4000 ppm)',
                    'product'    => 'Sel pour piscine (pastilles ou vrac)',
                    'dose'       => $kgSel > 0
                        ? "Ajouter ~{$kgSel} kg de sel (pour viser ~{$selTarget} ppm)"
                        : 'Ajouter du sel pour viser la valeur recommandée par le fabricant (~4000 ppm à défaut)',
                    'note'       => 'Verser le sel directement dans le bassin (pas dans le skimmer), filtration en marche 24 h. Re-tester ensuite — la cible exacte dépend de la cellule (voir notice fabricant).',
                    'safety_ref' => $safetyRef,
                ];
            }

            if ($selPpm > $selHigh) {
                $fraction = min($selFracCap, ($selPpm - $selOffset) / $selPpm);
                $drainM3  = $volume > 0 ? (int) round($volume * $fraction) : 0;
                $pct      = (int) round($fraction * 100);

                $cards[] = [
                    'param'      => 'Sel',
                    'current'    => $mesures['selPpm'] . ' ppm (trop haut)',
                    'target'     => '3000 — 5000 ppm',
                    'product'    => 'Vidange partielle + recomplétion eau du réseau',
                    'dose'       => $drainM3 > 0
                        ? "Vidanger {$drainM3} m³ (~{$pct} %) puis recompléter"
                        : "Vidanger {$pct} % du bassin puis recompléter",
                    'note'       => 'Un taux trop élevé peut endommager l\'électrolyseur et corroder les équipements. Re-tester après recomplétion.',
                    'safety_ref' => $safetyRef,
                ];
            }
        }

        return $cards;
    }

    /**
     * Formule choc chlore pour les feuilles de l'arbre de décision.
     *
     * INCHANGÉ par rapport au mockup/audit (3.1) :
     *   - léger : 15 g/m³ hypochlorite de calcium (~65 %)
     *   - algues : 30 g/m³
     *
     * Audit P1 : si TH > 300 mg/L → basculer sur hypochlorite de SODIUM (eau dure, évite entartrage).
     *
     * @param  float  $volumeM3  Volume du bassin en m³
     * @param  string $type      'leger' (défaut) | 'algues'
     * @param  float|null $th    Dureté calcique (mg/L) — optionnel, pour switch calcium/sodium
     * @return array{'title': string, 'product': string, 'dose': string, 'rule': string}
     */
    public static function chloreChoc(float $volumeM3, string $type = 'leger', ?float $th = null): array
    {
        $cfg = config('diagnostic-formulas');

        $thSwitch = $cfg['thresholds']['th_sodium_switch'];
        $eauDure  = ($th !== null && $th > $thSwitch);

        // Switch calcium → sodium en eau dure (audit P1 section 5 + §10)
        $product = $eauDure
            ? 'Hypochlorite de sodium (eau de Javel concentrée) — eau dure, évite l\'entartrage'
            : 'Hypochlorite de calcium en poudre (~65 %)';

        $gm3 = $type === 'algues'
            ? $cfg['coefficients']['chlore_choc_algues_gm3']
            : $cfg['coefficients']['chlore_choc_leger_gm3'];

        $doseG = (int) round($gm3 * $volumeM3);

        return [
            'title'   => 'Chlore choc — ' . ($type === 'algues' ? 'traitement algues avancées' : 'choc léger'),
            'product' => $product,
            'dose'    => "{$doseG} g de {$product}",
            'rule'    => "Règle : {$gm3} g par m³ — pour {$volumeM3} m³"
                . ($eauDure ? ' (TH élevé → hypochlorite de sodium)' : ' (sans stabilisant ajouté)')
                . '. Note : le dosage dépend du titre du produit (~65 % pour le calcul ci-dessus).',
        ];
    }

    /**
     * Formule pH- unique et plafonnée (audit P1 section 3 : supprimer 3.3, garder 3.4).
     *
     * Vise au maximum cfg.ph_minus_cap_delta pH de baisse par application.
     * Jamais une dose finale unique — toujours re-tester avant la dose suivante.
     * Arrondi à la baisse sur acide (prudent — audit section 8).
     *
     * @param  float       $volumeM3   Volume du bassin en m³
     * @param  float|null  $currentPh  pH actuel (si connu) — plafonné à cap_delta de correction
     * @return array{'title': string, 'dose': string, 'rule': string}
     */
    public static function phMinus(float $volumeM3, ?float $currentPh = null): array
    {
        $cfg     = config('diagnostic-formulas');
        $capDelta = $cfg['coefficients']['ph_minus_cap_delta'];
        $gPer01  = $cfg['coefficients']['ph_minus_g_per_01ph_per_10m3'];

        $delta = ($currentPh !== null && $currentPh > 7.4)
            ? min($currentPh - 7.4, $capDelta)
            : $capDelta;

        $doseG = self::roundCautiousDown(($delta / 0.1) * $gPer01 * ($volumeM3 / 10));

        return [
            'title' => 'pH-',
            'dose'  => "{$doseG} g de pH- (bisulfate de sodium) — application unique plafonnée",
            'rule'  => $currentPh !== null && $currentPh > 7.4
                ? "Pour pH {$currentPh} → correction max " . round($delta, 1) . " pH : {$gPer01} g abaisse 0,1 pH par 10 m³. Re-tester avant dose suivante."
                : "Règle : {$gPer01} g abaisse 0,1 pH par 10 m³ — correction standard plafonnée à " . round($capDelta, 1) . " pH par application.",
        ];
    }

    // ──────────────────────────────────────────────────────────────────────────
    // Helpers privés
    // ──────────────────────────────────────────────────────────────────────────

    /**
     * Parse une valeur float en acceptant la virgule décimale française.
     * Retourne null si la valeur est null ou vide.
     */
    private static function toFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) str_replace(',', '.', (string) $value);
    }

    /**
     * Arrondi prudent vers le multiple de 5 le plus proche (doses en grammes).
     * Jamais 3 chiffres significatifs non arrondis (audit section 8).
     */
    private static function roundCautious(int $grams): int
    {
        return (int) (round($grams / 5) * 5);
    }

    /**
     * Arrondi à la baisse sur acide/chlore (prudent — audit section 8 : "arrondir prudemment").
     * Arrondi vers le multiple de 5 inférieur.
     */
    private static function roundCautiousDown(float $grams): int
    {
        return (int) (floor($grams / 5) * 5);
    }
}
