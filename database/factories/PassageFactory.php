<?php

namespace Database\Factories;

use App\Models\Client;
use App\Models\Passage;
use App\Models\Piscine;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Passage>
 */
class PassageFactory extends Factory
{
    protected $model = Passage::class;

    public function definition(): array
    {
        return [
            'client_uuid'  => (string) Str::uuid(),
            'client_id'    => Client::factory(),
            'piscine_id'   => Piscine::factory(),
            'visited_at'   => fake()->dateTimeBetween('-6 months', 'now'),
            'status'       => 'draft',
            'ph_avant'     => 7.2,
            'ph_apres'     => null,
            'chlore_libre' => 1.5,
            'chlore_total' => null,
            'tac'          => null,
            'th'           => null,
            'sel_g_l'      => null,
            'actions'      => null,
            'notes'        => null,
            'pdf_path'     => null,
            'signature_path' => null,
            'synced_at'    => null,
        ];
    }
}
