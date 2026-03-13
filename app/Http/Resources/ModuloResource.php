<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuloResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'codigo'       => $this->codigo,
            'nombre'       => $this->nombre,
            'descripcion'  => $this->descripcion,
            'horas_totales' => $this->horas_totales,

            'ciclo_formativo' => [
                'id'     => $this->cicloFormativo->id,
                'codigo' => $this->cicloFormativo->codigo,
                'nombre' => $this->cicloFormativo->nombre,
                'grado'  => $this->cicloFormativo->grado,
                'familia_profesional' => [
                    'id'     => $this->cicloFormativo->familiaProfesional->id,
                    'codigo' => $this->cicloFormativo->familiaProfesional->codigo,
                    'nombre' => $this->cicloFormativo->familiaProfesional->nombre,
                ],
            ],

            // Ecosistema activo (puede ser null si el módulo no tiene ecosistema aún)
            'ecosistema_activo' => $this->whenLoaded('ecosistemasLaborales', function () {
                $ecosistema = $this->ecosistemasLaborales
                    ->where('activo', true)
                    ->first();

                return $ecosistema ? [
                    'id'     => $ecosistema->id,
                    'codigo' => $ecosistema->codigo,
                    'nombre' => $ecosistema->nombre,
                    'total_scs' => $ecosistema->situaciones_competencia_count ?? null,
                ] : null;
            }),

            // RA del módulo (trazabilidad curricular)
            'resultados_aprendizaje' => $this->whenLoaded('resultadosAprendizaje', function () {
                return $this->resultadosAprendizaje->map(fn($ra) => [
                    'id'               => $ra->id,
                    'codigo'           => $ra->codigo,
                    'descripcion'      => $ra->descripcion,
                    'peso_porcentaje'  => $ra->peso_porcentaje,
                ]);
            }),

            'links' => [
                'self'       => route('api.v1.modulos.show', $this->id),
                'ecosistema' => $this->ecosistemasLaborales?->where('activo', true)->first()
                    ? route('api.v1.ecosistemas.show',
                        $this->ecosistemasLaborales->where('activo', true)->first()->id)
                    : null,
            ],
        ];
    }
}
