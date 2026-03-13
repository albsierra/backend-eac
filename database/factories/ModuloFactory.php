<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Modulo>
 */
class ModuloFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'ciclo_formativo_id' => \App\Models\CicloFormativo::factory(),
            'nombre' => $this->faker->word(),
            'codigo' => strtoupper($this->faker->bothify('????-###')), // Ej: "ABCD-123"
            'horas_totales' => $this->faker->numberBetween(100, 300),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
