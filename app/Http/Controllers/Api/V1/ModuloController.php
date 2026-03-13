<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ModuloCollection;
use App\Http\Resources\ModuloResource;
use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    /**
     * GET /api/v1/modulos
     * Catálogo paginado de módulos que tienen al menos un ecosistema activo.
     */
    public function index(Request $request): ModuloCollection
    {
        $modulos = Modulo::whereHas('ecosistemasLaborales', fn($q) => $q->where('activo', true))
            ->with([
                'cicloFormativo.familiaProfesional',
                'ecosistemasLaborales' => fn($q) => $q->where('activo', true)
                    ->withCount('situacionesCompetencia'),
                'resultadosAprendizaje',
            ])
            ->when($request->filled('buscar'), fn($q) =>
                $q->where('nombre', 'like', "%{$request->buscar}%")
                  ->orWhere('codigo', 'like', "%{$request->buscar}%")
            )
            ->when($request->filled('ciclo_id'), fn($q) =>
                $q->where('ciclo_formativo_id', $request->ciclo_id)
            )
            ->orderBy('nombre')
            ->paginate($request->integer('per_page', 12));

        return new ModuloCollection($modulos);
    }

    /**
     * GET /api/v1/modulos/{modulo}
     * Detalle completo: jerarquía curricular, RA, CE y ecosistema activo con SCs.
     */
    public function show(Modulo $modulo): ModuloResource
    {
        $modulo->load([
            'cicloFormativo.familiaProfesional',
            'resultadosAprendizaje.criteriosEvaluacion',
            'ecosistemasLaborales' => fn($q) => $q->where('activo', true)
                ->withCount('situacionesCompetencia'),
        ]);

        return new ModuloResource($modulo);
    }
}
