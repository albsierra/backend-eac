<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HuellaTalento extends Model
{
    protected $fillable = [
        'estudiante_id', 'ecosistema_laboral_id',
        'payload', 'ngsi_ld_id', 'generada_en',
    ];

    protected $table = 'huellas_talento';

    protected $casts = [
        'payload'      => 'array',
        'generada_en'  => 'datetime',
    ];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(User::class, 'estudiante_id');
    }

    public function ecosistemaLaboral(): BelongsTo
    {
        return $this->belongsTo(EcosistemaLaboral::class);
    }
}
