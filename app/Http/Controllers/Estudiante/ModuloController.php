<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Models\PerfilHabilitacion;
use App\Services\GrafoService;
use App\Services\RecomendacionService;
use Illuminate\Contracts\View\View;

class ModuloController extends Controller
{
    public function __construct(
        private readonly GrafoService         $grafoService,
        private readonly RecomendacionService $recomendacionService,
    ) {}

    public function __invoke(Modulo $modulo): View
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
