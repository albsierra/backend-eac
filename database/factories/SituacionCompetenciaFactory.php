<?php

namespace Database\Factories;

use App\Models\EcosistemaLaboral;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SituacionCompetencia>
 */
class SituacionCompetenciaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ecosistema_laboral_id' => EcosistemaLaboral::factory(),
            'codigo'                => 'SC-' . str_pad($this->faker->unique()->numberBetween(1, 99), 2, '0', STR_PAD_LEFT),
            'titulo'                => $this->faker->sentence(6),
            'descripcion'           => $this->faker->paragraph(),
            'umbral_maestria'       => $this->faker->randomElement([70.00, 75.00, 80.00, 85.00]),
            'nivel_complejidad'     => $this->faker->numberBetween(1, 5),
            'activa'                => true,
        ];
    }
}
