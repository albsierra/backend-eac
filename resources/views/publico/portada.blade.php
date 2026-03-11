@extends('layouts.eac')
@section('title', 'Inicio')

@section('content')
<div class="bg-vfds-primary">
    <div class="max-w-7xl mx-auto px-4 py-20 text-center">
        <h1 class="text-4xl font-bold text-white leading-tight">
            Vocational Federated<br>
            <span class="text-vfds-accent">Data Space</span>
        </h1>
        <p class="mt-4 text-lg text-gray-300 max-w-2xl mx-auto">
            Espacio competencial para la Formación Profesional.
            Consulta los módulos disponibles y los ecosistemas de aprendizaje activos.
        </p>
        <div class="mt-8 flex justify-center gap-4">
            <a href="{{ route('publico.modulos.index') }}" class="btn-primary text-base px-6 py-3">
                Ver catálogo de módulos
            </a>
        </div>
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 py-16">
    <h2 class="text-2xl font-bold text-vfds-primary mb-8">Módulos con ecosistema activo</h2>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($modulos as $modulo)
            <a href="{{ route('publico.modulos.show', $modulo) }}"
               class="card hover:shadow-md transition-shadow group">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-xs font-mono text-vfds-secondary font-semibold">
                        {{ $modulo->codigo }}
                    </span>
                    <span class="text-xs text-gray-400">
                        {{ ucfirst($modulo->cicloFormativo->grado) }}
                    </span>
                </div>
                <h3 class="text-base font-semibold text-gray-800 group-hover:text-vfds-primary
                           transition-colors leading-snug">
                    {{ $modulo->nombre }}
                </h3>
                <p class="text-xs text-gray-500 mt-1">
                    {{ $modulo->cicloFormativo->familiaProfesional->nombre }}
                    · {{ $modulo->cicloFormativo->nombre }}
                </p>
                <div class="mt-4 flex items-center justify-between">
                    <span class="text-xs text-gray-400">
                        {{ $modulo->ecosistemasLaborales->count() }} ecosistema(s) activo(s)
                    </span>
                    <span class="text-vfds-accent text-sm">→</span>
                </div>
            </a>
        @endforeach
    </div>
</div>
@endsection
