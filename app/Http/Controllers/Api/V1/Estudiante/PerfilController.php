<?php

namespace App\Http\Controllers\Api\V1\Estudiante;

use App\Http\Controllers\Controller;
use App\Http\Resources\PerfilHabilitacionResource;
use App\Models\EcosistemaLaboral;
use App\Models\PerfilHabilitacion;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PerfilController extends Controller
{
    /**
     * GET /api/v1/estudiante/perfil
     * Lista todos los perfiles del estudiante autenticado (uno por ecosistema matriculado).
     */
    public function index(): AnonymousResourceCollection
    {
        $perfiles = PerfilHabilitacion::where('estudiante_id', auth()->id())
            ->with([
                'ecosistemaLaboral.modulo',
                'situacionesConquistadas',
            ])
            ->get();

        return PerfilHabilitacionResource::collection($perfiles)
            ->additional([
                'meta' => [
                    'version'   => '1.0',
                    'timestamp' => now()->toIso8601String(),
                ],
            ]);
    }

    /**
     * GET /api/v1/estudiante/perfil/{ecosistema}
     * Perfil completo del estudiante autenticado en un ecosistema concreto.
     * Esta respuesta es la que consumirá OrionSyncService en la Unidad 6.
     */
    public function show(EcosistemaLaboral $ecosistema): PerfilHabilitacionResource|JsonResponse
    {
        $perfil = PerfilHabilitacion::where('estudiante_id', auth()->id())
            ->where('ecosistema_laboral_id', $ecosistema->id)
            ->with([
                'ecosistemaLaboral',
                'situacionesConquistadas',
            ])
            ->first();

        if (! $perfil) {
            return response()->json([
                'type'   => 'https://backend-eac.test/errors/not-found',
                'title'  => 'Perfil no encontrado',
                'status' => 404,
                'detail' => 'No tienes perfil de habilitación en este ecosistema. Matricúlate primero.',
            ], 404);
        }

        return new PerfilHabilitacionResource($perfil);
    }
}
