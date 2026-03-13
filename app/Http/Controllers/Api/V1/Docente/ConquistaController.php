<?php

namespace App\Http\Controllers\Api\V1\Docente;

use App\Http\Controllers\Controller;
use App\Models\EcosistemaLaboral;
use App\Models\PerfilHabilitacion;
use App\Models\SituacionCompetencia;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ConquistaController extends Controller
{
    /**
     * POST /api/v1/docente/ecosistemas/{ecosistema}/conquistas
     *
     * Body:
     * {
     *   "estudiante_id": 5,
     *   "sc_codigo": "SC-01",
     *   "gradiente_autonomia": "supervisado",
     *   "puntuacion_conquista": 82.5
     * }
     */
    public function __invoke(Request $request, EcosistemaLaboral $ecosistema): JsonResponse
    {
        $this->autorizarDocente($ecosistema);

        $data = $request->validate([
            'estudiante_id'       => ['required', 'integer', 'exists:users,id'],
            'sc_codigo'           => ['required', 'string'],
            'gradiente_autonomia' => ['required', Rule::in(['asistido','guiado','supervisado','autonomo'])],
            'puntuacion_conquista' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);

        // Verificar que la SC pertenece a este ecosistema
        $sc = SituacionCompetencia::where('ecosistema_laboral_id', $ecosistema->id)
            ->where('codigo', $data['sc_codigo'])
            ->firstOrFail();

        // Verificar que la puntuación supera el umbral de maestría
        if ($data['puntuacion_conquista'] < $sc->umbral_maestria) {
            return response()->json([
                'type'   => 'https://backend-eac.test/errors/umbral-no-alcanzado',
                'title'  => 'Umbral de maestría no alcanzado',
                'status' => 422,
                'detail' => "La puntuación {$data['puntuacion_conquista']} no supera el umbral "
                          . "de maestría de la SC {$sc->codigo} ({$sc->umbral_maestria}%).",
            ], 422);
        }

        // Verificar que el estudiante está matriculado en el módulo
        $matriculado = \App\Models\Matricula::where('modulo_id', $ecosistema->modulo_id)
            ->where('estudiante_id', $data['estudiante_id'])
            ->exists();

        abort_unless($matriculado, 422, 'El estudiante no está matriculado en este módulo.');

        $perfil = PerfilHabilitacion::where('estudiante_id', $data['estudiante_id'])
            ->where('ecosistema_laboral_id', $ecosistema->id)
            ->firstOrFail();

        DB::transaction(function () use ($perfil, $sc, $data) {
            $yaConquistada = $perfil->situacionesConquistadas()
                ->where('situacion_competencia_id', $sc->id)
                ->exists();

            if ($yaConquistada) {
                // Actualizar el gradiente si mejora
                $perfil->situacionesConquistadas()->updateExistingPivot($sc->id, [
                    'gradiente_autonomia'  => $data['gradiente_autonomia'],
                    'puntuacion_conquista' => $data['puntuacion_conquista'],
                    'intentos'             => DB::raw('intentos + 1'),
                    'fecha_conquista'      => now(),
                ]);
            } else {
                $perfil->situacionesConquistadas()->attach($sc->id, [
                    'gradiente_autonomia'  => $data['gradiente_autonomia'],
                    'puntuacion_conquista' => $data['puntuacion_conquista'],
                    'intentos'             => 1,
                    'fecha_conquista'      => now(),
                ]);
            }

            // Recalcular calificación actual del perfil
            // (lógica completa en Unidad 7; aquí usamos media ponderada simple)
            $nuevaCalificacion = $perfil->situacionesConquistadas()
                ->avg('perfil_situacion.puntuacion_conquista');

            $perfil->update(['calificacion_actual' => round($nuevaCalificacion, 2)]);
        });

        return response()->json([
            'data' => [
                'perfil_habilitacion_id' => $perfil->id,
                'sc_codigo'              => $sc->codigo,
                'gradiente_autonomia'    => $data['gradiente_autonomia'],
                'puntuacion_conquista'   => $data['puntuacion_conquista'],
                'mensaje'                => "SC {$sc->codigo} registrada correctamente.",
            ],
            'meta' => [
                'version'   => '1.0',
                'timestamp' => now()->toIso8601String(),
            ],
        ], 201);
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
