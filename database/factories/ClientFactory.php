<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    protected $model = Client::class;

    public function definition(): array
    {
        return [
            'uuid'    => (string) Str::uuid(),
            'name'    => fake('fr_FR')->lastName() . ' ' . fake('fr_FR')->firstName(),
            'email'   => fake('fr_FR')->unique()->safeEmail(),
            'phone'   => fake('fr_FR')->numerify('0696######'),
            'address' => fake('fr_FR')->streetAddress() . ', ' . fake('fr_FR')->randomElement([
                'Fort-de-France',
                'Le Lamentin',
                'Schoelcher',
                'Les Trois-Îlets',
                'Sainte-Anne',
                'Le Robert',
            ]),
            'notes'                 => null,
            'magic_link_token'      => null,
            'magic_link_expires_at' => null,
        ];
    }
}
