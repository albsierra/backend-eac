<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FamiliaProfesional extends Model
{
    protected $fillable = ['nombre', 'codigo', 'descripcion'];
    protected $table = 'familias_profesionales';

    public function ciclosFormativos(): HasMany
    {
        return $this->hasMany(CicloFormativo::class);
    }
}
