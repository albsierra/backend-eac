<!DOCTYPE html>
<html lang="es" class="h-full bg-vfds-surface">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Backend EAC') — VFDS</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full font-sans text-gray-800 antialiased">

    @include('components.navbar')

    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="rounded-lg bg-green-50 border border-green-200 p-4 text-green-800 text-sm">
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 mt-4">
            <div class="rounded-lg bg-red-50 border border-red-200 p-4 text-red-800 text-sm">
                {{ session('error') }}
            </div>
        </div>
    @endif

    <main class="min-h-screen">
        @yield('content')
    </main>

    @include('components.footer')
    @stack('scripts')
</body>
</html>
