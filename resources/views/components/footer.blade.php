<footer class="bg-white border-t border-gray-200">
    <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col sm:flex-row justify-between items-center text-sm text-gray-600">
        <div class="text-center sm:text-left">
            © {{ now()->year }} VFDS — Backend EAC. Todos los derechos reservados.
        </div>

        <div class="mt-3 sm:mt-0 flex items-center space-x-4">
            <a href="{{ route('publico.portada') }}" class="text-vfds-primary hover:underline">Inicio</a>
            <a href="{{ route('publico.modulos.index') }}" class="text-vfds-primary hover:underline">Catálogo</a>
            <a href="/legal" class="text-vfds-primary hover:underline">Aviso legal</a>
        </div>
    </div>
</footer>
