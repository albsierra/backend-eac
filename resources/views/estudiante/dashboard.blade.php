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
            <div class="bg-white border border-gray-200 rounded-xl p-5 flex items-center gap-6 flex-wrap">

                {{-- Info del módulo --}}
                <div class="flex-1 min-w-[200px]">
                    <p class="font-mono text-xs text-gray-400">
                        {{ $perfil->ecosistemaLaboral->modulo->codigo }}
                    </p>
                    <h3 class="font-semibold text-gray-900 mt-0.5">
                        {{ $perfil->ecosistemaLaboral->modulo->nombre }}
                    </h3>
                    <p class="text-xs text-gray-400 mt-1">
                        {{ $perfil->ecosistemaLaboral->modulo->cicloFormativo->nombre }}
                    </p>
                </div>

                {{-- Barra de progreso --}}
                @php
                    $total        = $perfil->ecosistemaLaboral->situacionesCompetencia->count();
                    $conquistadas = $perfil->situacionesConquistadas->count();
                    $progreso     = $total > 0 ? round(($conquistadas / $total) * 100) : 0;
                @endphp

                <div class="flex-1 min-w-[160px]">
                    <div class="flex justify-between text-xs text-gray-500 mb-1">
                        <span>{{ $conquistadas }} / {{ $total }} SCs</span>
                        <span>{{ $progreso }}%</span>
                    </div>
                    <div class="w-full bg-gray-100 rounded-full h-2">
                        <div class="h-2 rounded-full transition-all
                                    {{ $perfil->completado ? 'bg-green-500' : 'bg-vfds-primary' }}"
                            style="width: {{ $progreso }}%">
                        </div>
                    </div>

                    {{-- Línea de estado: disponibles o completado --}}
                    <p class="text-xs mt-1
                            {{ $perfil->completado ? 'text-green-600 font-medium' : 'text-gray-400' }}">
                        @if($perfil->completado)
                            ✓ Ecosistema completado
                        @elseif($perfil->zdp_count > 0)
                            {{ $perfil->zdp_count }}
                            {{ Str::plural('SC disponible', $perfil->zdp_count) }} ahora
                        @else
                            Sin SCs disponibles en este momento
                        @endif
                    </p>
                </div>

                {{-- Calificación --}}
                <div class="text-center min-w-[60px]">
                    <p class="text-2xl font-bold
                            {{ $perfil->completado ? 'text-green-600' : 'text-vfds-primary' }}">
                        {{ number_format($perfil->calificacion_actual, 1) }}
                    </p>
                    <p class="text-xs text-gray-400">Calificación</p>
                </div>

                {{-- Acción --}}
                <a href="{{ route('estudiante.modulo', $perfil->ecosistemaLaboral->modulo) }}"
                class="bg-vfds-primary hover:bg-vfds-primary/80 text-white text-sm font-medium
                        px-4 py-2 rounded-lg transition whitespace-nowrap">
                    {{ $perfil->completado ? 'Ver resumen' : 'Continuar' }}
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
