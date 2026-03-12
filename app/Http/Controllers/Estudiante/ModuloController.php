<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\FamiliaProfesional;
use App\Models\Modulo;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $familias = FamiliaProfesional::orderBy('nombre')->get();

        $modulos = Modulo::with([
                'cicloFormativo.familiaProfesional',
                'ecosistemasLaborales' => fn($q) => $q->where('activo', true),
            ])
            ->whereHas('ecosistemasLaborales', fn($q) => $q->where('activo', true))
            ->whereHas('matriculas', fn($q) => $q->where('estudiante_id', auth()->id()))
            ->when($request->filled('familia'), fn($q) =>
                $q->whereHas('cicloFormativo',
                    fn($q2) => $q2->where('familia_profesional_id', $request->familia))
            )
            ->orderBy('codigo')
            ->paginate(15);

        return view('publico.modulos.index', compact('modulos', 'familias'));
    }
}
