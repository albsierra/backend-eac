<?php

namespace App\Providers;

use App\Services\CalificacionService;
use App\Services\EACAnalyticsService;
use App\Services\HuellaService;
use App\Services\GrafoService;
use App\Services\VerifierJwksService;
use App\Services\RecomendacionService;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(GrafoService::class);

        $this->app->singleton(RecomendacionService::class, function ($app) {
            return new RecomendacionService($app->make(GrafoService::class));
        });

        $this->app->singleton(CalificacionService::class);
        $this->app->singleton(HuellaService::class);
        $this->app->singleton(EACAnalyticsService::class, function ($app) {
            return new EACAnalyticsService(
                $app->make(CalificacionService::class)
            );
        });
        $this->app->singleton(VerifierJwksService::class);

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
