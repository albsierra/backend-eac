<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PerfilHabilitacion extends Model
{
    protected $fillable = [
        'estudiante_id', 'ecosistema_laboral_id', 'calificacion_actual',
    ];
    protected $table = 'perfiles_habilitacion';

    protected $casts = ['calificacion_actual' => 'decimal:2'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function ecosistemaLaboral(): BelongsTo
    {
        return $this->belongsTo(EcosistemaLaboral::class);
    }

    // SCs conquistadas por este estudiante en este ecosistema
    public function situacionesConquistadas(): BelongsToMany
    {
        return $this->belongsToMany(
            SituacionCompetencia::class,
            'perfil_situacion',
            'perfil_habilitacion_id',
            'situacion_competencia_id'
        )->withPivot([
            'gradiente_autonomia',
            'puntuacion_conquista',
            'intentos',
            'fecha_conquista',
        ]);
    }

    // Códigos de SCs conquistadas (útil para el motor ZDP)
    public function codigosConquistados(): array
    {
        return $this->situacionesConquistadas()
                    ->pluck('codigo')
                    ->toArray();
    }
}
