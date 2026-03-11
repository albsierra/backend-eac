@extends('layouts.eac')
@section('title', $ecosistema->nombre)

@section('content')
<div class="max-w-5xl mx-auto px-4 py-10">

    <div class="mb-6">
        <a href="{{ route('publico.modulos.show', $ecosistema->modulo) }}"
           class="text-sm text-gray-400 hover:text-vfds-primary">
            ← {{ $ecosistema->modulo->nombre }}
        </a>
    </div>

    <div class="card mb-8">
        <span class="font-mono text-vfds-secondary text-sm font-semibold">
            {{ $ecosistema->codigo }}
        </span>
        <h1 class="text-2xl font-bold text-vfds-primary mt-1">{{ $ecosistema->nombre }}</h1>
        @if($ecosistema->descripcion)
            <p class="text-gray-600 mt-3 text-sm leading-relaxed">{{ $ecosistema->descripcion }}</p>
        @endif
    </div>

    <section>
        <h2 class="text-lg font-bold text-vfds-primary mb-4">
            Situaciones de Competencia
            <span class="text-base font-normal text-gray-400">
                ({{ $ecosistema->situacionesCompetencia->count() }} en total)
            </span>
        </h2>
        <div class="space-y-3">
            @foreach($ecosistema->situacionesCompetencia->sortBy('codigo') as $sc)
                <div class="card">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <span class="font-mono text-sm font-bold text-vfds-accent">
                                    {{ $sc->codigo }}
                                </span>
                                <span class="text-sm font-semibold text-gray-800">
                                    {{ $sc->titulo }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                                {{ $sc->descripcion }}
                            </p>
                            @if($sc->prerequisitos->count() > 0)
                                <p class="text-xs text-gray-400 mt-2">
                                    Requiere:
                                    @foreach($sc->prerequisitos as $pre)
                                        <span class="font-mono text-vfds-secondary">
                                            {{ $pre->codigo }}
                                        </span>{{ !$loop->last ? ',' : '' }}
                                    @endforeach
                                </p>
                            @endif
                        </div>
                        <div class="shrink-0 text-right">
                            <span class="text-xs text-gray-400 block">Umbral</span>
                            <span class="text-sm font-bold text-vfds-primary">
                                {{ $sc->umbral_maestria }}%
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

</div>
@endsection
