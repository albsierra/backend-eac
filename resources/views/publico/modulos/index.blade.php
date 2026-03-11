@extends('layouts.eac')
@section('title', 'Catálogo de módulos')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-10">

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-vfds-primary">Catálogo de módulos</h1>
            <p class="text-gray-500 text-sm mt-1">
                Módulos formativos con ecosistema de aprendizaje competencial disponible.
            </p>
        </div>
        <form method="GET" action="{{ route('publico.modulos.index') }}"
              class="flex items-center gap-2">
            <select name="familia" onchange="this.form.submit()"
                    class="text-sm border border-gray-300 rounded-lg px-3 py-2
                           focus:ring-vfds-accent focus:border-vfds-accent">
                <option value="">Todas las familias</option>
                @foreach($familias as $familia)
                    <option value="{{ $familia->id }}"
                            {{ request('familia') == $familia->id ? 'selected' : '' }}>
                        {{ $familia->nombre }}
                    </option>
                @endforeach
            </select>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($modulos as $modulo)
            <div class="card flex items-center justify-between">
                <div class="flex-1">
                    <div class="flex items-center gap-3">
                        <span class="font-mono text-sm text-vfds-secondary font-semibold">
                            {{ $modulo->codigo }}
                        </span>
                        <span class="text-base font-semibold text-gray-800">
                            {{ $modulo->nombre }}
                        </span>
                    </div>
                    <p class="text-sm text-gray-500 mt-1">
                        {{ $modulo->cicloFormativo->familiaProfesional->nombre }}
                        · {{ $modulo->cicloFormativo->nombre }}
                        · {{ $modulo->horas_totales }}h
                    </p>
                    <div class="flex flex-wrap gap-2 mt-2">
                        @foreach($modulo->ecosistemasLaborales->where('activo', true) as $eco)
                            <a href="{{ route('publico.ecosistemas.show', $eco) }}"
                               class="text-xs px-2 py-1 rounded-full bg-vfds-surface
                                      text-vfds-primary hover:bg-vfds-secondary hover:text-white
                                      transition-colors border border-vfds-primary/20">
                                {{ $eco->codigo }}
                            </a>
                        @endforeach
                    </div>
                </div>
                <a href="{{ route('publico.modulos.show', $modulo) }}"
                   class="btn-secondary ml-4 shrink-0">
                    Ver ficha
                </a>
            </div>
        @empty
            <p class="text-center text-gray-400 py-12">
                No hay módulos disponibles para los criterios seleccionados.
            </p>
        @endforelse
    </div>

    <div class="mt-6">{{ $modulos->withQueryString()->links() }}</div>
</div>
@endsection
