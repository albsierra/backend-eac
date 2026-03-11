@extends('layouts.eac')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="flex gap-8">

        <aside class="w-64 shrink-0">
            <nav class="card space-y-1">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">
                    Mi docencia
                </p>
                <a href="{{ route('docente.dashboard') }}"
                   class="flex items-center px-3 py-2 rounded-lg text-sm
                          {{ request()->routeIs('docente.dashboard') ? 'bg-vfds-surface text-vfds-primary font-semibold' : 'text-gray-600 hover:bg-gray-50' }}">
                    Panel general
                </a>
            </nav>
        </aside>

        <div class="flex-1 min-w-0">
            @yield('panel')
        </div>
    </div>
</div>
@endsection
