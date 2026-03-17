<?php

namespace App\Http\Controllers\Api\V1\Docente;

use App\Http\Controllers\Controller;
use App\Models\EcosistemaLaboral;
use App\Models\PerfilHabilitacion;
use App\Services\CalificacionService;
use Illuminate\Http\JsonResponse;

class CalificacionController extends Controller
{
    public function __construct(
        private readonly CalificacionService $calificacionService,
    ) {}

    /**
     * GET /api/v1/docente/ecosistemas/{ecosistema}/calificacion/{estudiante_id}
     * Desglose completo de calificación de un estudiante.
     */
    public function __invoke(EcosistemaLaboral $ecosistema, int $estudianteId): JsonResponse
    {
        $this->autorizarDocente($ecosistema);

        $perfil = PerfilHabilitacion::where('estudiante_id', $estudianteId)
            ->where('ecosistema_laboral_id', $ecosistema->id)
            ->firstOrFail();

        $desglose = $this->calificacionService->desglose($perfil);

        return response()->json([
            'data' => [
                'estudiante_id'      => $estudianteId,
                'ecosistema_id'      => $ecosistema->id,
                'calificacion_total' => $desglose['calificacion_total'],
                'desglose_ra'        => $desglose['desglose_ra'],
            ],
            'meta' => [
                'version'   => '1.0',
                'timestamp' => now()->toIso8601String(),
            ],
        ]);
    }

    private function autorizarDocente(EcosistemaLaboral $ecosistema): void
    {
        abort_unless(
            auth()->user()
                ->userRoles()
                ->where('ecosistema_laboral_id', $ecosistema->id)
                ->where('name', 'docente')
                ->exists(),
            403
        );
    }
}
