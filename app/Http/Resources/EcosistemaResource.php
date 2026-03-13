<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EcosistemaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'codigo'      => $this->codigo,
            'nombre'      => $this->nombre,
            'descripcion' => $this->descripcion,
            'activo'      => $this->activo,

            'modulo' => [
                'id'     => $this->modulo->id,
                'codigo' => $this->modulo->codigo,
                'nombre' => $this->modulo->nombre,
            ],

            'situaciones_competencia' => $this->whenLoaded(
                'situacionesCompetencia',
                fn() => SituacionCompetenciaResource::collection($this->situacionesCompetencia)
            ),

            'meta' => [
                'version'   => '1.0',
                'timestamp' => now()->toIso8601String(),
            ],

            'links' => [
                'self'       => route('api.ecosistemas.show', $this->id),
                'situaciones' => route('api.ecosistemas.situaciones', $this->id),
                'modulo'     => route('api.modulos.show', $this->modulo_id),
            ],
        ];
    }
}
