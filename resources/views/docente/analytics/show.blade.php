@extends('layouts.eac')

@section('title', 'Analítica — ' . $ecosistema->nombre)

@section('content')
<div class="container py-4">

    {{-- Cabecera --}}
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <h1 class="h3 mb-0">Analítica del ecosistema</h1>
            <p class="text-muted mb-0">{{ $ecosistema->nombre }}</p>
        </div>
        <a href="{{ route('docente.ecosistemas.show', $ecosistema) }}"
           class="btn btn-outline-secondary btn-sm">
            ← Volver al ecosistema
        </a>
    </div>

    {{-- Tarjetas de resumen --}}
    <div class="row g-3 mb-4">
        <div class="col-sm-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-primary">{{ $totalEstudiantes }}</div>
                    <div class="text-muted small mt-1">Estudiantes matriculados</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-success">{{ $totalConquistas }}</div>
                    <div class="text-muted small mt-1">Conquistas totales</div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <div class="card text-center h-100">
                <div class="card-body">
                    <div class="display-6 fw-bold text-info">{{ $mediaConquistas }}</div>
                    <div class="text-muted small mt-1">Media de SCs por estudiante</div>
                </div>
            </div>
        </div>
    </div>

    {{-- Fila 1: Ranking + Gradiente --}}
    <div class="row g-4 mb-4">
        <div class="col-lg-7">
            <div class="card h-100">
                <div class="card-body">
                    {!! $chartRanking->container() !!}
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card h-100">
                <div class="card-body d-flex align-items-center justify-content-center">
                    {!! $chartGradiente->container() !!}
                </div>
            </div>
        </div>
    </div>

    {{-- Fila 2: Evolución temporal --}}
    <div class="row g-4">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    {!! $chartEvolucion->container() !!}
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@push('scripts')
    {!! $chartRanking->script() !!}
    {!! $chartGradiente->script() !!}
    {!! $chartEvolucion->script() !!}

    {{-- Chart.js — necesario para ConsoleTVs/Charts --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
@endpush
