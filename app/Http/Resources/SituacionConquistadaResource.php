<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SituacionConquistadaResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'codigo'             => $this->codigo,
            'titulo'             => $this->titulo,
            'gradiente_autonomia' => $this->pivot->gradiente_autonomia,
            'puntuacion_conquista' => (float) $this->pivot->puntuacion_conquista,
            'intentos'           => $this->pivot->intentos,
            'fecha_conquista'    => $this->pivot->fecha_conquista,
        ];
    }
}
