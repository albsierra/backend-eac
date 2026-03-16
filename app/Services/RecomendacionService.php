<?php

namespace App\Services;

use App\Models\EcosistemaLaboral;
use App\Models\SituacionCompetencia;
use Illuminate\Support\Collection;

class RecomendacionService
{
    public function __construct(
        private readonly GrafoService $grafoService
    ) {}

    /**
     * Devuelve la SC más recomendada de la ZDP del estudiante,
     * o null si la ZDP está vacía (ecosistema completado).
     *
     * Criterios de ordenación (por prioridad):
     *   1. Menor nivel_complejidad
     *   2. Mayor número de CE pendientes que cubre
     *   3. Mayor número de SCs que desbloquearía su conquista
     */
    public function recomendar(
        EcosistemaLaboral $ecosistema,
        array $codigosConquistados
    ): ?SituacionCompetencia {
        $zdp = $this->grafoService->calcularZdp($ecosistema, $codigosConquistados);

        if ($zdp->isEmpty()) {
            return null;
        }

        // Cargar relaciones necesarias para los criterios 2 y 3
        $zdp->load([
            'criteriosEvaluacion',
            'dependientes.prerequisitos',
        ]);

        // CE ya cubiertos por las SCs conquistadas
        $ceYaCubiertos = $this->cesCubiertos($ecosistema, $codigosConquistados);

        return $zdp
            ->sortBy([
                // Criterio 1: menor complejidad primero
                fn($a, $b) => $a->nivel_complejidad <=> $b->nivel_complejidad,

                // Criterio 2: mayor cobertura de CE pendientes (descendente)
                fn($a, $b) => $this->cesPendientesCubiertos($b, $ceYaCubiertos)
                           <=> $this->cesPendientesCubiertos($a, $ceYaCubiertos),

                // Criterio 3: mayor número de SCs desbloqueadas (descendente)
                fn($a, $b) => $this->scsDesbloqueadas($b, $codigosConquistados)
                           <=> $this->scsDesbloqueadas($a, $codigosConquistados),
            ])
            ->first();
    }

    /**
     * Devuelve la ZDP ordenada con la puntuación de recomendación de cada SC.
     * Útil para mostrar al estudiante no solo la primera opción sino el ranking completo.
     *
     * @return Collection  Cada elemento: ['sc' => SituacionCompetencia, 'score' => int, 'motivos' => array]
     */
    public function rankingZdp(
        EcosistemaLaboral $ecosistema,
        array $codigosConquistados
    ): Collection {
        $zdp = $this->grafoService->calcularZdp($ecosistema, $codigosConquistados);

        if ($zdp->isEmpty()) {
            return collect();
        }

        $zdp->load(['criteriosEvaluacion', 'dependientes.prerequisitos']);

        $ceYaCubiertos = $this->cesCubiertos($ecosistema, $codigosConquistados);
        $maxComplejidad = $zdp->max('nivel_complejidad') ?: 1;

        return $zdp->map(function (SituacionCompetencia $sc) use ($codigosConquistados, $ceYaCubiertos, $maxComplejidad) {
            $puntosSencillez  = ($maxComplejidad - $sc->nivel_complejidad + 1) * 10;
            $puntosCe         = $this->cesPendientesCubiertos($sc, $ceYaCubiertos) * 5;
            $puntosDesbloqueo = $this->scsDesbloqueadas($sc, $codigosConquistados) * 3;
            $score            = $puntosSencillez + $puntosCe + $puntosDesbloqueo;

            return [
                'sc'      => $sc,
                'score'   => $score,
                'motivos' => [
                    'nivel_complejidad'   => $sc->nivel_complejidad,
                    'ce_pendientes'       => $this->cesPendientesCubiertos($sc, $ceYaCubiertos),
                    'scs_desbloqueadas'   => $this->scsDesbloqueadas($sc, $codigosConquistados),
                ],
            ];
        })
        ->sortByDesc('score')
        ->values();
    }

    // ─── Helpers privados ────────────────────────────────────────────────────

    /**
     * IDs de CE ya cubiertos por las SCs conquistadas.
     */
    private function cesCubiertos(
        EcosistemaLaboral $ecosistema,
        array $codigosConquistados
    ): array {
        if (empty($codigosConquistados)) {
            return [];
        }

        return $ecosistema->situacionesCompetencia()
            ->whereIn('codigo', $codigosConquistados)
            ->with('criteriosEvaluacion:id')
            ->get()
            ->flatMap(fn($sc) => $sc->criteriosEvaluacion->pluck('id'))
            ->unique()
            ->toArray();
    }

    /**
     * Número de CE que esta SC cubre y que aún no están cubiertos.
     */
    private function cesPendientesCubiertos(
        SituacionCompetencia $sc,
        array $ceYaCubiertos
    ): int {
        return $sc->criteriosEvaluacion
            ->filter(fn($ce) => !in_array($ce->id, $ceYaCubiertos))
            ->count();
    }

    /**
     * Número de SCs adicionales que quedarían en la ZDP si se conquistara esta SC.
     */
    private function scsDesbloqueadas(
        SituacionCompetencia $sc,
        array $codigosConquistados
    ): int {
        $hipotetico = array_merge($codigosConquistados, [$sc->codigo]);

        return $sc->dependientes->filter(function ($dep) use ($hipotetico) {
            $reqs = $dep->prerequisitos->pluck('codigo')->toArray();
            return count(array_diff($reqs, $hipotetico)) === 0;
        })->count();
    }
}
