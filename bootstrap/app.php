<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => \App\Http\Middleware\EnsureUserHasRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        $exceptions->render(function (\Throwable $e, \Illuminate\Http\Request $request) {
            if (! $request->expectsJson() && ! $request->is('api/*')) {
                return null; // Dejar que el handler web actúe
            }

            $status = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 500;

            return response()->json([
                'type'   => "https://backend-eac.test/errors/{$status}",
                'title'  => $e->getMessage() ?: 'Error interno',
                'status' => $status,
            ], $status);
        });
    })
    ->create();
