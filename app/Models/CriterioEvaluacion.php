<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CriterioEvaluacion extends Model
{
    protected $fillable = [
        'resultado_aprendizaje_id', 'codigo', 'descripcion',
    ];

    protected $table = 'criterios_evaluacion';

    public function resultadoAprendizaje(): BelongsTo
    {
        return $this->belongsTo(ResultadoAprendizaje::class);
    }

    // Un CE puede ser cubierto por varias SCs
    public function situacionesCompetencia(): BelongsToMany
    {
        return $this->belongsToMany(
            SituacionCompetencia::class,
            'sc_criterios_evaluacion',
            'criterio_evaluacion_id',
            'situacion_competencia_id'
        )->withPivot('peso_en_sc');
    }
}
