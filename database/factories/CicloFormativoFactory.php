<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CicloFormativo>
 */
class CicloFormativoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'familia_profesional_id' => \App\Models\FamiliaProfesional::factory(),
            'nombre' => $this->faker->word(),
            'codigo' => strtoupper($this->faker->bothify('????-###')), // Ej: "ABCD-123"
            'grado' => $this->faker->randomElement(['GB', 'GM', 'GS']),
            'descripcion' => $this->faker->sentence(),
        ];
    }
}
