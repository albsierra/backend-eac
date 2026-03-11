@extends('layouts.estudiante')
@section('title', $perfil->ecosistemaLaboral->nombre)

@section('panel')
<div class="space-y-6">

    <div>
        <a href="{{ route('estudiante.dashboard') }}"
           class="text-sm text-gray-400 hover:text-vfds-primary">← Panel</a>
        <h1 class="text-xl font-bold text-vfds-primary mt-2">
            {{ $perfil->ecosistemaLaboral->nombre }}
        </h1>
        <p class="text-xs text-gray-400 mt-0.5">
            Módulo: {{ $perfil->ecosistemaLaboral->modulo->codigo }}
            · {{ $perfil->ecosistemaLaboral->modulo->nombre }}
        </p>
    </div>

    @php $conquistadasIds = $perfil->situacionesConquistadas->pluck('id')->toArray(); @endphp

    <section>
        <h2 class="text-base font-bold text-vfds-primary mb-3">Situaciones de Competencia</h2>
        <div class="space-y-2">
            @foreach($perfil->ecosistemaLaboral->situacionesCompetencia->sortBy('codigo') as $sc)
                @php $estaConquistada = in_array($sc->id, $conquistadasIds); @endphp
                <div class="card flex items-start justify-between gap-4
                            {{ $estaConquistada ? 'border-l-4 border-l-green-400' : '' }}">
                    <div class="flex-1">
                        <div class="flex items-center gap-2">
                            <span class="font-mono text-sm font-bold text-vfds-accent">
                                {{ $sc->codigo }}
                            </span>
                            <span class="text-sm font-semibold text-gray-800">
                                {{ $sc->titulo }}
                            </span>
                        </div>
                        @if($estaConquistada)
                            @php
                                $pivot = $perfil->situacionesConquistadas
                                               ->firstWhere('id', $sc->id)->pivot;
                            @endphp
                            <div class="mt-2 flex items-center gap-3 text-xs text-gray-500">
                                <x-gradiente-badge :gradiente="$pivot->gradiente_autonomia" />
                                <span>
                                    {{ \Carbon\Carbon::parse($pivot->fecha_conquista)->format('d/m/Y') }}
                                </span>
                                <span>Intentos: {{ $pivot->intentos }}</span>
                            </div>
                        @else
                            @if($sc->prerequisitos->count() > 0)
                                <p class="text-xs text-gray-400 mt-1">
                                    Pendiente de:
                                    @foreach($sc->prerequisitos as $pre)
                                        <span class="font-mono
                                            {{ in_array($pre->id, $conquistadasIds) ? 'text-green-600 line-through' : 'text-red-400' }}">
                                            {{ $pre->codigo }}
                                        </span>{{ !$loop->last ? ', ' : '' }}
                                    @endforeach
                                </p>
                            @endif
                        @endif
                    </div>
                    <span class="text-lg shrink-0">{{ $estaConquistada ? '✅' : '⬜' }}</span>
                </div>
            @endforeach
        </div>
    </section>

</div>
@endsection
