<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Diagnostic;
use App\Models\Facture;
use App\Models\Piscine;
use App\Models\Produit;
use App\Models\Signature;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Dev/test fixtures only — never run in production (see DatabaseSeeder env gate).
 *
 * Builds a believable demo dataset so the pro dashboard, client portal and
 * diagnostic screens look populated:
 *   - 10 clients, each with >=1 piscine (every 3rd client gets a 2nd basin)
 *   - 1 contrat per client (ponctuel / forfait_mensuel / forfait_saisonnier)
 *   - 6 diagnostics (3 wizard attachés + 3 leads anonymes)
 *   - 4–8 passages per piscine over ~6 months, across draft/signed/synced
 *   - a Signature on every closed (signed/synced) passage
 *   - ~8 factures tied to forfait contrats, across brouillon/envoyee/payee
 *
 * All French copy uses le vouvoiement. Opérateur = Pierre ADAM.
 */
class DevDataSeeder extends Seeder
{
    /** Recommandations de diagnostic (vouvoiement). */
    private const RECOMMANDATIONS = [
        'Ajustez le pH avec un correcteur pour le ramener entre 7,0 et 7,4.',
        'Effectuez un traitement choc puis brossez les parois du bassin.',
        'Vérifiez le taux de stabilisant avant la prochaine intervention.',
        'Nettoyez le panier du skimmer et le préfiltre de la pompe.',
        'Augmentez la durée de filtration pendant les fortes chaleurs.',
        'Contrôlez l\'alcalinité (TAC) pour stabiliser le pH dans la durée.',
    ];

    /** Tâches d\'entretien réalisées lors d\'un passage (vouvoiement neutre). */
    private const ACTIONS = [
        'Nettoyage du préfiltre de pompe',
        'Brossage des parois et de la ligne d\'eau',
        'Ajout de chlore lent',
        'Contrôle et réglage du pH',
        'Nettoyage des paniers de skimmers',
        'Backwash du filtre à sable',
        'Aspiration du fond du bassin',
        'Contrôle du niveau de sel',
    ];

    /** Notes de passage réalistes (vouvoiement, opérateur Pierre ADAM). */
    private const NOTES = [
        'Eau limpide à mon arrivée. Pierre ADAM a procédé au contrôle complet, aucun point de vigilance.',
        'pH légèrement haut, corrigé sur place. Vous pouvez vous baigner sans attendre.',
        'Pensez à compléter le niveau d\'eau, il est descendu sous les skimmers.',
        'Traitement choc effectué après l\'épisode de pluie. Laissez filtrer 24 h avant la baignade.',
        'Filtre nettoyé, pression revenue à la normale. Tout est conforme.',
        'Robot encrassé, je l\'ai rincé. Pensez à le nettoyer entre deux passages.',
    ];

    private const COMMUNES = [
        'Fort-de-France', 'Le Lamentin', 'Schoelcher',
        'Les Trois-Îlets', 'Sainte-Anne', 'Le Robert',
    ];

    private const UA_MOBILE = [
        'Mozilla/5.0 (iPhone; CPU iPhone OS 17_4 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/17.4 Mobile/15E148 Safari/604.1',
        'Mozilla/5.0 (Linux; Android 14; Pixel 8) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Mobile Safari/537.36',
        'Mozilla/5.0 (Linux; Android 13; SM-G991B) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/123.0.0.0 Mobile Safari/537.36',
    ];

    private const SIGNATURE_STUB = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYPhfDwAChwGA60e6kgAAAABJRU5ErkJggg==';

