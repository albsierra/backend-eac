<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\FamiliaProfesional;
use App\Models\Modulo;
use App\Models\PerfilHabilitacion;
use App\Services\GrafoService;
use App\Services\RecomendacionService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ModuloController extends Controller
{
    public function __construct(
        private readonly GrafoService         $grafoService,
        private readonly RecomendacionService $recomendacionService,
    ) {}

    /**
    * Handle the incoming request.
    */
    public function index(Request $request) : View
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

    public function show(Modulo $modulo): View
    {
        abort_unless(
            auth()->user()->matriculas()->where('modulo_id', $modulo->id)->exists(),
            403, 'No estás matriculado en este módulo.'
        );

        $ecosistema = $modulo->ecosistemasLaborales()
            ->where('activo', true)
            ->firstOrFail();

        $perfil = PerfilHabilitacion::where('estudiante_id', auth()->id())
            ->where('ecosistema_laboral_id', $ecosistema->id)
            ->with('situacionesConquistadas')
            ->first();

        $codigosConquistados = $perfil?->codigosConquistados() ?? [];

        $clasificacion = $this->grafoService->clasificar($ecosistema, $codigosConquistados);
        $recomendacion = $this->recomendacionService->recomendar($ecosistema, $codigosConquistados);

        return view('estudiante.modulo', compact(
            'modulo', 'ecosistema', 'perfil',
            'clasificacion', 'recomendacion', 'codigosConquistados'
        ));
    }
}
