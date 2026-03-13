<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\ResourceCollection;

class ModuloCollection extends ResourceCollection
{
    public $collects = ModuloResource::class;

    public function toArray(Request $request): array
    {
        return [
            'data' => $this->collection,
            'meta' => [
                'version'   => '1.0',
                'timestamp' => now()->toIso8601String(),
                'total'     => $this->total(),
                'per_page'  => $this->perPage(),
                'page'      => $this->currentPage(),
            ],
        ];
    }
}
