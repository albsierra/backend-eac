@extends('layouts.app')
@section('title', $modulo->nombre)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    <div class="mb-6">
        <a href="{{ route('publico.modulos.index') }}"
           class="text-sm text-gray-400 hover:text-vfds-primary">← Catálogo</a>
    </div>

    <div class="card mb-8">
        <div>
            <span class="font-mono text-vfds-secondary text-sm font-semibold">
                {{ $modulo->codigo }}
            </span>
            <h1 class="text-2xl font-bold text-vfds-primary mt-1">{{ $modulo->nombre }}</h1>
            <p class="text-gray-500 text-sm mt-1">
                {{ $modulo->cicloFormativo->familiaProfesional->nombre }}
                · {{ $modulo->cicloFormativo->nombre }}
                ({{ ucfirst($modulo->cicloFormativo->grado) }})
                · {{ $modulo->horas_totales }} horas
            </p>
            @if($modulo->descripcion)
                <p class="text-gray-600 mt-3 text-sm leading-relaxed">
                    {{ $modulo->descripcion }}
                </p>
            @endif
        </div>
    </div>

    {{-- Resultados de Aprendizaje (del módulo, no del ecosistema) --}}
    <section class="mb-8">
        <h2 class="text-lg font-bold text-vfds-primary mb-4">Resultados de Aprendizaje</h2>
        <div class="space-y-4">
            @foreach($modulo->resultadosAprendizaje->sortBy('orden') as $ra)
                <div class="card">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="font-mono text-sm font-bold text-vfds-accent">
                            {{ $ra->codigo }}
                        </span>
                        <span class="text-sm font-semibold text-gray-700">
                            {{ $ra->descripcion }}
                        </span>
                        <span class="ml-auto text-xs text-gray-400">
                            {{ $ra->peso_porcentaje }}%
                        </span>
                    </div>
                    <ul class="mt-2 space-y-1 pl-4 border-l-2 border-vfds-surface">
                        @foreach($ra->criteriosEvaluacion->sortBy('orden') as $ce)
                            <li class="text-xs text-gray-600">
                                <span class="font-mono text-vfds-secondary font-semibold">
                                    {{ $ce->codigo }}
                                </span>
                                · {{ $ce->descripcion }}
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Ecosistemas activos del módulo --}}
    <section>
        <h2 class="text-lg font-bold text-vfds-primary mb-4">
            Ecosistemas de aprendizaje disponibles
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            @foreach($modulo->ecosistemasLaborales->where('activo', true) as $eco)
                <a href="{{ route('publico.ecosistemas.show', $eco) }}"
                   class="card hover:shadow-md transition-shadow group">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-mono text-sm text-vfds-secondary font-semibold">
                            {{ $eco->codigo }}
                        </span>
                        <span class="text-xs text-green-600 font-semibold">Activo</span>
                    </div>
                    <p class="text-sm font-semibold text-gray-800 group-hover:text-vfds-primary">
                        {{ $eco->nombre }}
                    </p>
                    <p class="text-xs text-gray-400 mt-2">
                        {{ $eco->situacionesCompetencia->count() }} situaciones de competencia
                    </p>
                </a>
            @endforeach
        </div>
    </section>

</div>
@endsection
