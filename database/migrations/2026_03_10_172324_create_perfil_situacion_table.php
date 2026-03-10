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
        Schema::create('perfil_situacion', function (Blueprint $table) {
            $table->foreignId('perfil_habilitacion_id')
                ->constrained('perfiles_habilitacion')
                ->cascadeOnDelete();
            $table->foreignId('situacion_competencia_id')
                ->constrained('situaciones_competencia')
                ->cascadeOnDelete();

            // Gradiente de Autonomía alcanzado
            $table->enum('gradiente_autonomia', [
                'asistido',
                'guiado',
                'supervisado',
                'autonomo',
            ]);

            // Puntuación que obtuvo en la evaluación que conquistó la SC
            $table->decimal('puntuacion_conquista', 5, 2)->nullable();

            // Número de intentos hasta conquistar la SC
            $table->unsignedSmallInteger('intentos')->default(1);

            $table->timestamp('fecha_conquista')->useCurrent();

            $table->primary(['perfil_habilitacion_id', 'situacion_competencia_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfil_situacion');
    }
};
