<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\V1;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::prefix('v1')->name('api.v1.')->group(function () {

    // ── Públicos ────────────────────────────────────────────────────────────────
    Route::get('modulos',         [V1\ModuloController::class, 'index'])->name('modulos.index');
    Route::get('modulos/{modulo}', [V1\ModuloController::class, 'show'])->name('modulos.show');

    Route::get('ecosistemas/{ecosistema}',              [V1\EcosistemaController::class, 'show'])
        ->name('ecosistemas.show');
    Route::get('ecosistemas/{ecosistema}/situaciones',  [V1\EcosistemaController::class, 'situaciones'])
        ->name('ecosistemas.situaciones');

    // ── Autenticados (Sanctum) ───────────────────────────────────────────────────
    Route::middleware('auth:verifier,sanctum')->group(function () {

        // Estudiante
        Route::prefix('estudiante')->name('estudiante.')->group(function () {
            Route::get('perfil',                [V1\Estudiante\PerfilController::class, 'index'])
                ->name('perfil.index');
            Route::prefix('perfil/{ecosistema}')->group(function () {
                Route::get('/',   [V1\Estudiante\PerfilController::class, 'show'])
                    ->name('perfil.show');
                Route::get('huellas',  [V1\Estudiante\HuellaController::class, 'index']) ->name('huellas.index');
                Route::get('huella',   [V1\Estudiante\HuellaController::class, 'show'])  ->name('huella.show');
                Route::post('huella',  [V1\Estudiante\HuellaController::class, 'store']) ->name('huella.store');
            });
            Route::post('matriculas',           V1\Estudiante\MatriculaController::class)
                ->name('matriculas.store');
            Route::get('perfil/{ecosistema}/zdp', V1\Estudiante\ZdpController::class)
                ->name('zdp');
        });

        // Docente
        Route::prefix('docente')->name('docente.')->group(function () {
            Route::get('ecosistemas/{ecosistema}/progreso',
                V1\Docente\ProgresoController::class)->name('progreso');
            Route::post('ecosistemas/{ecosistema}/conquistas',
                V1\Docente\ConquistaController::class)->name('conquistas');
            Route::get('ecosistemas/{ecosistema}/calificacion/{estudianteId}',
                V1\Docente\CalificacionController::class
            )->name('calificacion');
        });
    });
});
