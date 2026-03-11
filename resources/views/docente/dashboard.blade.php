@extends('layouts.docente')
@section('title', 'Mi docencia')

@section('panel')
<div class="space-y-6">

    <div>
        <h1 class="text-xl font-bold text-vfds-primary">
            Hola, {{ auth()->user()->name }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">Ecosistemas que gestionas</p>
    </div>

    @forelse($ecosistemas as $ecosistema)
        <div class="card">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <span class="font-mono text-xs text-vfds-secondary font-semibold">
                        {{ $ecosistema->codigo }}
                    </span>
                    <h2 class="text-base font-bold text-vfds-primary mt-0.5">
                        {{ $ecosistema->nombre }}
                    </h2>
                    <p class="text-xs text-gray-400">
                        {{ $ecosistema->modulo->codigo }} · {{ $ecosistema->modulo->nombre }}
                    </p>
                </div>
            </div>

            @php
                $totalScs         = $ecosistema->situacionesCompetencia->count();
                $totalEstudiantes = $ecosistema->perfilesHabilitacion->count();
            @endphp

            <div class="flex gap-6 text-sm text-gray-600 mt-2">
                <span><strong class="text-vfds-primary">{{ $totalScs }}</strong> SCs definidas</span>
                <span><strong class="text-vfds-primary">{{ $totalEstudiantes }}</strong> estudiantes</span>
            </div>

            <div class="mt-4 flex gap-3">
                <a href="{{ route('docente.ecosistemas.show', $ecosistema) }}"
                   class="btn-primary text-xs">
                    Gestionar ecosistema
                </a>
                <a href="{{ route('docente.progreso.show', $ecosistema) }}"
                   class="btn-secondary text-xs">
                    Ver progreso del grupo
                </a>
            </div>
        </div>
    @empty
        <div class="card text-center py-12">
            <p class="text-gray-400">No tienes ecosistemas asignados.</p>
        </div>
    @endforelse

</div>
@endsection
