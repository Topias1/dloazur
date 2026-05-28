<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Piscine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Piscine>
 */
class PiscineFactory extends Factory
{
    protected $model = Piscine::class;

    public function definition(): array
    {
        return [
            'client_id'  => Client::factory(),
            'nom'        => 'Piscine principale',
            'volume_m3'  => fake()->randomFloat(2, 8, 80),
            'type'       => fake()->randomElement(['enterrée', 'hors-sol', 'spa']),
            'filtration' => fake()->randomElement(['sable', 'cartouche', 'diatomées']),
            'traitement' => fake()->randomElement(['chlore', 'sel', 'brome']),
            'equipements'=> null,
            'notes'      => null,
        ];
    }
}
