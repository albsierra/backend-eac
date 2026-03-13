<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PerfilHabilitacionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $conquistadas = $this->whenLoaded('situacionesConquistadas');

        return [
            'id'          => $this->id,

            // Campos que mapean directamente a propiedades NGSI-LD
            'estudiante'  => [
                'id'     => $this->estudiante_id,
                // Sin name ni email: privacidad por diseño
            ],
            'ecosistema'  => [
                'id'     => $this->ecosistema_laboral_id,
                'codigo' => $this->whenLoaded('ecosistemaLaboral',
                    fn() => $this->ecosistemaLaboral->codigo),
            ],

            'calificacion_actual'       => (float) $this->calificacion_actual,

            // Situaciones conquistadas con su gradiente de autonomía
            'situaciones_conquistadas'  => $this->whenLoaded(
                'situacionesConquistadas',
                fn() => SituacionConquistadaResource::collection($this->situacionesConquistadas)
            ),

            // Códigos para el motor ZDP (se expande en Unidad 5)
            'codigos_conquistados'      => $this->whenLoaded(
                'situacionesConquistadas',
                fn() => $this->situacionesConquistadas->pluck('codigo')
            ),

            'ngsi_ld_id' => sprintf(
                'urn:ngsi-ld:PerfilHabilitacion:estudiante-%d-ecosistema-%d',
                $this->estudiante_id,
                $this->ecosistema_laboral_id
            ),

            'meta' => [
                'version'      => '1.0',
                'timestamp'    => now()->toIso8601String(),
                'updated_at'   => $this->updated_at->toIso8601String(),
            ],
        ];
    }
}
