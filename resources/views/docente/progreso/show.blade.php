@extends('layouts.docente')
@section('title', 'Progreso · ' . $ecosistema->nombre)

@section('panel')
<div class="space-y-6">

    <div>
        <a href="{{ route('docente.dashboard') }}"
           class="text-sm text-gray-400 hover:text-vfds-primary">← Mi docencia</a>
        <h1 class="text-xl font-bold text-vfds-primary mt-2">Progreso del grupo</h1>
        <p class="text-sm text-gray-500">
            {{ $ecosistema->nombre }} · <span class="font-mono">{{ $ecosistema->codigo }}</span>
        </p>
    </div>

    <div class="card overflow-x-auto">
        <table class="w-full text-sm">
            <thead>
                <tr class="border-b border-gray-100">
                    <th class="text-left py-3 pr-4 text-gray-500 font-medium">Estudiante</th>
                    @foreach($ecosistema->situacionesCompetencia->sortBy('codigo') as $sc)
                        <th class="py-3 px-2 text-center font-mono text-xs text-vfds-secondary">
                            {{ $sc->codigo }}
                        </th>
                    @endforeach
                    <th class="py-3 pl-4 text-right text-gray-500 font-medium">Calif.</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach($perfiles as $perfil)
                    @php
                        $conquistadasIds = $perfil->situacionesConquistadas->pluck('id')->toArray();
                    @endphp
                    <tr class="hover:bg-vfds-surface/50">
                        <td class="py-3 pr-4 font-medium text-gray-700">
                            {{ $perfil->estudiante->name }}
                        </td>
                        @foreach($ecosistema->situacionesCompetencia->sortBy('codigo') as $sc)
                            <td class="py-3 px-2 text-center">
                                @if(in_array($sc->id, $conquistadasIds))
                                    @php
                                        $g = $perfil->situacionesConquistadas
                                                    ->firstWhere('id', $sc->id)
                                                    ->pivot->gradiente_autonomia;
                                    @endphp
                                    <x-gradiente-badge :gradiente="$g" />
                                @else
                                    <span class="text-gray-200">—</span>
                                @endif
                            </td>
                        @endforeach
                        <td class="py-3 pl-4 text-right font-bold text-vfds-primary">
                            {{ number_format($perfil->calificacion_actual, 1) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

</div>
@endsection
