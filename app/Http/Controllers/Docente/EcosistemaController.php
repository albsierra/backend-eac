<?php
namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\EcosistemaLaboral;
use App\Models\PerfilSituacion;
use Illuminate\Contracts\View\View;

class EcosistemaController extends Controller
{
    public function __invoke(EcosistemaLaboral $ecosistema): View
    {
        // Verificar que el usuario autenticado tiene rol docente en este ecosistema
        $esDocente = auth()->user()
            ->userRoles()
            ->where('ecosistema_laboral_id', $ecosistema->id)
            ->where('name', 'docente')
            ->exists();

        abort_unless($esDocente, 403, 'No tienes rol de docente en este ecosistema.');

        $ecosistema->load([
            'modulo.cicloFormativo.familiaProfesional',
            'modulo.resultadosAprendizaje.criteriosEvaluacion',
            'situacionesCompetencia.prerequisitos',
            'situacionesCompetencia.dependientes',
            'situacionesCompetencia.criteriosEvaluacion',
            'situacionesCompetencia.nodosRequisito',
        ]);

        // Estadísticas rápidas de progreso del grupo
        $totalEstudiantes = $ecosistema->perfilesHabilitacion()->count();

        // Para cada SC: cuántos estudiantes la han conquistado
        $conquistasPorSc = $ecosistema->situacionesCompetencia
            ->mapWithKeys(function ($sc) {
                return [
                    $sc->codigo => PerfilSituacion::whereHas(
                        'perfilHabilitacion',
                        fn($q) => $q->where('ecosistema_laboral_id', $sc->ecosistema_laboral_id)
                    )
                    ->where('situacion_competencia_id', $sc->id)
                    ->count(),
                ];
            });

        return view('docente.ecosistemas.show', compact(
            'ecosistema',
            'totalEstudiantes',
            'conquistasPorSc'
        ));
    }
}
