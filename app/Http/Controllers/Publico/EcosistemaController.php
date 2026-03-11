<?php

namespace App\Http\Controllers\Publico;

use App\Http\Controllers\Controller;
use App\Models\EcosistemaLaboral;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class EcosistemaController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(EcosistemaLaboral $ecosistema): View
    {
        $ecosistema->load([
            'modulo.cicloFormativo.familiaProfesional',
            'situacionesCompetencia.prerequisitos',
        ]);

        return view('publico.ecosistemas.show', compact('ecosistema'));
    }
}
