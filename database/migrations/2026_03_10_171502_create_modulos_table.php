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
        Schema::create('modulos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ciclo_formativo_id')
                ->nullable()
                ->constrained('ciclos_formativos')
                ->cascadeOnDelete();
            $table->string('nombre');
            $table->string('codigo', 20);             // Ej: "0614"
            $table->unsignedSmallInteger('horas_totales')->default(0);
            $table->text('descripcion')->nullable();
            $table->timestamps();

            $table->unique(['ciclo_formativo_id', 'codigo']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modulos');
    }
};
