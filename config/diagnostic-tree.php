<?php

/*
|--------------------------------------------------------------------------
| Arbre de décision — Diagnostic piscine (DIAG-01, D-02, Plan 05-01)
|--------------------------------------------------------------------------
|
| Structure versionnée. Chaque release de Pierre ou correction expert
| incrémente 'version'. La clé 'version' est utilisée dans les tests
| (DecisionTreeTest) et permet de suivre les évolutions.
|
| Sources autoritaires (implémentées verbatim) :
|   - 05-RESEARCH.md — Full Decision Tree Reference (nodes extraits du mockup)
|   - 05-FLOCULANT-BRANCH-SPEC.md — branche eau trouble (supersède D-08)
|   - 05-DIAGNOSTIC-EXPERT-AUDIT.md — corrections P0/P1 pisciniste
|
| INVARIANT DIAG-02 : cet arbre contient du texte statique UNIQUEMENT.
| Aucune formule arithmétique, aucun coefficient ne doit figurer ici.
| Les calculs de doses sont dans app/Services/Diagnostic/DoseEngine.php.
|
| Blocs sécurité systématiques (audit §6 P0) :
|   Chaque feuille avec geste chimique DOIT intégrer :
|   - EPI (gants + lunettes)
|   - Ne jamais mélanger
|   - Produit dans l'eau, jamais l'eau dans le produit
|   - Délai de baignade (chlore < 3 mg/L + pH 7,0-7,6)
|   - Re-tester avant la dose suivante
|
*/