    public function run(): void
    {
        $this->seedProduits();

        $clients = Client::factory()->count(10)->create();

        /** @var \Illuminate\Support\Collection<int,\App\Models\Piscine> $piscines */
        $piscines = collect();
        /** @var array<int,\App\Models\Contrat> $forfaitContrats */
        $forfaitContrats = [];

        $contratTypes = ['ponctuel', 'forfait_mensuel', 'forfait_saisonnier'];

        foreach ($clients->values() as $i => $client) {
            // 1 piscine principale par client.
            $piscines->push(
                Piscine::factory()->create(['client_id' => $client->id])
            );

            // ~30 % des clients ont un 2e bassin distinct.
            if ($i % 3 === 0) {
                $piscines->push(
                    Piscine::factory()->create([
                        'client_id' => $client->id,
                        'nom'       => $i % 2 === 0 ? 'Spa' : 'Bassin enfants',
                        'volume_m3' => fake()->randomFloat(2, 4, 12),
                        'type'      => 'spa',
                    ])
                );
            }

            // 1 contrat par client, type varié.
            $type = $contratTypes[$i % 3];
            $isForfait = $type !== 'ponctuel';

            $contrat = Contrat::create([
                'client_id'        => $client->id,
                'type'             => $type,
                'libelle'          => 'Contrat entretien ' . $client->name,
                'prix_ht_mensuel'  => $isForfait ? fake()->numberBetween(180, 260) : null,
                'jour_facturation' => $isForfait ? fake()->numberBetween(1, 28) : null,
                'date_debut'       => $isForfait ? now()->subMonths(fake()->numberBetween(2, 12)) : null,
                'actif'            => true,
            ]);

            if ($isForfait) {
                $forfaitContrats[] = $contrat;
            }
        }

        $this->seedDiagnostics($clients, $piscines);

        $this->seedPassagesSignaturesFactures($piscines, $forfaitContrats);
    }

    private function seedDiagnostics($clients, $piscines): void
    {
        // 3 diagnostics rattachés (wizard) à des clients/piscines existants.
        foreach ($piscines->take(3) as $piscine) {
            Diagnostic::factory()->create([
                'client_id'       => $piscine->client_id,
                'piscine_id'      => $piscine->id,
                'created_via'     => 'wizard',
                'volume_m3'       => $piscine->volume_m3,
                'mesures'         => $this->mesures(),
                'recommandations' => fake()->randomElements(self::RECOMMANDATIONS, 2),
            ]);
        }

        // 3 diagnostics anonymes (lead-capture).
        for ($n = 0; $n < 3; $n++) {
            Diagnostic::factory()->create([
                'client_id'       => null,
                'piscine_id'      => null,
                'created_via'     => 'lead',
                'mesures'         => $this->mesures(),
                'recommandations' => fake()->randomElements(self::RECOMMANDATIONS, 2),
                'prenom'          => fake('fr_FR')->firstName(),
                'commune'         => fake()->randomElement(self::COMMUNES),
                'email'           => fake('fr_FR')->safeEmail(),
                'site_web'        => fake()->boolean(40) ? fake('fr_FR')->url() : null,
            ]);
        }
    }

