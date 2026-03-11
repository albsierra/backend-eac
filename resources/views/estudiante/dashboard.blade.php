@extends('layouts.estudiante')
@section('title', 'Mi espacio')

@section('panel')
<div class="space-y-8">

    <div>
        <h1 class="text-xl font-bold text-vfds-primary">
            Bienvenido/a, {{ auth()->user()->name }}
        </h1>
        <p class="text-sm text-gray-500 mt-1">Resumen de tu progreso competencial</p>
    </div>

    @forelse($perfiles as $perfil)
        <div class="card">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <span class="text-xs font-mono text-vfds-secondary font-semibold">
                        {{ $perfil->ecosistemaLaboral->modulo->codigo }}
                    </span>
                    <h2 class="text-base font-bold text-vfds-primary mt-0.5">
                        {{ $perfil->ecosistemaLaboral->nombre }}
                    </h2>
                    <p class="text-xs text-gray-400">
                        {{ $perfil->ecosistemaLaboral->modulo->nombre }}
                    </p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-vfds-primary">
                        {{ number_format($perfil->calificacion_actual, 1) }}
                    </span>
                    <span class="text-xs text-gray-400 block">calificación actual</span>
                </div>
            </div>

            @php
                $total        = $perfil->ecosistemaLaboral->situacionesCompetencia->count();
                $conquistadas = $perfil->situacionesConquistadas->count();
                $pct          = $total > 0 ? round($conquistadas / $total * 100) : 0;
            @endphp
            <div class="flex items-center gap-3">
                <div class="flex-1 bg-gray-100 rounded-full h-2">
                    <div class="bg-vfds-accent h-2 rounded-full transition-all duration-500"
                         style="width: {{ $pct }}%"></div>
                </div>
                <span class="text-xs text-gray-500 whitespace-nowrap">
                    {{ $conquistadas }}/{{ $total }} SCs ({{ $pct }}%)
                </span>
            </div>

            @if($perfil->situacionesConquistadas->count() > 0)
                <div class="mt-4 flex flex-wrap gap-2">
                    @foreach($perfil->situacionesConquistadas->sortBy('codigo') as $sc)
                        <span class="font-mono text-xs px-2 py-1 rounded-full
                                     bg-green-50 text-green-700 border border-green-200
                                     flex items-center gap-1">
                            {{ $sc->codigo }}
                            <x-gradiente-badge :gradiente="$sc->pivot->gradiente_autonomia" />
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="mt-4">
                <a href="{{ route('estudiante.perfil.show', $perfil->id) }}"
                   class="btn-secondary text-xs">
                    Ver detalle →
                </a>
            </div>
        </div>
    @empty
        <div class="card text-center py-12">
            <p class="text-gray-400">Todavía no tienes módulos matriculados.</p>
            <a href="{{ route('publico.modulos.index') }}" class="btn-primary mt-4 inline-flex">
                Explorar catálogo
            </a>
        </div>
    @endforelse

</div>
@endsection
