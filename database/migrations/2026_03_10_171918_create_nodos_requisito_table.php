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
        Schema::create('nodos_requisito', function (Blueprint $table) {
            $table->id();
            $table->foreignId('situacion_competencia_id')
                ->constrained('situaciones_competencia')
                ->cascadeOnDelete();
            $table->enum('tipo', ['conocimiento', 'habilidad']);
            $table->text('descripcion');
            $table->unsignedSmallInteger('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('nodos_requisito');
    }
};
