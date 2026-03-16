@extends('layouts.estudiante')

@section('title', $modulo->nombre)

@section('content')

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('estudiante.dashboard') }}" class="hover:text-gray-700">Mi espacio</a>
        <span class="mx-2">›</span>
        <span class="text-gray-900">{{ $modulo->nombre }}</span>
    </nav>

    {{-- Cabecera del módulo --}}
    <div class="mb-8 flex items-start justify-between flex-wrap gap-4">
        <div>
            <p class="font-mono text-xs text-gray-400">{{ $modulo->codigo }}</p>
            <h1 class="text-2xl font-bold text-gray-900 mt-0.5">{{ $modulo->nombre }}</h1>
            @if($perfil)
                <p class="text-sm text-gray-500 mt-1">
                    Calificación actual:
                    <span class="font-semibold text-vfds-primary">
                        {{ number_format($perfil->calificacion_actual, 2) }}
                    </span>
                </p>
            @endif
        </div>
        <a href="{{ route('publico.modulos.show', $modulo) }}"
           class="text-sm text-gray-400 hover:text-gray-600 underline">
            Ver detalle del módulo
        </a>
    </div>

    {{-- Baner de recomendación (solo si la ZDP no está vacía) --}}
    @if($recomendacion)
        <div class="bg-vfds-primary/5 border border-vfds-primary/20 rounded-xl px-5 py-4 mb-8
                    flex items-start gap-4">
            <div class="flex-shrink-0 w-9 h-9 rounded-full bg-vfds-primary/10 flex items-center
                        justify-center text-vfds-primary font-bold text-sm">
                →
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs font-semibold uppercase tracking-wide text-vfds-primary mb-0.5">
                    Recomendación
                </p>
                <p class="text-sm font-medium text-gray-800">
                    <span class="font-mono text-vfds-primary mr-2">{{ $recomendacion->codigo }}</span>
                    {{ $recomendacion->titulo }}
                </p>
                <p class="text-xs text-gray-500 mt-1">
                    Nivel de complejidad {{ $recomendacion->nivel_complejidad }}/5
                    @if($recomendacion->prerequisitos->isEmpty())
                        · Sin prerequisitos
                    @else
                        · Requiere: {{ $recomendacion->prerequisitos->pluck('codigo')->join(', ') }}
                    @endif
                </p>
            </div>
        </div>
    @elseif($clasificacion['zdp']->isEmpty() && $clasificacion['bloqueadas']->isEmpty())
        <div class="bg-green-50 border border-green-200 rounded-xl px-5 py-4 mb-8 flex items-center gap-3">
            <span class="text-green-500 text-xl">✓</span>
            <p class="text-green-700 text-sm font-medium">
                Has completado todas las situaciones de competencia de este ecosistema.
            </p>
        </div>
    @endif

    {{-- Leyenda de estados --}}
    <div class="flex flex-wrap gap-3 mb-6 text-xs">
        @foreach([
            ['bg-green-100 text-green-700',             'Conquistadas',  $clasificacion['conquistadas']->count()],
            ['bg-vfds-primary/10 text-vfds-primary',    'Disponibles',   $clasificacion['zdp']->count()],
            ['bg-gray-100 text-gray-400',               'Bloqueadas',    $clasificacion['bloqueadas']->count()],
        ] as [$cls, $label, $count])
            <span class="flex items-center gap-1.5 px-3 py-1 rounded-full {{ $cls }}">
                {{ $label }}
                <span class="font-bold">{{ $count }}</span>
            </span>
        @endforeach
    </div>

    {{-- Grupos de SCs --}}
    @foreach([
        'zdp'          => ['Disponibles',  'border-vfds-primary/30 bg-vfds-primary/5'],
        'conquistadas' => ['Conquistadas', 'border-green-200 bg-green-50'],
        'bloqueadas'   => ['Bloqueadas',   'border-gray-200 bg-gray-50'],
    ] as $grupo => [$label, $estilos])

        @php $items = $clasificacion[$grupo]; @endphp

        @if($items->isNotEmpty())
            <section class="mb-8">
                <h2 class="text-sm font-semibold uppercase tracking-wide text-gray-500 mb-3">
                    {{ $label }} ({{ $items->count() }})
                </h2>

                <div class="space-y-2">
                    @foreach($items as $sc)
                        <div class="border {{ $estilos }} rounded-xl p-4
                                    flex items-start gap-3
                                    {{ $recomendacion?->id === $sc->id ? 'ring-2 ring-vfds-primary' : '' }}">

                            {{-- Código SC --}}
                            <span class="font-mono text-xs px-2 py-0.5 rounded
                                         bg-white border border-gray-200 text-gray-600
                                         flex-shrink-0 mt-0.5">
                                {{ $sc->codigo }}
                            </span>

                            {{-- Título, nodos y prerequisitos faltantes --}}
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-gray-800">
                                    {{ $sc->titulo }}
                                    @if($recomendacion?->id === $sc->id)
                                        <span class="ml-2 text-xs font-normal text-vfds-primary">
                                            ← recomendada
                                        </span>
                                    @endif
                                </p>

                                {{-- Nodos de requisito --}}
                                @if($sc->nodosRequisito->isNotEmpty())
                                    <div class="mt-2 flex flex-wrap gap-1">
                                        @foreach($sc->nodosRequisito as $nodo)
                                            <span class="text-xs bg-white border border-gray-200
                                                         rounded px-2 py-0.5 text-gray-500">
                                                {{ ucfirst($nodo->tipo) }}: {{ Str::limit($nodo->descripcion, 50) }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Prerequisitos pendientes (solo bloqueadas) --}}
                                @if($grupo === 'bloqueadas')
                                    @php
                                        $pendientes = $sc->prerequisitos
                                            ->pluck('codigo')
                                            ->filter(fn($c) => !in_array($c, $codigosConquistados));
                                    @endphp
                                    <p class="text-xs text-gray-400 mt-1">
                                        Pendiente de conquistar:
                                        <span class="font-medium text-gray-600">
                                            {{ $pendientes->join(', ') }}
                                        </span>
                                    </p>
                                @endif
                            </div>

                            {{-- Indicador de complejidad --}}
                            <div class="flex flex-col items-end gap-2 flex-shrink-0">
                                <div class="flex items-center gap-0.5">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="w-1.5 h-1.5 rounded-full
                                            {{ $i <= $sc->nivel_complejidad
                                                ? 'bg-vfds-primary'
                                                : 'bg-gray-200' }}">
                                        </span>
                                    @endfor
                                </div>

                                {{-- Badge de gradiente (solo conquistadas) --}}
                                @if($grupo === 'conquistadas')
                                    @php
                                        $pivot = $perfil->situacionesConquistadas
                                            ->firstWhere('codigo', $sc->codigo)?->pivot;
                                    @endphp
                                    <x-gradiente-badge
                                        :codigo="$sc->codigo"
                                        :gradiente="$pivot?->gradiente_autonomia"
                                    />
                                @endif
                            </div>

                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    @endforeach

@endsection
