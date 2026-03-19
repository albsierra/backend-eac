<?php

namespace App\Http\Controllers\Estudiante;

use App\Charts\RadarHuellaChart;
use App\Http\Controllers\Controller;
use App\Models\EcosistemaLaboral;
use App\Models\PerfilHabilitacion;
use App\Services\EACAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class HuellaRadarController extends Controller
{
    public function __construct(
        private readonly EACAnalyticsService $analyticsService
    ) {}

    public function __invoke(Request $request, EcosistemaLaboral $ecosistema): View
    {
        $estudiante = $request->user();

        $perfil = PerfilHabilitacion::where('estudiante_id', $estudiante->id)
            ->where('ecosistema_laboral_id', $ecosistema->id)
            ->firstOrFail();

        $datosRadar = $this->analyticsService->radarHuella($perfil);

        $chartRadar = new RadarHuellaChart();
        $chartRadar
            ->labels($datosRadar['labels'])
            ->dataset('Mi cobertura competencial', 'radar', $datosRadar['data'])
                ->backgroundColor('rgba(99, 102, 241, 0.25)')
                ->options([
                    'responsive' => true,
                    'plugins'    => [
                        'legend' => ['position' => 'top'],
                        'title'  => [
                            'display' => true,
                            'text'    => 'Huella de Talento — ' . $ecosistema->nombre,
                        ],
                    ],
                    'scales' => [
                        'r' => [
                            'min'        => 0,
                            'max'        => $datosRadar['max'],
                            'ticks'      => ['stepSize' => 20],
                            'pointLabels'=> ['font' => ['size' => 13]],
                        ],
                    ],
                ]);

        // Añadir el dataset de referencia (100 en cada RA) para contextualizar
        $chartRadar
            ->dataset('Máximo posible', 'radar', array_fill(0, count($datosRadar['labels']), 100))
                ->backgroundColor('rgba(229, 231, 235, 0.15)');

        $calificacion = $perfil->calificacion_actual;

        return view('estudiante.huella-radar', compact(
            'ecosistema',
            'perfil',
            'chartRadar',
            'calificacion',
        ));
    }
}
