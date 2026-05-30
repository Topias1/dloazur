<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Formules et seuils chimiques — Diagnostic piscine (DIAG-02 — D-02)
    |--------------------------------------------------------------------------
    |
    | SURFACE DE REVUE UNIQUE pour la validation chimique de Pierre (pré-lancement).
    | Toute modification de seuil ou de coefficient DOIT être validée par Pierre ADAM
    | avant la mise en production. Ce fichier lie DIAG-02 à D-02 (moteur serveur,
    | formules jamais dans le JS client).
    |
    | Version : 1
    | Audit : 05-DIAGNOSTIC-EXPERT-AUDIT.md (corrections P0/P1 appliquées)
    | Validé par : Pierre ADAM (validation pré-lancement — cf. DIAG-02 gate)
    |
    */

    'version' => 1,

    /*
    |--------------------------------------------------------------------------
    | Plages cibles (valeurs normales)
    |--------------------------------------------------------------------------
    */
    'targets' => [
        'ph_min'         => 7.2,    // pH cible minimum
        'ph_max'         => 7.4,    // pH cible maximum
        'ph_low_trigger' => 7.2,    // seuil déclenchant pH+
        'ph_high_trigger' => 7.6,   // seuil déclenchant pH-

        'tac_min'         => 80,    // TAC (alcalinité) cible min — mg/L
        'tac_max'         => 120,   // TAC cible max — mg/L
        'tac_low_trigger' => 80,    // seuil déclenchant TAC+
        'tac_high_trigger' => 120,  // seuil déclenchant TAC-

        // Gate chloration : si pH ou TAC en-dehors de ces limites, chloration bloquée
        'ph_chloration_min'  => 7.0,   // pH minimum pour autoriser la chloration
        'ph_chloration_max'  => 7.6,   // pH maximum pour autoriser la chloration
        'tac_chloration_min' => 60,    // TAC minimum pour autoriser la chloration

        'chlore_min'     => 1.0,    // chlore libre cible min — mg/L
        'chlore_max'     => 3.0,    // chlore libre cible max — mg/L

        'stabilisant_min' => 30,    // stabilisant cible min — mg/L
        'stabilisant_max' => 50,    // stabilisant cible max — mg/L

        'sel_target'      => 4000,  // sel cible par défaut — ppm (valeur fabricant à défaut)
        'sel_low_trigger' => 3000,  // seuil sel trop bas — ppm
        'sel_high_trigger' => 6000, // seuil sel trop haut — ppm
    ],

    /*
    |--------------------------------------------------------------------------
    | Seuils d'alerte spéciaux
    |--------------------------------------------------------------------------
    */
    'thresholds' => [
        // Stabilisant
        'stabilisant_low'  => 30,   // < 30 mg/L → apport de stabilisant (cause racine tropicale, audit P1)
        'stabilisant_high' => 75,   // > 75 mg/L → vidange partielle
        'stabilisant_chlorelock' => 80, // > 80 mg/L → risque chlore-lock (arbre décision)

        // Vidange partielle stabilisant
        'stabilisant_fraction_low'  => 0.33,    // fraction vidange si 75 < stab <= 100
        'stabilisant_fraction_high' => 0.50,    // fraction vidange si stab > 100
        'stabilisant_fraction_threshold' => 100, // seuil pour passer de 33% à 50%

        // TH — dureté calcique : switch hypochlorite calcium → sodium (audit P1)
        'th_sodium_switch' => 300,   // TH > 300 mg/L → utiliser hypochlorite de sodium (pas calcium)

        // Sel
        'sel_high_fraction_cap' => 0.50,         // vidange sel max 50% du bassin
        'sel_high_offset'       => 4500,          // offset pour le calcul de fraction vidange sel
    ],

    /*
    |--------------------------------------------------------------------------
    | Coefficients de dosage
    |--------------------------------------------------------------------------
    |
    | Unités : g/m³ par unité de mesure sauf indication contraire.
    | Arrondir prudemment (à la baisse sur acide et chlore — audit section 8).
    |
    */
    'coefficients' => [
        // pH+ (carbonate de soude) — formule INCHANGÉE (audit "3.2 OK")
        // Pas de modification par rapport au mockup/audit
        // Formula: steps = ceil((7.2 - pH) / 0.1); dose = steps * ph_plus_per_step * volume
        'ph_plus_step_size' => 0.1,          // pH unité par step
        'ph_plus_per_step'  => 3.0,          // g/m³ par step (= 30 g/m³ pour 1 unité de pH)

        // pH- (bisulfate de sodium) — formule CORRIGÉE (audit P1 : une seule formule plafonnée)
        // Plafonner à max 0.2 pH de baisse par application (TAC-governed, jamais dose finale)
        // Formula: correction plafonnée à ph_minus_cap_delta → 100 g par 0.1 pH pour 10 m³
        'ph_minus_g_per_01ph_per_10m3' => 100.0,   // g pour baisser 0.1 pH dans 10 m³
        'ph_minus_cap_delta' => 0.2,                 // baisse max par application (prudent)

        // TAC+ (bicarbonate de sodium) — INCHANGÉ (audit "3.5 exact")
        // Formula: steps = ceil((80 - TAC) / tac_plus_step_size); dose = steps * 18 * volume
        'tac_plus_step_size' => 20.0,        // ppm par step
        'tac_plus_per_step'  => 18.0,        // g/m³ par step (18 g/m³ par 10 ppm manquant)

        // Chlore rattrapage — CORRECTION P0 (séparé du choc)
        // "~3-4 g/m³ pour remonter ~2 ppm vers la cible, suivi d'un re-test"
        'chlore_rattrapage_gm3' => 3.5,      // g/m³ (milieu de fourchette 3-4, arrondi prudent)

        // Chlore choc léger — INCHANGÉ (audit "3.1 OK")
        'chlore_choc_leger_gm3'  => 15.0,   // g/m³ hypochlorite de calcium (~65 %)
        'chlore_choc_algues_gm3' => 30.0,   // g/m³ pour algues avancées

        // Odeur forte — point de rupture chloramines (audit P1)
        'odeur_forte_breakpoint_factor' => 10.0,  // dose = 10 × combiné (mg/L) × volume (m³)

        // Stabilisant bas — apport indicatif
        'stabilisant_apport_gm3' => 40.0,   // g/m³ pour apport stabilisant (valeur de départ)
    ],

    /*
    |--------------------------------------------------------------------------
    | Bloc sécurité systématique (audit P0 section 6)
    |--------------------------------------------------------------------------
    |
    | Référence affichée sur chaque card chimique.
    | Ne jamais supprimer cette référence sur un produit chimique.
    |
    */
    'safety' => [
        'ref' => 'EPI : gants + lunettes. Ne jamais mélanger les produits (chlore + acide = gaz toxique). Diluer dans un seau d\'eau avant de verser. Délai de baignade : chlore libre < 3 mg/L et pH 7,0–7,6.',
        'epi' => 'Gants + lunettes obligatoires lors de toute manipulation de produit chimique.',
        'mixing_warning' => 'Ne jamais mélanger les produits, en particulier chlore et acide (production de chlore gazeux toxique).',
        'baignade_delay' => 'Délai de baignade : chlore libre < 3 mg/L et pH entre 7,0 et 7,6 avant retour à l\'eau.',
    ],
];
