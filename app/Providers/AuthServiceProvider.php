<?php

namespace App\Providers;

use App\Auth\VerifierGuard;
use App\Services\VerifierJwksService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
     /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Auth::extend('verifier', function ($app, $name, array $config) {
            return new VerifierGuard(
                Auth::createUserProvider($config['provider']),
                $app->make(\Illuminate\Http\Request::class),
                $app->make(VerifierJwksService::class)
            );
        });
    }
}
