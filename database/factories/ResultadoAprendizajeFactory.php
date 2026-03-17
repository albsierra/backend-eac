<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ResultadoAprendizaje>
 */
class ResultadoAprendizajeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'modulo_id'       => \App\Models\Modulo::factory(),
            'codigo'          => 'RA' . $this->faker->unique()->numberBetween(1, 99),
            'descripcion'     => $this->faker->sentence(),
            // 'peso_porcentaje' => $this->faker->randomElement([25, 30, 35, 40]),
            // 'orden'           => $this->faker->numberBetween(1, 10),
        ];
    }
}
