<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\EcosistemaLaboral;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ProgresoController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EcosistemaLaboral $ecosistema): View
    {
        $esDocente = auth()->user()
            ->userRoles()
            ->where('ecosistema_laboral_id', $ecosistema->id)
            ->whereHas('role', fn($q) => $q->where('name', 'docente'))
            ->exists();

        abort_unless($esDocente, 403);

        $ecosistema->load(['situacionesCompetencia', 'modulo']);

        $perfiles = $ecosistema->perfilesHabilitacion()
            ->with(['estudiante', 'situacionesConquistadas'])
            ->get();

        return view('docente.progreso.show', compact('ecosistema', 'perfiles'));
    }
}
