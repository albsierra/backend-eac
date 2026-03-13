<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SituacionCompetenciaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'codigo'            => $this->codigo,
            'titulo'            => $this->titulo,
            'descripcion'       => $this->descripcion,
            'umbral_maestria'   => (float) $this->umbral_maestria,
            'nivel_complejidad' => $this->nivel_complejidad,
            'activa'            => $this->activa,

            // Prerequisitos del grafo (solo códigos, para no crear recursión)
            'prerequisitos' => $this->whenLoaded(
                'prerequisitos',
                fn() => $this->prerequisitos->map(fn($pre) => [
                    'id'     => $pre->id,
                    'codigo' => $pre->codigo,
                ])
            ),

            // Nodos de requisito (conocimientos y habilidades previos)
            'nodos_requisito' => $this->whenLoaded(
                'nodosRequisito',
                fn() => $this->nodosRequisito->map(fn($nodo) => [
                    'tipo'        => $nodo->tipo,
                    'descripcion' => $nodo->descripcion,
                ])
            ),

            // CE del currículo que cubre (trazabilidad)
            'criterios_evaluacion' => $this->whenLoaded(
                'criteriosEvaluacion',
                fn() => $this->criteriosEvaluacion->map(fn($ce) => [
                    'codigo'      => $ce->codigo,
                    'descripcion' => $ce->descripcion,
                    'peso_en_sc'  => (float) $ce->pivot->peso_en_sc,
                ])
            ),
        ];
    }
}
