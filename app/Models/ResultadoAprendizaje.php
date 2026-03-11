<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ResultadoAprendizaje extends Model
{
    protected $fillable = [
        'modulo_id', 'codigo', 'descripcion',
    ];
    protected $table = 'resultados_aprendizaje';

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function criteriosEvaluacion(): HasMany
    {
        return $this->hasMany(CriterioEvaluacion::class);
    }
}
