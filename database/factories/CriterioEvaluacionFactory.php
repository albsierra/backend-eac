<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CriterioEvaluacion>
 */
class CriterioEvaluacionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'resultado_aprendizaje_id' => \App\Models\ResultadoAprendizaje::factory(),
            'codigo'                   => 'CE' . $this->faker->unique()->bothify('#?'),
            'descripcion'              => $this->faker->sentence(),
            // 'peso_porcentaje'          => $this->faker->randomElement([20, 25, 30, 50]),
            // 'orden'                    => $this->faker->numberBetween(1, 10),
        ];
    }
}
