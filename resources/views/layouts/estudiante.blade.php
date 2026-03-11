@extends('layouts.app')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex gap-8">

        <aside class="w-64 shrink-0">
            <nav class="card space-y-1">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                    Mi espacio
                </p>
                <a href="{{ route('estudiante.dashboard') }}"
                   class="flex items-center px-3 py-2 rounded-lg text-sm
                          {{ request()->routeIs('estudiante.dashboard') ? 'bg-vfds-surface text-vfds-primary font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    Panel general
                </a>
                <a href="{{ route('estudiante.modulos.index') }}"
                   class="flex items-center px-3 py-2 rounded-lg text-sm
                          {{ request()->routeIs('estudiante.modulos*') ? 'bg-vfds-surface text-vfds-primary font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    Mis módulos
                </a>
            </nav>
        </aside>

        <div class="flex-1 min-w-0">
            @yield('panel')
        </div>
    </div>
</div>
@endsection
