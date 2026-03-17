<?php
namespace Database\Factories;

use App\Models\EcosistemaLaboral;
use App\Models\HuellaTalento;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HuellaTalentoFactory extends Factory
{
    protected $model = HuellaTalento::class;

    public function definition(): array
    {
        $estudianteId  = User::factory()->create()->id;
        $ecosistemaId  = EcosistemaLaboral::factory()->create(['activo' => true])->id;

        return [
            'estudiante_id'         => $estudianteId,
            'ecosistema_laboral_id' => $ecosistemaId,
            'payload'               => [
                'ngsi_ld_id'              => "urn:ngsi-ld:PerfilHabilitacion:estudiante-{$estudianteId}-ecosistema-{$ecosistemaId}",
                'calificacion'            => 0.0,
                'situaciones_conquistadas' => [],
                'desglose_curricular'     => [],
                'version'                 => '1.0',
                'generada_en'             => now()->toIso8601String(),
            ],
            'ngsi_ld_id'   => null,
            'generada_en'  => now(),
        ];
    }
}
