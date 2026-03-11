<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Modulo extends Model
{
    protected $fillable = [
        'ciclo_formativo_id', 'nombre', 'codigo', 'horas_totales', 'descripcion',
    ];

    public function cicloFormativo(): BelongsTo
    {
        return $this->belongsTo(CicloFormativo::class);
    }

    public function ecosistemasLaborales(): HasMany
    {
        return $this->hasMany(EcosistemaLaboral::class);
    }

    public function resultadosAprendizaje(): HasMany
    {
        return $this->hasMany(ResultadoAprendizaje::class);
    }
}
