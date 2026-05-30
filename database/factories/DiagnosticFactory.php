<?php

namespace Database\Factories;

use App\Models\Diagnostic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Diagnostic>
 */
class DiagnosticFactory extends Factory
{
    protected $model = Diagnostic::class;

    public function definition(): array
    {
        return [
            'client_id'              => null,
            'piscine_id'             => null,
            'volume_m3'              => $this->faker->randomFloat(2, 10, 200),
            'type_probleme'          => 'wizard',
            'mesures'                => [
                'ph'         => '7.2',
                'chlore'     => '1.5',
                'alcalinite' => '100',
            ],
            'recommandations'        => [],
            'disclaimer_accepted_at' => now(),
            'created_via'            => 'wizard',
        ];
    }
}
