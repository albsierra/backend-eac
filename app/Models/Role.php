<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Role extends Model
{
    protected $fillable = ['name', 'description'];

    public function ecosistemaLaboral() : BelongsTo
    {
        return $this->belongsTo(EcosistemaLaboral::class, 'ecosistema_laboral_id');
    }
}
