<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProgresoEstudianteResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $total        = $this->resource['total_scs'];
        $conquistadas = $this->resource['conquistadas'];

        return [
            'estudiante_id'  => $this->resource['estudiante_id'],
            'conquistadas'   => $conquistadas,
            'total_scs'      => $total,
            'progreso_pct'   => $total > 0 ? round(($conquistadas / $total) * 100, 1) : 0,
            'calificacion'   => (float) $this->resource['calificacion'],
            'detalle'        => $this->resource['detalle'],
        ];
    }
}
