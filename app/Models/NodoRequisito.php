<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NodoRequisito extends Model
{
    protected $fillable = [
        'situacion_competencia_id', 'tipo', 'descripcion', 'orden',
    ];
    protected $table = 'nodos_requisito';

    public function situacionCompetencia(): BelongsTo
    {
        return $this->belongsTo(SituacionCompetencia::class);
    }
}
