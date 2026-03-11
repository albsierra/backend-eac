<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CicloFormativo extends Model
{
    protected $fillable = ['familia_profesional_id', 'nombre', 'codigo', 'grado', 'descripcion'];
    protected $table = 'ciclos_formativos';

    public function familiaProfesional(): BelongsTo
    {
        return $this->belongsTo(FamiliaProfesional::class);
    }

    public function modulos(): HasMany
    {
        return $this->hasMany(Modulo::class);
    }
}
