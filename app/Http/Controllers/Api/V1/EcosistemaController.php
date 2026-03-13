<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\EcosistemaResource;
use App\Http\Resources\SituacionCompetenciaResource;
use App\Models\EcosistemaLaboral;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class EcosistemaController extends Controller
{
    /**
     * GET /api/v1/ecosistemas/{ecosistema}
     * Detalle del ecosistema con el grafo completo de SCs.
     */
    public function show(EcosistemaLaboral $ecosistema): EcosistemaResource
    {
        $ecosistema->load([
            'modulo.cicloFormativo',
            'situacionesCompetencia.prerequisitos',
            'situacionesCompetencia.nodosRequisito',
            'situacionesCompetencia.criteriosEvaluacion',
        ]);

        return new EcosistemaResource($ecosistema);
    }

    /**
     * GET /api/v1/ecosistemas/{ecosistema}/situaciones
     * Lista plana de SCs con prerequisitos — útil para renderizar el grafo en el cliente.
     */
    public function situaciones(EcosistemaLaboral $ecosistema): AnonymousResourceCollection
    {
        $scs = $ecosistema->situacionesCompetencia()
            ->with(['prerequisitos', 'dependientes', 'nodosRequisito', 'criteriosEvaluacion'])
            ->orderBy('nivel_complejidad')
            ->orderBy('codigo')
            ->get();

        return SituacionCompetenciaResource::collection($scs)
            ->additional([
                'meta' => [
                    'version'        => '1.0',
                    'timestamp'      => now()->toIso8601String(),
                    'ecosistema_id'  => $ecosistema->id,
                    'total'          => $scs->count(),
                ],
            ]);
    }
}
