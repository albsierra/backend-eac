@extends('layouts.docente')

@section('title', 'Ecosistema · ' . $ecosistema->modulo->nombre)

@section('content')

    {{-- Breadcrumb --}}
    <nav class="text-sm text-gray-500 mb-6">
        <a href="{{ route('docente.dashboard') }}" class="hover:text-gray-700">Panel docente</a>
        <span class="mx-2">›</span>
        <span class="text-gray-900">{{ $ecosistema->modulo->nombre }}</span>
    </nav>

    {{-- Cabecera --}}
    <div class="bg-eac-900 text-white rounded-xl px-8 py-6 mb-8">
        <div class="flex items-start justify-between gap-4 flex-wrap">
            <div>
                <p class="text-xs font-mono text-eac-50 opacity-70 mb-1">{{ $ecosistema->codigo }}</p>
                <h1 class="text-2xl font-bold">{{ $ecosistema->nombre }}</h1>
                <p class="text-gray-300 text-sm mt-1">
                    {{ $ecosistema->modulo->cicloFormativo->familiaProfesional->nombre }}
                    · {{ $ecosistema->modulo->cicloFormativo->nombre }}
                    · {{ $ecosistema->modulo->codigo }}
                </p>
                <a href="{{ route('docente.ecosistemas.analytics', $ecosistema) }}"
                    class="btn btn-outline-primary btn-sm">
                        📊 Ver analítica
                </a>
            </div>
            <div class="flex gap-6 text-center">
                <div>
                    <p class="text-3xl font-bold">{{ $ecosistema->situacionesCompetencia->count() }}</p>
                    <p class="text-xs text-gray-400">SCs</p>
                </div>
                <div>
                    <p class="text-3xl font-bold">{{ $totalEstudiantes }}</p>
                    <p class="text-xs text-gray-400">Estudiantes</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Columna izquierda: Grafo de SCs con cobertura del grupo --}}
        <div class="lg:col-span-2 space-y-6">

            <section>
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-gray-900">Situaciones de Competencia</h2>
                    <a href="{{ route('docente.progreso.show', $ecosistema) }}"
                       class="text-sm text-eac-500 hover:text-eac-700 underline">
                        Ver progreso del grupo →
                    </a>
                </div>

                <div class="space-y-3">
                    @foreach($ecosistema->situacionesCompetencia->sortBy('nivel_complejidad') as $sc)
                        @php
                            $conquistadas = $conquistasPorSc[$sc->codigo] ?? 0;
                            $porcentaje   = $totalEstudiantes > 0
                                ? round(($conquistadas / $totalEstudiantes) * 100)
                                : 0;
                        @endphp

                        <div class="border border-gray-200 rounded-xl overflow-hidden">

                            {{-- Cabecera de la SC --}}
                            <div class="flex items-center gap-3 px-4 py-3 bg-gray-50">
                                <span class="font-mono text-xs bg-eac-900 text-white px-2 py-0.5 rounded flex-shrink-0">
                                    {{ $sc->codigo }}
                                </span>
                                <span class="text-sm font-medium text-gray-800 flex-1">
                                    {{ $sc->titulo }}
                                </span>
                                {{-- Nivel de complejidad --}}
                                <div class="flex gap-0.5 flex-shrink-0">
                                    @for($i = 1; $i <= 5; $i++)
                                        <span class="w-1.5 h-1.5 rounded-full {{ $i <= $sc->nivel_complejidad ? 'bg-eac-500' : 'bg-gray-200' }}"></span>
                                    @endfor
                                </div>
                            </div>

                            <div class="px-4 py-3 space-y-3">

                                {{-- Barra de conquista del grupo --}}
                                <div>
                                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                                        <span>Conquistada por el grupo</span>
                                        <span>{{ $conquistadas }} / {{ $totalEstudiantes }} ({{ $porcentaje }}%)</span>
                                    </div>
                                    <div class="w-full bg-gray-100 rounded-full h-1.5">
                                        <div class="bg-eac-500 h-1.5 rounded-full transition-all"
                                             style="width: {{ $porcentaje }}%"></div>
                                    </div>
                                </div>

                                {{-- Prerequisitos --}}
                                @if($sc->prerequisitos->isNotEmpty())
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs text-gray-400">Requiere:</span>
                                        @foreach($sc->prerequisitos as $pre)
                                            <span class="font-mono text-xs bg-yellow-50 border border-yellow-200
                                                         text-yellow-700 px-2 py-0.5 rounded">
                                                {{ $pre->codigo }}
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Criterios de Evaluación cubiertos --}}
                                @if($sc->criteriosEvaluacion->isNotEmpty())
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="text-xs text-gray-400">CE cubiertos:</span>
                                        @foreach($sc->criteriosEvaluacion as $ce)
                                            <span class="font-mono text-xs bg-blue-50 border border-blue-100
                                                         text-blue-600 px-2 py-0.5 rounded"
                                                  title="{{ $ce->descripcion }}">
                                                {{ $ce->codigo }}
                                                <span class="opacity-60">({{ $ce->pivot->peso_en_sc }}%)</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        </div>

        {{-- Columna derecha: trazabilidad RA → CE --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-gray-900">Trazabilidad curricular</h2>

            @foreach($ecosistema->modulo->resultadosAprendizaje as $ra)
                <div class="border border-gray-200 rounded-xl overflow-hidden">
                    <div class="bg-gray-50 px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-xs bg-eac-900 text-white px-2 py-0.5 rounded">
                                {{ $ra->codigo }}
                            </span>
                            <span class="text-xs font-medium text-gray-700 line-clamp-1"
                                  title="{{ $ra->descripcion }}">
                                {{ Str::limit($ra->descripcion, 40) }}
                            </span>
                        </div>
                        <span class="text-xs text-gray-400 flex-shrink-0 ml-2">{{ $ra->peso_porcentaje }}%</span>
                    </div>
                    <ul class="divide-y divide-gray-100">
                        @foreach($ra->criteriosEvaluacion as $ce)
                            <li class="px-4 py-2 flex items-start gap-2">
                                <span class="font-mono text-xs text-gray-400 flex-shrink-0 mt-0.5">
                                    {{ $ce->codigo }}
                                </span>
                                <span class="text-xs text-gray-600">{{ $ce->descripcion }}</span>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>

    </div>

@endsection
