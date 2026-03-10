<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('situaciones_competencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecosistema_laboral_id')
                ->constrained('ecosistemas_laborales')
                ->cascadeOnDelete();
            $table->string('codigo', 20);             // Ej: "SC-01"
            $table->string('titulo');
            $table->text('descripcion');

            // Umbral de Maestría: porcentaje mínimo para considerar la SC conquistada
            $table->decimal('umbral_maestria', 5, 2)->default(80.00);

            // Complejidad relativa (para ordenación y ZDP)
            $table->unsignedTinyInteger('nivel_complejidad')->default(1); // 1-5

            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->unique(['ecosistema_laboral_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('situaciones_competencia');
    }
};
