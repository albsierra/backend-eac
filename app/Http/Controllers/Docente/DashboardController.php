<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(): View
    {
        $docenteRoleId = Role::where('name', 'docente')->value('id');

        $ecosistemas = auth()->user()
            ->userRoles()
            ->where('role_id', $docenteRoleId)
            ->with([
                'ecosistemaLaboral.modulo',
                'ecosistemaLaboral.situacionesCompetencia',
                'ecosistemaLaboral.perfilesHabilitacion',
            ])
            ->get()
            ->pluck('ecosistemaLaboral')
            ->filter();

        return view('docente.dashboard', compact('ecosistemas'));
    }
}
