<?php

namespace App\Providers;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Blade::if define una nueva directiva condicional @role(...) usable en vistas
        Blade::if('role', function (string $role): bool {
            // auth()->check() comprueba que hay un usuario autenticado
            // auth()->user()->hasRole($role) reutiliza el helper definido en User
            return auth()->check() && auth()->user()->hasRole($role);
        });
    }
}
