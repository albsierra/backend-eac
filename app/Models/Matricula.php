<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Matricula extends Model
{
    protected $fillable = ['estudiante_id', 'modulo_id'];

    public function estudiante()
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class);
    }
}
