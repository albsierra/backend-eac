<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;

class PerfilSituacion extends Pivot
{
    protected $table = 'perfil_situacion';

    protected $fillable = [
        'perfil_habilitacion_id', 'situacion_competencia_id',
        'gradiente_autonomia', 'puntuacion_conquista', 'intentos', 'fecha_conquista',
    ];

    protected $casts = [
        'gradiente_autonomia' => 'decimal:2',
        'puntuacion_conquista' => 'decimal:2',
        'fecha_conquista' => 'datetime',
    ];

    public function perfilHabilitacion()
    {
        return $this->belongsTo(PerfilHabilitacion::class);
    }
}
