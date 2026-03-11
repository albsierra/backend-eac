<nav class="bg-vfds-primary shadow-md">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16 items-center">

            <div class="flex items-center space-x-3">
                <a href="{{ route('publico.portada') }}" class="flex items-center space-x-2">
                    <span class="text-vfds-accent font-bold text-xl tracking-tight">VFDS</span>
                    <span class="text-white text-sm font-light hidden sm:block">Backend EAC</span>
                </a>
            </div>

            <div class="hidden md:flex items-center space-x-6">
                <a href="{{ route('publico.modulos.index') }}"
                   class="text-gray-300 hover:text-white text-sm transition-colors
                          {{ request()->routeIs('publico.modulos*') ? 'text-white font-semibold' : '' }}">
                    Catálogo
                </a>
                @auth
                    @role('estudiante')
                        <a href="{{ route('estudiante.dashboard') }}"
                           class="text-gray-300 hover:text-white text-sm transition-colors
                                  {{ request()->routeIs('estudiante.*') ? 'text-white font-semibold' : '' }}">
                            Mi espacio
                        </a>
                    @endrole
                    @role('docente')
                        <a href="{{ route('docente.dashboard') }}"
                           class="text-gray-300 hover:text-white text-sm transition-colors
                                  {{ request()->routeIs('docente.*') ? 'text-white font-semibold' : '' }}">
                            Mi docencia
                        </a>
                    @endrole
                @endauth
            </div>

            <div class="flex items-center space-x-3">
                @guest
                    <a href="{{ route('login') }}" class="text-gray-300 hover:text-white text-sm">
                        Entrar
                    </a>
                @endguest
                @auth
                    <span class="text-gray-300 text-sm hidden sm:block">
                        {{ auth()->user()->name }}
                    </span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                                class="text-gray-300 hover:text-white text-sm transition-colors">
                            Salir
                        </button>
                    </form>
                @endauth
            </div>

        </div>
    </div>
</nav>
