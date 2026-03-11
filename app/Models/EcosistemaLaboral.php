<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EcosistemaLaboral extends Model
{
    protected $fillable = [
        'modulo_id', 'nombre', 'codigo', 'descripcion', 'activo',
    ];

    protected $table = 'ecosistemas_laborales';

    protected $casts = ['activo' => 'boolean'];

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class);
    }

    public function situacionesCompetencia(): HasMany
    {
        return $this->hasMany(SituacionCompetencia::class);
    }

    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    public function perfilesHabilitacion(): HasMany
    {
        return $this->hasMany(PerfilHabilitacion::class);
    }
}
