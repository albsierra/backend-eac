<?php

namespace App\Http\Controllers\Api\V1\Estudiante;

use App\Http\Controllers\Controller;
use App\Models\Modulo;
use App\Models\PerfilHabilitacion;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MatriculaController extends Controller
{
    /**
     * POST /api/v1/estudiante/matriculas
     * Body: { "modulo_id": 1 }
     *
     * Matricula al estudiante autenticado en el módulo indicado
     * y crea su perfil de habilitación en el ecosistema activo.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $request->validate([
            'modulo_id' => ['required', 'integer', 'exists:modulos,id'],
        ]);

        $user   = auth()->user();
        $modulo = Modulo::findOrFail($request->modulo_id);

        if ($user->matriculas()->where('modulo_id', $modulo->id)->exists()) {
            return response()->json([
                'type'   => 'https://backend-eac.test/errors/conflict',
                'title'  => 'Matrícula duplicada',
                'status' => 409,
                'detail' => 'Ya estás matriculado en este módulo.',
            ], 409);
        }

        $ecosistema = $modulo->ecosistemasLaborales()->where('activo', true)->first();

        if (! $ecosistema) {
            return response()->json([
                'type'   => 'https://backend-eac.test/errors/unavailable',
                'title'  => 'Módulo no disponible',
                'status' => 422,
                'detail' => 'Este módulo no tiene un ecosistema activo y no acepta matrículas.',
            ], 422);
        }

        DB::transaction(function () use ($user, $modulo, $ecosistema) {
            $user->matriculas()->create(['modulo_id' => $modulo->id]);

            $rolEstudiante = Role::where('name', 'estudiante')->first();
            if ($rolEstudiante) {
                DB::table('user_roles')->insertOrIgnore([
                    'user_id'               => $user->id,
                    'role_id'               => $rolEstudiante->id,
                    'ecosistema_laboral_id' => $ecosistema->id,
                    'created_at'            => now(),
                    'updated_at'            => now(),
                ]);
            }

            PerfilHabilitacion::firstOrCreate(
                [
                    'estudiante_id'         => $user->id,
                    'ecosistema_laboral_id' => $ecosistema->id,
                ],
                ['calificacion_actual' => 0.00]
            );
        });

        return response()->json([
            'data' => [
                'modulo_id'      => $modulo->id,
                'ecosistema_id'  => $ecosistema->id,
                'mensaje'        => 'Matrícula realizada correctamente.',
            ],
            'meta' => [
                'version'   => '1.0',
                'timestamp' => now()->toIso8601String(),
            ],
        ], 201);
    }
}