return [

    'version' => 1,

    // ─────────────────────────────────────────────────────────────
    // Nœuds de questions
    // ─────────────────────────────────────────────────────────────
    'questions' => [

        'start' => [
            'id'       => 'start',
            'question' => 'Quel est le problème avec ton eau ?',
            'subtitle' => null,
            'options'  => [
                ['label' => 'Eau verte',                   'emoji' => '🟢', 'value' => 'eau-verte',   'next' => ['kind' => 'question', 'id' => 'green-1']],
                ['label' => 'Eau trouble',                 'emoji' => '⚪', 'value' => 'eau-trouble', 'next' => ['kind' => 'question', 'id' => 'cloudy-1']],
                ['label' => 'Eau marron / colorée',        'emoji' => '🟤', 'value' => 'eau-marron',  'next' => ['kind' => 'question', 'id' => 'brown-1']],
                ['label' => 'Eau claire mais problème',    'emoji' => '💎', 'value' => 'eau-claire',  'next' => ['kind' => 'question', 'id' => 'clear-1']],
                ['label' => 'Problème d\'électrolyseur',   'emoji' => '⚡', 'value' => 'electro',     'next' => ['kind' => 'question', 'id' => 'electro-1']],
                // Audit P1 #11 : entrée électrolyseur accessible sans cocher "sel"
                ['label' => 'J\'ai un électrolyseur au sel et ça ne marche plus', 'emoji' => '🔌', 'value' => 'electro-direct', 'next' => ['kind' => 'question', 'id' => 'electro-1']],
                // Entrée directe eau verte urgence
                ['label' => 'Eau verte opaque — fond invisible', 'emoji' => '🚨', 'value' => 'eau-verte-urgence', 'next' => ['kind' => 'result', 'id' => 'algues-avancees']],
                // Eau boueuse après pluie (depuis brown-1)
                ['label' => 'Eau boueuse après une pluie',  'emoji' => '🌧️', 'value' => 'eau-boueuse', 'next' => ['kind' => 'result', 'id' => 'eau-boueuse']],
            ],
        ],

        // ── Eau verte ────────────────────────────────────────────
        'green-1' => [
            'id'       => 'green-1',
            'question' => 'Vois-tu le fond de la piscine ?',
            'subtitle' => 'Un fond visible ou non oriente le traitement',
            'options'  => [
                ['label' => 'Non, fond invisible',  'emoji' => '🚫', 'value' => 'non', 'next' => ['kind' => 'result', 'id' => 'algues-avancees']],
                ['label' => 'Oui, fond visible',    'emoji' => '✅', 'value' => 'oui', 'next' => ['kind' => 'question', 'id' => 'green-stab']],
            ],
        ],

        // Audit P0 #4 : green-1 doit tester le stabilisant avant de conclure algues
        'green-stab' => [
            'id'       => 'green-stab',
            'question' => 'Connais-tu ton taux de stabilisant (cyanurique) ?',
            'subtitle' => 'Le stabilisant protège le chlore du soleil — très important en tropical',
            'options'  => [
                ['label' => 'Taux élevé (> 80 mg/L ou "trop haut")',   'emoji' => '📈', 'value' => 'haut',   'next' => ['kind' => 'result', 'id' => 'chlore-lock']],
                ['label' => 'Taux bas (< 30 mg/L ou "très bas")',       'emoji' => '📉', 'value' => 'bas',    'next' => ['kind' => 'result', 'id' => 'manque-de-stabilisant']],
                ['label' => 'Normal (30-75 mg/L) ou je ne sais pas',    'emoji' => '➡️', 'value' => 'normal', 'next' => ['kind' => 'question', 'id' => 'auto-green']],
            ],
        ],

        // Nœud conditionnel auto-green : chlore OK + pH > 7,6 → ph-calc ; sinon → algues-installees
        // Géré côté Alpine avec les données du wizard chimique si disponibles
        'auto-green' => [
            'id'       => 'auto-green',
            'question' => 'Ton eau est légèrement verdâtre mais le fond est visible. Ton pH est-il élevé ?',
            'subtitle' => 'Un pH > 7,6 avec du chlore présent indique un déséquilibre pH plutôt que des algues',
            'options'  => [
                ['label' => 'pH au-dessus de 7,6',    'emoji' => '⬆️', 'value' => 'ph-haut', 'next' => ['kind' => 'result', 'id' => 'ph-calc']],
                ['label' => 'pH normal (7,0-7,6)',     'emoji' => '✅', 'value' => 'ph-ok',   'next' => ['kind' => 'result', 'id' => 'algues-installees']],
                ['label' => 'Je ne sais pas',          'emoji' => '❓', 'value' => 'inconnue', 'next' => ['kind' => 'result', 'id' => 'algues-installees']],
            ],
        ],

        // ── Eau trouble ──────────────────────────────────────────
        // Audit P1 #12 : bifurquer eau calcaire avant le sous-arbre floculant
        'cloudy-1' => [
            'id'       => 'cloudy-1',
            'question' => 'À quoi ressemble l\'eau trouble ?',
            'subtitle' => 'Le type de trouble oriente le traitement (calcaire ≠ particules organiques)',
            'options'  => [
                ['label' => 'Laiteuse / blanchâtre même avec filtration en marche', 'emoji' => '🥛', 'value' => 'laiteuse', 'next' => ['kind' => 'question', 'id' => 'cloudy-calcaire']],
                ['label' => 'Particules fines en suspension, filtration fonctionnelle', 'emoji' => '💭', 'value' => 'particules', 'next' => ['kind' => 'question', 'id' => 'cloudy-filtration']],
                ['label' => 'Filtration défectueuse ou arrêtée',                     'emoji' => '⚙️', 'value' => 'filtration', 'next' => ['kind' => 'result', 'id' => 'filtration-insuffisante']],
            ],
        ],

        // Audit P1 : bifurcation eau calcaire
        'cloudy-calcaire' => [
            'id'       => 'cloudy-calcaire',
            'question' => 'L\'eau est-elle laiteuse même après backwash ou remplacement de cartouche ?',
            'subtitle' => 'Une eau laiteuse persistante malgré un filtre propre évoque une précipitation calcaire',
            'options'  => [
                ['label' => 'Oui, toujours laiteuse — pH/TAC élevés',   'emoji' => '⚗️', 'value' => 'calcaire', 'next' => ['kind' => 'result', 'id' => 'eau-calcaire']],
                ['label' => 'Non, c\'est amélioré avec filtration',      'emoji' => '✅', 'value' => 'ok',      'next' => ['kind' => 'question', 'id' => 'cloudy-filtration']],
            ],
        ],

        // Après confirmation que la filtration fonctionne → branche floculant/filtre
        'cloudy-filtration' => [
            'id'       => 'cloudy-filtration',
            'question' => 'La filtration fonctionne-t-elle correctement ?',
            'subtitle' => 'Un filtre qui tourne aide à clarifier ; un filtre défectueux empêche tout traitement',
            'options'  => [
                ['label' => 'Oui, filtration normale',      'emoji' => '✅', 'value' => 'oui', 'next' => ['kind' => 'question', 'id' => 'filter-type']],
                ['label' => 'Non, filtre défectueux',       'emoji' => '❌', 'value' => 'non', 'next' => ['kind' => 'result', 'id' => 'filtration-insuffisante']],
            ],
        ],

        // FLOCULANT-BRANCH-SPEC §2 : type de filtre AVANT toute reco produit
        'filter-type' => [
            'id'       => 'filter-type',
            'question' => 'Quel type de filtre as-tu ?',
            'subtitle' => 'Le traitement dépend entièrement du type de filtre — les méthodes sont incompatibles entre elles',
            'options'  => [
                ['label' => 'Sable',            'emoji' => '🏖️', 'value' => 'sable',      'filter_type' => 'sable',      'next' => ['kind' => 'question', 'id' => 'cloudy-ph-gate']],
                ['label' => 'Verre',            'emoji' => '🔬', 'value' => 'verre',      'filter_type' => 'verre',      'next' => ['kind' => 'question', 'id' => 'cloudy-ph-gate']],
                ['label' => 'Cartouche',        'emoji' => '🔵', 'value' => 'cartouche',  'filter_type' => 'cartouche',  'next' => ['kind' => 'question', 'id' => 'cloudy-ph-gate-cartouche']],
                ['label' => 'Diatomées (DE)',   'emoji' => '🌊', 'value' => 'diatomees',  'filter_type' => 'diatomees',  'next' => ['kind' => 'result', 'id' => 'floculant-diatomees']],
                ['label' => 'Je ne sais pas',   'emoji' => '❓', 'value' => 'inconnu',   'filter_type' => 'inconnu',    'next' => ['kind' => 'question', 'id' => 'cloudy-ph-gate']],
            ],
        ],

        // FLOCULANT-BRANCH-SPEC §4 : pH bloquant pour sable/verre/inconnu
        'cloudy-ph-gate' => [
            'id'       => 'cloudy-ph-gate',
            'question' => 'Ton pH est-il entre 7,0 et 7,4 ?',
            'subtitle' => 'Précondition obligatoire — un pH hors plage réduit fortement l\'efficacité du floculant',
            'options'  => [
                ['label' => 'Oui, pH dans la plage 7,0-7,4', 'emoji' => '✅', 'value' => 'ok',     'next' => ['kind' => 'result', 'id' => 'floculant-sable-ph-ok']],
                ['label' => 'Non, pH hors plage',             'emoji' => '⚠️', 'value' => 'hors',  'next' => ['kind' => 'result', 'id' => 'floculant-sable-ph-ajust']],
                ['label' => 'Je ne sais pas',                 'emoji' => '❓', 'value' => 'nsp',    'next' => ['kind' => 'result', 'id' => 'floculant-sable-ph-ajust']],
            ],
        ],

        // FLOCULANT-BRANCH-SPEC §4 : pH bloquant pour cartouche → clarifiant
        'cloudy-ph-gate-cartouche' => [
            'id'       => 'cloudy-ph-gate-cartouche',
            'question' => 'Ton pH est-il entre 7,0 et 7,4 ?',
            'subtitle' => 'Précondition obligatoire avant tout traitement clarifiant',
            'options'  => [
                ['label' => 'Oui, pH dans la plage 7,0-7,4', 'emoji' => '✅', 'value' => 'ok',    'next' => ['kind' => 'result', 'id' => 'clarifiant-cartouche']],
                ['label' => 'Non, pH hors plage',             'emoji' => '⚠️', 'value' => 'hors', 'next' => ['kind' => 'result', 'id' => 'clarifiant-cartouche-ph-ajust']],
                ['label' => 'Je ne sais pas',                 'emoji' => '❓', 'value' => 'nsp',   'next' => ['kind' => 'result', 'id' => 'clarifiant-cartouche-ph-ajust']],
            ],
        ],

        // ── Eau marron ───────────────────────────────────────────
        'brown-1' => [
            'id'       => 'brown-1',
            'question' => 'Le problème est-il apparu après une pluie ?',
            'subtitle' => null,
            'options'  => [
                ['label' => 'Oui, après une pluie',  'emoji' => '🌧️', 'value' => 'oui', 'next' => ['kind' => 'result', 'id' => 'eau-boueuse']],
                ['label' => 'Non',                   'emoji' => '❌', 'value' => 'non', 'next' => ['kind' => 'question', 'id' => 'brown-2']],
            ],
        ],

        'brown-2' => [
            'id'       => 'brown-2',
            'question' => 'Y a-t-il présence de métaux dans l\'eau ?',
            'subtitle' => 'Taches rouille/noires sur les parois, eau qui change de couleur juste après ajout de chlore',
            'options'  => [
                ['label' => 'Oui, taches ou eau colorée après chlore', 'emoji' => '🔴', 'value' => 'oui', 'next' => ['kind' => 'result', 'id' => 'metaux']],
                ['label' => 'Non',                                     'emoji' => '❌', 'value' => 'non', 'next' => ['kind' => 'result', 'id' => 'pollution-organique']],
            ],
        ],

        // ── Eau claire mais problème ─────────────────────────────
        'clear-1' => [
            'id'       => 'clear-1',
            'question' => 'Quel est le problème exactement ?',
            'subtitle' => null,
            'options'  => [
                ['label' => 'Algues sur les parois (eau claire)',        'emoji' => '🌿', 'value' => 'algues-parois', 'next' => ['kind' => 'result', 'id' => 'algues-parois']],
                ['label' => 'Odeur forte (chlore, œuf pourri)',         'emoji' => '👃', 'value' => 'odeur',         'next' => ['kind' => 'result', 'id' => 'odeur-forte']],
                ['label' => 'Irritation des yeux ou de la peau',        'emoji' => '👁️', 'value' => 'irritation',   'next' => ['kind' => 'result', 'id' => 'irritation-yeux']],
                ['label' => 'Écume persistante',                        'emoji' => '🫧', 'value' => 'ecume',         'next' => ['kind' => 'result', 'id' => 'irritation-yeux']],
            ],
        ],

        // ── Électrolyseur ────────────────────────────────────────
        'electro-1' => [
            'id'       => 'electro-1',
            'question' => 'Que se passe-t-il avec ton électrolyseur ?',
            'subtitle' => null,
            'options'  => [
                ['label' => 'Aucun chlore produit',                     'emoji' => '0️⃣', 'value' => 'no-chlore', 'next' => ['kind' => 'question', 'id' => 'electro-2']],
                ['label' => 'Voyant d\'alarme / message d\'erreur',     'emoji' => '🔴', 'value' => 'alarme',    'next' => ['kind' => 'question', 'id' => 'electro-3']],
                ['label' => 'Manque de débit / alarme flow',            'emoji' => '💧', 'value' => 'debit',     'next' => ['kind' => 'result',   'id' => 'electro-debit']],
                ['label' => 'Cellule à nettoyer / inversion de polarité', 'emoji' => '🧹', 'value' => 'cellule', 'next' => ['kind' => 'question', 'id' => 'electro-4']],
                ['label' => 'Autre problème / inconnue',                'emoji' => '❓', 'value' => 'autre',     'next' => ['kind' => 'question', 'id' => 'electro-4']],
            ],
        ],

        'electro-2' => [
            'id'       => 'electro-2',
            'question' => 'Le taux de sel est-il correct ?',
            'subtitle' => 'Au moins 3000 ppm requis pour que la cellule produise du chlore',
            'options'  => [
                ['label' => 'Oui, sel entre 3000 et 5000 ppm', 'emoji' => '✅', 'value' => 'ok',      'next' => ['kind' => 'question', 'id' => 'electro-4']],
                ['label' => 'Non, sel trop bas (< 3000 ppm)',  'emoji' => '📉', 'value' => 'trop-bas', 'next' => ['kind' => 'result',   'id' => 'electro-sel-bas']],
                ['label' => 'Je ne sais pas',                  'emoji' => '❓', 'value' => 'nsp',      'next' => ['kind' => 'result',   'id' => 'electro-sel-bas']],
            ],
        ],

        'electro-3' => [
            'id'       => 'electro-3',
            'question' => 'Quel message ou alarme ?',
            'subtitle' => null,
            'options'  => [
                ['label' => 'Alarme sel bas',                   'emoji' => '📉', 'value' => 'sel-bas', 'next' => ['kind' => 'result',   'id' => 'electro-sel-bas']],
                ['label' => 'Manque de débit / alarme flow',    'emoji' => '💧', 'value' => 'debit',   'next' => ['kind' => 'result',   'id' => 'electro-debit']],
                ['label' => 'Cellule à inspecter / nettoyer',   'emoji' => '🔍', 'value' => 'cellule', 'next' => ['kind' => 'question', 'id' => 'electro-4']],
                ['label' => 'Autre / message inconnu',          'emoji' => '❓', 'value' => 'autre',   'next' => ['kind' => 'question', 'id' => 'electro-4']],
            ],
        ],

        'electro-4' => [
            'id'       => 'electro-4',
            'question' => 'Inspecte la cellule (électrodes)',
            'subtitle' => 'Coupe l\'alimentation, démonte la cellule, regarde les plaques',
            'options'  => [
                ['label' => 'Plaques couvertes de calcaire blanc',       'emoji' => '⚪', 'value' => 'calcaire', 'next' => ['kind' => 'result', 'id' => 'electro-entartree']],
                ['label' => 'Plaques noircies, rongées ou abîmées',      'emoji' => '⬛', 'value' => 'usee',    'next' => ['kind' => 'result', 'id' => 'electro-usee']],
                ['label' => 'Plaques propres, rien produit (cellule OK)', 'emoji' => '🔌', 'value' => 'panne',   'next' => ['kind' => 'result', 'id' => 'electro-panne']],
            ],
        ],

    ],

    // ─────────────────────────────────────────────────────────────
    // Feuilles de résultats
    // ─────────────────────────────────────────────────────────────
    'results' => [

        // ── Eau verte ────────────────────────────────────────────

        'algues-avancees' => [
            'id'         => 'algues-avancees',
            'diagnostic' => 'Algues avancées',
            'analyse'    => "L'eau verte avec un fond invisible indique une prolifération massive d'algues. Un traitement choc s'impose immédiatement.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                "Équilibrer le pH à 7,0-7,2 avant le choc (condition sine qua non d'efficacité)",
                'Brosser énergiquement les parois, le fond et la ligne d\'eau',
                'Chlore choc à l\'hypochlorite de calcium en poudre (~65 %) : 30 g/m³ — la dose dépend du titre de ton produit, vérifie la notice',
                'Ajouter un anti-algues curatif (polyquaternaire compatible chlore) APRÈS la redescente du chlore',
                'Filtration en continu 24-48 h, nettoyer le filtre régulièrement',
                'Nettoyer le filtre après traitement, vérifier le stabilisant',
                'Re-tester avant toute nouvelle dose',
            ],
            'safety_block' => "EPI : porte des gants et des lunettes. Ne mélange jamais deux produits. Verse le produit dans l'eau, jamais l'inverse. Délai de baignade : attendre que le chlore libre redescende sous 3 mg/L et le pH entre 7,0 et 7,6.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        'algues-installees' => [
            'id'         => 'algues-installees',
            'diagnostic' => 'Algues en cours d\'installation',
            // Audit P2 : reformulé (analyse contradictoire dans le mockup)
            'analyse'    => "Chlore et pH semblent corrects, pourtant les algues s'installent. En Martinique, la vraie cause est souvent un stabilisant trop bas (chlore détruit par les UV) ou trop haut (chlore-lock). Vérifie le stabilisant.",
            'confidence' => 'moyen',
            'safety'     => false,
            'plan'       => [
                'Mesurer le stabilisant (cyanurique) : idéal 30-50 mg/L en tropical',
                'Si stabilisant bas (< 30) : ajouter du stabilisant (acide cyanurique) selon la notice',
                'Si stabilisant élevé (> 75) : vidange partielle nécessaire',
                'Brosser les parois et le fond',
                'Anti-algues préventif après brossage',
                'Vérifier le TAC (80-120 mg/L)',
                'Filtration continue 12 h minimum',
                'Re-tester après 24 h',
            ],
            'safety_block' => null,
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        // Audit P0 #4 : nouvelles feuilles stabilisant
        'chlore-lock' => [
            'id'         => 'chlore-lock',
            'diagnostic' => 'Chlore-lock — Surstabilisation',
            'analyse'    => "Un stabilisant élevé (> 80 mg/L) neutralise progressivement le chlore : l'eau peut verdir malgré un taux de chlore « correct » sur bandelette. C'est le chlore-lock, fréquent en piscine non vidangée depuis plusieurs saisons.",
            'confidence' => 'eleve',
            'safety'     => false,
            'plan'       => [
                'Confirmer le stabilisant > 80 mg/L avec un kit de test précis (pas une bandelette seule)',
                'Vidange partielle obligatoire : entre 30 % (stabilisant 80-100) et 50 % (> 100) du volume',
                'Recompléter à l\'eau du réseau (eau neuve = 0 mg/L de stabilisant)',
                'Rééquilibrer pH, TAC, puis chlore APRÈS la vidange',
                'Utiliser exclusivement de l\'hypochlorite de calcium (sans stabilisant) pour les prochains traitements',
                'Re-tester le stabilisant après 48 h',
            ],
            'safety_block' => null,
            'retest_reminder' => 'Re-teste le stabilisant avant la dose suivante.',
        ],

        'manque-de-stabilisant' => [
            'id'         => 'manque-de-stabilisant',
            'diagnostic' => 'Manque de stabilisant — Cause racine fréquente',
            'analyse'    => "En Martinique, un stabilisant insuffisant (< 30 mg/L) détruit le chlore en quelques heures sous le soleil intense. L'eau reste désinfectée la nuit mais devient vulnérable le jour — les algues s'installent rapidement.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                'Ajouter du stabilisant (acide cyanurique en poudre ou granulés)',
                'Dose orientative : 2-3 kg pour 100 m³ pour gagner ~20-30 mg/L (vérifie la notice de ton produit)',
                'Dissoudre le stabilisant dans un seau d\'eau chaude avant de verser, filtration en marche',
                'Cible : 30-50 mg/L (ne pas dépasser 75 en usage courant)',
                'Patienter 24 h pour homogénéisation, re-tester',
                'Vérifier chlore et pH après stabilisation',
            ],
            'safety_block' => "Verse le produit dissous dans l'eau du bassin, jamais sec directement dans le skimmer. Gants recommandés.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        'ph-calc' => [
            'id'         => 'ph-calc',
            'diagnostic' => 'Rééquilibrage pH — pH trop élevé',
            'analyse'    => "L'eau est légèrement verte alors que le chlore est présent : un pH > 7,6 réduit drastiquement l'efficacité du chlore (jusqu'à 80 % de perte au-dessus de 8). Priorité : descendre le pH avant tout autre traitement.",
            'confidence' => 'eleve',
            'safety'     => true,
            // Audit P1 §3 pH- : une seule formule, incrémentale, plafonnée
            'plan'       => [
                'Mesurer le pH précisément (kit liquide, pas bandelette seule)',
                'Ajouter du pH- (bisulfate de sodium) en petites doses incrémentales',
                'Ne jamais viser une correction de plus de 0,2 pH à la fois',
                'Filtration en marche au moins 4 h après chaque dose',
                'Re-tester avant la dose suivante — ne pas estimer une dose finale unique',
                'Cible : pH 7,2-7,4',
                'Une fois le pH équilibré, vérifier et ajuster le chlore',
            ],
            'safety_block' => "EPI : gants et lunettes obligatoires pour manipuler le pH-. Diluer dans un seau d'eau avant de verser dans le bassin. Produit dans l'eau, jamais l'inverse. Délai de baignade : chlore < 3 mg/L + pH 7,0-7,6.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        // ── Eau trouble — Branche floculant (FLOCULANT-BRANCH-SPEC) ─────────

        // Méthode A — Sable/verre, pH OK (FLOCULANT-BRANCH-SPEC §3)
        'floculant-sable-ph-ok' => [
            'id'         => 'floculant-sable-ph-ok',
            'diagnostic' => 'Eau trouble — Floculant choc (filtre sable / verre)',
            'analyse'    => "Filtre sable ou verre + pH dans la plage : le floculant choc peut décanter les particules fines efficacement.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                // FLOCULANT-BRANCH-SPEC §3 Méthode A
                '1. Vérifier pH entre 7,0 et 7,4 (précondition bloquante)',
                '2. Verser le floculant liquide : 1 à 2,5 L pour 100 m³ selon la turbidité (suivre la notice du produit — concentrations très variables)',
                '3. Filtration EN MARCHE 30 min à 1 h pour disperser le floculant',
                '4. ARRÊTER la filtration complètement — décantation 8 à 24 h (une nuit entière recommandée). Toute remise en route prématurée annule la décantation.',
                '5. Aspirer lentement le dépôt au fond, vanne en position ÉGOUT, vitesse basse, sans remuer',
                '6. Appoint d\'eau, rééquilibrage (pH, désinfectant, sel si électrolyseur)',
                '7. Lavage à contre-courant (backwash) + rinçage du filtre',
                'Si piscine au sel : l\'aspiration à l\'égout fait perdre eau et sel — prévoir appoint et réajustement de la salinité après remplissage',
                'Re-tester après 48 h',
            ],
            'safety_block' => "EPI : gants et lunettes lors de la manipulation du floculant liquide. Produit dans l'eau, jamais l'inverse. Délai de baignade : chlore < 3 mg/L + pH 7,0-7,6.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
            'methods' => [
                'sable' => 'floculant-choc',
                'verre' => 'floculant-choc',
            ],
        ],

        // Méthode A — Sable/verre, pH hors plage → ajuster pH d'abord
        'floculant-sable-ph-ajust' => [
            'id'         => 'floculant-sable-ph-ajust',
            'diagnostic' => 'Eau trouble — Ajuster le pH avant le floculant (filtre sable / verre)',
            'analyse'    => "Précondition bloquante : le pH doit être entre 7,0 et 7,4 avant tout floculant. Un pH hors plage réduit fortement l'efficacité et peut rendre le dépôt difficile à aspirer.",
            'confidence' => 'moyen',
            'safety'     => true,
            'plan'       => [
                '1. Mesurer le pH avec un kit précis',
                '2. Si pH > 7,4 : ajouter du pH- par petites doses incrémentales (0,2 pH à la fois max), filtration 4 h, re-tester',
                '3. Si pH < 7,0 : ajouter du pH+ (carbonate de soude), filtration 2 h, re-tester',
                '4. Une fois pH entre 7,0 et 7,4 : appliquer le protocole floculant choc (décantation sable/verre)',
                '5. Verser le floculant liquide : 1 à 2,5 L pour 100 m³',
                '6. Filtration 30 min-1 h, puis ARRÊT complet — décantation 8-24 h',
                '7. Aspiration lente vanne en ÉGOUT, backwash, rééquilibrage',
                'Re-tester après 48 h',
            ],
            'safety_block' => "EPI : gants et lunettes pour pH- et floculant. Ne jamais mélanger des produits entre eux. Produit dans l'eau, jamais l'inverse.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        // Méthode B — Cartouche (FLOCULANT-BRANCH-SPEC §3 Méthode B)
        // INVARIANT : le mot "floculant" ne doit JAMAIS apparaître dans cette feuille
        'clarifiant-cartouche' => [
            'id'         => 'clarifiant-cartouche',
            'diagnostic' => 'Eau trouble — Clarifiant (filtre cartouche)',
            'analyse'    => "Filtre cartouche : le clarifiant est la seule méthode compatible. Les produits de décantation sont incompatibles avec ce type de filtre — ils colmatent les plis et risquent d'endommager la cartouche.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                // FLOCULANT-BRANCH-SPEC §3 Méthode B — le mot "floculant" est INTERDIT ici
                '1. Vérifier pH entre 7,0 et 7,4',
                '2. Verser le clarifiant (dose selon la notice — type cartouche recommandé)',
                '3. Filtration EN CONTINU 24 à 72 h',
                '4. Nettoyer la cartouche quand la pression monte : retirer et rincer au jet, ou remplacer si usée',
                '5. Répéter si nécessaire après nettoyage de la cartouche',
                'Re-tester après 48 h',
            ],
            'safety_block' => "EPI : gants. Produit dans l'eau, jamais l'inverse. Délai de baignade : chlore < 3 mg/L + pH 7,0-7,6.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
            'methods' => [
                'cartouche' => 'clarifiant',
            ],
        ],

        // Méthode B — Cartouche, pH hors plage
        'clarifiant-cartouche-ph-ajust' => [
            'id'         => 'clarifiant-cartouche-ph-ajust',
            'diagnostic' => 'Eau trouble — Ajuster le pH avant le clarifiant (filtre cartouche)',
            'analyse'    => "Le pH doit être entre 7,0 et 7,4 avant tout traitement clarifiant. Corrige le pH d'abord.",
            'confidence' => 'moyen',
            'safety'     => true,
            'plan'       => [
                '1. Mesurer le pH avec un kit précis',
                '2. Corriger le pH vers 7,0-7,4 (pH+ ou pH- par petites doses)',
                '3. Filtration 2-4 h après chaque ajout, re-tester',
                '4. Une fois pH équilibré : appliquer le protocole clarifiant cartouche',
                '5. Verser le clarifiant, filtration continue 24-72 h',
                '6. Nettoyer la cartouche quand la pression monte',
            ],
            'safety_block' => "EPI : gants et lunettes. Ne jamais mélanger des produits. Produit dans l'eau, jamais l'inverse.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        // Méthode C — Diatomées (FLOCULANT-BRANCH-SPEC §3 Méthode C)
        'floculant-diatomees' => [
            'id'         => 'floculant-diatomees',
            'diagnostic' => 'Eau trouble — Nettoyage filtre + clarifiant (diatomées)',
            'analyse'    => "Filtre à diatomées : nettoyer le filtre en premier (capte déjà la majorité des fines). Si insuffisant, un clarifiant en seconde intention.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                // FLOCULANT-BRANCH-SPEC §3 Méthode C
                '1. Backwash du filtre DE + recharge de diatomées, ou nettoyage manuel selon le modèle',
                '2. Vérifier le pH entre 7,0 et 7,4',
                '3. Si eau toujours trouble après nettoyage filtre : ajouter un clarifiant + filtration continue',
                '4. Ne pas utiliser de produit de décantation avec un filtre diatomées — risque de colmatage rapide',
                'Re-tester après 48 h',
            ],
            'safety_block' => "EPI : gants. Produit dans l'eau, jamais l'inverse.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
            'methods' => [
                'diatomees' => 'nettoyage+clarifiant',
            ],
        ],

        // ── Eau trouble — Eau calcaire (Audit P1 #12) ────────────
        'eau-calcaire' => [
            'id'         => 'eau-calcaire',
            'diagnostic' => 'Eau laiteuse — Précipitation calcaire',
            'analyse'    => "Une eau laiteuse/blanchâtre persistante avec un pH ou TAC élevés indique une précipitation calcaire (les sels de calcium précipitent). Le clarifiant ou la décantation n'aideront pas — il faut corriger le pH/TAC et utiliser un séquestrant calcaire.",
            'confidence' => 'moyen',
            'safety'     => true,
            'plan'       => [
                'Mesurer pH, TAC et idéalement le TH (dureté calcique)',
                'Baisser le pH à 7,0-7,2 progressivement avec du pH- (0,2 pH à la fois max, re-tester)',
                'Si TAC > 120 mg/L : baisser avec du pH- progressif (quelques jours, re-test quotidien)',
                'Ajouter un séquestrant calcaire (anti-tartre, anti-calcaire pour piscine) selon la dose de la notice',
                'Filtration en continu pendant le traitement',
                'Éviter tout produit de décantation sur eau calcaire (aggrave la précipitation)',
                'Re-tester après 48 h',
            ],
            'safety_block' => "EPI : gants et lunettes pour le pH-. Produit dans l'eau, jamais l'inverse. Ne jamais mélanger des produits.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        // ── Eau trouble — Filtration insuffisante ────────────────
        'filtration-insuffisante' => [
            'id'         => 'filtration-insuffisante',
            'diagnostic' => 'Filtration insuffisante',
            'analyse'    => "L'eau reste trouble car la filtration ne fonctionne pas correctement. Aucun produit chimique ne remplace une filtration efficace.",
            'confidence' => 'eleve',
            'safety'     => false,
            'plan'       => [
                'Vérifier l\'état du filtre (manomètre, pression de service)',
                'Nettoyer le filtre selon le type : sable/verre = backwash + rinçage ; cartouche = retirer et rincer ; diatomées = backwash + recharge',
                'Vérifier la pompe et les paniers (skimmer + pompe)',
                'Augmenter le temps de filtration quotidien (règle : heures = température / 2, mini 8 h)',
                'Contrôler le débit après remise en route',
                'Si la pompe ne démarre pas ou tourne mal : contacter Dlo Azur',
            ],
            'safety_block' => null,
            'retest_reminder' => 'Re-teste après 24 h de filtration.',
        ],

        // ── Eau marron ───────────────────────────────────────────

        'eau-boueuse' => [
            'id'         => 'eau-boueuse',
            'diagnostic' => 'Eau boueuse après pluie ou apport extérieur',
            // Audit P0 #5 : réordonner — choc chlore AVANT clarification, floculant EN DERNIER
            'analyse'    => "Les pluies tropicales et la brume de sable chargent la piscine en matières organiques et suspensions. L'ordre de traitement est impératif : désinfection d'abord, clarification ensuite.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                'Aspirer les gros dépôts au fond (sable, feuilles) à l\'égout si possible',
                'Brosser les parois et le fond pour décoller les dépôts',
                'Chlore choc : 15-30 g/m³ d\'hypochlorite de calcium (~65 %) selon la turbidité — dose dépend du titre du produit, vérifie la notice',
                'Rééquilibrer pH (7,0-7,4) après le choc',
                'Laisser le chlore redescendre sous 5 mg/L avant la clarification (le chlore élevé gêne la floculation)',
                'ENSUITE (filtre sable/verre) : appliquer le protocole clarifiant/décantation selon ton type de filtre',
                'ENSUITE (filtre cartouche) : clarifiant + filtration continue, nettoyer la cartouche',
                'Filtration en continu 24 h, nettoyer le filtre après traitement',
                'Appoint d\'eau + rééquilibrage sel si électrolyseur',
                'Re-tester après 48 h',
            ],
            'safety_block' => "EPI : gants et lunettes. Ne mélange jamais deux produits. Produit dans l'eau, jamais l'inverse. Délai de baignade : chlore < 3 mg/L + pH 7,0-7,6.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        'metaux' => [
            'id'         => 'metaux',
            'diagnostic' => 'Présence de métaux (fer, cuivre, manganèse)',
            // Audit P1 : ne pas choquer avant séquestration
            'analyse'    => "Les taches rouille ou la décoloration après ajout de chlore indiquent la présence de métaux. Ne jamais choquer avant d'avoir séquestré les métaux — sinon les taches se fixent définitivement sur les parois.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                'Mesurer pH à 7,2-7,4 (éviter le choc en dehors de cette plage)',
                'Ajouter un séquestrant de métaux en premier (avant tout chlore supplémentaire)',
                'Filtration 24 h après le séquestrant',
                'Filtrer intensément, nettoyer le filtre',
                'Ajuster le pH, TAC, chlore ensuite',
                'Faire analyser l\'eau de remplissage (source des métaux peut être le réseau ou les équipements)',
                'Re-tester après 48 h',
            ],
            'safety_block' => "EPI : gants. Ne jamais mélanger des produits. Produit dans l'eau, jamais l'inverse.",
            'retest_reminder' => 'Re-teste après traitement.',
        ],

        'pollution-organique' => [
            'id'         => 'pollution-organique',
            'diagnostic' => 'Pollution organique',
            // Audit P0 #5 : ordre comme eau-boueuse, floculant en dernier
            'analyse'    => "Pollution organique (matières végétales, crème solaire, etc.) — même ordre impératif que l'eau boueuse : choc désinfection en premier, clarification en dernier.",
            'confidence' => 'moyen',
            'safety'     => true,
            'plan'       => [
                'Nettoyage mécanique complet (brossage parois, aspiration fond)',
                'Chlore choc : 15 g/m³ d\'hypochlorite de calcium (~65 %)',
                'Rééquilibrer pH',
                'Attendre que le chlore redescende sous 5 mg/L',
                'ENSUITE : clarification selon ton type de filtre (sable/verre = décantation, cartouche = clarifiant seul)',
                'Filtration 24 h, nettoyer le filtre',
                'Re-tester après 48 h',
            ],
            'safety_block' => "EPI : gants et lunettes. Ne mélange jamais deux produits. Produit dans l'eau, jamais l'inverse. Délai de baignade : chlore < 3 mg/L + pH 7,0-7,6.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        // ── Eau claire mais problème ─────────────────────────────

        'algues-parois' => [
            'id'         => 'algues-parois',
            'diagnostic' => 'Algues fixées sur les parois',
            'analyse'    => "Les algues sur les parois (eau claire) indiquent un taux de désinfectant insuffisant ou des zones mortes sans circulation. En tropical, le stabilisant est souvent la cause racine.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                'Brosser soigneusement toutes les parois (les algues doivent être en suspension pour être traitées)',
                'Vérifier le stabilisant (idéal 30-50 mg/L en Martinique)',
                // Audit P2 : indexer la cible chlore sur le stabilisant
                'Maintenir le chlore libre au niveau adapté au stabilisant : environ 5-7,5 % du taux de stabilisant (ex : stab 40 → chlore cible ~2-3 mg/L)',
                'Anti-algues préventif après brossage',
                'Vérifier le pH (7,2-7,4)',
                'Zones mortes : améliorer la circulation (buses orientées vers le fond)',
                'Re-tester après 24 h',
            ],
            'safety_block' => "EPI : gants. Produit dans l'eau, jamais l'inverse.",
            'retest_reminder' => 'Re-teste après 24 h.',
        ],

        'odeur-forte' => [
            'id'         => 'odeur-forte',
            'diagnostic' => 'Odeur forte de chlore — Chloramines',
            // Audit P1 #9 : odeur = chloramines, point de rupture
            'analyse'    => "Paradoxe : une forte odeur de chlore signale souvent un manque de chlore actif, pas un excès. Les chloramines (chlore combiné) se forment en oxydant les matières organiques (sueur, urine, crèmes) — elles piquent les yeux et dégagent l'odeur caractéristique. La solution : briser le point de rupture (break-point chlorination).",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                'Si tu as mesuré le chlore total et le chlore libre : combiné = total - libre',
                'Dose de break-point : 10 fois le chlore combiné mesuré (ex : combiné = 1 mg/L → viser ~10 mg/L de chlore libre)',
                'Si tu n\'as pas le combiné : choc généreux visant 10-15 mg/L de chlore libre avec de l\'hypochlorite de calcium',
                'Aération maximale du bassin (fontaine, cascade, bâches retirées)',
                'Filtration continue 24 h',
                'Attendre la redescente naturelle du chlore avant baignade (peut prendre 48-72 h selon l\'ensoleillement)',
                'Re-tester chlore et pH avant le retour à l\'eau',
            ],
            'safety_block' => "EPI : gants et lunettes. Ne mélange jamais deux produits. Produit dans l'eau, jamais l'inverse. Délai de baignade : attendre chlore libre < 3 mg/L + pH 7,0-7,6 — ce traitement génère un taux très élevé temporairement.",
            'retest_reminder' => 'Re-teste avant la dose suivante.',
        ],

        'irritation-yeux' => [
            'id'         => 'irritation-yeux',
            'diagnostic' => 'Irritation des yeux / de la peau',
            'analyse'    => "L'irritation des yeux est causée par un déséquilibre pH (hors 7,0-7,6) ou par les chloramines (même mécanisme que l'odeur forte). Vérifier les deux paramètres en priorité.",
            'confidence' => 'moyen',
            'safety'     => false,
            'plan'       => [
                'Mesurer le pH en priorité : doit être entre 7,0 et 7,6',
                'Si pH hors plage : ajuster avec pH+ ou pH- (en doses incrémentales, filtration entre chaque)',
                'Mesurer le chlore libre et total (si possible)',
                'Si chlore combiné élevé (odeur + irritation) : traitement choc break-point (voir plan odeur forte)',
                'Filtration continue 24 h',
                'Re-tester avant le retour à l\'eau',
            ],
            'safety_block' => null,
            'retest_reminder' => 'Re-teste avant le retour à l\'eau.',
        ],

        // ── Électrolyseur ────────────────────────────────────────

        'electro-debit' => [
            'id'         => 'electro-debit',
            'diagnostic' => 'Défaut de débit — Alarme flow',
            'analyse'    => "L'électrolyseur coupe sa production si le débit est insuffisant (sécurité intégrée). Vérifier le circuit hydraulique en entier.",
            'confidence' => 'eleve',
            'safety'     => false,
            'plan'       => [
                'Vérifier que la pompe tourne et que les vannes sont ouvertes',
                'Nettoyer les paniers (skimmer + pompe)',
                'Backwash + rinçage du filtre',
                'Contrôler le détecteur de débit (flow switch) — à démonter et nettoyer si accessible',
                'Vérifier la présence d\'air dans le circuit (pompe qui aspire de l\'air)',
                'Si tout est OK : le capteur de débit est peut-être défectueux — contacter Dlo Azur pour diagnostic',
            ],
            'safety_block' => null,
            'retest_reminder' => null,
        ],

        'electro-entartree' => [
            'id'         => 'electro-entartree',
            'diagnostic' => 'Cellule entartrée',
            // Audit P0 #2 : vinaigre blanc en premier, acide chlorhydrique en opt-in avec EPI renforcés
            'analyse'    => "Le calcaire se dépose sur les plaques de la cellule et réduit la production de chlore. Commencer par du vinaigre blanc (sécurisé). L'acide chlorhydrique n'est à utiliser qu'en dernier recours, avec des EPI complets, en extérieur.",
            'confidence' => 'eleve',
            'safety'     => true,
            'plan'       => [
                'Couper l\'alimentation électrique de l\'électrolyseur et arrêter la pompe',
                'Fermer les vannes de part et d\'autre de la cellule, démonter délicatement',
                '--- Méthode 1 : Vinaigre blanc (à essayer en premier) ---',
                'Plonger la cellule dans du vinaigre blanc pur pendant 30 à 60 min',
                'Rincer abondamment à l\'eau claire, remonter et tester',
                '--- Méthode 2 : Acide chlorhydrique (si vinaigre insuffisant — EPI renforcés obligatoires) ---',
                'EN EXTÉRIEUR VENTILÉ UNIQUEMENT — jamais en intérieur',
                'EPI : gants chimiques résistants aux acides, lunettes de protection hermétiques, vieux vêtements',
                'Mélange : 1 volume d\'acide + 9 volumes d\'eau (toujours acide dans l\'eau, jamais l\'inverse)',
                'Trempage limité à 5-10 min max (l\'acide érode le revêtement et raccourcit la vie de la cellule)',
                'Ne nettoyer que si entartrage visible — pas de nettoyage préventif à l\'acide',
                'Rincer abondamment, jamais d\'outil métallique sur les plaques',
                'En cas de doute : arrête et contacte Dlo Azur',
                'Pour éviter récidive : maintenir pH 7,2-7,4, vérifier l\'inversion de polarité automatique',
            ],
            'safety_block' => "SÉCURITÉ : gants chimiques + lunettes hermétiques pour toute manipulation d'acide. En extérieur ventilé UNIQUEMENT. Acide dans l'eau, JAMAIS l'inverse. Ne jamais mélanger l'acide chlorhydrique avec d'autres produits — dégagement de chlore gazeux toxique.",
            'retest_reminder' => null,
            'hard_stop_if_acid' => true,
        ],

        'electro-usee' => [
            'id'         => 'electro-usee',
            'diagnostic' => 'Électrodes usées',
            'analyse'    => "Les plaques de la cellule sont abîmées, noircies ou rongées : la cellule ne peut plus produire de chlore correctement. Remplacement nécessaire.",
            'confidence' => 'eleve',
            'safety'     => false,
            'plan'       => [
                'Relever la référence exacte de la cellule (modèle + référence fabricant)',
                'Commander une cellule compatible (durée de vie typique : 3-7 ans selon l\'entretien)',
                'En attendant le remplacement : chloration manuelle avec de l\'hypochlorite de calcium',
                'Instructions de remplacement : couper l\'alimentation + pompe, fermer les vannes, démonter, monter la nouvelle cellule en vérifiant le joint',
                'Si hésitation sur le montage ou le câblage : contacter Dlo Azur',
            ],
            'safety_block' => null,
            'retest_reminder' => null,
        ],

        'electro-panne' => [
            'id'         => 'electro-panne',
            'diagnostic' => 'Panne boîtier électrolyseur',
            'analyse'    => "La cellule semble propre et fonctionnelle, mais l'électrolyseur ne produit pas. Le problème vient du coffret électronique (alimentation, carte de contrôle).",
            'confidence' => 'moyen',
            'safety'     => false,
            'plan'       => [
                'Vérifier l\'alimentation électrique du coffret (disjoncteur, câbles)',
                'Tester avec une autre cellule si tu en as une disponible',
                'Faire diagnostiquer le coffret par un professionnel (électronique, 230 V — hors manipulation particulier)',
                'En attendant : chloration manuelle avec de l\'hypochlorite de calcium (~3-4 g/m³ pour rattrapage, 15-30 g/m³ pour choc)',
                'Contacter Dlo Azur pour intervention',
            ],
            'safety_block' => null,
            'retest_reminder' => null,
        ],

        'electro-sel-bas' => [
            'id'         => 'electro-sel-bas',
            'diagnostic' => 'Taux de sel insuffisant (< 3000 ppm)',
            'analyse'    => "L'électrolyseur ne peut pas produire de chlore sans sel suffisant. Cible typique : 4000 ppm (vérifier la valeur recommandée par le fabricant de ton électrolyseur).",
            'confidence' => 'eleve',
            'safety'     => false,
            'plan'       => [
                'Mesurer le taux de sel avec un testeur numérique ou un kit liquide (bandelette imprécise)',
                'Ajouter du sel pour piscine (chlorure de sodium en pastilles ou en granulés)',
                'Dose orientative : pour gagner 1000 ppm sur 50 m³, ajouter environ 50 kg (vérifie sur la notice de ton sel)',
                'Viser la valeur recommandée par le fabricant de ton électrolyseur — à défaut 4000 ppm',
                'Verser le sel directement dans le bassin (pas dans le skimmer), filtration en marche 24 h',
                'Re-tester ensuite — le sel se dissout lentement (24-48 h pour homogénéisation)',
            ],
            'safety_block' => null,
            'retest_reminder' => 'Re-teste le sel après 24 h.',
        ],

    ],

];
