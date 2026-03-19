<?php

namespace App\Http\Controllers\Docente;

use App\Charts\BarrasConquistasChart;
use App\Charts\DoughnutGradienteChart;
use App\Charts\LineasEvolucionChart;
use App\Http\Controllers\Controller;
use App\Models\EcosistemaLaboral;
use App\Services\EACAnalyticsService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    public function __construct(
        private readonly EACAnalyticsService $analyticsService
    ) {}

    public function __invoke(Request $request, EcosistemaLaboral $ecosistema): View
    {
        // ── Gráfica 1: Ranking de SCs ─────────────────────────────────────
        $datosRanking = $this->analyticsService->rankingConquistas($ecosistema);

        $chartRanking = new BarrasConquistasChart();
        $chartRanking
            ->labels($datosRanking['labels'])
            ->dataset('Nº de conquistas', 'bar', $datosRanking['data'])
                ->backgroundColor($datosRanking['colores'])
                ->options([
                    'indexAxis'  => 'y',       // barras horizontales en Chart.js v4
                    'responsive' => true,
                    'plugins'    => [
                        'legend' => ['display' => false],
                        'title'  => [
                            'display' => true,
                            'text'    => 'SCs más conquistadas',
                        ],
                    ],
                ]);

        // ── Gráfica 2: Distribución del Gradiente ─────────────────────────
        $datosGradiente = $this->analyticsService->distribucionGradiente($ecosistema);

        $chartGradiente = new DoughnutGradienteChart();
        $chartGradiente
            ->labels($datosGradiente['labels'])
            ->dataset('Conquistas', 'doughnut', $datosGradiente['data'])
                ->backgroundColor($datosGradiente['colores'])
                ->options([
                    'responsive' => true,
                    'plugins'    => [
                        'legend' => ['position' => 'bottom'],
                        'title'  => [
                            'display' => true,
                            'text'    => 'Distribución del Gradiente de Autonomía',
                        ],
                    ],
                ]);

        // ── Gráfica 3: Evolución temporal ─────────────────────────────────
        $datosEvolucion = $this->analyticsService->evolucionTemporal($ecosistema, semanas: 8);

        $chartEvolucion = new LineasEvolucionChart();
        $chartEvolucion
            ->labels($datosEvolucion['labels'])
            ->dataset('Conquistas por semana', 'line', $datosEvolucion['data'])
                ->options([
                    'responsive' => true,
                    'plugins'    => [
                        'legend' => ['display' => false],
                        'title'  => [
                            'display' => true,
                            'text'    => 'Evolución de conquistas (últimas 8 semanas)',
                        ],
                    ],
                    'scales' => [
                        'y' => [
                            'beginAtZero' => true,
                            'ticks'       => ['stepSize' => 1],
                        ],
                    ],
                    'backgroundColor' => 'rgba(99, 102, 241, 0.15)',
                    'borderColor'     => '#6366f1',
                    'pointBackgroundColor' => '#6366f1',
                    'fill'            => true,
                ]);

        // ── Estadísticas de resumen ────────────────────────────────────────
        $totalEstudiantes = $ecosistema->perfilesHabilitacion()->count();
        $totalConquistas  = $ecosistema->situacionesCompetencia()
            ->withCount('perfilesHabilitacion as conquistas')
            ->get()
            ->sum('conquistas');
        $mediaConquistas  = $totalEstudiantes > 0
            ? round($totalConquistas / $totalEstudiantes, 1)
            : 0;

        return view('docente.analytics.show', compact(
            'ecosistema',
            'chartRanking',
            'chartGradiente',
            'chartEvolucion',
            'totalEstudiantes',
            'totalConquistas',
            'mediaConquistas',
        ));
    }
}
