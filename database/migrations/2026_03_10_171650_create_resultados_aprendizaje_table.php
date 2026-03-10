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
        Schema::create('resultados_aprendizaje', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ecosistema_laboral_id')
                ->constrained('ecosistemas_laborales')
                ->cascadeOnDelete();
            $table->string('codigo', 20);             // Ej: "RA1", "RA2"
            $table->text('descripcion');
            $table->decimal('peso_porcentaje', 5, 2)->default(0);
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();

            $table->unique(['ecosistema_laboral_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('resultados_aprendizaje');
    }
};
