<?php

namespace App\Http\Controllers\Estudiante;

use App\Http\Controllers\Controller;
use App\Services\GrafoService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(GrafoService $grafoService): View
    {
        $perfiles = auth()->user()
            ->perfilesHabilitacion()
            ->with([
                'ecosistemaLaboral.modulo',
                'ecosistemaLaboral.situacionesCompetencia',
                'situacionesConquistadas',
            ])
            ->get();

        // Añadir resumen ZDP a cada perfil para mostrarlo en las tarjetas del dashboard
        $perfiles = $perfiles->map(function ($perfil) use ($grafoService) {
            $codigosConquistados = $perfil->codigosConquistados();
            $clasificacion       = $grafoService->clasificar(
                $perfil->ecosistemaLaboral,
                $codigosConquistados
            );

            $perfil->zdp_count       = $clasificacion['zdp']->count();
            $perfil->completado      = $clasificacion['zdp']->isEmpty()
                                    && $clasificacion['bloqueadas']->isEmpty();

            return $perfil;
        });

        return view('estudiante.dashboard', compact('perfiles'));
    }
}
