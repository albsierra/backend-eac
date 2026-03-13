<?php

namespace App\Http\Controllers\Api\V1\Docente;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProgresoEstudianteResource;
use App\Models\EcosistemaLaboral;
use App\Models\Matricula;
use App\Models\PerfilHabilitacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ProgresoController extends Controller
{
    /**
     * GET /api/v1/docente/ecosistemas/{ecosistema}/progreso
     * Progreso de todos los estudiantes matriculados en el módulo del ecosistema.
     */
    public function __invoke(EcosistemaLaboral $ecosistema): AnonymousResourceCollection|JsonResponse
    {
        $this->autorizarDocente($ecosistema);

        $totalScs = $ecosistema->situacionesCompetencia()->count();

        $progreso = Matricula::where('modulo_id', $ecosistema->modulo_id)
            ->with('estudiante')
            ->get()
            ->map(function ($matricula) use ($ecosistema, $totalScs) {
                $perfil = PerfilHabilitacion::where('estudiante_id', $matricula->estudiante_id)
                    ->where('ecosistema_laboral_id', $ecosistema->id)
                    ->withCount('situacionesConquistadas')
                    ->with('situacionesConquistadas:id,codigo,titulo')
                    ->first();

                return new ProgresoEstudianteResource([
                    'estudiante_id' => $matricula->estudiante_id,
                    'conquistadas'  => $perfil?->situaciones_conquistadas_count ?? 0,
                    'total_scs'     => $totalScs,
                    'calificacion'  => $perfil?->calificacion_actual ?? 0,
                    'detalle'       => $perfil?->situacionesConquistadas
                        ->map(fn($sc) => [
                            'codigo'              => $sc->codigo,
                            'gradiente_autonomia' => $sc->pivot->gradiente_autonomia,
                            'fecha_conquista'     => $sc->pivot->fecha_conquista,
                        ]) ?? [],
                ]);
            });

        return ProgresoEstudianteResource::collection($progreso)
            ->additional([
                'meta' => [
                    'version'        => '1.0',
                    'timestamp'      => now()->toIso8601String(),
                    'ecosistema_id'  => $ecosistema->id,
                    'total_scs'      => $totalScs,
                    'total_matriculados' => $progreso->count(),
                ],
            ]);
    }

    private function autorizarDocente(EcosistemaLaboral $ecosistema): void
    {
        $esDocente = auth()->user()
            ->userRoles()
            ->where('ecosistema_laboral_id', $ecosistema->id)
            ->where('name', 'docente')
            ->exists();

        abort_unless($esDocente, 403, 'No tienes rol de docente en este ecosistema.');
    }
}