    private function seedPassagesSignaturesFactures($piscines, array $forfaitContrats): void
    {
        $factureCount = 0;
        $maxFactures = 8;

        foreach ($piscines as $piscine) {
            $count = fake()->numberBetween(4, 8);

            /** @var array<int,\App\Models\Passage> $syncedPassages */
            $syncedPassages = [];

            for ($k = 0; $k < $count; $k++) {
                // Le plus récent passage est k=0 ; espacé toutes les 2 semaines.
                $visitedAt = now()->subWeeks($k * 2);

                // 1–2 passages les plus récents en draft ; les autres alternent signed/synced.
                if ($k < 2) {
                    $status = 'draft';
                } else {
                    $status = $k % 2 === 0 ? 'synced' : 'signed';
                }

                $phAvant = fake()->randomFloat(1, 6.9, 7.4);

                $passage = $piscine->passages()->create([
                    'client_uuid'  => (string) Str::uuid(),
                    'client_id'    => $piscine->client_id,
                    'visited_at'   => $visitedAt,
                    'status'       => $status,
                    'ph_avant'     => $phAvant,
                    'ph_apres'     => 7.2,
                    'chlore_libre' => fake()->randomFloat(1, 0.8, 2.0),
                    'chlore_total' => fake()->boolean(60) ? fake()->randomFloat(1, 1.0, 2.5) : null,
                    'tac'          => fake()->numberBetween(70, 110),
                    'th'           => fake()->boolean(50) ? fake()->numberBetween(100, 300) : null,
                    'sel_g_l'      => fake()->boolean(30) ? fake()->randomFloat(1, 3.0, 5.0) : null,
                    'actions'      => fake()->randomElements(self::ACTIONS, fake()->numberBetween(1, 3)),
                    'notes'        => fake()->randomElement(self::NOTES),
                    'synced_at'    => $status === 'synced' ? now() : null,
                ]);

                // Signature sur tout passage clos (signed/synced).
                if (in_array($status, ['signed', 'synced'], true)) {
                    Signature::create([
                        'passage_id'     => $passage->id,
                        'client_id'      => $piscine->client_id,
                        'signature_data' => self::SIGNATURE_STUB,
                        'signed_at'      => $visitedAt,
                        'signer_name'    => $piscine->client->name,
                        'ip'             => fake()->ipv4(),
                        'user_agent'     => fake()->randomElement(self::UA_MOBILE),
                    ]);

                    if ($status === 'synced') {
                        $syncedPassages[] = $passage;
                    }
                }
            }

            // ~8 factures tied to forfait contrats : 1–2 mois facturés par contrat
            // forfait jusqu'à atteindre la cible, sur la piscine principale du client.
            $contrat = collect($forfaitContrats)
                ->firstWhere('client_id', $piscine->client_id);

            if ($contrat && $factureCount < $maxFactures) {
                $months = $factureCount + 2 <= $maxFactures ? 2 : 1;

                for ($m = 0; $m < $months && $factureCount < $maxFactures; $m++) {
                    $factureCount++;

                    // Adosse la facture à un passage synced différent par mois quand possible.
                    $passageForFacture = $syncedPassages[$m] ?? ($syncedPassages[0] ?? null);
                    $visited = $passageForFacture?->visited_at ?? now()->subMonths($m + 1);

                    $prixHt = (float) ($contrat->prix_ht_mensuel ?? 220);
                    $tvaRate = 8.50;
                    $tva = round($prixHt * $tvaRate / 100, 2);
                    $totalTtc = round($prixHt + $tva, 2);

                    // Varie le statut : brouillon / envoyee / payee.
                    $statut = ['brouillon', 'envoyee', 'payee'][$factureCount % 3];

                    Facture::create([
                        'uuid'         => (string) Str::uuid(),
                        'numero'       => $statut === 'brouillon' ? null : sprintf('FA-2026-%04d', $factureCount),
                        'client_id'    => $piscine->client_id,
                        'contrat_id'   => $contrat->id,
                        'passage_id'   => $passageForFacture?->id,
                        'lignes'       => [
                            [
                                'libelle' => 'Forfait entretien mensuel',
                                'qte'     => 1,
                                'prix_ht' => $prixHt,
                            ],
                        ],
                        'total_ht'     => $prixHt,
                        'tva'          => $tva,
                        'total_ttc'    => $totalTtc,
                        'tva_rate'     => $tvaRate,
                        'statut'       => $statut,
                        'date_echeance' => $visited->copy()->addDays(30),
                    ]);
                }
            }
        }
    }

    /** @return array<string,string> */
    private function mesures(): array
    {
        return [
            'ph'         => (string) fake()->randomFloat(1, 6.8, 7.6),
            'chlore'     => (string) fake()->randomFloat(1, 0.5, 2.0),
            'alcalinite' => (string) fake()->numberBetween(70, 140),
        ];
    }

    private function seedProduits(): void
    {
        // 5 demo produits covering the typical catalogue
        $produits = [
            [
                'sku'        => 'PASS-STD',
                'libelle'    => 'Passage entretien standard',
                'prix_ht'    => 60.00,
                'unite'      => 'passage',
                'actif'      => true,
            ],
            [
                'sku'        => 'FORF-MENSUEL',
                'libelle'    => 'Forfait mensuel 1 passage/semaine',
                'prix_ht'    => 220.00,
                'unite'      => 'mois',
                'actif'      => true,
            ],
            [
                'sku'        => 'ANAL-COMPL',
                'libelle'    => 'Analyse complète de l\'eau',
                'prix_ht'    => 35.00,
                'unite'      => 'unité',
                'actif'      => true,
            ],
            [
                'sku'        => 'CHOC-5KG',
                'libelle'    => 'Chlore choc 5kg',
                'prix_ht'    => 28.00,
                'unite'      => 'unité',
                'actif'      => true,
            ],
            [
                'sku'        => 'ACID-5L',
                'libelle'    => 'Acide chlorhydrique 5L',
                'prix_ht'    => 12.00,
                'unite'      => 'unité',
                'actif'      => true,
            ],
        ];

        foreach ($produits as $produit) {
            Produit::create($produit);
        }
    }
}
