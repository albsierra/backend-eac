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
        Schema::create('sc_criterios_evaluacion', function (Blueprint $table) {
            $table->foreignId('situacion_competencia_id')
                ->constrained('situaciones_competencia')
                ->cascadeOnDelete();
            $table->foreignId('criterio_evaluacion_id')
                ->constrained('criterios_evaluacion')
                ->cascadeOnDelete();

            // Peso del CE dentro de la evaluación de esta SC concreta
            $table->decimal('peso_en_sc', 5, 2)->default(0);

            $table->primary(['situacion_competencia_id', 'criterio_evaluacion_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sc_criterios_evaluacion');
    }
};
