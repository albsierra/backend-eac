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
        Schema::create('perfiles_habilitacion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('ecosistema_laboral_id')
                ->constrained('ecosistemas_laborales')
                ->cascadeOnDelete();

            // Calificación calculada dinámicamente (se actualiza en cada conquista)
            $table->decimal('calificacion_actual', 4, 2)->default(0.00);

            $table->timestamps();

            // Un estudiante solo puede tener un perfil por ecosistema
            $table->unique(['estudiante_id', 'ecosistema_laboral_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfiles_habilitacion');
    }
};
