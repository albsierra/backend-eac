<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\FamiliaProfesional;
use App\Models\Modulo;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    public function index(Request $request): View
    {
        $familias = FamiliaProfesional::orderBy('nombre')->get();

        $modulos = Modulo::with([
                'cicloFormativo.familiaProfesional',
                'ecosistemasLaborales' => fn($q) => $q->where('activo', true),
            ])
            ->whereHas('ecosistemasLaborales', fn($q) => $q->where('activo', true))
            ->when($request->filled('familia'), fn($q) =>
                $q->whereHas('cicloFormativo',
                    fn($q2) => $q2->where('familia_profesional_id', $request->familia))
            )
            ->orderBy('codigo')
            ->paginate(15);

        return view('publico.modulos.index', compact('modulos', 'familias'));
    }

    public function show(Modulo $modulo): View
    {
        // resultadosAprendizaje → FK modulo_id (no ecosistema_laboral_id)
        $modulo->load([
            'cicloFormativo.familiaProfesional',
            'resultadosAprendizaje.criteriosEvaluacion',
            'ecosistemasLaborales' => fn($q) => $q->where('activo', true)
                                                   ->withCount('situacionesCompetencia'),
        ]);

        return view('publico.modulos.show', compact('modulo'));
    }
}
