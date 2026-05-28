<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\Contrat;
use App\Models\Piscine;
use App\Models\Produit;
use Illuminate\Database\Seeder;

/**
 * Dev/test fixtures only — never run in production (see DatabaseSeeder env gate).
 *
 * Creates: 3 demo clients + 1 piscine each + 5 produits + 1 contrat per client.
 */
class DevDataSeeder extends Seeder
{
    public function run(): void
    {
        // 3 demo clients Pierre would use for local development
        $clients = Client::factory()->count(3)->create();

        foreach ($clients as $client) {
            // 1 piscine per client
            Piscine::factory()->create(['client_id' => $client->id]);

            // 1 contrat per client (mixed types)
            Contrat::create([
                'client_id' => $client->id,
                'type'      => fake()->randomElement(['ponctuel', 'forfait_mensuel', 'forfait_saisonnier']),
                'libelle'   => 'Contrat entretien ' . $client->name,
                'actif'     => true,
            ]);
        }

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
